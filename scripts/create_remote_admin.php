<?php

declare(strict_types=1);

// Скрипт для создания администратора в удаленной БД

if (PHP_SAPI !== 'cli') {
    die('This script must be run from the command line.');
}

$baseDir = dirname(__DIR__);

// Загружаем переменные окружения из .env.remote
$envFile = $baseDir . '/.env.remote';
if (!file_exists($envFile)) {
    die("❌ Файл .env.remote не найден!\n");
}

$envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($envLines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }
    if (str_contains($line, '=')) {
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
        $_ENV[trim($key)] = trim($value);
    }
}

$host = getenv('REMOTE_DB_HOST') ?: '';
$port = getenv('REMOTE_DB_PORT') ?: '3306';
$dbname = getenv('REMOTE_DB_NAME') ?: '';
$user = getenv('REMOTE_DB_USER') ?: '';
$password = getenv('REMOTE_DB_PASSWORD') ?: '';

if (empty($host) || empty($dbname) || empty($user) || empty($password)) {
    die("❌ Ошибка: Заполните параметры подключения в .env.remote\n");
}

if ($dbname === 'change_me' || $user === 'change_me' || $password === 'change_me') {
    die("❌ Ошибка: Замените значения 'change_me' в .env.remote на реальные данные\n");
}

echo "=== Создание администратора в удаленной БД ===\n\n";

// Получаем данные из аргументов командной строки
if ($argc >= 3) {
    $email = $argv[1];
    $phone = $argv[2];
    $name = $argv[3] ?? 'Admin';
    $address = $argv[4] ?? '';
    $password_plain = $argv[5] ?? $phone; // По умолчанию пароль = телефон
} else {
    echo "Использование: php scripts/create_remote_admin.php <email> <phone> [name] [address] [password]\n";
    echo "Пример: php scripts/create_remote_admin.php admin@example.com 89156352098 \"Admin Name\" \"Address\" \"password123\"\n\n";
    
    echo "Email: ";
    $email = trim(fgets(STDIN));
    
    echo "Телефон: ";
    $phone = trim(fgets(STDIN));
    
    echo "Имя (по умолчанию 'Admin'): ";
    $name = trim(fgets(STDIN));
    if (empty($name)) {
        $name = 'Admin';
    }
    
    echo "Адрес (необязательно): ";
    $address = trim(fgets(STDIN));
    
    echo "Пароль (по умолчанию = телефон): ";
    $password_plain = trim(fgets(STDIN));
    if (empty($password_plain)) {
        $password_plain = $phone;
    }
}

if (empty($email) || empty($phone)) {
    die("\n❌ Ошибка: Email и телефон обязательны!\n");
}

echo "\nПодключение к удаленной БД...\n";
echo "Host: {$host}\n";
echo "Database: {$dbname}\n";
echo "User: {$user}\n\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✓ Подключение установлено\n\n";
    
    // Проверяем существование пользователя
    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
    $stmt->execute([mb_strtolower($email)]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        die("❌ Ошибка: Пользователь с email '{$email}' уже существует (ID: {$existing['id']})\n");
    }
    
    // Создаем нового администратора
    $passwordHash = password_hash($password_plain, PASSWORD_DEFAULT);
    $createdAt = date('Y-m-d H:i:s');
    
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, phone, address, role, password_hash, user_type, created_at)
        VALUES (?, ?, ?, ?, 'admin', ?, 'admin', ?)
    ");
    
    $stmt->execute([
        $name,
        $email,
        $phone,
        $address,
        $passwordHash,
        $createdAt
    ]);
    
    $newId = $pdo->lastInsertId();
    
    echo "✅ Администратор успешно создан в удаленной БД!\n\n";
    echo "ID: {$newId}\n";
    echo "Email: {$email}\n";
    echo "Имя: {$name}\n";
    echo "Телефон: {$phone}\n";
    echo "Адрес: {$address}\n";
    echo "Пароль: {$password_plain}\n\n";
    
} catch (PDOException $e) {
    die("❌ Ошибка БД: " . $e->getMessage() . "\n");
}
