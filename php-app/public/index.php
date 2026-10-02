<?php
declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/RedisClient.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Connection health checks
$dbStatus    = null;
$dbError     = null;
$redisStatus = null;
$redisError  = null;

try {
    $pdo         = Database::getInstance();
    $dbStatus    = $pdo->query('SELECT version()')->fetchColumn();
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

try {
    $redis       = RedisClient::getInstance();
    $redis->set('health_check', 'ok', ['ex' => 60]);
    $redisStatus = $redis->ping();
} catch (Throwable $e) {
    $redisError = $e->getMessage();
}

$appEnv = getenv('APP_ENV') ?: 'development';

echo json_encode([
    'app'           => 'Pure PHP Vue 3 App API',
    'environment'   => $appEnv,
    'status'        => 'running',
    'tenant'        => tenant_config(),
    'authenticated' => auth_check(),
    'session_id'    => session_id(),
    'visits'        => $_SESSION['visits'] ?? 0,
    'database'      => [
        'connected' => $dbError === null,
        'version'   => $dbStatus,
        'error'     => $dbError,
    ],
    'redis'         => [
        'connected' => $redisError === null,
        'ping'      => $redisStatus,
        'error'     => $redisError,
    ],
]);
exit;
