#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Email verification job worker.
 *
 * Runs as a long-lived CLI process inside the php-app container.
 * Pulls jobs from the Redis list "queue:emails" and sends emails.
 *
 * Start:
 *   docker exec -d php-app php /var/www/html/workers/email_worker.php
 *
 * Job payload:
 * {
 *   "type":    "verify_email",
 *   "to":      "user@example.com",
 *   "name":    "John",
 *   "token":   "<hex>",
 *   "user_id": 42
 * }
 */

require_once __DIR__ . '/../bootstrap.php';

const DEFAULT_QUEUES = [
    'queue:emails',
    'queue:doctor:emails',
    'queue:marketplace:emails',
    'queue:enterprise:emails',
];
const DEFAULT_FRONTEND_URL = 'http://localhost:5173';
const SMTP_FROM            = 'noreply@example.com';

echo "[worker] Tenant-aware Email Worker started.\n";
echo "[worker] Listening on queues: " . implode(', ', DEFAULT_QUEUES) . "...\n";

while (true) {
    $job = pop_job(DEFAULT_QUEUES, 5);
    if ($job === null) {
        continue;
    }

    $tenant   = $job['tenant'] ?? 'doctor';
    $uniqueId = $job['unique_id'] ?? $job['user_id'] ?? uniqid('', true);
    $model    = $job['model'] ?? 'user_register';
    $jobId    = $job['id'] ?? "{$tenant}:{$uniqueId}_{$model}";

    echo sprintf("[worker] [%s] Picked up job [%s] (model: %s, tenant: %s, type: %s)\n",
        date('Y-m-d H:i:s'), $jobId, $model, $tenant, $job['type'] ?? 'unknown');

    try {
        update_job_status($jobId, 'processing', ['started_at' => date('c')]);

        match ($job['type'] ?? '') {
            'verify_email' => handle_verify_email($job, $jobId),
            default        => printf("[worker] Unknown job type: %s\n", $job['type'] ?? '?'),
        };

        update_job_status($jobId, 'completed', ['completed_at' => date('c')]);
        echo sprintf("[worker] [SUCCESS] Job [%s] completed successfully.\n", $jobId);

    } catch (Throwable $e) {
        $errorMsg = $e->getMessage();
        echo sprintf("[worker] [ERROR] Job [%s] failed: %s\n", $jobId, $errorMsg);

        $attempts = ($job['_attempts'] ?? 0) + 1;
        $job['_attempts'] = $attempts;

        if ($attempts < 3) {
            update_job_status($jobId, 'retrying', [
                'error'    => $errorMsg,
                'attempts' => $attempts,
            ]);
            push_job('queue:emails', $job);
            echo sprintf("[worker] Re-queued [%s] (attempt %d/3)\n", $jobId, $attempts);
        } else {
            update_job_status($jobId, 'failed', [
                'error'        => $errorMsg,
                'attempts'     => $attempts,
                'abandoned_at' => date('c'),
            ]);
            echo sprintf("[worker] [ABANDONED] Job [%s] exceeded max attempts: %s\n", $jobId, json_encode($job));
        }
    }
}

function handle_verify_email(array $job, string $jobId): void
{
    $to    = filter_var($job['to'] ?? '', FILTER_VALIDATE_EMAIL);
    $name  = $job['name']  ?? 'User';
    $token = $job['token'] ?? '';

    if (!$to || !$token) {
        throw new InvalidArgumentException("Invalid verify_email payload: missing 'to' or 'token'.");
    }

    $frontendUrl = getenv('FRONTEND_URL') ?: DEFAULT_FRONTEND_URL;
    $link        = rtrim($frontendUrl, '/') . '/verify?token=' . urlencode($token);
    $backendLink = 'http://localhost:8080/api/verify.php?token=' . urlencode($token);

    // Save active link in job status in Redis for immediate dev access
    update_job_status($jobId, 'processing', [
        'verify_link'         => $link,
        'backend_verify_link' => $backendLink,
    ]);

    echo sprintf("[worker] [VERIFY LINK] %s\n", $link);

    $subject = trans('email_verify_subject');
    $body    = trans('email_verify_greeting', ['name' => $name]) . "\n\n"
             . trans('email_verify_instructions') . "\n\n"
             . "{$link}\n\n"
             . "Alternative direct link: {$backendLink}\n\n"
             . trans('email_verify_ignore') . "\n";

    $mail_driver    = strtolower(getenv('MAIL_MAILER') ?: 'smtp');
    $mail_from      = getenv('MAIL_FROM_ADDRESS') ?: SMTP_FROM;
    $mail_from_name = getenv('MAIL_FROM_NAME') ?: 'PHP App';

    $headers  = "From: " . ($mail_from_name ? "\"{$mail_from_name}\" <{$mail_from}>" : $mail_from) . "\r\n";
    $headers .= "Reply-To: {$mail_from}\r\n";
    $headers .= "X-Mailer: PHP/" . PHP_VERSION . "\r\n";
    $headers .= "X-Tenant-ID: " . ($job['tenant'] ?? 'default') . "\r\n";
    $headers .= "X-Job-ID: {$jobId}";

    if ($mail_driver === 'smtp') {
        $smtp_host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
        $smtp_port = (int)(getenv('MAIL_PORT') ?: 587);
        $smtp_user = getenv('MAIL_USERNAME') ?: '';
        $smtp_pass = getenv('MAIL_PASSWORD') ?: '';
        $smtp_enc  = strtolower(getenv('MAIL_ENCRYPTION') ?: 'tls');

        send_via_smtp(
            $smtp_host,
            $smtp_port,
            $smtp_user,
            $smtp_pass,
            $smtp_enc,
            $mail_from,
            $mail_from_name,
            $to,
            $subject,
            $body,
            $headers
        );
        echo "[worker] Verification email delivered via SMTP to {$to}\n";
    } else {
        if (!@mail($to, $subject, $body, $headers)) {
            throw new RuntimeException("Local mail() driver failed to send to {$to}");
        }
        echo "[worker] Verification email delivered via mail() to {$to}\n";
    }
}

function send_via_smtp(
    string $host,
    int $port,
    string $user,
    string $pass,
    string $encryption,
    string $from,
    string $fromName,
    string $to,
    string $subject,
    string $body,
    string $extraHeaders = ''
): bool {
    $timeout = 10;
    $remote  = "$host:$port";
    $ctx     = stream_context_create([
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ]
    ]);
    $flags   = STREAM_CLIENT_CONNECT;
    $errno   = 0;
    $errstr  = '';

    $fp = @stream_socket_client($remote, $errno, $errstr, $timeout, $flags, $ctx);
    if (!$fp) {
        throw new RuntimeException("SMTP connection to {$remote} failed: {$errstr} ({$errno})");
    }
    stream_set_timeout($fp, $timeout);

    $res = smtp_get($fp);
    if (strpos($res, '220') !== 0) {
        fclose($fp);
        throw new RuntimeException("Invalid SMTP greeting: " . trim($res));
    }

    $hostname = gethostname() ?: 'localhost';
    smtp_put($fp, "EHLO {$hostname}\r\n");
    $res = smtp_get($fp);

    // STARTTLS if requested
    if ($encryption === 'tls') {
        if (stripos($res, 'STARTTLS') !== false) {
            smtp_put($fp, "STARTTLS\r\n");
            $r = smtp_get($fp);
            if (strpos($r, '220') !== 0) {
                fclose($fp);
                throw new RuntimeException("STARTTLS rejected: " . trim($r));
            }

            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                throw new RuntimeException("Failed to establish TLS encryption with SMTP server.");
            }

            // Re-EHLO after TLS negotiation
            smtp_put($fp, "EHLO {$hostname}\r\n");
            $res = smtp_get($fp);
        }
    }

    // Authenticate if username provided
    if ($user !== '') {
        smtp_put($fp, "AUTH LOGIN\r\n");
        $r = smtp_get($fp);
        if (strpos($r, '334') !== 0) {
            fclose($fp);
            throw new RuntimeException("SMTP AUTH LOGIN rejected: " . trim($r));
        }

        smtp_put($fp, base64_encode($user) . "\r\n");
        $r = smtp_get($fp);
        if (strpos($r, '334') !== 0) {
            fclose($fp);
            throw new RuntimeException("SMTP Username rejected: " . trim($r));
        }

        smtp_put($fp, base64_encode($pass) . "\r\n");
        $r = smtp_get($fp);
        if (strpos($r, '235') !== 0) {
            fclose($fp);
            throw new RuntimeException("SMTP Authentication failed for {$user}: " . trim($r) . " (Check MAIL_USERNAME and MAIL_PASSWORD in .env)");
        }
    }

    // MAIL FROM
    smtp_put($fp, "MAIL FROM:<{$from}>\r\n");
    $r = smtp_get($fp);
    if (strpos($r, '250') !== 0) {
        fclose($fp);
        throw new RuntimeException("SMTP MAIL FROM <{$from}> rejected: " . trim($r));
    }

    // RCPT TO
    smtp_put($fp, "RCPT TO:<{$to}>\r\n");
    $r = smtp_get($fp);
    if (strpos($r, '250') !== 0 && strpos($r, '251') !== 0) {
        fclose($fp);
        throw new RuntimeException("SMTP RCPT TO <{$to}> rejected: " . trim($r));
    }

    // DATA
    smtp_put($fp, "DATA\r\n");
    $r = smtp_get($fp);
    if (strpos($r, '354') !== 0) {
        fclose($fp);
        throw new RuntimeException("SMTP DATA command rejected: " . trim($r));
    }

    $fromHeader = $fromName ? sprintf('%s <%s>', addcslashes($fromName, "\""), $from) : $from;
    $hdrs  = "From: {$fromHeader}\r\n";
    $hdrs .= "To: {$to}\r\n";
    $hdrs .= "Subject: {$subject}\r\n";
    $hdrs .= "MIME-Version: 1.0\r\n";
    $hdrs .= "Content-Type: text/plain; charset=UTF-8\r\n";
    if ($extraHeaders) {
        $hdrs .= $extraHeaders . "\r\n";
    }

    $data = $hdrs . "\r\n" . $body . "\r\n.\r\n";
    smtp_put($fp, $data);
    $r = smtp_get($fp);
    if (strpos($r, '250') !== 0) {
        fclose($fp);
        throw new RuntimeException("SMTP message delivery rejected: " . trim($r));
    }

    smtp_put($fp, "QUIT\r\n");
    fclose($fp);
    return true;
}

function smtp_get($fp): string
{
    $data = '';
    while (($line = fgets($fp)) != false) {
        $data .= $line;
        // multi-line responses have a hyphen after the code
        if (preg_match('/^\d{3} /', $line)) break;
    }
    return $data;
}

function smtp_put($fp, $cmd): void
{
    fwrite($fp, $cmd);
}

