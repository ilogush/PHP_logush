<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Logush\DataStore;

if (PHP_SAPI !== 'cli') {
    die('This script must be run from the command line.');
}

echo "=== Создание нового администратора ===\n\n";

echo "Имя: ";
$name = trim(fgets(STDIN));

echo "Email: ";
$email = trim(fgets(STDIN));

echo "Телефон: ";
$phone = trim(fgets(STDIN));

echo "Адрес: ";
$address = trim(fgets(STDIN));

echo "Пароль: ";
$password = trim(fgets(STDIN));

if (empty($name) || empty($email) || empty($password)) {
    die("\nОшибка: Имя, email и пароль обязательны!\n");
}

$store = new DataStore(__DIR__ . '/../storage/data');
$users = $store->read('users');

// Проверка на существующий email
foreach ($users as $user) {
    if (is_array($user) && mb_strtolower($user['email'] ?? '') === mb_strtolower($email)) {
        die("\nОшибка: Пользователь с таким email уже существует!\n");
    }
}

// Генерация нового ID
$maxId = 0;
foreach ($users as $user) {
    if (is_array($user)) {
        $id = (int) ($user['id'] ?? 0);
        if ($id > $maxId) {
            $maxId = $id;
        }
    }
}
$newId = (string) ($maxId + 1);

// Создание нового пользователя
$newUser = [
    'id' => $newId,
    'name' => $name,
    'email' => $email,
    'phone' => $phone ?: '',
    'address' => $address ?: '',
    'role' => 'admin',
    'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
    'userType' => 'admin',
    'createdAt' => date('c'),
];

$users[] = $newUser;
$store->write('users', $users);

echo "\n✓ Администратор успешно создан!\n";
echo "ID: {$newId}\n";
echo "Email: {$email}\n";
echo "Имя: {$name}\n\n";
