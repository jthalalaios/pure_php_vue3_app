<?php
declare(strict_types=1);

/**
 * Session helper — Redis-backed sessions.
 * Call session_boot() once at the top of every page (via bootstrap.php).
 */

function session_boot(): void
{
    if (session_status() == PHP_SESSION_ACTIVE) return;

    // session.save_handler and save_path are already set via
    // /usr/local/etc/php/conf.d/session-redis.ini written by entrypoint.sh.
    session_name('PHPSESSID');

    session_set_cookie_params([
        'lifetime' => 7200,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Rotate session id on first boot to prevent fixation
    if (empty($_SESSION['_initiated'])) {
        session_regenerate_id(true);
        $_SESSION['_initiated'] = true;
    }
}

function session_set(string $key, mixed $value): void
{
    $_SESSION[$key] = $value;
}

function session_get(string $key, mixed $default = null): mixed
{
    return $_SESSION[$key] ?? $default;
}

function session_forget(string $key): void
{
    unset($_SESSION[$key]);
}

function session_destroy_all(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function flash_set(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    $msg = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $msg;
}

function auth_check(): bool
{
    return !empty($_SESSION['user_id']);
}

function auth_required(): void
{
    if (!auth_check()) {
        header('Location: /login.php');
        exit;
    }
}

function auth_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}
