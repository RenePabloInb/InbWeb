<?php
// NO usar declare strict types - causa 500 error en algunos hosts
try {
  header('Content-Type: application/json; charset=utf-8');
  $payload = [
    'ok'   => true,
    'time' => date('c'),
    'php'  => PHP_VERSION,
    'json_extension' => function_exists('json_encode'),
  ];

  if (function_exists('json_encode')) {
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  } else {
    // Fallback muy básico si el host no tiene la extensión json habilitada
    echo '{'
      . '"ok":true,'
      . '"time":"' . addslashes($payload['time']) . '",'
      . '"php":"' . addslashes($payload['php']) . '",'
      . '"json_extension":false'
      . '}';
  }
} catch (Throwable $e) {
  http_response_code(500);
  header('Content-Type: text/plain; charset=utf-8');
  echo 'health_error:' . $e->getMessage();
}
