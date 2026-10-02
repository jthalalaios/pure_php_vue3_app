<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$rawInput = @file_get_contents('php://input');
$json = json_decode($rawInput, true);
$data = is_array($json) ? $json : $_REQUEST;

$token = trim((string)($data['token'] ?? ''));

if ($token === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => trans('verify_invalid_link')]);
    exit;
}

$user = db_row(
    'SELECT id, is_verified, token_expires_at FROM users WHERE verify_token = :token',
    [':token' => $token]
);

if (!$user) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => trans('verify_invalid_or_used')]);
    exit;
}

if ($user['is_verified']) {
    echo json_encode(['status' => 'ok', 'verified' => true, 'message' => trans('verify_already_verified')]);
    exit;
}

if (strtotime((string)$user['token_expires_at']) < time()) {
    http_response_code(410);
    echo json_encode(['status' => 'error', 'message' => trans('verify_expired')]);
    exit;
}

db_execute(
    'UPDATE users SET is_verified = TRUE, verify_token = NULL,
     token_expires_at = NULL, updated_at = NOW() WHERE id = :id',
    [':id' => $user['id']]
);

echo json_encode(['status' => 'ok', 'verified' => true, 'message' => trans('verify_success')]);
