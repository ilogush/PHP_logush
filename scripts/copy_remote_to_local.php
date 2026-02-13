<?php

declare(strict_types=1);

/**
 * Скрипт для копирования данных из удаленной БД в локальную
 * 
 * Использование:
 * php scripts/copy_remote_to_local.php --remote-host=HOST --remote-db=DB --remote-user=USER --remote-pass=PASS
 * 
 * Или создайте .env.remote с параметрами удаленной БД:
 * REMOTE_DB_HOST=...
 * REMOTE_DB_NAME=...
 * REMOTE_DB_USER=...
 * REMOTE_DB_PASSWORD=...
 * REMOTE_DB_PORT=3306
 */

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

// Парсинг аргументов командной строки
$options = getopt('', [
    'remote-host:',
    'remote-db:',
    'remote-user:',
    'remote-pass:',
    'remote-port::',
    'tables::',
    'skip-tables::',
]);

// Загрузка параметров из .env.remote если существует
$remoteEnvFile = $baseDir . '/.env.remote';
if (is_file($remoteEnvFile)) {
    $lines = file($remoteEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }
            $parts = explode('=', $trimmed, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1], "\"'");
                putenv($key . '=' . $value);
            }
        }
    }
}

// Получение параметров удаленной БД
$remoteHost = $options['remote-host'] ?? getenv('REMOTE_DB_HOST') ?: '';
$remoteDb = $options['remote-db'] ?? getenv('REMOTE_DB_NAME') ?: '';
$remoteUser = $options['remote-user'] ?? getenv('REMOTE_DB_USER') ?: '';
$remotePass = $options['remote-pass'] ?? getenv('REMOTE_DB_PASSWORD') ?: '';
$remotePort = $options['remote-port'] ?? getenv('REMOTE_DB_PORT') ?: '3306';

if (empty($remoteHost) || empty($remoteDb) || empty($remoteUser)) {
    fwrite(STDERR, "Ошибка: Не указаны параметры удаленной БД\n\n");
    fwrite(STDERR, "Использование:\n");
    fwrite(STDERR, "  php scripts/copy_remote_to_local.php --remote-host=HOST --remote-db=DB --remote-user=USER --remote-pass=PASS\n\n");
    fwrite(STDERR, "Или создайте файл .env.remote с параметрами:\n");
    fwrite(STDERR, "  REMOTE_DB_HOST=...\n");
    fwrite(STDERR, "  REMOTE_DB_NAME=...\n");
    fwrite(STDERR, "  REMOTE_DB_USER=...\n");
    fwrite(STDERR, "  REMOTE_DB_PASSWORD=...\n");
    fwrite(STDERR, "  REMOTE_DB_PORT=3306\n");
    exit(1);
}

// Подключение к локальной БД
fwrite(STDOUT, "Подключение к локальной БД...\n");
$localPdo = Logush\Database::connectFromEnv();
if (!$localPdo) {
    fwrite(STDERR, "Ошибка: Не удалось подключиться к локальной БД\n");
    exit(1);
}

// Подключение к удаленной БД
fwrite(STDOUT, "Подключение к удаленной БД ($remoteHost:$remotePort/$remoteDb)...\n");
try {
    $remoteDsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $remoteHost,
        $remotePort,
        $remoteDb
    );
    $remotePdo = new PDO($remoteDsn, $remoteUser, $remotePass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "Ошибка подключения к удаленной БД: " . $e->getMessage() . "\n");
    exit(1);
}

// Список таблиц для копирования
$allTables = [
    'products',
    'categories',
    'colors',
    'sizes',
    'orders',
    'quotes',
    'users',
    'settings',
];

// Фильтрация таблиц
$tablesToCopy = $allTables;
if (!empty($options['tables'])) {
    $tablesToCopy = array_filter(
        explode(',', $options['tables']),
        fn($t) => in_array(trim($t), $allTables, true)
    );
}
if (!empty($options['skip-tables'])) {
    $skipTables = array_map('trim', explode(',', $options['skip-tables']));
    $tablesToCopy = array_diff($tablesToCopy, $skipTables);
}

fwrite(STDOUT, "\nБудут скопированы таблицы: " . implode(', ', $tablesToCopy) . "\n\n");
fwrite(STDOUT, "ВНИМАНИЕ: Существующие данные в локальной БД будут удалены!\n");
fwrite(STDOUT, "Продолжить? (yes/no): ");

$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    fwrite(STDOUT, "Отменено.\n");
    exit(0);
}

// Копирование данных
$localPdo->beginTransaction();

try {
    foreach ($tablesToCopy as $table) {
        fwrite(STDOUT, "\nКопирование таблицы: $table\n");
        
        // Очистка локальной таблицы
        fwrite(STDOUT, "  - Очистка локальной таблицы...\n");
        $localPdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $localPdo->exec("TRUNCATE TABLE `$table`");
        $localPdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        // Получение данных из удаленной БД
        fwrite(STDOUT, "  - Получение данных из удаленной БД...\n");
        $stmt = $remotePdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll();
        
        if (empty($rows)) {
            fwrite(STDOUT, "  - Таблица пуста, пропускаем\n");
            continue;
        }
        
        fwrite(STDOUT, "  - Найдено записей: " . count($rows) . "\n");
        
        // Получение списка колонок
        $columns = array_keys($rows[0]);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnsList = implode(', ', array_map(fn($c) => "`$c`", $columns));
        
        // Вставка данных в локальную БД
        fwrite(STDOUT, "  - Вставка данных в локальную БД...\n");
        $insertSql = "INSERT INTO `$table` ($columnsList) VALUES ($placeholders)";
        $insertStmt = $localPdo->prepare($insertSql);
        
        $inserted = 0;
        foreach ($rows as $row) {
            $values = array_values($row);
            $insertStmt->execute($values);
            $inserted++;
            
            if ($inserted % 100 === 0) {
                fwrite(STDOUT, "  - Вставлено: $inserted/" . count($rows) . "\r");
            }
        }
        
        fwrite(STDOUT, "  - Вставлено: $inserted/" . count($rows) . " ✓\n");
    }
    
    $localPdo->commit();
    fwrite(STDOUT, "\n✓ Копирование завершено успешно!\n");
    
} catch (Exception $e) {
    $localPdo->rollBack();
    fwrite(STDERR, "\n✗ Ошибка: " . $e->getMessage() . "\n");
    exit(1);
}
