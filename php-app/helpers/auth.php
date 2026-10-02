<?php
declare(strict_types=1);

/**
 * Auth helper — password hashing, token generation, CSRF.
 */

function hash_password(string $plain): string
{
    return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password(string $plain, string $hash): bool
{
    return password_verify($plain, $hash);
}

function generate_token(int $bytes = 32): string
{
    return bin2hex(random_bytes($bytes));
}

// ---------------------------------------------------------------------------
// CSRF protection
// ---------------------------------------------------------------------------

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = generate_token();
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return "<input type=\"hidden\" name=\"_csrf\" value=\"{$token}\">";
}

function csrf_verify(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? '';
    if (empty($token)) {
        $raw = @file_get_contents('php://input');
        if (!empty($raw)) {
            $parsed = json_decode($raw, true);
            if (is_array($parsed) && !empty($parsed['_csrf'])) {
                $token = $parsed['_csrf'];
            }
        }
    }

    if (!empty($token) && hash_equals(csrf_token(), (string)$token)) {
        return;
    }

    // Modern SPA requests using XMLHttpRequest / JSON with SameSite session cookies
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return;
    }

    http_response_code(403);
    if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid or missing CSRF token.']);
    } else {
        echo 'Invalid CSRF token.';
    }
    exit;
}
