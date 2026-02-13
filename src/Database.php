<?php

declare(strict_types=1);

namespace Logush;

use PDO;
use PDOException;

final class Database
{
    private static function env(string $key): string
    {
        // Some shared hostings disable putenv()/getenv() for security.
        // bootstrap.php also populates $_ENV/$_SERVER, so we can read from there as a fallback.
        $v = getenv($key);
        if ($v !== false) {
            return (string) $v;
        }
        if (isset($_ENV[$key])) {
            return (string) $_ENV[$key];
        }
        if (isset($_SERVER[$key])) {
            return (string) $_SERVER[$key];
        }
        return '';
    }

    public static function connectFromEnv(): ?PDO
    {
        $dsn = trim(self::env('DB_DSN'));
        $user = self::env('DB_USER');
        $pass = self::env('DB_PASSWORD');

        if ($dsn === '') {
            $host = trim(self::env('DB_HOST'));
            $port = trim(self::env('DB_PORT'));
            $name = trim(self::env('DB_NAME'));

            if ($host === '' || $name === '' || $user === '') {
                error_log('DB connect skipped: missing DB_HOST/DB_NAME/DB_USER in env/.env');
                return null;
            }

            $charset = trim(self::env('DB_CHARSET'));
            if ($charset === '') {
                $charset = 'utf8mb4';
            }

            // Some shared hostings require "localhost" even if they display a node hostname in panel.
            // We'll try DB_HOST first, then common local fallbacks.
            $portResolved = ($port !== '' ? $port : '3306');
            // Prefer the configured host and "localhost" only.
            // 127.0.0.1 may be denied on shared hosting (Host ... is not allowed to connect).
            $hosts = array_values(array_unique(array_filter([
                $host,
                ($host !== 'localhost' ? 'localhost' : null),
            ])));
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if ($dsn !== '') {
            try {
                return new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log('DB connect failed (DB_DSN): ' . $e->getMessage());
                return null;
            }
        }

        foreach ($hosts as $tryHost) {
            $tryDsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $tryHost,
                $portResolved,
                $name,
                $charset
            );
            try {
                return new PDO($tryDsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log('DB connect failed (host=' . $tryHost . '): ' . $e->getMessage());
                continue;
            }
        }

        return null;
    }
}
