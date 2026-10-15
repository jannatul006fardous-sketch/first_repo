<?php
declare(strict_types=1);

if (PHP_VERSION_ID < 70400) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'PHP 7.4 বা তার উপরের ভার্সন লাগবে। এখন: ' . PHP_VERSION . ' — cPanel → Select PHP Version থেকে 8.0/8.1 চালু করুন।';
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        session_set_cookie_params(0, '/', '', false, true);
    }
    @session_start();
}

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

define('ROOT_PATH', dirname(__DIR__));
define('STORAGE_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'storage');
define('UPLOAD_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads');

$logFile = STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'php-error.log';
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
@ini_set('error_log', $logFile);
error_reporting(E_ALL);

function refa_fail(Throwable $e): void
{
    $logFile = STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'php-error.log';
    @file_put_contents($logFile, date('c') . ' ' . $e . PHP_EOL, FILE_APPEND);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }
    echo '<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><title>REFA</title></head><body>';
    echo '<p>সাইট লোড করা যায়নি।</p><pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine(), ENT_QUOTES, 'UTF-8') . '</pre>';
    echo '</body></html>';
    exit;
}

set_exception_handler(static function (Throwable $e): void {
    refa_fail($e);
});

register_shutdown_function(static function (): void {
    $err = error_get_last();
    if (!$err) {
        return;
    }
    $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($err['type'], $fatal, true)) {
        return;
    }
    refa_fail(new ErrorException($err['message'], 0, $err['type'], $err['file'], $err['line']));
});

require_once ROOT_PATH . '/config/env.php';
load_env(ROOT_PATH . '/.env');

$credentialsFile = ROOT_PATH . '/config/credentials.php';
if (is_file($credentialsFile)) {
    $credentials = require $credentialsFile;
    if (is_array($credentials)) {
        foreach ($credentials as $key => $value) {
            env_put((string) $key, (string) $value);
        }
    }
}

date_default_timezone_set(env('APP_TZ', 'Europe/Helsinki'));

define('APP_NAME', env('APP_NAME', 'REFA'));
define('APP_DEBUG', env('APP_DEBUG', 'false') === 'true');

function detect_app_url(): string
{
    $configured = rtrim((string) env('APP_URL', ''), '/');
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    $hostIsLocal = $host === '' || (bool) preg_match('/^(localhost|127\.0\.0\.1)(:|$)/i', $host);
    $configIsLocal = $configured === ''
        || str_contains($configured, 'localhost')
        || str_contains($configured, '127.0.0.1');

    if ($configured !== '' && (!$configIsLocal || $hostIsLocal)) {
        return $configured;
    }
    if ($host === '') {
        return $configured;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    $scheme = $https ? 'https' : 'http';

    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = rtrim(dirname($scriptName), '/');
    if (preg_match('#/(admin|member)$#', $dir)) {
        $dir = dirname($dir);
    }
    if ($dir === '/' || $dir === '\\' || $dir === '.') {
        $dir = '';
    }

    return rtrim($scheme . '://' . $host . $dir, '/');
}

define('APP_URL', detect_app_url());

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/lang/load.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/mailer.php';
require_once ROOT_PATH . '/includes/uploads.php';
