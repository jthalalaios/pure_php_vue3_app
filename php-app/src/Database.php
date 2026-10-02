<?php
declare(strict_types=1);

/**
 * Singleton PDO wrapper for PostgreSQL.
 * Usage: $db = Database::getInstance();
 *        $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance == null) {
            $host     = getenv('DB_HOST')     ?: 'postgres-18.3-alpine';
            $port     = getenv('DB_PORT')     ?: '5432';
            $dbname   = getenv('DB_DATABASE') ?: 'php_app';
            $user     = getenv('DB_USERNAME') ?: 'php_app_user';
            $password = getenv('DB_PASSWORD') ?: '';

            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=disable";

            self::$instance = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$instance;
    }
}
