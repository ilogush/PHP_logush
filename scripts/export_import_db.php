<?php

declare(strict_types=1);

/**
 * Простой скрипт для экспорта/импорта данных БД через JSON
 * 
 * Использование:
 * php scripts/export_import_db.php export > dump.json  # На удаленном сервере
 * php scripts/export_import_db.php import < dump.json  # Локально
 */

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

$action = $argv[1] ?? 'export';

$pdo = Logush\Database::connectFromEnv();
if (!$pdo) {
    fwrite(STDERR, "Ошибка подключения к БД\n");
    exit(1);
}

$tables = [
    'products',
    'categories',
    'colors',
    'sizes',
    'orders',
    'quotes',
    'users',
    'settings',
];

if ($action === 'export') {
    $data = [];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data[$table] = $rows;
        fwrite(STDERR, "Exported $table: " . count($rows) . " rows\n");
    }
    
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} elseif ($action === 'import') {
    $json = file_get_contents('php://stdin');
    $data = json_decode($json, true);
    
    if (!$data) {
        fwrite(STDERR, "Ошибка: Неверный формат JSON\n");
        exit(1);
    }
    
    $pdo->beginTransaction();
    
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        foreach ($tables as $table) {
            if (!isset($data[$table])) {
                fwrite(STDERR, "Пропуск $table: нет данных\n");
                continue;
            }
            
            fwrite(STDERR, "Импорт $table...\n");
            
            // Очистка таблицы
            $pdo->exec("TRUNCATE TABLE `$table`");
            
            $rows = $data[$table];
            if (empty($rows)) {
                fwrite(STDERR, "  Таблица пуста\n");
                continue;
            }
            
            // Подготовка INSERT
            $columns = array_keys($rows[0]);
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $columnsList = implode(', ', array_map(fn($c) => "`$c`", $columns));
            
            $insertSql = "INSERT INTO `$table` ($columnsList) VALUES ($placeholders)";
            $stmt = $pdo->prepare($insertSql);
            
            foreach ($rows as $row) {
                $stmt->execute(array_values($row));
            }
            
            fwrite(STDERR, "  Импортировано: " . count($rows) . " записей\n");
        }
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        $pdo->commit();
        
        fwrite(STDERR, "\n✓ Импорт завершен успешно!\n");
        
    } catch (Exception $e) {
        $pdo->rollBack();
        fwrite(STDERR, "Ошибка: " . $e->getMessage() . "\n");
        exit(1);
    }
    
} else {
    fwrite(STDERR, "Использование:\n");
    fwrite(STDERR, "  php scripts/export_import_db.php export > dump.json\n");
    fwrite(STDERR, "  php scripts/export_import_db.php import < dump.json\n");
    exit(1);
}
