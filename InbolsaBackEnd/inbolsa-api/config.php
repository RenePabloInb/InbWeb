<?php
// config.php - VERSIÓN CORREGIDA
// Reads environment and returns configuration array.
// You can set APP_ENV via Apache (SetEnv) or system env.
// Values: dev | prod
$env = getenv('APP_ENV');
if (!$env) {
  $host = $_SERVER['HTTP_HOST'] ?? '';
  if ($host && stripos($host, 'inbolsa') !== false) {
    $env = 'prod';
  } else {
    $env = 'dev';
  }
}

$whatsappDefaults = [
  'api_version'      => getenv('WHATSAPP_API_VERSION') ?: 'v17.0',
  'phone_number_id'  => getenv('WHATSAPP_PHONE_NUMBER_ID') ?: '',
  'access_token'     => getenv('WHATSAPP_ACCESS_TOKEN') ?: '',
  'verify_token'     => getenv('WHATSAPP_VERIFY_TOKEN') ?: '',
  'dev_bearer_token' => getenv('WHATSAPP_DEV_TOKEN') ?: 'example_token',
];

if ($env === 'dev' && $whatsappDefaults['verify_token'] === '') {
  // Token de verificación de respaldo para entorno local
  $whatsappDefaults['verify_token'] = 'dev-verify-token';
}

if ($env === 'dev') {
  if ($whatsappDefaults['phone_number_id'] === '') {
    $whatsappDefaults['phone_number_id'] = '830985646767491';
  }
  if ($whatsappDefaults['access_token'] === '') {
    $whatsappDefaults['access_token'] = 'EAAT8M6fz1HkBPxQ8rGNqr2gE3PL7tZAyvGWZAdj61eT5Oph5FrzB1UyzgNWzRfs7OjYXeCIr0DAKnB3ZBbq848ZBPRExpnZAiQ7ZCMXs3SGFAitdggWqq6f0o9XAN3ep5Wm9LUKC8tYvFrQ2luZAZCZCpd4DDWtd1QUtMfTZCzYYH6ZAyvJC7hDi33ZBYE4WqM0Yab8usU5P45TulX8OgQZC8YBAQL0WP8rNtUQkv4OFEVRohRhQZD';
  }
  if ($whatsappDefaults['verify_token'] === 'dev-verify-token') {
    $whatsappDefaults['verify_token'] = 'inbolsa-dev-verify';
  }
}

// Permitir token de ejemplo sólo en desarrollo
$whatsappDefaults['allow_dev_token'] = $env === 'dev';

if ($env === 'dev') {
  // CONFIGURACIÓN LOCAL XAMPP
  return [
    'env' => $env,
    'db' => [
      'host'    => '127.0.0.1',
      'port'    => 3306,
      'name'    => 'inbolsa_dev',
      'user'    => 'root',
      'pass'    => '', // XAMPP default root has empty password
      'charset' => 'utf8mb4'
    ],
    // Para desarrollo permitimos múltiples orígenes (se normaliza en middleware)
    'cors_origin'          => '*',
    'token_secret'         => 'dev_change_me_to_a_long_random_secret',
    'access_ttl_seconds'   => 600,
    'session_name'         => 'inb_admin',
    'session_lifetime'     => 86400,
    'session_samesite'     => 'Lax',
    'session_secure'       => false, // false for local development
    'session_http_only'    => true,
    'session_storage'      => __DIR__ . '/storage/sessions',
    'whatsapp'             => $whatsappDefaults
  ];
}

// CONFIGURACIÓN PRODUCCIÓN iPAGE
return [
  'env' => $env,
  'db' => [
    'host'    => 'inbolsanet.ipagemysql.com',
    'port'    => 3306,
    'name'    => 'inbolsa_dev',
    'user'    => 'deploy',
    'pass'    => 'Inbo+2025.',
    'charset' => 'utf8mb4'
  ],
  'cors_origin'          => ['https://inbolsa.net','https://inbolsa.com'],
  'token_secret'         => 'prod_una_clave_secreta_larga_y_aleatoria_para_produccion',
  'access_ttl_seconds'   => 600,
  'session_name'         => 'inb_admin',
  'session_lifetime'     => 86400,
  'session_samesite'     => 'Lax', // Lax para same-site en producción
  'session_secure'       => true, // true para HTTPS
  'session_http_only'    => true,
  'session_storage'      => __DIR__ . '/storage/sessions',
  'whatsapp'             => $whatsappDefaults
];
