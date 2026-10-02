<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'message' => trans('api_method_not_allowed')]));
}

csrf_verify();

$rawInput = @file_get_contents('php://input');
$json = json_decode($rawInput, true);
$data = is_array($json) ? $json : $_POST;

$email    = trim(filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL) ?: '');
$password = (string)($data['password'] ?? '');

$v = new Validator($data);
$v->required('email')->email('email')
  ->required('password');

if ($v->fails()) {
    http_response_code(422);
    exit(json_encode(['status' => 'error', 'errors' => $v->errors()]));
}

// Rate-limit: 10 login attempts per minute per IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (!rate_limit("rl:login:{$ip}", 10, 60)) {
    http_response_code(429);
    exit(json_encode(['status' => 'error', 'message' => trans('api_too_many_attempts')]));
}

$user = db_row('SELECT * FROM users WHERE email = :email', [':email' => $email]);

if (!$user || !verify_password($password, $user['password'])) {
    http_response_code(401);
    exit(json_encode(['status' => 'error', 'message' => trans('auth_invalid_credentials')]));
}

if (!$user['is_verified']) {
    http_response_code(403);
    exit(json_encode(['status' => 'error', 'message' => trans('auth_verify_first')]));
}

session_regenerate_id(true);
session_set('user_id',   $user['id']);
session_set('user_name', $user['name']);

echo json_encode([
    'status'   => 'ok',
    'redirect' => '/dashboard',
    'user'     => [
        'id'          => (int)$user['id'],
        'name'        => $user['name'],
        'email'       => $user['email'],
        'is_verified' => (bool)$user['is_verified'],
        'created_at'  => $user['created_at'] ?? null,
    ]
]);
