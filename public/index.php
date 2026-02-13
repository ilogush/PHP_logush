<?php

declare(strict_types=1);

$uriPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

// Support PHP built-in server: allow existing static files to be served directly.
if (PHP_SAPI === 'cli-server' && $uriPath !== '/') {
    $file = __DIR__ . $uriPath;
    if (is_file($file)) {
        return false;
    }
}

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

$app = new Logush\App($baseDir);
$app->run();
