<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

$pdo = Logush\Database::connectFromEnv();
if (!$pdo) {
    fwrite(STDERR, "DB не настроена. Укажите DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD в .env\n");
    exit(1);
}

Logush\DatabaseMigrator::migrate($pdo);

$seed = in_array('--seed', $argv, true);
if ($seed) {
    $store = new Logush\DataStore($baseDir);
    $store->seed();
}

fwrite(STDOUT, "Миграция завершена" . ($seed ? " + сиды" : "") . "\n");
