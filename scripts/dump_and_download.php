<?php

declare(strict_types=1);

/**
 * Скрипт для создания дампа удаленной БД и скачивания его по FTP
 * 
 * Шаг 1: Загружает скрипт создания дампа на удаленный сервер
 * Шаг 2: Выполняет его через HTTP запрос
 * Шаг 3: Скачивает созданный дамп
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

// Загрузка параметров удаленной БД
$remoteEnvFile = $baseDir . '/.env.remote';
if (!is_file($remoteEnvFile)) {
    fwrite(STDERR, "Ошибка: Создайте файл .env.remote с параметрами удаленной БД\n");
    exit(1);
}

$lines = file($remoteEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$remoteDbConfig = [];
foreach ($lines as $line) {
    $trimmed = trim($line);
    if ($trimmed === '' || str_starts_with($trimmed, '#')) {
        continue;
    }
    $parts = explode('=', $trimmed, 2);
    if (count($parts) === 2) {
        $key = trim($parts[0]);
        $value = trim($parts[1], "\"'");
        $remoteDbConfig[$key] = $value;
    }
}

$remoteHost = $remoteDbConfig['REMOTE_DB_HOST'] ?? 'localhost';
$remoteDb = $remoteDbConfig['REMOTE_DB_NAME'] ?? '';
$remoteUser = $remoteDbConfig['REMOTE_DB_USER'] ?? '';
$remotePass = $remoteDbConfig['REMOTE_DB_PASSWORD'] ?? '';

if (empty($remoteDb) || empty($remoteUser)) {
    fwrite(STDERR, "Ошибка: Укажите REMOTE_DB_NAME и REMOTE_DB_USER в .env.remote\n");
    exit(1);
}

// Создание скрипта для выполнения на удаленном сервере
$remoteScript = <<<'PHP'
<?php
// Временный скрипт для создания дампа БД
error_reporting(0);
header('Content-Type: text/plain; charset=utf-8');

$dbHost = 'DB_HOST_PLACEHOLDER';
$dbName = 'DB_NAME_PLACEHOLDER';
$dbUser = 'DB_USER_PLACEHOLDER';
$dbPass = 'DB_PASS_PLACEHOLDER';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $dumpFile = __DIR__ . '/db_dump_' . date('YmdHis') . '.sql';
    $fp = fopen($dumpFile, 'w');
    
    fwrite($fp, "-- Database dump created at " . date('Y-m-d H:i:s') . "\n");
    fwrite($fp, "SET FOREIGN_KEY_CHECKS = 0;\n\n");
    
    // Получение списка таблиц
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        fwrite($fp, "-- Table: $table\n");
        fwrite($fp, "DROP TABLE IF EXISTS `$table`;\n");
        
        // Структура таблицы
        $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch();
        fwrite($fp, $createTable[1] . ";\n\n");
        
        // Данные таблицы
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $columnsList = implode('`, `', $columns);
            
            foreach ($rows as $row) {
                $values = array_map(function($v) use ($pdo) {
                    return $v === null ? 'NULL' : $pdo->quote($v);
                }, array_values($row));
                
                fwrite($fp, "INSERT INTO `$table` (`$columnsList`) VALUES (" . implode(', ', $values) . ");\n");
            }
            fwrite($fp, "\n");
        }
    }
    
    fwrite($fp, "SET FOREIGN_KEY_CHECKS = 1;\n");
    fclose($fp);
    
    echo "SUCCESS: $dumpFile";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
    exit(1);
}
PHP;

$remoteScript = str_replace('DB_HOST_PLACEHOLDER', $remoteHost, $remoteScript);
$remoteScript = str_replace('DB_NAME_PLACEHOLDER', $remoteDb, $remoteScript);
$remoteScript = str_replace('DB_USER_PLACEHOLDER', $remoteUser, $remoteScript);
$remoteScript = str_replace('DB_PASS_PLACEHOLDER', $remotePass, $remoteScript);

// Подключение к FTP
fwrite(STDOUT, "Подключение к FTP ($ftpHost)...\n");
$conn = ftp_connect($ftpHost, $ftpPort, 30);
if (!$conn) {
    fwrite(STDERR, "Ошибка подключения к FTP\n");
    exit(1);
}

if (!ftp_login($conn, $ftpUser, $ftpPass)) {
    fwrite(STDERR, "Ошибка авторизации на FTP\n");
    ftp_close($conn);
    exit(1);
}

ftp_pasv($conn, true);
fwrite(STDOUT, "✓ Подключение установлено\n");

// Загрузка скрипта на сервер
$localScriptPath = sys_get_temp_dir() . '/remote_dump.php';
file_put_contents($localScriptPath, $remoteScript);

$remoteScriptPath = rtrim(str_replace('/public_html', '', $ftpRemotePath), '/') . '/dump_db_temp.php';
fwrite(STDOUT, "Загрузка скрипта на сервер...\n");

if (!ftp_put($conn, $remoteScriptPath, $localScriptPath, FTP_BINARY)) {
    fwrite(STDERR, "Ошибка загрузки скрипта\n");
    ftp_close($conn);
    unlink($localScriptPath);
    exit(1);
}

unlink($localScriptPath);
fwrite(STDOUT, "✓ Скрипт загружен\n");

// Выполнение скрипта через HTTP
fwrite(STDOUT, "Создание дампа БД на удаленном сервере...\n");
$url = 'http://logush.ru/dump_database_temp.php';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 300);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !str_starts_with($response, 'SUCCESS:')) {
    fwrite(STDERR, "Ошибка создания дампа: $response\n");
    ftp_delete($conn, $remoteScriptPath);
    ftp_close($conn);
    exit(1);
}

$dumpFileName = trim(str_replace('SUCCESS:', '', $response));
$dumpFileName = basename($dumpFileName);
fwrite(STDOUT, "✓ Дамп создан: $dumpFileName\n");

// Скачивание дампа
$localDumpPath = $baseDir . '/storage/data/' . $dumpFileName;
fwrite(STDOUT, "Скачивание дампа...\n");

$remoteDumpPath = $ftpRemotePath . '/' . $dumpFileName;
if (!ftp_get($conn, $localDumpPath, $remoteDumpPath, FTP_BINARY)) {
    fwrite(STDERR, "Ошибка скачивания дампа\n");
    ftp_delete($conn, $remoteScriptPath);
    ftp_close($conn);
    exit(1);
}

fwrite(STDOUT, "✓ Дамп скачан: $localDumpPath\n");

// Удаление временных файлов с сервера
ftp_delete($conn, $remoteScriptPath);
ftp_delete($conn, $remoteDumpPath);
ftp_close($conn);

// Импорт в локальную БД
fwrite(STDOUT, "\nИмпорт в локальную БД...\n");
$localPdo = Logush\Database::connectFromEnv();
if (!$localPdo) {
    fwrite(STDERR, "Ошибка подключения к локальной БД\n");
    exit(1);
}

$sql = file_get_contents($localDumpPath);
if ($sql === false) {
    fwrite(STDERR, "Ошибка чтения файла дампа\n");
    exit(1);
}

try {
    $localPdo->exec($sql);
    fwrite(STDOUT, "✓ Импорт завершен успешно!\n");
    fwrite(STDOUT, "\nДамп сохранен в: $localDumpPath\n");
} catch (PDOException $e) {
    fwrite(STDERR, "Ошибка импорта: " . $e->getMessage() . "\n");
    exit(1);
}
