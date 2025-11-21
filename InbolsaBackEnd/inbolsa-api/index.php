<?php
// NO usar declare strict types - puede causar problemas en algunos hosts
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Manejo de errores fatales ANTES de cualquier otra cosa
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        echo json_encode([
            'error' => 'fatal_error',
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        exit;
    }
});

// Handler de excepciones
set_exception_handler(function($e) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
    }
    echo json_encode([
        'error' => 'exception',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
});

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/qr.php';

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

    // Normalizar la ruta quitando el prefijo base
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $baseDir = dirname($scriptName);

    if ($baseDir !== '/' && strpos($uri, $baseDir) === 0) {
        $uri = substr($uri, strlen($baseDir));
    }

    if ($uri === '' || $uri[0] !== '/') {
        $uri = '/' . $uri;
    }

    // ====== RUTAS ESPECIALES QR (sin /api) ======
    if ($uri === '/qr/open' && $method === 'GET') {
        route_qr_open();
        exit;
    }

    // ====== HEALTH CHECK ======
    if (($uri === '/health' || $uri === '/api/health') && $method === 'GET') {
        cors_headers();
        echo json_encode(['ok' => true, 'ts' => time()]);
        exit;
    }

    // ====== RUTAS API ======
    $path = $uri;
    if (strpos($path, '/api/') === 0) {
        $path = substr($path, 4);
    }

    // AUTH routes
    if ($path === '/auth/login' && $method === 'POST') {
        route_auth_login();
        exit;
    }

    if ($path === '/auth/logout' && $method === 'POST') {
        route_auth_logout();
        exit;
    }

    if ($path === '/auth/me' && $method === 'GET') {
        route_auth_me();
        exit;
    }

    // QR API routes
    if ($path === '/qr/create' && $method === 'POST') {
        route_qr_create();
        exit;
    }

    if ($path === '/qr/list' && $method === 'GET') {
        route_qr_list();
        exit;
    }

    if ($path === '/qr/revoke' && $method === 'POST') {
        route_qr_revoke();
        exit;
    }

    if ($path === '/qr/validate' && $method === 'GET') {
        route_qr_validate();
        exit;
    }

    if ($path === '/access/payload' && $method === 'GET') {
        route_access_payload();
        exit;
    }

    if ($path === '/qr/stats' && $method === 'GET') {
        route_qr_stats();
        exit;
    }

    // ====== 404 ======
    cors_headers();
    preflight_if_options();
    http_response_code(404);
    echo json_encode([
        'error' => 'Not found',
        'path' => $uri,
        'method' => $method
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode([
        'error' => 'server_error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
}
