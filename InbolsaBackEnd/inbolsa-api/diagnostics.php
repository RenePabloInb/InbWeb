<?php
/**
 * Script de diagnóstico para deployment de Inbolsa
 * Este archivo ayuda a verificar la configuración del servidor
 */

header('Content-Type: application/json; charset=utf-8');

$diagnostics = [
    'timestamp' => date('c'),
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'checks' => []
];

// 1. Verificar extensiones PHP
$diagnostics['checks']['extensions'] = [
    'json' => extension_loaded('json'),
    'pdo' => extension_loaded('pdo'),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'session' => extension_loaded('session'),
    'mbstring' => extension_loaded('mbstring'),
];

// 2. Verificar archivos necesarios
$requiredFiles = ['config.php', 'db.php', 'auth.php', 'qr.php', 'middleware.php', 'index.php'];
$diagnostics['checks']['files'] = [];
foreach ($requiredFiles as $file) {
    $path = __DIR__ . '/' . $file;
    $diagnostics['checks']['files'][$file] = [
        'exists' => file_exists($path),
        'readable' => is_readable($path),
        'size' => file_exists($path) ? filesize($path) : 0
    ];
}

// 3. Verificar configuración
try {
    $cfg = require __DIR__ . '/config.php';
    $diagnostics['checks']['config'] = [
        'loaded' => true,
        'env' => $cfg['env'] ?? 'unknown',
        'db_host' => $cfg['db']['host'] ?? 'not set',
        'db_name' => $cfg['db']['name'] ?? 'not set',
        'cors_origin' => $cfg['cors_origin'] ?? 'not set'
    ];
} catch (Throwable $e) {
    $diagnostics['checks']['config'] = [
        'loaded' => false,
        'error' => $e->getMessage()
    ];
}

// 4. Verificar conexión a base de datos
try {
    require_once __DIR__ . '/db.php';
    $pdo = db_conn();
    $diagnostics['checks']['database'] = [
        'connected' => true,
        'driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
        'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
    ];

    // Verificar tablas
    $tables = ['admin_users', 'qr_codes', 'qr_events'];
    $diagnostics['checks']['database']['tables'] = [];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            $diagnostics['checks']['database']['tables'][$table] = [
                'exists' => true,
                'count' => (int)$count
            ];
        } catch (PDOException $e) {
            $diagnostics['checks']['database']['tables'][$table] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} catch (Throwable $e) {
    $diagnostics['checks']['database'] = [
        'connected' => false,
        'error' => $e->getMessage()
    ];
}

// 5. Verificar permisos de directorios
$diagnostics['checks']['permissions'] = [
    'current_dir_writable' => is_writable(__DIR__),
    'current_dir_readable' => is_readable(__DIR__),
];

// 6. Verificar variables de entorno
$diagnostics['checks']['environment'] = [
    'APP_ENV' => getenv('APP_ENV') ?: 'not set',
    'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'not set',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'not set',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'not set',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'not set',
];

// 7. Verificar mod_rewrite
$diagnostics['checks']['apache_modules'] = [
    'mod_rewrite' => function_exists('apache_get_modules')
        ? in_array('mod_rewrite', apache_get_modules())
        : 'unknown (function not available)',
    'mod_headers' => function_exists('apache_get_modules')
        ? in_array('mod_headers', apache_get_modules())
        : 'unknown (function not available)',
];

// 8. Resumen
$allChecks = true;
$criticalIssues = [];

// Extensiones críticas
foreach (['json', 'pdo', 'pdo_mysql'] as $ext) {
    if (!$diagnostics['checks']['extensions'][$ext]) {
        $allChecks = false;
        $criticalIssues[] = "Missing extension: $ext";
    }
}

// Archivos críticos
foreach ($requiredFiles as $file) {
    if (!$diagnostics['checks']['files'][$file]['exists']) {
        $allChecks = false;
        $criticalIssues[] = "Missing file: $file";
    }
}

// Base de datos
if (!$diagnostics['checks']['database']['connected']) {
    $allChecks = false;
    $criticalIssues[] = "Database connection failed";
}

$diagnostics['summary'] = [
    'status' => $allChecks ? 'OK' : 'ISSUES_FOUND',
    'critical_issues' => $criticalIssues,
    'total_checks' => count($diagnostics['checks']),
];

// Output
echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
