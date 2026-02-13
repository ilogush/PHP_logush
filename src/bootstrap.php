<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__);

$envFile = $baseDir . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $parts = explode('=', $trimmed, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $value = trim($value, "\"'");

            if ($key === '') {
                continue;
            }

            if (getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Production-safe defaults:
// - errors are logged to file
// - display in browser only when APP_DEBUG=true
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('error_log', $baseDir . '/storage/php_errors.log');

// Reduce filesystem overhead on shared hosting (stat/realpath calls).
// If the hosting forbids changing these values, ini_set will simply have no effect.
@ini_set('realpath_cache_size', '4096K');
@ini_set('realpath_cache_ttl', '600');

$debugRaw = trim((string) getenv('APP_DEBUG'));
$debug = filter_var($debugRaw === '' ? '0' : $debugRaw, FILTER_VALIDATE_BOOLEAN);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');

date_default_timezone_set('Europe/Moscow');

spl_autoload_register(static function (string $class) use ($baseDir): void {
    if (!str_starts_with($class, 'Logush\\')) {
        return;
    }

    $relative = substr($class, strlen('Logush\\'));
    $path = $baseDir . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    // Lazy session start: only for pages that need it (cart, checkout, admin)
    // This is handled in App.php based on the route
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    // Session will be started on-demand, not here
}

if (PHP_SAPI !== 'cli') {
    // Basic security headers (safe defaults for shared hosting).
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    // Optional canonical host + HTTPS redirects (enable via .env).
    $forceHttps = filter_var((string) getenv('APP_FORCE_HTTPS'), FILTER_VALIDATE_BOOLEAN);
    $canonicalHost = trim((string) getenv('APP_CANONICAL_HOST'));
    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    $hostNoPort = preg_replace('~:\\d+$~', '', $host);
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');

    $needsHostRedirect = ($canonicalHost !== '' && $hostNoPort !== '' && strcasecmp($hostNoPort, $canonicalHost) !== 0);
    $needsHttpsRedirect = ($forceHttps && !$isHttps);

    if ($needsHostRedirect || $needsHttpsRedirect) {
        $targetHost = $canonicalHost !== '' ? $canonicalHost : $hostNoPort;
        $targetScheme = $needsHttpsRedirect ? 'https' : ($isHttps ? 'https' : 'http');
        header('Location: ' . $targetScheme . '://' . $targetHost . $uri, true, 301);
        exit;
    }
}
