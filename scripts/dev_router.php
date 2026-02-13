<?php

declare(strict_types=1);

// Dev router for `php -S` so pretty URLs work locally.
// Not needed on shared hosting with Apache/Nginx rewrites.

$publicDir = dirname(__DIR__) . '/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$file = $publicDir . $uri;

if ($uri !== '/' && is_file($file)) {
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
    ];
    $mimeType = $mimeTypes[$extension] ?? (function_exists('mime_content_type') ? (mime_content_type($file) ?: '') : '');
    if ($mimeType !== '') {
        header('Content-Type: ' . $mimeType);
    }
    readfile($file);
    return true;
}

require $publicDir . '/index.php';

