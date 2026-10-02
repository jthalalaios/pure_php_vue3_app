<?php
declare(strict_types=1);

/**
 * bootstrap.php — loaded at the top of every page.
 * Autoloads helpers and boots the session.
 */

$app_env = getenv('APP_ENV') ?: 'development';

if ($app_env == 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

require_once __DIR__ . '/helpers/db.php';
require_once __DIR__ . '/helpers/session.php';
require_once __DIR__ . '/helpers/redis.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/validator.php';
require_once __DIR__ . '/helpers/translator.php';
require_once __DIR__ . '/helpers/tenant.php';

// ---------------------------------------------------------------------------
// CORS handling (supporting SPA on localhost:5173 / custom domains)
// ---------------------------------------------------------------------------
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!empty($origin)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization,X-Tenant-ID,X-CSRF-Token,X-Timezone,Accept');
    header('Access-Control-Max-Age: 1728000');
}

if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS') {
    http_response_code(204);
    exit;
}

session_boot();

if (isset($_GET['lang'])) {
    $lang = preg_replace('/[^a-zA-Z_-]/', '', (string) $_GET['lang']);
    if ($lang != '') set_locale($lang);
}
