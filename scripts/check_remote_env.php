<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

$ftpHost = getenv('FTP_HOST');
$ftpUser = getenv('FTP_USER');
$ftpPass = getenv('FTP_PASS');
$ftpPort = (int)(getenv('FTP_PORT') ?: 21);
$ftpRemotePath = rtrim(getenv('FTP_REMOTE_PATH') ?: '/www/logush.ru', '/');

$conn = ftp_connect($ftpHost, $ftpPort, 30);
if (!$conn || !ftp_login($conn, $ftpUser, $ftpPass)) {
    fwrite(STDERR, "Ошибка подключения к FTP\n");
    exit(1);
}

ftp_pasv($conn, true);

// Проверка наличия .env
$remoteEnvPath = $ftpRemotePath . '/.env';
$localTempEnv = sys_get_temp_dir() . '/remote_env_check.txt';

if (ftp_get($conn, $localTempEnv, $remoteEnvPath, FTP_BINARY)) {
    echo "Содержимое удаленного .env:\n";
    echo "================================\n";
    echo file_get_contents($localTempEnv);
    echo "\n================================\n";
    unlink($localTempEnv);
} else {
    echo "Файл .env не найден на удаленном сервере\n";
}

ftp_close($conn);
