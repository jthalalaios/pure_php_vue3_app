<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';

session_destroy_all();

if (
    (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) ||
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {
    header('Content-Type: application/json');
    header('X-Content-Type-Options: nosniff');
    echo json_encode(['status' => 'ok', 'message' => 'Logged out successfully']);
    exit;
}

header('Location: /login.php');
exit;
