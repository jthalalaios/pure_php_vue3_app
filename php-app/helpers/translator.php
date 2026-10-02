<?php
declare(strict_types=1);

/**
 * Simple translation helper.
 * - Loads translations from `php-app/lang/{locale}.php` which return an array of key=>string.
 * - Locale is stored in session under `locale` and defaults to `APP_LOCALE` env or `en`.
 */

function load_translations(string $locale): array
{
    static $cache = [];
    if (isset($cache[$locale])) {
        return $cache[$locale];
    }
    $file = __DIR__ . '/../lang/' . $locale . '.php';
    if (!is_file($file)) {
        $cache[$locale] = [];
        return $cache[$locale];
    }
    $cache[$locale] = include $file;
    return $cache[$locale];
}

function set_locale(string $locale): void
{
    $_SESSION['locale'] = $locale;
}

function get_locale(): string
{
    if (!empty($_SESSION['locale'])) {
        return (string) $_SESSION['locale'];
    }
    $env = getenv('APP_LOCALE');
    return $env ?: 'en';
}

function trans(string $key, array $params = []): string
{
    $locale = get_locale();
    $messages = load_translations($locale);
    if (isset($messages[$key])) {
        $value = $messages[$key];
    } else {
        // support dot.notation keys by falling back to underscore_notation
        $alt = str_replace('.', '_', $key);
        if ($alt != $key && isset($messages[$alt])) {
            $value = $messages[$alt];
        } else {
            $value = $key;
        }
    }
    foreach ($params as $k => $v) {
        $value = str_replace(['{' . $k . '}', ':' . $k], (string) $v, $value);
    }
    return $value;
}
