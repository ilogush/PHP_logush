<?php

declare(strict_types=1);

/**
 * Скрипт для проверки здоровья системы
 * Проверяет БД, права доступа, конфигурацию
 */

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

echo "=== Проверка здоровья системы ===\n\n";

$errors = [];
$warnings = [];

// 1. Проверка PHP версии
echo "1. PHP версия: ";
$phpVersion = PHP_VERSION;
echo "$phpVersion ";
if (version_compare($phpVersion, '8.1.0', '>=')) {
    echo "✓\n";
} else {
    echo "✗\n";
    $errors[] = "Требуется PHP 8.1 или выше";
}

// 2. Проверка расширений PHP
echo "\n2. PHP расширения:\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl', 'gd'];
foreach ($requiredExtensions as $ext) {
    echo "   - $ext: ";
    if (extension_loaded($ext)) {
        echo "✓\n";
    } else {
        echo "✗\n";
        $errors[] = "Отсутствует расширение: $ext";
    }
}

// 3. Проверка подключения к БД
echo "\n3. База данных: ";
$pdo = Logush\Database::connectFromEnv();
if ($pdo) {
    echo "✓ Подключено\n";
    
    // Проверка таблиц
    $tables = ['products', 'categories', 'colors', 'sizes', 'orders', 'quotes', 'users', 'settings'];
    echo "   Таблицы:\n";
    foreach ($tables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "   - $table: $count записей ✓\n";
        } catch (Exception $e) {
            echo "   - $table: ✗\n";
            $errors[] = "Таблица $table не найдена или повреждена";
        }
    }
} else {
    echo "✗\n";
    $errors[] = "Не удалось подключиться к БД";
}

// 4. Проверка прав доступа к папкам
echo "\n4. Права доступа:\n";
$writableDirs = [
    'storage/data',
    'storage/uploads',
    'storage/backups',
];

foreach ($writableDirs as $dir) {
    $path = $baseDir . '/' . $dir;
    echo "   - $dir: ";
    
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
    
    if (is_writable($path)) {
        echo "✓ Доступна для записи\n";
    } else {
        echo "✗ Нет прав на запись\n";
        $errors[] = "Папка $dir недоступна для записи";
    }
}

// 5. Проверка .env файла
echo "\n5. Конфигурация (.env):\n";
$envFile = $baseDir . '/.env';
if (is_file($envFile)) {
    echo "   - Файл .env: ✓\n";
    
    $requiredVars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
    foreach ($requiredVars as $var) {
        $value = getenv($var);
        echo "   - $var: ";
        if ($value !== false && $value !== '') {
            echo "✓\n";
        } else {
            echo "✗\n";
            $errors[] = "Переменная $var не установлена в .env";
        }
    }
    
    // Проверка режима отладки
    $debug = getenv('APP_DEBUG');
    echo "   - APP_DEBUG: ";
    if ($debug === '1' || $debug === 'true') {
        echo "⚠️  Включен (отключите на продакшене)\n";
        $warnings[] = "Режим отладки включен - отключите на продакшене";
    } else {
        echo "✓ Отключен\n";
    }
} else {
    echo "   - Файл .env: ✗\n";
    $errors[] = "Файл .env не найден";
}

// 6. Проверка размера логов
echo "\n6. Логи:\n";
$logFile = $baseDir . '/storage/php_errors.log';
if (is_file($logFile)) {
    $logSize = filesize($logFile);
    $logSizeMB = round($logSize / 1024 / 1024, 2);
    echo "   - php_errors.log: $logSizeMB МБ ";
    
    if ($logSizeMB > 10) {
        echo "⚠️  Большой размер\n";
        $warnings[] = "Лог-файл слишком большой ($logSizeMB МБ) - рекомендуется очистить";
    } else {
        echo "✓\n";
    }
} else {
    echo "   - php_errors.log: Не создан\n";
}

// 7. Проверка загруженных файлов
echo "\n7. Загруженные файлы:\n";
$uploadsDir = $baseDir . '/storage/uploads';
if (is_dir($uploadsDir)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    $count = 0;
    $totalSize = 0;
    
    foreach ($files as $file) {
        if ($file->isFile()) {
            $count++;
            $totalSize += $file->getSize();
        }
    }
    
    $totalSizeMB = round($totalSize / 1024 / 1024, 2);
    echo "   - Файлов: $count\n";
    echo "   - Общий размер: $totalSizeMB МБ\n";
} else {
    echo "   - Папка uploads не найдена\n";
}

// Итоги
echo "\n" . str_repeat("=", 50) . "\n";
echo "ИТОГИ ПРОВЕРКИ\n";
echo str_repeat("=", 50) . "\n\n";

if (empty($errors) && empty($warnings)) {
    echo "✓ Все проверки пройдены успешно!\n";
    exit(0);
}

if (!empty($warnings)) {
    echo "⚠️  ПРЕДУПРЕЖДЕНИЯ (" . count($warnings) . "):\n";
    foreach ($warnings as $i => $warning) {
        echo "   " . ($i + 1) . ". $warning\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "✗ ОШИБКИ (" . count($errors) . "):\n";
    foreach ($errors as $i => $error) {
        echo "   " . ($i + 1) . ". $error\n";
    }
    echo "\n";
    exit(1);
}

exit(0);
