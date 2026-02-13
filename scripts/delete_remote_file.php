<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

$ftpHost = getenv('FTP_HOST');
$ftpUser = getenv('FTP_USER');
$ftpPass = getenv('FTP_PASS');
$ftpPort = (int)(getenv('FTP_PORT') ?: 21);
$ftpRemotePath = rtrim(getenv('FTP_REMOTE_PATH') ?: '/www/logush.ru', '/');

$fileToDelete = $argv[1] ?? '';
if ($fileToDelete === '') {
    echo "Использование: php scripts/delete_remote_file.php <путь к файлу>\n";
    exit(1);
}

$conn = ftp_connect($ftpHost, $ftpPort, 30);
if (!$conn || !ftp_login($conn, $ftpUser, $ftpPass)) {
    echo "❌ Ошибка подключения к FTP\n";
    exit(1);
}

ftp_pasv($conn, true);

$remotePath = $ftpRemotePath . '/' . ltrim($fileToDelete, '/');

if (ftp_delete($conn, $remotePath)) {
    echo "✅ Файл удален: {$remotePath}\n";
} else {
    echo "❌ Не удалось удалить файл: {$remotePath}\n";
    exit(1);
}

ftp_close($conn);
