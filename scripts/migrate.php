<?php

declare(strict_types=1);

// Run DB migrations without serving HTTP.
// Intended for shared hosting deploys where you want to avoid running migrations during requests.

require __DIR__ . '/../src/bootstrap.php';

$baseDir = dirname(__DIR__);
$store = new Logush\DataStore($baseDir);

echo "DB schema version: " . Logush\DatabaseMigrator::SCHEMA_VERSION . PHP_EOL;
echo "OK\n";

