<?php

declare(strict_types=1);

// Скрипт для загрузки файлов на FTP-сервер

require_once __DIR__ . '/../src/bootstrap.php';

// FTP настройки
$ftpHost = getenv('FTP_HOST') ?: '62.173.139.159';
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

// Increase timeouts for shared hosting.
ftp_set_option($conn, FTP_TIMEOUT_SEC, 60);

// Функция для создания директории рекурсивно
function ftpMkdir($conn, $dir) {
    $parts = explode('/', $dir);
    $path = '';
    
    foreach ($parts as $part) {
        if (empty($part)) continue;
        
        $path .= '/' . $part;
        if (!@ftp_chdir($conn, $path)) {
            if (!@ftp_mkdir($conn, $path)) {
                return false;
            }
            ftp_chdir($conn, $path);
        }
    }
    
    return true;
}

function uploadFileIfChanged($conn, string $localPath, string $remotePath, int &$uploaded, int &$failed): void
{
    if (!is_file($localPath)) {
        return;
    }

    $localSize = filesize($localPath);
    $remoteSize = ftp_size($conn, $remotePath);

    if ($remoteSize !== -1 && $localSize !== false && (int) $remoteSize === (int) $localSize) {
        return;
    }

    $remoteDir = dirname($remotePath);
    ftpMkdir($conn, $remoteDir);

    echo "📤 Загрузка: " . basename($localPath);
    if (@ftp_put($conn, $remotePath, $localPath, FTP_BINARY)) {
        echo " ✅\n";
        $uploaded++;
        return;
    }

    echo " ❌\n";
    $failed++;
}

// Функция для загрузки файлов рекурсивно
function uploadDirectory($conn, $localDir, $remoteDir, &$uploaded, &$failed) {
    $items = scandir($localDir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $localPath = $localDir . '/' . $item;
        $remotePath = $remoteDir . '/' . $item;

        // Не загружаем runtime-данные и логи.
        if (str_contains($localPath, '/storage/data/') || str_contains($localPath, '/storage/uploads/')) {
            continue;
        }
        if (basename($localPath) === 'php_errors.log') {
            continue;
        }
        
        if (is_dir($localPath)) {
            echo "📁 Создание директории: {$remotePath}\n";
            ftpMkdir($conn, $remotePath);
            uploadDirectory($conn, $localPath, $remotePath, $uploaded, $failed);
        } else {
            $localSize = filesize($localPath);
            $remoteSize = ftp_size($conn, $remotePath);

            // Загружаем только если файла нет на сервере или отличается по размеру.
            if ($remoteSize !== -1 && $localSize !== false && (int) $remoteSize === (int) $localSize) {
                continue;
            }

            echo "📤 Загрузка: {$item}";
            if (ftp_put($conn, $remotePath, $localPath, FTP_BINARY)) {
                echo " ✅\n";
                $uploaded++;
            } else {
                echo " ❌\n";
                $failed++;
            }
        }
    }
}

$projectDir = dirname(__DIR__);
$remoteDir = rtrim($ftpRemotePath, '/');

echo "📂 Начало загрузки проекта из: {$projectDir}\n";
echo "📂 На сервер в: {$remoteDir}\n\n";

$uploaded = 0;
$failed = 0;

// If file paths are passed as args, upload only those (relative to project root).
// Example: php scripts/ftp_upload.php views/pages/cart.php views/pages/checkout.php
global $argv;
if (is_array($argv) && count($argv) > 1) {
    foreach (array_slice($argv, 1) as $rel) {
        $rel = ltrim((string) $rel, '/');
        if ($rel === '' || str_contains($rel, '..')) {
            continue;
        }
        $localPath = $projectDir . '/' . $rel;
        $remotePath = $remoteDir . '/' . $rel;
        uploadFileIfChanged($conn, $localPath, $remotePath, $uploaded, $failed);
    }

    echo "\n═══════════════════════════════════════════\n";
    echo "📊 РЕЗУЛЬТАТЫ ЗАГРУЗКИ\n";
    echo "═══════════════════════════════════════════\n";
    echo "✅ Загружено: {$uploaded} файлов\n";
    echo "❌ Ошибок: {$failed} файлов\n";
    ftp_close($conn);
    exit($failed === 0 ? 0 : 1);
}

// Загружаем все необходимые папки
$folders = ['public', 'src', 'views', 'scripts'];

foreach ($folders as $folder) {
    $localPath = $projectDir . '/' . $folder;
    $remotePath = $remoteDir . '/' . $folder;
    
    if (is_dir($localPath)) {
        echo "\n📁 Загрузка папки: {$folder}\n";
        ftpMkdir($conn, $remotePath);
        uploadDirectory($conn, $localPath, $remotePath, $uploaded, $failed);
    }
}

// Загружаем корневые файлы
$rootFiles = ['.htaccess', '.env'];
foreach ($rootFiles as $file) {
    $localPath = $projectDir . '/' . $file;
    $remotePath = $remoteDir . '/' . $file;
    
    if (file_exists($localPath)) {
        // .env всегда грузим (даже если размер совпал), потому что там могут меняться пароли.
        if ($file !== '.env') {
            $localSize = filesize($localPath);
            $remoteSize = ftp_size($conn, $remotePath);
            if ($remoteSize !== -1 && $localSize !== false && (int) $remoteSize === (int) $localSize) {
                continue;
            }
        }

        echo "📤 Загрузка: {$file}";
        if (ftp_put($conn, $remotePath, $localPath, FTP_BINARY)) {
            echo " ✅\n";
            $uploaded++;
        } else {
            echo " ❌\n";
            $failed++;
        }
    }
}

echo "\n═══════════════════════════════════════════\n";
echo "📊 РЕЗУЛЬТАТЫ ЗАГРУЗКИ\n";
echo "═══════════════════════════════════════════\n";
echo "✅ Загружено: {$uploaded} файлов\n";
echo "❌ Ошибок: {$failed} файлов\n";

if ($failed === 0) {
    echo "\n🎉 Все файлы успешно загружены!\n";
}

ftp_close($conn);
