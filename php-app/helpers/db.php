<?php
declare(strict_types=1);

/**
 * Database helper — PDO / PostgreSQL
 * All application code should use these functions instead of instantiating
 * PDO directly. The connection is created once per request (singleton).
 */

function db(): PDO
{
    static $pdo = null;

    if ($pdo == null) {
        $host   = getenv('DB_HOST')     ?: 'postgres-18.3-alpine';
        $port   = getenv('DB_PORT')     ?: '5432';
        $dbname = getenv('DB_DATABASE') ?: 'php_app';
        $user   = getenv('DB_USERNAME') ?: 'php_app_user';
        $pass   = getenv('DB_PASSWORD') ?: '';

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=disable";

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    return $pdo;
}

/**
 * Return all rows for a query.
 *
 * @param  array<string,mixed> $params
 * @return array<int,array<string,mixed>>
 */
function db_select(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Return a single row or null if not found.
 *
 * @param  array<string,mixed> $params
 * @return array<string,mixed>|null
 */
function db_row(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row != false ? $row : null;
}

/**
 * Execute an INSERT / UPDATE / DELETE and return affected rows.
 *
 * @param  array<string,mixed> $params
 */
function db_execute(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * INSERT a row and return the new id via RETURNING id.
 *
 * @param  array<string,mixed> $params
 */
function db_insert(string $sql, array $params = []): int|string
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row['id'] ?? db()->lastInsertId();
}
