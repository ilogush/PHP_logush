<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$ftpHost = getenv('FTP_HOST') ?: '';
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

function listRemoteDir($conn, $dir, $indent = '') {
    $list = @ftp_nlist($conn, $dir);
    
    if ($list === false) {
        return;
    }
    
    sort($list);
    
    foreach ($list as $item) {
        $itemName = basename($item);
        if ($itemName === '.' || $itemName === '..') {
            continue;
        }
        
        $size = @ftp_size($conn, $item);
        
        if ($size === -1) {
            echo $indent . "📁 " . $itemName . "/\n";
            if (strlen($indent) < 8) { // Ограничиваем глубину
                listRemoteDir($conn, $item, $indent . '  ');
            }
        } else {
            $sizeKb = round($size / 1024, 1);
            echo $indent . "📄 " . $itemName . " ({$sizeKb} KB)\n";
        }
    }
}

echo "📂 Структура директории: {$ftpRemotePath}\n";
echo "═══════════════════════════════════════════\n\n";

listRemoteDir($conn, $ftpRemotePath);

ftp_close($conn);
