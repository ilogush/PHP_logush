<?php

declare(strict_types=1);

// Скрипт для запуска seed на удаленном сервере через HTTP

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

$remoteUrl = 'https://logush.ru/seed_remote.php';

echo "🌱 Запуск seed на удаленном сервере...\n";
echo "URL: {$remoteUrl}\n\n";

$ch = curl_init($remoteUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Ошибка: {$error}\n";
    exit(1);
}

echo "HTTP код: {$httpCode}\n";
echo "Ответ:\n";
echo "================================\n";
echo $response;
echo "\n================================\n";

if ($httpCode === 200) {
    echo "\n✅ Seed успешно выполнен!\n";
} else {
    echo "\n❌ Ошибка выполнения seed\n";
    exit(1);
}
