<?php

declare(strict_types=1);

// Скрипт для проверки соответствия локальных файлов с FTP-сервером

require_once __DIR__ . '/../src/bootstrap.php';

// FTP настройки (заполните данными из ISPmanager)
$ftpHost = getenv('FTP_HOST') ?: 'ns88.wmrs.ru';
$ftpUser = getenv('FTP_USER') ?: '';
$ftpPass = getenv('FTP_PASS') ?: '';
$ftpPort = (int)(getenv('FTP_PORT') ?: 21);
$ftpRemotePath = getenv('FTP_REMOTE_PATH') ?: '/';

if (empty($ftpUser) || empty($ftpPass)) {
    echo "❌ Ошибка: Укажите FTP_USER и FTP_PASS в .env файле\n";
    exit(1);
}

echo "🔌 Подключение к FTP: {$ftpHost}:{$ftpPort}\n";

$conn = ftp_connect($ftpHost, $ftpPort, 30);
if (!$conn) {
    echo "❌ Не удалось подключиться к FTP-серверу\n";
    exit(1);
}

if (!ftp_login($conn, $ftpUser, $ftpPass)) {
    echo "❌ Ошибка авторизации на FTP\n";
    ftp_close($conn);
    exit(1);
}

ftp_pasv($conn, true);
echo "✅ Подключение установлено\n\n";

// Функция для получения списка файлов рекурсивно
function getRemoteFiles($conn, $dir = '.', $prefix = '') {
    $files = [];
    $list = ftp_nlist($conn, $dir);
    
    if ($list === false) {
        return $files;
    }
    
    foreach ($list as $item) {
        $itemName = basename($item);
        if ($itemName === '.' || $itemName === '..') {
            continue;
        }
        
        $fullPath = $prefix . $itemName;
        $size = ftp_size($conn, $item);
        
        if ($size === -1) {
            // Это директория
            $subFiles = getRemoteFiles($conn, $item, $fullPath . '/');
            $files = array_merge($files, $subFiles);
        } else {
            $files[$fullPath] = $size;
        }
    }
    
    return $files;
}

// Функция для получения локальных файлов
function getLocalFiles($dir, $baseDir = null) {
    if ($baseDir === null) {
        $baseDir = $dir;
    }
    
    $files = [];
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $fullPath = $dir . '/' . $item;
        $relativePath = str_replace($baseDir . '/', '', $fullPath);
        
        if (is_dir($fullPath)) {
            $subFiles = getLocalFiles($fullPath, $baseDir);
            $files = array_merge($files, $subFiles);
        } else {
            $files[$relativePath] = filesize($fullPath);
        }
    }
    
    return $files;
}

echo "📂 Сканирование удаленных файлов...\n";
$remoteFiles = getRemoteFiles($conn, $ftpRemotePath);
echo "Найдено удаленных файлов: " . count($remoteFiles) . "\n\n";

echo "📂 Сканирование локальных файлов...\n";
$localDir = dirname(__DIR__) . '/public';
$localFiles = getLocalFiles($localDir);
echo "Найдено локальных файлов: " . count($localFiles) . "\n\n";

// Сравнение
$onlyLocal = [];
$onlyRemote = [];
$different = [];
$same = 0;

foreach ($localFiles as $file => $size) {
    if (!isset($remoteFiles[$file])) {
        $onlyLocal[] = $file;
    } elseif ($remoteFiles[$file] !== $size) {
        $different[] = [
            'file' => $file,
            'local' => $size,
            'remote' => $remoteFiles[$file]
        ];
    } else {
        $same++;
    }
}

foreach ($remoteFiles as $file => $size) {
    if (!isset($localFiles[$file])) {
        $onlyRemote[] = $file;
    }
}

// Вывод результатов
echo "═══════════════════════════════════════════\n";
echo "📊 РЕЗУЛЬТАТЫ СРАВНЕНИЯ\n";
echo "═══════════════════════════════════════════\n\n";

echo "✅ Одинаковые файлы: {$same}\n\n";

if (!empty($onlyLocal)) {
    echo "📤 Только локально (" . count($onlyLocal) . "):\n";
    foreach ($onlyLocal as $file) {
        echo "  - {$file}\n";
    }
    echo "\n";
}

if (!empty($onlyRemote)) {
    echo "📥 Только на сервере (" . count($onlyRemote) . "):\n";
    foreach ($onlyRemote as $file) {
        echo "  - {$file}\n";
    }
    echo "\n";
}

if (!empty($different)) {
    echo "⚠️  Различаются по размеру (" . count($different) . "):\n";
    foreach ($different as $item) {
        echo "  - {$item['file']}\n";
        echo "    Локально: {$item['local']} байт | Сервер: {$item['remote']} байт\n";
    }
    echo "\n";
}

if (empty($onlyLocal) && empty($onlyRemote) && empty($different)) {
    echo "🎉 Все файлы полностью совпадают!\n";
}

ftp_close($conn);
