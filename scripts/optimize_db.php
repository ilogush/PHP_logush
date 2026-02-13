#!/usr/bin/env php
<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

echo "🔧 Оптимизация таблиц базы данных\n";
echo str_repeat('=', 60) . "\n\n";

$pdo = Logush\Database::connectFromEnv();
if (!$pdo) {
    fwrite(STDERR, "❌ Ошибка подключения к БД\n");
    exit(1);
}

$tables = ['products', 'categories', 'colors', 'sizes', 'orders', 'quotes', 'users', 'settings'];

foreach ($tables as $table) {
    echo "Оптимизация таблицы $table... ";
    try {
        $pdo->exec("OPTIMIZE TABLE $table");
        echo "✅\n";
    } catch (Throwable $e) {
        echo "❌ " . $e->getMessage() . "\n";
    }
}

echo "\n";
echo str_repeat('=', 60) . "\n";
echo "✅ Оптимизация завершена\n";
