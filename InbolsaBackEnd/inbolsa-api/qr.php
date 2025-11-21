<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';

// Función para obtener la URL base correcta
function getQRBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'inbolsa.net';

    // Base del backend en producción (subdirectorio /inbolsa-api en raíz)
    return $protocol . '://' . $host . '/inbolsa-api';
}

// Función para obtener la URL del frontend
function getFrontendUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'inbolsa.net';

    // Frontend vive en la raíz del dominio
    return $protocol . '://' . $host;
}

function ip_addr() {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
}

function user_agent() { 
    return $_SERVER['HTTP_USER_AGENT'] ?? ''; 
}

function random_code($len = 16) {
    return rtrim(strtr(base64_encode(random_bytes($len)), '+/', '-_'), '=');
}

function qr_issue_token(string $code): string {
    $cfg = require __DIR__ . '/config.php';
    $ttl = (int)($cfg['access_ttl_seconds'] ?? 600);
    $payload = ['code'=>$code, 'exp'=> time() + $ttl, 'iat'=> time()];
    return hmac_sign($payload);
}

function require_admin() {
    start_session_if_needed();
    if (empty($_SESSION['admin_id'])) {
        json_response(['error'=>'No autorizado'], 401); 
        exit;
    }
}

// Log simple
function log_event($pdo, $qr_row_or_id, string $code, string $event) {
    error_log("QR Event: $event, Code: $code");
    return;
}

/** CREATE QR - MEJORADO CON URL CORRECTA */
function route_qr_create() {
    cors_headers();
    preflight_if_options();
    require_admin();

    $pdo = db_conn();
    if ($pdo === null) {
        json_response(['error' => 'Base de datos no disponible'], 503);
        return;
    }
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!is_array($input)) {
        json_response(['error' => 'Datos de entrada inválidos'], 400);
        return;
    }
    
    $type = (string)($input['type'] ?? 'landing-access');
    $payload = $input['payload'] ?? [];
    
    if (!is_array($payload)) { 
        $payload = []; 
    }
    
    // Normalizar payload
    if (!isset($payload['section'])) $payload['section'] = 'productos';
    if (!isset($payload['allow']))   $payload['allow']   = 'all';
    
    if ($payload['allow'] === 'include' && empty($payload['products'])) {
        json_response(['error'=>'products vacío'], 400); 
        return;
    }

    $expiresRaw = $input['expires_at'] ?? null;
    $expiresSql = $expiresRaw ? date('Y-m-d H:i:s', strtotime($expiresRaw)) : null;
    $usageLimitIn = $input['usage_limit'] ?? null;
    $usageLimit = (is_null($usageLimitIn) || $usageLimitIn === '') ? null : (int)$usageLimitIn;

    try {
        $code = random_code(16);
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        
        if ($payloadJson === false) {
            json_response(['error' => 'Error al codificar payload'], 500);
            return;
        }
        
        $stmt = $pdo->prepare(
            'INSERT INTO qr_codes (code, type, payload, status, usage_count, usage_limit, expires_at, created_by)
             VALUES (?, ?, ?, "active", 0, ?, ?, ?)'
        );
        $stmt->execute([$code, $type, $payloadJson, $usageLimit, $expiresSql, $_SESSION['admin_id']]);

        $qr_id = (int)$pdo->lastInsertId();
        log_event($pdo, $qr_id, $code, 'create');
        
        // IMPORTANTE: Generar la URL correcta para el QR
        $qrUrl = getQRBaseUrl() . '/qr/open?code=' . $code;
        
        error_log("QR Created - Code: $code, URL: $qrUrl");

        json_response([
            'ok' => true, 
            'code' => $code,
            'url' => $qrUrl  // Incluir la URL completa
        ]);
    } catch (Exception $e) {
        error_log("QR Create Error: " . $e->getMessage());
        json_response(['error' => 'Error al crear QR'], 500);
    }
}

/** LIST QRs - MEJORADO CON URLs */
function route_qr_list() {
    cors_headers();
    preflight_if_options();
    require_admin();

    $pdo = db_conn();
    if ($pdo === null) {
        json_response(['items' => []]);
        return;
    }
    $stmt = $pdo->query('SELECT id, code, type, payload, status, usage_count, usage_limit, expires_at, created_at
                         FROM qr_codes ORDER BY id DESC LIMIT 200');
    $rows = $stmt->fetchAll();
    
    // Agregar URL a cada item
    $baseUrl = getQRBaseUrl();
    foreach ($rows as &$row) {
        $row['url'] = $baseUrl . '/qr/open?code=' . $row['code'];
    }
    
    json_response(['items' => $rows]);
}

/** REVOKE QR */
function route_qr_revoke() {
    cors_headers();
    preflight_if_options();
    require_admin();

    $pdo = db_conn();
    if ($pdo === null) {
        json_response(['error'=>'Base de datos no disponible'], 503);
        return;
    }
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $code = (string)($input['code'] ?? '');
    
    if (!$code) { 
        json_response(['error'=>'code requerido'], 400); 
        return; 
    }

    try {
        // Buscar el QR
        $sel = $pdo->prepare('SELECT id, status FROM qr_codes WHERE code = ? LIMIT 1');
        $sel->execute([$code]); 
        $row = $sel->fetch();
        
        if (!$row) {
            json_response(['error'=>'QR no existe'], 404); 
            return;
        }
        
        if ($row['status'] === 'revoked') {
            json_response(['ok'=>true, 'message'=>'QR ya estaba revocado']); 
            return;
        }
        
        // Actualizar status a revoked
        $stmt = $pdo->prepare('UPDATE qr_codes SET status = "revoked" WHERE code = ?');
        $stmt->execute([$code]);
        
        log_event($pdo, (int)$row['id'], $code, 'revoke');
        
        json_response([
            'ok' => true, 
            'revoked' => true, 
            'code' => $code,
            'message' => 'QR revocado exitosamente'
        ]);
        
    } catch (Exception $e) {
        error_log("Error en revoke: " . $e->getMessage());
        json_response(['error' => 'Error al revocar QR'], 500);
    }
}

/** VALIDATE QR */
function route_qr_validate() {
    cors_headers();
    preflight_if_options();

    $pdo = db_conn();
    if ($pdo === null) {
        json_response(['valid'=>false, 'reason'=>'servicio no disponible'], 503);
        return;
    }
    $code = (string)($_GET['code'] ?? '');
    
    if (!$code) { 
        json_response(['valid'=>false, 'reason'=>'code vacío'], 400); 
        return; 
    }

    $stmt = $pdo->prepare('SELECT * FROM qr_codes WHERE code = ? LIMIT 1');
    $stmt->execute([$code]);
    $row = $stmt->fetch();
    
    if (!$row) { 
        json_response(['valid'=>false, 'reason'=>'no existe'], 200); 
        return; 
    }
    
    if ($row['status'] !== 'active') { 
        json_response(['valid'=>false, 'reason'=>'revoked'], 200); 
        return; 
    }
    
    if ($row['expires_at'] && strtotime($row['expires_at']) < time()) { 
        json_response(['valid'=>false, 'reason'=>'expired'], 200); 
        return; 
    }
    
    if (!is_null($row['usage_limit']) && (int)$row['usage_limit'] > 0 && (int)$row['usage_count'] >= (int)$row['usage_limit']) {
        json_response(['valid'=>false, 'reason'=>'usage_limit'], 200); 
        return;
    }

    log_event($pdo, $row, $code, 'validate');
    
    $token = qr_issue_token($code);
    json_response(['valid'=>true, 'token'=>$token]);
}

/** OPEN QR - FUNCIÓN CRÍTICA QUE MANEJA CUANDO SE ESCANEA EL QR */
function route_qr_open() {
    try {
        $code = (string)($_GET['code'] ?? '');
        if ($code === '') {
            http_response_code(400);
            echo '<!DOCTYPE html><html><body><h1>Error: Código QR faltante</h1></body></html>';
            return;
        }

        $pdo = db_conn();
        if ($pdo === null) {
            http_response_code(503);
            echo '<!DOCTYPE html><html><body><h1>Servicio temporalmente no disponible</h1></body></html>';
            return;
        }
        $stmt = $pdo->prepare('SELECT id, code, type, payload, status, expires_at, usage_limit, usage_count 
                              FROM qr_codes WHERE code = ? LIMIT 1');
        $stmt->execute([$code]);
        $qr = $stmt->fetch();
        
        if (!$qr) { 
            http_response_code(404); 
            echo '<!DOCTYPE html><html><body><h1>Error: QR no encontrado</h1></body></html>'; 
            return; 
        }

        // Validaciones
        if ($qr['status'] !== 'active') { 
            http_response_code(410); 
            echo '<!DOCTYPE html><html><body><h1>Error: Este QR ha sido revocado</h1></body></html>'; 
            return; 
        }
        
        if (!empty($qr['expires_at']) && strtotime($qr['expires_at']) < time()) { 
            http_response_code(410); 
            echo '<!DOCTYPE html><html><body><h1>Error: Este QR ha expirado</h1></body></html>'; 
            return; 
        }
        
        if (!is_null($qr['usage_limit']) && (int)$qr['usage_limit'] > 0 && (int)$qr['usage_count'] >= (int)$qr['usage_limit']) {
            http_response_code(429); 
            echo '<!DOCTYPE html><html><body><h1>Error: Límite de usos alcanzado</h1></body></html>'; 
            return;
        }

        // Incrementar contador de uso
        $pdo->prepare('UPDATE qr_codes SET usage_count = usage_count + 1 WHERE id = ?')
            ->execute([(int)$qr['id']]);

        // Generar token
        $ttl = 60 * 60 * 24 * 7; // 7 días
        $exp = time() + $ttl;
        $jwt = hmac_sign(['code' => $qr['code'], 'exp' => $exp]);

        // IMPORTANTE: Borrar cookies viejas PRIMERO para evitar conflictos
        // Esto garantiza que cada QR tenga sus propios productos, sin importar el ambiente
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $cookieOptions = [
            'expires'  => time() - 3600, // Expirar hace 1 hora
            'path'     => '/',
            'secure'   => $isSecure,
            'httponly' => false,
            'samesite' => 'Lax',
        ];

        // Borrar todas las cookies relacionadas con acceso QR
        setcookie('inb_access', '', $cookieOptions);
        setcookie('qrauth', '', $cookieOptions);
        setcookie('priv_mode', '', $cookieOptions);
        setcookie('inbolsa:qr:ok', '', $cookieOptions);

        // Ahora establecer las cookies NUEVAS con el token correcto
        $newCookieOptions = [
            'expires'  => $exp,
            'path'     => '/',
            'secure'   => $isSecure,
            'samesite' => 'Lax',
        ];

        setcookie('inb_access', $jwt, array_merge($newCookieOptions, ['httponly' => true]));
        setcookie('qrauth', $jwt, array_merge($newCookieOptions, ['httponly' => false]));
        setcookie('priv_mode', '1', array_merge($newCookieOptions, ['httponly' => false]));

        // Preparar URL de redirección al frontend
        $frontendUrl = getFrontendUrl();
        $productParams = '';
        
        if (!empty($qr['payload'])) {
            $payload = json_decode($qr['payload'], true);
            if (isset($payload['section']) && $payload['section'] === 'productos') {
                if ($payload['allow'] === 'include' && !empty($payload['products'])) {
                    $productParams = '&p=' . implode(',', $payload['products']);
                }
            }
        }
        
        // URL de destino
        $target = $frontendUrl . '/privado?accessToken=' . urlencode($jwt) . $productParams . '&from=qr';
        
        error_log("QR Open - Redirecting to: " . $target);
        
        // Redirección con HTML como fallback
        header('Location: ' . $target, true, 302);
        echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target) . '"></head><body>Redirigiendo...</body></html>';
        exit;

    } catch (Throwable $e) {
        error_log("QR Open Error: " . $e->getMessage());
        http_response_code(500);
        echo '<!DOCTYPE html><html><body><h1>Error del servidor</h1><p>' . htmlspecialchars($e->getMessage()) . '</p></body></html>';
    }
}

/** ACCESS PAYLOAD - Verificación de acceso */
function route_access_payload() {
    cors_headers();
    preflight_if_options();

    // Obtener token de diferentes fuentes
    $jwt = '';

    // 1. Query string
    if (isset($_GET['accessToken']) && $_GET['accessToken'] !== '') {
        $jwt = (string)$_GET['accessToken'];
    } elseif (isset($_GET['token']) && $_GET['token'] !== '') {
        $jwt = (string)$_GET['token'];
    }

    // 2. Header Authorization
    if ($jwt === '') {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if ($auth && stripos($auth, 'Bearer ') === 0) {
            $jwt = trim(substr($auth, 7));
        }
    }

    // 3. Cookies
    if ($jwt === '') {
        $jwt = (string)($_COOKIE['qrauth'] ?? ($_COOKIE['inb_access'] ?? ''));
    }

    // Si no hay token
    if ($jwt === '') {
        error_log("Access Payload - No token found");
        json_response(['ok'=>false, 'error'=>'no_token'], 401);
        return;
    }

    // Verificar token
    $payload = hmac_verify($jwt);

    if (!$payload || empty($payload['code'])) {
        error_log("Access Payload - Invalid token");
        json_response(['ok'=>false, 'error'=>'invalid_token'], 401);
        return;
    }

    // Verificar si el token expiró
    if (($payload['exp'] ?? 0) < time()) {
        error_log("Access Payload - Token expired");
        json_response(['ok'=>false, 'error'=>'token_expired'], 401);
        return;
    }

    try {
        $pdo = db_conn();
        if ($pdo === null) {
            // Sin BD, solo validar el token JWT
            json_response([
                'ok' => true,
                'payload' => [
                    'code' => $payload['code'],
                    'exp' => $payload['exp']
                ],
                'qr' => [
                    'code' => $payload['code'],
                    'type' => 'landing-access',
                    'payload' => ['section' => 'productos', 'allow' => 'all'],
                    'status' => 'active'
                ]
            ]);
            return;
        }
        $stmt = $pdo->prepare('SELECT code, type, payload, status, expires_at, usage_count, usage_limit 
                              FROM qr_codes WHERE code = ? LIMIT 1');
        $stmt->execute([$payload['code']]);
        $row = $stmt->fetch();
        
        if (!$row) { 
            error_log("Access Payload - QR not found: " . $payload['code']);
            json_response(['ok'=>false, 'error'=>'qr_not_found'], 404); 
            return; 
        }
        
        // VERIFICACIÓN CRÍTICA: Si el QR está revocado
        if ($row['status'] === 'revoked') {
            error_log("Access Payload - QR is REVOKED: " . $payload['code']);
            json_response([
                'ok' => false, 
                'error' => 'qr_revoked', 
                'revoked' => true,
                'message' => 'Este QR ha sido revocado'
            ], 401); 
            return;
        }
        
        // Verificar si el QR expiró
        if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
            error_log("Access Payload - QR expired: " . $payload['code']);
            json_response([
                'ok' => false, 
                'error' => 'qr_expired', 
                'expired' => true,
                'message' => 'Este QR ha expirado'
            ], 401);
            return;
        }
        
        // Verificar límite de usos
        if (!is_null($row['usage_limit']) && (int)$row['usage_limit'] > 0) {
            if ((int)$row['usage_count'] >= (int)$row['usage_limit']) {
                error_log("Access Payload - Usage limit reached: " . $payload['code']);
                json_response([
                    'ok' => false, 
                    'error' => 'usage_limit_exceeded', 
                    'message' => 'Límite de usos alcanzado'
                ], 401);
                return;
            }
        }

        // Decodificar payload del QR
        $qrPayload = $row['payload'];
        if (is_string($qrPayload)) { 
            $qrPayload = json_decode($qrPayload, true); 
            if ($qrPayload === null) {
                error_log("Access Payload - Failed to decode QR payload");
                $qrPayload = [];
            }
        }

        // Todo OK - devolver acceso válido
        json_response([
            'ok' => true,
            'payload' => [
                'code' => $payload['code'], 
                'exp' => $payload['exp']
            ],
            'qr' => [
                'code' => $row['code'],
                'type' => $row['type'],
                'payload' => $qrPayload,
                'status' => $row['status'],
                'expires_at' => $row['expires_at'],
                'usage_count' => (int)$row['usage_count'],
                'usage_limit' => $row['usage_limit'] ? (int)$row['usage_limit'] : null
            ],
        ]);
        
    } catch (Throwable $e) {
        error_log("Access Payload Exception: " . $e->getMessage());
        json_response(['ok'=>false, 'error'=>'server_error'], 500);
    }
}

/** Stats (opcional) */
function route_qr_stats() {
    cors_headers();
    preflight_if_options();
    require_admin();

    $pdo = db_conn();
    if ($pdo === null) {
        json_response(['error'=>'Base de datos no disponible'], 503);
        return;
    }
    $code = (string)($_GET['code'] ?? '');
    
    if (!$code) { 
        json_response(['error'=>'code requerido'], 400); 
        return; 
    }

    $qri = $pdo->prepare('SELECT * FROM qr_codes WHERE code = ? LIMIT 1');
    $qri->execute([$code]); 
    $row = $qri->fetch();
    
    if (!$row) { 
        json_response(['error'=>'QR no existe'], 404); 
        return; 
    }

    json_response([
        'item' => $row,
        'events' => []
    ]);
}
