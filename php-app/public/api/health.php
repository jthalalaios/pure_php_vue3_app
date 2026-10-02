<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../bootstrap.php';

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

switch ($action) {
    case 'ping':
        echo json_encode(['status' => 'ok', 'timestamp' => date('Y-m-d H:i:s'), 'session_id' => session_id()]);
        break;

    case 'db_test':
        try {
            $row = db_row('SELECT version() AS version');
            echo json_encode(['status' => 'ok', 'version' => $row['version']]);
        } catch (Throwable $e) {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'redis_test':
        try {
            $r = redis();
            $r->set('health_check', 'ok', ['ex' => 60]);
            echo json_encode(['status' => 'ok', 'ping' => $r->ping()]);
        } catch (Throwable $e) {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => trans('api_unknown_action')]);
}
