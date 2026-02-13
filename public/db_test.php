<?php
// Временный файл для проверки подключения к БД
// УДАЛИТЬ ПОСЛЕ ПРОВЕРКИ!

require_once __DIR__ . '/../src/bootstrap.php';

echo "<h2>Проверка подключения к базе данных</h2>";
echo "<pre>";

echo "Параметры из .env:\n";
echo "DB_HOST: " . getenv('DB_HOST') . "\n";
echo "DB_PORT: " . getenv('DB_PORT') . "\n";
echo "DB_NAME: " . getenv('DB_NAME') . "\n";
echo "DB_USER: " . getenv('DB_USER') . "\n";
echo "DB_PASSWORD: " . (getenv('DB_PASSWORD') ? '***' : 'НЕ УСТАНОВЛЕН') . "\n\n";

try {
    $pdo = Logush\Database::connectFromEnv();
    
    if ($pdo) {
        echo "✅ Подключение к БД успешно!\n\n";
        
        // Проверяем таблицы
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "⚠️ База данных пустая. Нужно запустить миграции.\n";
            echo "Запустите: php scripts/migrate.php\n";
        } else {
            echo "Найдено таблиц: " . count($tables) . "\n";
            foreach ($tables as $table) {
                echo "  - {$table}\n";
            }
        }
    } else {
        echo "❌ Не удалось подключиться к БД\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<p style='color: red; font-weight: bold;'>⚠️ УДАЛИТЕ ЭТОТ ФАЙЛ ПОСЛЕ ПРОВЕРКИ!</p>";
