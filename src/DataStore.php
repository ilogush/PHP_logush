<?php

declare(strict_types=1);

namespace Logush;

use DateTimeImmutable;
use DateTimeZone;
use PDO;
use Throwable;

final class DataStore
{
    private string $dataDir;
    private string $uploadDir;
    private ?PDO $pdo;
    private ?bool $dbReady = null;
    private array $cache = []; // In-memory cache for read operations

    public function __construct(private readonly string $baseDir)
    {
        $this->dataDir = $this->baseDir . '/storage/data';
        $this->uploadDir = $this->baseDir . '/storage/uploads';
        $this->pdo = Database::connectFromEnv();
        if (!$this->pdo) {
            // Do not silently fall back to local JSON storage.
            $this->dbReady = false;
            throw new \RuntimeException('Database connection failed. Check DB_* variables in .env.');
        }

        try {
            $this->maybeMigrateDatabase();
            $this->dbReady = null;
        } catch (Throwable $e) {
            $this->dbReady = false;
            throw new \RuntimeException('Database migrations failed. Check DB user permissions.', 0, $e);
        }
    }

    private function maybeMigrateDatabase(): void
    {
        $stampPath = $this->baseDir . '/storage/.db_schema_version';
        $expected = (string) DatabaseMigrator::SCHEMA_VERSION;

        $current = '';
        if (is_file($stampPath)) {
            $raw = file_get_contents($stampPath);
            if (is_string($raw)) {
                $current = trim($raw);
            }
        }

        $force = filter_var((string) getenv('APP_RUN_MIGRATIONS'), FILTER_VALIDATE_BOOLEAN);
        $needs = ($current !== $expected);

        // On shared hosting we still want a working first deploy even without CLI access:
        // run migrations once when stamp is missing/outdated, then stamp the version.
        if (!$force && !$needs) {
            return;
        }

        DatabaseMigrator::migrate($this->pdo);

        // Best-effort stamp write; do not crash the app if FS is read-only.
        @file_put_contents($stampPath, $expected);
    }

    public function pdo(): ?PDO
    {
        return $this->pdo;
    }

    public function usesDatabase(): bool
    {
        return $this->isDbReady();
    }

    public function seed(): void
    {
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0775, true);
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        $now = gmdate('c');
        $defaults = $this->defaultCollections($now);

        // Only seed DB. Local JSON storage is not used in this project.
        $this->seedDatabase($defaults);
    }

    public function read(string $name): array
    {
        // Check cache first (in-memory for request lifecycle)
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $this->assertDbReady();

        $data = match ($name) {
            'products' => $this->readProductsFromDb(),
            'categories' => $this->readCategoriesFromDb(),
            'colors' => $this->readNamedFromDb('colors'),
            'sizes' => $this->readNamedFromDb('sizes'),
            'orders' => $this->readOrdersFromDb(),
            'quotes' => $this->readQuotesFromDb(),
            'users' => $this->readUsersFromDb(),
            'settings' => $this->readSettingsFromDb(),
            default => [],
        };

        // Cache the result
        $this->cache[$name] = $data;
        return $data;
    }

    public function write(string $name, array $data): void
    {
        $this->assertDbReady();

        match ($name) {
            'products' => $this->replaceProductsInDb($data),
            'categories' => $this->replaceCategoriesInDb($data),
            'colors' => $this->replaceNamedInDb('colors', $data),
            'sizes' => $this->replaceNamedInDb('sizes', $data),
            'orders' => $this->replaceOrdersInDb($data),
            'quotes' => $this->replaceQuotesInDb($data),
            'users' => $this->replaceUsersInDb($data),
            'settings' => $this->replaceSettingsInDb($data),
            default => null,
        };

        // Invalidate cache after write
        unset($this->cache[$name]);
    }

    public function nextId(string $name): string
    {
        $this->assertDbReady();

        $table = match ($name) {
            'products' => 'products',
            'categories' => 'categories',
            'colors' => 'colors',
            'sizes' => 'sizes',
            'orders' => 'orders',
            'quotes' => 'quotes',
            'users' => 'users',
            default => '',
        };

        if ($table === '') {
            return '1';
        }

        $stmt = $this->pdo->query('SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM ' . $table);
        $row = $stmt ? $stmt->fetch() : null;
        $next = is_array($row) ? (int) ($row['next_id'] ?? 1) : 1;
        return (string) max(1, $next);
    }

    private function assertDbReady(): void
    {
        if (!$this->isDbReady()) {
            throw new \RuntimeException('Database is not ready. Local JSON storage is disabled.');
        }
    }

    public function uploadsPath(string $relative = ''): string
    {
        if ($relative === '') {
            return $this->uploadDir;
        }
        return $this->uploadDir . '/' . ltrim($relative, '/');
    }

    private function seedFiles(array $defaults): void
    {
        foreach ($defaults as $name => $value) {
            $this->seedFile($name, $value);
        }
    }

    private function seedDatabase(array $defaults): void
    {
        $this->assertDbReady();

        if ($this->tableCount('products') === 0) {
            $source = $this->readFile('products');
            $this->replaceProductsInDb($this->isList($source) ? $source : $defaults['products']);
        }

        if ($this->tableCount('categories') === 0) {
            $source = $this->readFile('categories');
            $this->replaceCategoriesInDb($this->isList($source) ? $source : $defaults['categories']);
        }

        if ($this->tableCount('colors') === 0) {
            $source = $this->readFile('colors');
            $this->replaceNamedInDb('colors', $this->isList($source) ? $source : $defaults['colors']);
        }

        if ($this->tableCount('sizes') === 0) {
            $source = $this->readFile('sizes');
            $this->replaceNamedInDb('sizes', $this->isList($source) ? $source : $defaults['sizes']);
        }

        if ($this->tableCount('orders') === 0) {
            $source = $this->readFile('orders');
            $this->replaceOrdersInDb($this->isList($source) ? $source : $defaults['orders']);
        }

        if ($this->tableCount('quotes') === 0) {
            $source = $this->readFile('quotes');
            $this->replaceQuotesInDb($this->isList($source) ? $source : $defaults['quotes']);
        }

        if ($this->tableCount('users') === 0) {
            $source = $this->readFile('users');
            $this->replaceUsersInDb($this->isList($source) ? $source : $defaults['users']);
        }

        if ($this->tableCount('settings') === 0) {
            $source = $this->readFile('settings');
            $payload = $this->isAssoc($source) ? $source : $defaults['settings'];
            $this->replaceSettingsInDb($payload);
        }
    }

    private function readProductsFromDb(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM products ORDER BY id ASC');
        $rows = $stmt ? $stmt->fetchAll() : [];
        $result = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $result[] = [
                'id' => (string) ($row['id'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'category' => (string) ($row['category'] ?? ''),
                'mainCategory' => (string) ($row['main_category'] ?? ''),
                'article' => (string) ($row['article'] ?? ''),
                'price' => (float) ($row['price'] ?? 0),
                'stock' => (int) ($row['stock'] ?? 0),
                'inStock' => ((int) ($row['in_stock'] ?? 1)) === 1,
                'images' => $this->decodeList((string) ($row['images_json'] ?? '[]')),
                'colors' => $this->decodeList((string) ($row['colors_json'] ?? '[]')),
                'sizes' => $this->decodeList((string) ($row['sizes_json'] ?? '[]')),
                'description' => (string) ($row['description'] ?? ''),
                'material' => (string) ($row['material'] ?? ''),
                'care' => $this->decodeList((string) ($row['care_json'] ?? '[]')),
                'createdAt' => $this->toIsoDate((string) ($row['created_at'] ?? '')),
                'updatedAt' => $this->toIsoDate((string) (($row['updated_at'] ?? '') ?: ($row['created_at'] ?? ''))),
            ];
        }

        return $result;
    }

    private function readCategoriesFromDb(): array
    {
        $sql = 'SELECT c.id, c.name, c.parent_id, p.name AS parent_name
                FROM categories c
                LEFT JOIN categories p ON p.id = c.parent_id
                ORDER BY c.id ASC';
        $stmt = $this->pdo->query($sql);
        $rows = $stmt ? $stmt->fetchAll() : [];
        $result = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $parentId = $row['parent_id'] !== null ? (string) $row['parent_id'] : null;
            $result[] = [
                'id' => (string) ($row['id'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'parentId' => $parentId,
                'parentName' => $row['parent_name'] !== null ? (string) $row['parent_name'] : null,
            ];
        }

        return $result;
    }

    private function readNamedFromDb(string $table): array
    {
        $stmt = $this->pdo->query('SELECT id, name FROM ' . $table . ' ORDER BY id ASC');
        $rows = $stmt ? $stmt->fetchAll() : [];
        $result = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $result[] = [
                'id' => (string) ($row['id'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
            ];
        }

        return $result;
    }

    private function readOrdersFromDb(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM orders ORDER BY id DESC');
        $rows = $stmt ? $stmt->fetchAll() : [];
        $result = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $itemsDecoded = json_decode((string) ($row['items_json'] ?? '[]'), true);
            $items = is_array($itemsDecoded) ? $itemsDecoded : [];

            $result[] = [
                'id' => (string) ($row['id'] ?? ''),
                'customerName' => (string) ($row['customer_name'] ?? ''),
                'customerEmail' => (string) ($row['customer_email'] ?? ''),
                'customerPhone' => (string) ($row['customer_phone'] ?? ''),
                'items' => $items,
                'totalAmount' => (float) ($row['total_amount'] ?? 0),
                'status' => (string) ($row['status'] ?? 'new'),
                'deliveryAddress' => (string) ($row['delivery_address'] ?? ''),
                'paymentMethod' => (string) ($row['payment_method'] ?? ''),
                'createdAt' => $this->toIsoDate((string) ($row['created_at'] ?? '')),
            ];
        }

        return $result;
    }

    private function readQuotesFromDb(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM quotes ORDER BY id DESC');
        $rows = $stmt ? $stmt->fetchAll() : [];
        $result = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $result[] = [
                'id' => (string) ($row['id'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'email' => (string) ($row['email'] ?? ''),
                'phone' => (string) ($row['phone'] ?? ''),
                'message' => (string) ($row['message'] ?? ''),
                'createdAt' => $this->toIsoDate((string) ($row['created_at'] ?? '')),
            ];
        }

        return $result;
    }

    private function readUsersFromDb(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users ORDER BY id ASC');
        $rows = $stmt ? $stmt->fetchAll() : [];
        $result = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $result[] = [
                'id' => (string) ($row['id'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'email' => (string) ($row['email'] ?? ''),
                'phone' => (string) ($row['phone'] ?? ''),
                'address' => (string) ($row['address'] ?? ''),
                'role' => (string) ($row['role'] ?? 'admin'),
                'passwordHash' => (string) ($row['password_hash'] ?? ''),
                'userType' => (string) ($row['user_type'] ?? 'admin'),
                'createdAt' => $this->toIsoDate((string) ($row['created_at'] ?? '')),
                'updatedAt' => $this->toIsoDate((string) (($row['updated_at'] ?? '') ?: ($row['created_at'] ?? ''))),
            ];
        }

        return $result;
    }

    private function readSettingsFromDb(): array
    {
        $stmt = $this->pdo->prepare('SELECT data_json FROM settings WHERE id = 1');
        $stmt->execute();
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return [];
        }

        $decoded = json_decode((string) ($row['data_json'] ?? '{}'), true);
        return is_array($decoded) ? $decoded : [];
    }

    private function replaceProductsInDb(array $rows): void
    {
        $pdo = $this->pdo;
        $pdo->beginTransaction();
        try {
            $pdo->exec('DELETE FROM products');
            $stmt = $pdo->prepare('INSERT INTO products
                (id, name, category, main_category, article, price, stock, in_stock, images_json, colors_json, sizes_json, description, material, care_json, created_at, updated_at)
                VALUES
                (:id, :name, :category, :main_category, :article, :price, :stock, :in_stock, :images_json, :colors_json, :sizes_json, :description, :material, :care_json, :created_at, :updated_at)');

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $createdAt = $this->toDbDate($row['createdAt'] ?? null);
                $updatedAt = $this->toDbDate($row['updatedAt'] ?? null);
                if ($updatedAt === '') {
                    $updatedAt = $createdAt;
                }

                $stmt->execute([
                    ':id' => max(1, (int) ($row['id'] ?? 0)),
                    ':name' => trim((string) ($row['name'] ?? 'Новый товар')),
                    ':category' => trim((string) ($row['category'] ?? 'Свитера')),
                    ':main_category' => trim((string) ($row['mainCategory'] ?? 'ЖЕНСКАЯ')),
                    ':article' => trim((string) ($row['article'] ?? '')),
                    ':price' => (float) ($row['price'] ?? 0),
                    ':stock' => (int) ($row['stock'] ?? 0),
                    ':in_stock' => (!isset($row['inStock']) || (bool) $row['inStock']) ? 1 : 0,
                    ':images_json' => $this->encodeJson($this->normalizeStringList($row['images'] ?? [])),
                    ':colors_json' => $this->encodeJson($this->normalizeStringList($row['colors'] ?? [])),
                    ':sizes_json' => $this->encodeJson($this->normalizeStringList($row['sizes'] ?? [])),
                    ':description' => trim((string) ($row['description'] ?? '')),
                    ':material' => trim((string) ($row['material'] ?? '')),
                    ':care_json' => $this->encodeJson($this->normalizeStringList($row['care'] ?? [])),
                    ':created_at' => $createdAt,
                    ':updated_at' => $updatedAt,
                ]);
            }

            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function replaceCategoriesInDb(array $rows): void
    {
        $pdo = $this->pdo;
        $pdo->beginTransaction();
        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
            $pdo->exec('DELETE FROM categories');

            usort($rows, static function (mixed $a, mixed $b): int {
                $aParent = is_array($a) ? ($a['parentId'] ?? null) : null;
                $bParent = is_array($b) ? ($b['parentId'] ?? null) : null;
                if ($aParent === null && $bParent !== null) {
                    return -1;
                }
                if ($aParent !== null && $bParent === null) {
                    return 1;
                }
                return 0;
            });

            $stmt = $pdo->prepare('INSERT INTO categories (id, name, parent_id) VALUES (:id, :name, :parent_id)');
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $parentId = $row['parentId'] ?? null;
                $parentIdValue = ($parentId === null || $parentId === '') ? null : (int) $parentId;

                $stmt->execute([
                    ':id' => max(1, (int) ($row['id'] ?? 0)),
                    ':name' => trim((string) ($row['name'] ?? '')),
                    ':parent_id' => $parentIdValue,
                ]);
            }

            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            try {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            } catch (Throwable) {
            }
            throw $exception;
        }
    }

    private function replaceNamedInDb(string $table, array $rows): void
    {
        $pdo = $this->pdo;
        $pdo->beginTransaction();
        try {
            $pdo->exec('DELETE FROM ' . $table);
            $stmt = $pdo->prepare('INSERT INTO ' . $table . ' (id, name) VALUES (:id, :name)');

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $stmt->execute([
                    ':id' => max(1, (int) ($row['id'] ?? 0)),
                    ':name' => $name,
                ]);
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function replaceOrdersInDb(array $rows): void
    {
        $pdo = $this->pdo;
        $pdo->beginTransaction();
        try {
            $pdo->exec('DELETE FROM orders');
            $stmt = $pdo->prepare('INSERT INTO orders
                (id, customer_name, customer_email, customer_phone, items_json, total_amount, status, delivery_address, payment_method, created_at)
                VALUES
                (:id, :customer_name, :customer_email, :customer_phone, :items_json, :total_amount, :status, :delivery_address, :payment_method, :created_at)');

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $items = is_array($row['items'] ?? null) ? $row['items'] : [];
                $stmt->execute([
                    ':id' => max(1, (int) ($row['id'] ?? 0)),
                    ':customer_name' => trim((string) ($row['customerName'] ?? '')),
                    ':customer_email' => trim((string) ($row['customerEmail'] ?? '')),
                    ':customer_phone' => trim((string) ($row['customerPhone'] ?? '')),
                    ':items_json' => $this->encodeJson($items),
                    ':total_amount' => (float) ($row['totalAmount'] ?? 0),
                    ':status' => trim((string) ($row['status'] ?? 'new')),
                    ':delivery_address' => trim((string) ($row['deliveryAddress'] ?? '')),
                    ':payment_method' => trim((string) ($row['paymentMethod'] ?? '')),
                    ':created_at' => $this->toDbDate($row['createdAt'] ?? null),
                ]);
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function replaceQuotesInDb(array $rows): void
    {
        $pdo = $this->pdo;
        $pdo->beginTransaction();
        try {
            $pdo->exec('DELETE FROM quotes');
            $stmt = $pdo->prepare('INSERT INTO quotes
                (id, name, email, phone, message, created_at)
                VALUES
                (:id, :name, :email, :phone, :message, :created_at)');

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $stmt->execute([
                    ':id' => max(1, (int) ($row['id'] ?? 0)),
                    ':name' => trim((string) ($row['name'] ?? '')),
                    ':email' => trim((string) ($row['email'] ?? '')),
                    ':phone' => trim((string) ($row['phone'] ?? '')),
                    ':message' => trim((string) ($row['message'] ?? '')),
                    ':created_at' => $this->toDbDate($row['createdAt'] ?? null),
                ]);
            }

            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function replaceUsersInDb(array $rows): void
    {
        $pdo = $this->pdo;
        $pdo->beginTransaction();
        try {
            $pdo->exec('DELETE FROM users');
            $stmt = $pdo->prepare('INSERT INTO users
                (id, name, email, phone, address, role, password_hash, user_type, created_at, updated_at)
                VALUES
                (:id, :name, :email, :phone, :address, :role, :password_hash, :user_type, :created_at, :updated_at)');

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $passwordHash = trim((string) ($row['passwordHash'] ?? ''));
                if ($passwordHash === '') {
                    $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
                }

                $createdAt = $this->toDbDate($row['createdAt'] ?? null);
                $updatedAt = $this->toDbDate($row['updatedAt'] ?? null);
                if ($updatedAt === '') {
                    $updatedAt = $createdAt;
                }

                $stmt->execute([
                    ':id' => max(1, (int) ($row['id'] ?? 0)),
                    ':name' => trim((string) ($row['name'] ?? '')),
                    ':email' => mb_strtolower(trim((string) ($row['email'] ?? ''))),
                    ':phone' => trim((string) ($row['phone'] ?? '')),
                    ':address' => trim((string) ($row['address'] ?? '')),
                    ':role' => trim((string) ($row['role'] ?? 'admin')),
                    ':password_hash' => $passwordHash,
                    ':user_type' => trim((string) ($row['userType'] ?? 'admin')),
                    ':created_at' => $createdAt,
                    ':updated_at' => $updatedAt,
                ]);
            }

            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function replaceSettingsInDb(array $data): void
    {
        $payload = $this->encodeJson($data);
        $sql = 'INSERT INTO settings (id, data_json, updated_at)
                VALUES (1, :data_json, :updated_at)
                ON DUPLICATE KEY UPDATE data_json = VALUES(data_json), updated_at = VALUES(updated_at)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':data_json' => $payload,
            ':updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    private function tableCount(string $table): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS count_rows FROM ' . $table);
        $row = $stmt ? $stmt->fetch() : null;
        return is_array($row) ? (int) ($row['count_rows'] ?? 0) : 0;
    }

    private function isDbReady(): bool
    {
        if ($this->dbReady !== null) {
            return $this->dbReady;
        }

        if (!$this->pdo) {
            $this->dbReady = false;
            return false;
        }

        $requiredTables = ['products', 'categories', 'colors', 'sizes', 'orders', 'quotes', 'users', 'settings'];

        try {
            foreach ($requiredTables as $table) {
                // Some MySQL setups don't support prepared statements for SHOW TABLES reliably.
                // information_schema works consistently.
                $stmt = $this->pdo->prepare(
                    'SELECT 1 FROM information_schema.tables ' .
                    'WHERE table_schema = DATABASE() AND table_name = :table_name LIMIT 1'
                );
                $stmt->execute([':table_name' => $table]);
                if (!$stmt->fetchColumn()) {
                    $this->dbReady = false;
                    return false;
                }
            }
            $this->dbReady = true;
            return true;
        } catch (Throwable) {
            $this->dbReady = false;
            return false;
        }
    }

    private function defaultCollections(string $now): array
    {
        return [
            'products' => [
                [
                    'id' => '1',
                    'name' => 'Классический свитер',
                    'category' => 'Свитера',
                    'mainCategory' => 'ЖЕНСКАЯ',
                    'article' => 'SW-001',
                    'price' => 3500,
                    'stock' => 25,
                    'inStock' => true,
                    'images' => ['/images/logush_slide_1.jpg', '/images/logush_slide_2.jpg', '/images/logush_slide_3.jpg'],
                    'colors' => ['Черный', 'Серый', 'Бежевый'],
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'description' => 'Классический свитер из качественного трикотажа для повседневной носки.',
                    'material' => '100% шерсть мериноса',
                    'care' => ['Ручная стирка при 30°C', 'Не отбеливать', 'Сушить горизонтально'],
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ],
                [
                    'id' => '2',
                    'name' => 'Кардиган оверсайз',
                    'category' => 'Кардиганы',
                    'mainCategory' => 'ЖЕНСКАЯ',
                    'article' => 'CG-002',
                    'price' => 4200,
                    'stock' => 18,
                    'inStock' => true,
                    'images' => ['/images/logush_slide_2.jpg', '/images/logush_slide_1.jpg', '/images/logush_slide_3.jpg'],
                    'colors' => ['Черный', 'Белый', 'Серый'],
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'description' => 'Свободный кардиган с удобной посадкой и мягкой вязкой.',
                    'material' => '70% шерсть, 30% акрил',
                    'care' => ['Машинная стирка при 30°C', 'Не отбеливать'],
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ],
                [
                    'id' => '3',
                    'name' => 'Вязаная шапка',
                    'category' => 'Аксессуары',
                    'mainCategory' => 'ЖЕНСКАЯ',
                    'article' => 'HT-003',
                    'price' => 1200,
                    'stock' => 40,
                    'inStock' => true,
                    'images' => ['/images/logush_slide_3.jpg', '/images/logush_slide_1.jpg'],
                    'colors' => ['Черный', 'Серый', 'Бежевый'],
                    'sizes' => ['Универсальный'],
                    'description' => 'Теплая шапка с отворотом для холодного сезона.',
                    'material' => '100% шерсть',
                    'care' => ['Ручная стирка при 30°C'],
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ],
                [
                    'id' => '4',
                    'name' => 'Водолазка базовая',
                    'category' => 'Свитера',
                    'mainCategory' => 'МУЖСКАЯ',
                    'article' => 'TL-004',
                    'price' => 2800,
                    'stock' => 12,
                    'inStock' => true,
                    'images' => ['/images/logush_slide_1.jpg', '/images/logush_slide_3.jpg'],
                    'colors' => ['Черный', 'Белый', 'Серый'],
                    'sizes' => ['S', 'M', 'L', 'XL'],
                    'description' => 'Универсальная базовая водолазка на каждый день.',
                    'material' => '95% хлопок, 5% эластан',
                    'care' => ['Машинная стирка при 40°C'],
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ],
                [
                    'id' => '5',
                    'name' => 'Шарф вязаный',
                    'category' => 'Аксессуары',
                    'mainCategory' => 'ДЕТСКАЯ',
                    'article' => 'SC-005',
                    'price' => 1500,
                    'stock' => 30,
                    'inStock' => true,
                    'images' => ['/images/logush_slide_2.jpg', '/images/logush_slide_3.jpg'],
                    'colors' => ['Черный', 'Серый', 'Бежевый'],
                    'sizes' => ['Универсальный'],
                    'description' => 'Мягкий и теплый шарф для повседневной носки.',
                    'material' => '100% шерсть',
                    'care' => ['Ручная стирка при 30°C'],
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ],
            ],
            'categories' => [
                ['id' => '1', 'name' => 'ЖЕНСКАЯ', 'parentId' => null, 'parentName' => null],
                ['id' => '2', 'name' => 'МУЖСКАЯ', 'parentId' => null, 'parentName' => null],
                ['id' => '3', 'name' => 'ДЕТСКАЯ', 'parentId' => null, 'parentName' => null],
                ['id' => '4', 'name' => 'Свитера', 'parentId' => '1', 'parentName' => 'ЖЕНСКАЯ'],
                ['id' => '5', 'name' => 'Кардиганы', 'parentId' => '1', 'parentName' => 'ЖЕНСКАЯ'],
                ['id' => '6', 'name' => 'Аксессуары', 'parentId' => '1', 'parentName' => 'ЖЕНСКАЯ'],
            ],
            'colors' => [
                ['id' => '1', 'name' => 'Черный'],
                ['id' => '2', 'name' => 'Белый'],
                ['id' => '3', 'name' => 'Серый'],
                ['id' => '4', 'name' => 'Бежевый'],
            ],
            'sizes' => [
                ['id' => '1', 'name' => 'XS'],
                ['id' => '2', 'name' => 'S'],
                ['id' => '3', 'name' => 'M'],
                ['id' => '4', 'name' => 'L'],
                ['id' => '5', 'name' => 'XL'],
                ['id' => '6', 'name' => 'Универсальный'],
            ],
            'orders' => [],
            'quotes' => [],
            'users' => [
                [
                    'id' => '1',
                    'name' => 'Администратор',
                    'email' => 'admin@logush.ru',
                    'phone' => '+7 (910) 787-20-94',
                    'address' => 'Смоленск',
                    'role' => 'Администратор',
                    'passwordHash' => password_hash('admin123', PASSWORD_DEFAULT),
                    'userType' => 'admin',
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ],
            ],
            'settings' => [
                'phone' => '+7 (910) 787-20-94',
                'email' => 'a.2094@yandex.ru',
                'whatsapp' => 'https://wa.me/79938822094',
                'telegram' => 'https://t.me/ilogush',
                'slider1Images' => ['/images/logush_slide_1.jpg', '/images/logush_slide_2.jpg'],
                'slider2Images' => ['/images/logush_slide_3.jpg'],
                'pageContent' => [
                    'home' => [
                        'heroParagraph1' => 'Мы — производственный партнер для оптовиков и розничных сетей по всей России.',
                        'heroParagraph2' => 'Команда из 100+ специалистов выпускает до 10 000 изделий ежемесячно.',
                        'heroParagraph3' => 'Полный цикл: от разработки лекал до отгрузки готовой продукции.',
                        'heroButtonText' => 'Узнать больше',
                    ],
                    'about' => [
                        'title' => 'Наша история',
                        'subtitle' => 'с 2002 года',
                        'paragraph1' => 'Мы начали с небольшого цеха и выросли в стабильное производство полного цикла.',
                        'paragraph2' => 'Сегодня продолжаем масштабировать процессы и повышать качество.',
                    ],
                    'services' => [
                        'title' => 'Швейная мастерская',
                        'subtitle' => 'полный цикл',
                        'paragraph1' => 'Пошив и вязание изделий любой сложности.',
                        'paragraph2' => 'Работаем с партиями под опт и контрактное производство.',
                    ],
                    'vacancies' => [
                        'title' => 'Присоединяйтесь к команде',
                        'subtitle' => 'открытые вакансии',
                        'paragraph1' => 'Ищем специалистов швейного и вязального направления.',
                        'paragraph2' => 'Стабильная загрузка, современное оборудование, официальное оформление.',
                    ],
                ],
                'seo' => [
                    'home' => [
                        'title' => 'ИП Логуш — Швейное и вязальное производство',
                        'description' => 'Швейное и вязальное производство в Смоленске.',
                        'keywords' => 'швейное производство, вязальное производство, трикотаж',
                    ],
                ],
            ],
        ];
    }

    private function seedFile(string $name, array $default): void
    {
        if (!is_file($this->filePath($name))) {
            $this->writeFile($name, $default);
        }
    }

    private function readFile(string $name): array
    {
        $path = $this->filePath($name);
        if (!is_file($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function writeFile(string $name, array $data): void
    {
        $path = $this->filePath($name);
        $tmpPath = $path . '.tmp';

        $payload = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        if ($payload === false) {
            throw new \RuntimeException('JSON encode failed for ' . $name);
        }

        file_put_contents($tmpPath, $payload . PHP_EOL, LOCK_EX);
        rename($tmpPath, $path);
    }

    private function filePath(string $name): string
    {
        return $this->dataDir . '/' . $name . '.json';
    }

    private function normalizeStringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            $text = trim((string) $item);
            if ($text === '') {
                continue;
            }
            $result[] = $text;
        }

        return $result;
    }

    private function decodeList(string $json): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function encodeJson(mixed $value): string
    {
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $encoded === false ? '[]' : $encoded;
    }

    private function toDbDate(mixed $value): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return gmdate('Y-m-d H:i:s');
        }

        $timestamp = strtotime($text);
        if ($timestamp === false) {
            return gmdate('Y-m-d H:i:s');
        }

        return gmdate('Y-m-d H:i:s', $timestamp);
    }

    private function toIsoDate(string $value): string
    {
        $text = trim($value);
        if ($text === '') {
            return gmdate('c');
        }

        try {
            $dt = new DateTimeImmutable($text, new DateTimeZone('UTC'));
            return $dt->setTimezone(new DateTimeZone('UTC'))->format('c');
        } catch (Throwable) {
            $timestamp = strtotime($text);
            if ($timestamp === false) {
                return gmdate('c');
            }
            return gmdate('c', $timestamp);
        }
    }

    private function isList(array $value): bool
    {
        return array_is_list($value);
    }

    private function isAssoc(array $value): bool
    {
        return !array_is_list($value);
    }
}
