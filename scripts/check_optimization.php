#!/usr/bin/env php
<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

echo "🔍 Проверка оптимизаций для WMRS\n";
echo str_repeat('=', 60) . "\n\n";

// 1. Проверка подключения к БД
echo "1. Проверка базы данных...\n";
$pdo = Logush\Database::connectFromEnv();
if ($pdo) {
    echo "   ✅ Подключение к БД успешно\n";
    
    // Проверка индексов
    $tables = ['products', 'orders', 'categories'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW INDEX FROM $table");
        $indexes = $stmt ? $stmt->fetchAll() : [];
        echo "   📊 Таблица $table: " . count($indexes) . " индексов\n";
    }
} else {
    echo "   ❌ Ошибка подключения к БД\n";
}

echo "\n";

// 2. Проверка кэша
echo "2. Проверка кэширования...\n";
$store = new Logush\DataStore($baseDir);

$start = microtime(true);
$products1 = $store->read('products');
$time1 = (microtime(true) - $start) * 1000;

$start = microtime(true);
$products2 = $store->read('products');
$time2 = (microtime(true) - $start) * 1000;

echo sprintf("   Первое чтение: %.2fms\n", $time1);
echo sprintf("   Второе чтение: %.2fms (кэш)\n", $time2);

if ($time2 < $time1 * 0.1) {
    echo "   ✅ Кэш работает эффективно\n";
} else {
    echo "   ⚠️  Кэш может работать лучше\n";
}

echo "\n";

// 3. Проверка директорий
echo "3. Проверка прав доступа...\n";
$dirs = [
    'storage/uploads',
    'storage/data',
    'storage/backups',
    'storage/ssr',
];

foreach ($dirs as $dir) {
    $path = $baseDir . '/' . $dir;
    if (is_dir($path) && is_writable($path)) {
        echo "   ✅ $dir - доступна для записи\n";
    } else {
        echo "   ❌ $dir - нет прав на запись\n";
    }
}

echo "\n";

// 4. Проверка PHP настроек
echo "4. Проверка PHP конфигурации...\n";
$settings = [
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'opcache.enable' => ini_get('opcache.enable'),
    'opcache.enable_cli' => ini_get('opcache.enable_cli'),
    'opcache.validate_timestamps' => ini_get('opcache.validate_timestamps'),
    'realpath_cache_size' => ini_get('realpath_cache_size'),
    'realpath_cache_ttl' => ini_get('realpath_cache_ttl'),
];

foreach ($settings as $key => $value) {
    echo "   $key: $value\n";
}

$isCli = (PHP_SAPI === 'cli');
$opcacheEnabled = filter_var((string) ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN);
$opcacheEnabledCli = filter_var((string) ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOLEAN);

if ($isCli) {
    echo $opcacheEnabledCli ? "   ✅ OPcache включен для CLI\n" : "   ⚠️  OPcache выключен для CLI (это нормально на хостинге)\n";
} else {
    if ($opcacheEnabled) {
        echo "   ✅ OPcache включен\n";
    } else {
        echo "   ⚠️  OPcache выключен (рекомендуется включить)\n";
    }
}

echo "\n";

// 5. Проверка размера БД
echo "5. Статистика базы данных...\n";
if ($pdo) {
    $collections = ['products', 'categories', 'colors', 'sizes', 'orders', 'quotes', 'users'];
    foreach ($collections as $collection) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $collection");
        $row = $stmt ? $stmt->fetch() : null;
        $count = is_array($row) ? (int) ($row['count'] ?? 0) : 0;
        echo "   $collection: $count записей\n";
    }
}

echo "\n";
echo str_repeat('=', 60) . "\n";
echo "✅ Проверка завершена\n";
