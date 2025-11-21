<?php
function cors_headers() {
  $cfg = require __DIR__ . '/config.php';
  $allowed = $cfg['cors_origin'] ?? '*';
  $originHeader = $_SERVER['HTTP_ORIGIN'] ?? '';

  // Determinar qué origin enviar
  if (is_array($allowed)) {
    // Si el origin del request está en la lista, usarlo; sino usar el primero
    if ($originHeader && in_array($originHeader, $allowed, true)) {
      $originToSend = $originHeader;
    } else {
      $originToSend = $allowed[0] ?? '*';
    }
  } elseif ($allowed === '*' || $allowed === 'auto') {
    // En modo auto, reflejar el origin del request
    $originToSend = $originHeader ?: '*';
  } else {
    // Valor único string
    $originToSend = $allowed;
  }

  header('Access-Control-Allow-Origin: ' . $originToSend);
  header('Vary: Origin');

  if ($originToSend !== '*') {
    header('Access-Control-Allow-Credentials: true');
  }

  header('Access-Control-Allow-Headers: Content-Type, Authorization');
  header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
  header('Content-Type: application/json; charset=utf-8');
}

function preflight_if_options() {
  if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
  }
}

function start_session_if_needed() {
  if (session_status() === PHP_SESSION_NONE) {
    $cfg = require __DIR__ . '/config.php';

    if (!empty($cfg['session_storage'])) {
      $savePath = $cfg['session_storage'];
      if (!is_dir($savePath)) {
        @mkdir($savePath, 0775, true);
      }
      if (is_dir($savePath) && is_writable($savePath)) {
        session_save_path($savePath);
      } else {
        error_log('Session storage not writable: ' . $savePath);
      }
    }

    session_name($cfg['session_name']);

    // IMPORTANTE: El path debe ser / para que las cookies funcionen en PRODUCCIÓN
    // En producción iPage, el path / no funciona correctamente
    $cookiePath = '/';

    if (PHP_VERSION_ID >= 70300) {
      session_set_cookie_params([
        'lifetime' => $cfg['session_lifetime'],
        'path'     => $cookiePath,
        'domain'   => '',
        'secure'   => $cfg['session_secure'],
        'httponly' => $cfg['session_http_only'],
        'samesite' => $cfg['session_samesite']
      ]);
    } else {
      // Compatibilidad con PHP < 7.3
      session_set_cookie_params(
        (int)$cfg['session_lifetime'],
        $cookiePath,
        '',
        !empty($cfg['session_secure']),
        !empty($cfg['session_http_only'])
      );
    }

    try {
      session_start();
    } catch (Throwable $e) {
      error_log('Session start error: ' . $e->getMessage());
      throw $e;
    }
  }
}

function json_response($data, int $code = 200) {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

function b64url($data) {
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function hmac_sign(array $payload) {
  $cfg = require __DIR__ . '/config.php';
  $secret = $cfg['token_secret'];
  $header = ['alg'=>'HS256','typ'=>'JWT'];
  $enc = b64url(json_encode($header)).'.'.b64url(json_encode($payload));
  $sig = b64url(hash_hmac('sha256', $enc, $secret, true));
  return $enc.'.'.$sig;
}

function hmac_verify($jwt) {
  $cfg = require __DIR__ . '/config.php';
  $secret = $cfg['token_secret'];
  $parts = explode('.', $jwt);
  if (count($parts) !== 3) return false;
  [$h, $p, $s] = $parts;
  $calc = b64url(hash_hmac('sha256', "$h.$p", $secret, true));
  if (!hash_equals($calc, $s)) return false;
  $payload = json_decode(base64_decode(strtr($p, '-_', '+/')), true);
  if (!is_array($payload)) return false;
  if (isset($payload['exp']) && time() > (int)$payload['exp']) return false;
  return $payload;
}
