<?php
declare(strict_types=1);

/**
 * Redis helper — caching, rate-limiting, and job queue.
 *
 * Queue design (simple RPUSH / BLPOP list):
 *   push_job('queue:emails', ['type' => 'verify', 'email' => '...'])
 *   Jobs are consumed by workers/email_worker.php
 */

function redis(): Redis
{
    static $instance = null;

    if ($instance == null) {
        $host = getenv('REDIS_HOST') ?: 'redis-alpine';
        $port = (int) (getenv('REDIS_PORT') ?: 6379);
        $r = new Redis();
        $r->connect($host, $port, 2.0);
        $instance = $r;
    }

    return $instance;
}

/**
 * Push a job payload onto a Redis list queue.
 *
 * @param array<string,mixed> $payload
 */
function push_job(string $queue, array $payload): void
{
    redis()->rPush($queue, json_encode($payload, JSON_THROW_ON_ERROR));
}

/**
 * Create and push a tenant-aware job to Redis.
 *
 * Job identifier format: {tenant}:{unique_id}_{model}
 * Example for user registration: doctor:1_user_register or marketplace:2_user_register
 *
 * @param string $tenant        e.g. 'doctor', 'marketplace', 'enterprise'
 * @param string $model         e.g. 'user_register'
 * @param int|string $uniqueId  e.g. $userId
 * @param array<string,mixed> $payload
 * @param string $queue         queue list name
 * @return string $jobId        e.g. 'doctor:1_user_register'
 */
function push_tenant_job(string $tenant, string $model, int|string $uniqueId, array $payload, string $queue = 'queue:emails'): string
{
    $jobId = "{$tenant}:{$uniqueId}_{$model}";
    $payload['id']         = $jobId;
    $payload['tenant']     = $tenant;
    $payload['model']      = $model;
    $payload['unique_id']  = $uniqueId;
    $payload['status']     = 'pending';
    $payload['created_at'] = date('Y-m-d H:i:sP');
    $payload['_attempts']  = 0;

    $json = json_encode($payload, JSON_THROW_ON_ERROR);

    // Save job tracking record in Redis: job:{tenant}:{unique_id}_{model}
    redis()->set("job:{$jobId}", $json);

    // Push into queue
    redis()->rPush($queue, $json);

    return $jobId;
}

/**
 * Update the status of a tracked job in Redis.
 */
function update_job_status(string $jobId, string $status, array $extra = []): void
{
    $raw = redis()->get("job:{$jobId}");
    $data = $raw ? json_decode($raw, true) : [];
    $data['status']     = $status;
    $data['updated_at'] = date('Y-m-d H:i:sP');
    foreach ($extra as $k => $v) {
        $data[$k] = $v;
    }
    redis()->set("job:{$jobId}", json_encode($data, JSON_THROW_ON_ERROR));
}

/**
 * Pop the next job from one or more queues (blocking, timeout in seconds).
 * Returns null on timeout.
 *
 * @param string|array<string> $queue
 * @return array<string,mixed>|null
 */
function pop_job(string|array $queue, int $timeout = 5): ?array
{
    $queues = is_array($queue) ? $queue : [$queue];
    $result = redis()->blPop($queues, $timeout);
    if (empty($result)) {
        return null;
    }
    $job = json_decode($result[1], true, 512, JSON_THROW_ON_ERROR);
    $job['_from_queue'] = $result[0];
    return $job;
}

function cache_set(string $key, mixed $value, int $ttl = 3600): void
{
    redis()->setex($key, $ttl, serialize($value));
}

function cache_get(string $key): mixed
{
    $raw = redis()->get($key);
    return $raw != false ? unserialize($raw) : null;
}

function cache_forget(string $key): void
{
    redis()->del($key);
}

/**
 * Simple per-key rate limiter.
 * Returns true if the action is allowed, false if the limit was hit.
 */
function rate_limit(string $key, int $max, int $windowSeconds): bool
{
    $r   = redis();
    $cnt = (int) $r->incr($key);
    if ($cnt == 1) {
        $r->expire($key, $windowSeconds);
    }
    return $cnt <= $max;
}
