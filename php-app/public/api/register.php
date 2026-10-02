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

$name     = trim(filter_var($data['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS) ?: '');
$email    = trim(filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL) ?: '');
$password = (string)($data['password'] ?? '');
$confirm  = (string)($data['password_confirm'] ?? '');

$v = new Validator($data);
$v->required('name')->between('name', 2, 120)
  ->required('email')->email('email')
  ->required('password')->min('password', 8)
  ->matches('password_confirm', 'password', trans('validation_passwords_match'));

if ($v->passes()) {
    $v->custom('email',
        fn($val) => !db_row('SELECT id FROM users WHERE email = :email', [':email' => $val]),
        trans('validation_email_registered')
    );
}

if ($v->fails()) {
    $code = isset($v->errors()['email']) && str_contains($v->errors()['email'], 'registered') ? 409 : 422;
    http_response_code($code);
    exit(json_encode(['status' => 'error', 'errors' => $v->errors()]));
}

$token   = generate_token();
$expires = date('Y-m-d H:i:sP', strtotime('+24 hours'));

$userId = db_insert(
    'INSERT INTO users (name, email, password, verify_token, token_expires_at)
     VALUES (:name, :email, :password, :token, :expires) RETURNING id',
    [
        ':name'     => $name,
        ':email'    => $email,
        ':password' => hash_password($password),
        ':token'    => $token,
        ':expires'  => $expires,
    ]
);

$tenant = current_tenant_id();

try {
    $jobId = push_tenant_job($tenant, 'user_register', (int)$userId, [
        'type'    => 'verify_email',
        'to'      => $email,
        'name'    => $name,
        'token'   => $token,
        'user_id' => (int)$userId,
    ]);
} catch (Throwable $e) {
    error_log('Failed to push email job: ' . $e->getMessage());
}

echo json_encode([
    'status'  => 'ok',
    'message' => trans('registration_success'),
    'job_id'  => $jobId ?? null,
    'tenant'  => $tenant,
]);
