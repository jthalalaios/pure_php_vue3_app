<?php
declare(strict_types=1);

/**
 * Singleton Redis wrapper (uses ext-redis).
 * Usage: $r = RedisClient::getInstance();
 *        $r->set('key', 'value', ['ex' => 60]);
 */
class RedisClient
{
    private static ?Redis $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): Redis
    {
        if (self::$instance == null) {
            $host = getenv('REDIS_HOST') ?: 'redis-alpine';
            $port = (int) (getenv('REDIS_PORT') ?: 6379);

            $redis = new Redis();
            $redis->connect($host, $port, 2.0);

            self::$instance = $redis;
        }

        return self::$instance;
    }
}
