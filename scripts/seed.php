<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

$store = new Logush\DataStore($baseDir);
if (!$store->usesDatabase()) {
    fwrite(STDERR, "БД не готова. Сначала запустите php scripts/migrate.php\n");
    exit(1);
}

$store->seed();
fwrite(STDOUT, "Сиды применены\n");
