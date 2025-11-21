<?php
/**
 * Normaliza la URI para que el router vea rutas como /auth/login
 * aunque la petición llegue como /inbolsa-api/auth/login.
 * No toca tu lógica; solo ajusta REQUEST_URI/PATH_INFO.
 */
$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$prefix = '/inbolsa-api';

if (strncmp($uri, $prefix, strlen($prefix)) === 0) {
  $uri = substr($uri, strlen($prefix));
  if ($uri === '' || $uri[0] !== '/') {
    $uri = '/' . $uri;
  }
}

$_SERVER['REQUEST_URI'] = $uri;
$_SERVER['PATH_INFO']   = $uri;
