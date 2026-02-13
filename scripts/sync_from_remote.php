<?php

declare(strict_types=1);

/**
 * Скрипт для синхронизации данных с удаленного сервера
 * 
 * Шаг 1: Загружает export_import_db.php на удаленный сервер через FTP
 * Шаг 2: Выполняет экспорт через SSH или создает веб-эндпоинт
 * Шаг 3: Скачивает JSON дамп
 * Шаг 4: Импортирует в локальную БД
 */

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

// Параметры FTP
$ftpHost = getenv('FTP_HOST') ?: '';
$ftpUser = getenv('FTP_USER') ?: '';
$ftpPass = getenv('FTP_PASS') ?: '';
$ftpPort = (int)(getenv('FTP_PORT') ?: 21);
$ftpRemotePath = rtrim(getenv('FTP_REMOTE_PATH') ?: '/www/logush.ru', '/');

if (empty($ftpHost) || empty($ftpUser) || empty($ftpPass)) {
    fwrite(STDERR, "Ошибка: Укажите FTP_HOST, FTP_USER, FTP_PASS в .env\n");
    exit(1);
}

fwrite(STDOUT, "=== Синхронизация данных с удаленного сервера ===\n\n");

// Подключение к FTP
fwrite(STDOUT, "1. Подключение к FTP ($ftpHost)...\n");
$conn = ftp_connect($ftpHost, $ftpPort, 30);
if (!$conn || !ftp_login($conn, $ftpUser, $ftpPass)) {
    fwrite(STDERR, "Ошибка подключения к FTP\n");
    exit(1);
}

ftp_pasv($conn, true);
fwrite(STDOUT, "   ✓ Подключено\n\n");

// Загрузка скрипта экспорта
fwrite(STDOUT, "2. Загрузка скрипта экспорта на сервер...\n");
$localExportScript = $baseDir . '/scripts/export_import_db.php';
$remoteExportScript = $ftpRemotePath . '/scripts/export_import_db.php';

if (!ftp_put($conn, $remoteExportScript, $localExportScript, FTP_BINARY)) {
    fwrite(STDERR, "Ошибка загрузки скрипта\n");
    ftp_close($conn);
    exit(1);
}
fwrite(STDOUT, "   ✓ Скрипт загружен\n\n");

// Создание веб-эндпоинта для экспорта
fwrite(STDOUT, "3. Создание веб-эндпоинта для экспорта...\n");

$webExportScript = <<<'PHP'
<?php
// Временный эндпоинт для экспорта данных
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="db_export.json"');

// Чтение .env напрямую
$baseDir = dirname(__DIR__);
$envFile = $baseDir . '/.env';
$env = [];

if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) continue;
        $parts = explode('=', $trimmed, 2);
        if (count($parts) === 2) {
            $env[trim($parts[0])] = trim($parts[1], "\"'");
        }
    }
}

$dbHost = $env['DB_HOST'] ?? 'localhost';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbName = $env['DB_NAME'] ?? '';
$dbUser = $env['DB_USER'] ?? '';
$dbPass = $env['DB_PASSWORD'] ?? '';

// Пробуем разные варианты хоста для shared hosting
$hostsToTry = ['localhost', '127.0.0.1', $dbHost];
$pdo = null;

foreach ($hostsToTry as $tryHost) {
    try {
        $dsn = "mysql:host=$tryHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        break;
    } catch (Exception $e) {
        continue;
    }
}

if (empty($dbName) || empty($dbUser)) {
    http_response_code(500);
    echo json_encode(['error' => 'Database config missing']);
    exit(1);
}

// Пробуем разные варианты хоста для shared hosting
$hostsToTry = ['localhost', '127.0.0.1', $dbHost];
$pdo = null;

foreach ($hostsToTry as $tryHost) {
    try {
        $dsn = "mysql:host=$tryHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        break;
    } catch (Exception $e) {
        continue;
    }
}

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit(1);
}

$tables = ['products', 'categories', 'colors', 'sizes', 'orders', 'quotes', 'users', 'settings'];
$data = [];

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $data[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $data[$table] = [];
    }
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
PHP;

$localWebScript = sys_get_temp_dir() . '/web_export.php';
file_put_contents($localWebScript, $webExportScript);

$remoteWebScript = $ftpRemotePath . '/public/export_data_temp.php';
if (!ftp_put($conn, $remoteWebScript, $localWebScript, FTP_BINARY)) {
    fwrite(STDERR, "Ошибка загрузки веб-скрипта\n");
    ftp_close($conn);
    unlink($localWebScript);
    exit(1);
}

unlink($localWebScript);
fwrite(STDOUT, "   ✓ Эндпоинт создан\n\n");

// Скачивание данных через HTTP
fwrite(STDOUT, "4. Скачивание данных с удаленного сервера...\n");
$exportUrl = 'http://logush.ru/export_data_temp.php';

$ch = curl_init($exportUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 300);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$jsonData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || empty($jsonData)) {
    fwrite(STDERR, "Ошибка скачивания данных (HTTP $httpCode)\n");
    fwrite(STDERR, "Ответ: " . substr($jsonData, 0, 500) . "\n");
    ftp_delete($conn, $remoteWebScript);
    ftp_close($conn);
    exit(1);
}

$data = json_decode($jsonData, true);
if (!$data) {
    fwrite(STDERR, "Ошибка парсинга JSON\n");
    ftp_delete($conn, $remoteWebScript);
    ftp_close($conn);
    exit(1);
}

fwrite(STDOUT, "   ✓ Данные получены\n\n");

// Статистика
fwrite(STDOUT, "   Статистика:\n");
foreach ($data as $table => $rows) {
    fwrite(STDOUT, "   - $table: " . count($rows) . " записей\n");
}
fwrite(STDOUT, "\n");

// Удаление временного эндпоинта
ftp_delete($conn, $remoteWebScript);
ftp_close($conn);

// Импорт в локальную БД
fwrite(STDOUT, "5. Импорт в локальную БД...\n");
$localPdo = Logush\Database::connectFromEnv();
if (!$localPdo) {
    fwrite(STDERR, "Ошибка подключения к локальной БД\n");
    exit(1);
}

$tables = ['products', 'categories', 'colors', 'sizes', 'orders', 'quotes', 'users', 'settings'];

$localPdo->beginTransaction();

try {
    $localPdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    foreach ($tables as $table) {
        if (!isset($data[$table]) || empty($data[$table])) {
            fwrite(STDOUT, "   - $table: пропуск (нет данных)\n");
            continue;
        }
        
        // Очистка таблицы
        $localPdo->exec("TRUNCATE TABLE `$table`");
        
        $rows = $data[$table];
        $columns = array_keys($rows[0]);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnsList = implode(', ', array_map(fn($c) => "`$c`", $columns));
        
        $insertSql = "INSERT INTO `$table` ($columnsList) VALUES ($placeholders)";
        $stmt = $localPdo->prepare($insertSql);
        
        foreach ($rows as $row) {
            $stmt->execute(array_values($row));
        }
        
        fwrite(STDOUT, "   - $table: " . count($rows) . " записей ✓\n");
    }
    
    $localPdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $localPdo->commit();
    
    fwrite(STDOUT, "\n✓ Синхронизация завершена успешно!\n");
    
} catch (Exception $e) {
    if ($localPdo->inTransaction()) {
        $localPdo->rollBack();
    }
    fwrite(STDERR, "\nОшибка импорта: " . $e->getMessage() . "\n");
    exit(1);
}
