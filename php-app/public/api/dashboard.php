<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (!auth_check()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user = db_row('SELECT id, name, email, created_at, is_verified FROM users WHERE id = :id', [
    ':id' => auth_id(),
]);

if (!$user) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$_SESSION['visits'] = (int)($_SESSION['visits'] ?? 0) + 1;

echo json_encode([
    'status'     => 'ok',
    'tenant'     => tenant_config(),
    'session_id' => session_id(),
    'user'       => [
        'id'          => (int)$user['id'],
        'name'        => $user['name'],
        'email'       => $user['email'],
        'created_at'  => $user['created_at'],
        'is_verified' => (bool)$user['is_verified'],
    ],
    'visits'     => $_SESSION['visits'],
]);
