<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';

function route_auth_login() {
  cors_headers();
  preflight_if_options();
  start_session_if_needed();

  $input = json_decode(file_get_contents('php://input'), true) ?? [];
  $email = strtolower(trim((string)($input['email'] ?? '')));
  $password = (string)($input['password'] ?? '');

  if (!$email || !$password) {
    json_response(['error'=>'Email y password requeridos'], 400);
    return;
  }

  // OPCIÓN 1: Usar credenciales hardcoded
  if ($email === 'admin@inbolsa.com' && $password === '123456') {
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_email'] = 'admin@inbolsa.com';
    json_response(['ok'=>true, 'admin'=>['id'=>1, 'email'=>'admin@inbolsa.com']]);
    return;
  }

  // OPCIÓN 2: Consultar base de datos
  try {
    $pdo = db_conn();
    $stmt = $pdo->prepare('SELECT id, email, password_hash FROM admin_users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!$row) {
      json_response(['error'=>'Credenciales inválidas'], 401);
      return;
    }

    if (password_verify($password, $row['password_hash'])) {
      $_SESSION['admin_id'] = (int)$row['id'];
      $_SESSION['admin_email'] = $row['email'];
      json_response(['ok'=>true, 'admin'=>['id'=>(int)$row['id'],'email'=>$row['email']]]);
      return;
    } else {
      json_response(['error'=>'Credenciales inválidas'], 401);
      return;
    }
  } catch (Exception $e) {
    json_response(['error'=>'Credenciales inválidas'], 401);
    return;
  }
}

function route_auth_logout() {
  cors_headers();
  preflight_if_options();
  start_session_if_needed();
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]
    );
  }
  session_destroy();
  json_response(['ok'=>true]);
}

function route_auth_me() {
  cors_headers();
  preflight_if_options();
  start_session_if_needed();

  if (empty($_SESSION['admin_id'])) {
    json_response(['auth'=>false], 200);
    return;
  }

  json_response([
    'auth'=>true,
    'admin'=>[
      'id'    => (int)$_SESSION['admin_id'],
      'email' => $_SESSION['admin_email'] ?? null
    ]
  ]);
}
