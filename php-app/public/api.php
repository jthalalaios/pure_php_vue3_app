<?php
declare(strict_types=1);

/**
 * Minimal AJAX / JSON endpoint.
 * Add more actions as the app grows.
 */
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/RedisClient.php';

// session is started by session_boot() in bootstrap

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

switch ($action) {

    case 'ping':
        echo json_encode([
            'status'     => 'ok',
            'timestamp'  => date('Y-m-d H:i:s'),
            'session_id' => session_id(),
            'visits'     => $_SESSION['visits'] ?? 0,
        ]);
        break;

    case 'db_test':
        try {
            $pdo  = Database::getInstance();
            $row  = $pdo->query('SELECT version() AS version')->fetch();
            echo json_encode(['status' => 'ok', 'version' => $row['version']]);
        } catch (Throwable $e) {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'redis_test':
        try {
            $redis = RedisClient::getInstance();
            $redis->set('health_check', 'ok', ['ex' => 60]);
            echo json_encode(['status' => 'ok', 'ping' => $redis->ping()]);
        } catch (Throwable $e) {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => trans('api_unknown_action')]);
}
