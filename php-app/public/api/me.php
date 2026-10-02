<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (!auth_check()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'authenticated' => false, 'message' => 'Unauthenticated']);
    exit;
}

$user = db_row('SELECT id, name, email, is_verified, created_at, updated_at FROM users WHERE id = :id', [
    ':id' => auth_id(),
]);

if (!$user) {
    session_destroy_all();
    http_response_code(401);
    echo json_encode(['status' => 'error', 'authenticated' => false, 'message' => 'User not found']);
    exit;
}

echo json_encode([
    'status'        => 'ok',
    'authenticated' => true,
    'session_id'    => session_id(),
    'user'          => [
        'id'          => (int)$user['id'],
        'name'        => $user['name'],
        'email'       => $user['email'],
        'is_verified' => (bool)$user['is_verified'],
        'created_at'  => $user['created_at'],
        'updated_at'  => $user['updated_at'] ?? null,
    ],
]);
