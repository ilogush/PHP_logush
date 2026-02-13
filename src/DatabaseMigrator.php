<?php

declare(strict_types=1);

namespace Logush;

use PDO;

final class DatabaseMigrator
{
    public static function migrate(PDO $pdo): void
    {
        $statements = [
            "CREATE TABLE IF NOT EXISTS products (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                category VARCHAR(191) NOT NULL,
                main_category VARCHAR(191) NOT NULL,
                article VARCHAR(64) NOT NULL DEFAULT '',
                price DECIMAL(12,2) NOT NULL DEFAULT 0,
                stock INT NOT NULL DEFAULT 0,
                in_stock TINYINT(1) NOT NULL DEFAULT 1,
                images_json LONGTEXT NOT NULL,
                colors_json LONGTEXT NOT NULL,
                sizes_json LONGTEXT NOT NULL,
                description TEXT NOT NULL,
                material VARCHAR(255) NOT NULL,
                care_json LONGTEXT NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NULL,
                PRIMARY KEY (id),
                KEY idx_products_article (article),
                KEY idx_products_category (category)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS categories (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(191) NOT NULL,
                parent_id INT UNSIGNED NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_categories_name (name),
                KEY idx_categories_parent (parent_id),
                CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id)
                    REFERENCES categories (id)
                    ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS colors (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(191) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_colors_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS sizes (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(191) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_sizes_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS orders (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                customer_name VARCHAR(191) NOT NULL,
                customer_email VARCHAR(191) NOT NULL,
                customer_phone VARCHAR(64) NOT NULL,
                items_json LONGTEXT NOT NULL,
                total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                status VARCHAR(32) NOT NULL,
                delivery_address TEXT NOT NULL,
                payment_method VARCHAR(128) NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY idx_orders_status (status),
                KEY idx_orders_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS quotes (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(191) NOT NULL,
                email VARCHAR(191) NOT NULL,
                phone VARCHAR(64) NOT NULL,
                message TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY idx_quotes_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(191) NOT NULL,
                email VARCHAR(191) NOT NULL,
                phone VARCHAR(64) NOT NULL,
                address VARCHAR(255) NOT NULL,
                role VARCHAR(64) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                user_type VARCHAR(64) NOT NULL DEFAULT 'admin',
                created_at DATETIME NOT NULL,
                updated_at DATETIME NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_users_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS settings (
                id TINYINT UNSIGNED NOT NULL,
                data_json LONGTEXT NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];

        foreach ($statements as $sql) {
            $pdo->exec($sql);
        }

        // Add columns for older installs (CREATE TABLE IF NOT EXISTS does not modify existing tables).
        self::ensureColumn($pdo, 'products', 'article', "ALTER TABLE products ADD COLUMN article VARCHAR(64) NOT NULL DEFAULT '' AFTER main_category");
        self::ensureColumn($pdo, 'products', 'stock', "ALTER TABLE products ADD COLUMN stock INT NOT NULL DEFAULT 0 AFTER price");
        self::ensureColumn($pdo, 'products', 'in_stock', "ALTER TABLE products ADD COLUMN in_stock TINYINT(1) NOT NULL DEFAULT 1 AFTER stock");
        self::ensureColumn($pdo, 'products', 'updated_at', "ALTER TABLE products ADD COLUMN updated_at DATETIME NULL AFTER created_at");
        self::ensureIndex($pdo, 'products', 'idx_products_article', "CREATE INDEX idx_products_article ON products(article)");

        self::ensureColumn($pdo, 'users', 'updated_at', "ALTER TABLE users ADD COLUMN updated_at DATETIME NULL AFTER created_at");
    }

    private static function ensureColumn(PDO $pdo, string $table, string $column, string $sql): void
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE " . $pdo->quote($column));
        if (!$stmt || !$stmt->fetch()) {
            $pdo->exec($sql);
        }
    }

    private static function ensureIndex(PDO $pdo, string $table, string $index, string $sql): void
    {
        $stmt = $pdo->query("SHOW INDEX FROM `$table` WHERE Key_name = " . $pdo->quote($index));
        if (!$stmt || !$stmt->fetch()) {
            $pdo->exec($sql);
        }
    }
}
