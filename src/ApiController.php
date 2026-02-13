<?php

declare(strict_types=1);

namespace Logush;

final class ApiController
{
    private const ORDER_STATUSES = ['new', 'processing', 'shipped', 'delivered', 'cancelled'];
    private const ADMIN_ROLES = ['Администратор', 'Менеджер', 'Контент-менеджер'];

    public function __construct(
        private readonly string $baseDir,
        private readonly DataStore $store,
        private readonly Auth $auth
    ) {
    }

    public function handle(string $method, string $path): void
    {
        if ($path === '/api/auth/login') {
            $this->authLogin($method);
            return;
        }

        if ($path === '/api/auth/session') {
            $this->authSession($method);
            return;
        }

        if ($path === '/api/auth/logout') {
            $this->authLogout($method);
            return;
        }

        if ($path === '/api/products') {
            $this->products($method);
            return;
        }

        if (preg_match('#^/api/products/([^/]+)$#', $path, $matches) === 1) {
            $this->productById($method, rawurldecode($matches[1]));
            return;
        }

        if ($path === '/api/colors') {
            $this->namedCollection($method, 'colors', 'Color');
            return;
        }

        if (preg_match('#^/api/colors/([^/]+)$#', $path, $matches) === 1) {
            $this->namedById($method, 'colors', 'Color', rawurldecode($matches[1]));
            return;
        }

        if ($path === '/api/sizes') {
            $this->namedCollection($method, 'sizes', 'Size');
            return;
        }

        if (preg_match('#^/api/sizes/([^/]+)$#', $path, $matches) === 1) {
            $this->namedById($method, 'sizes', 'Size', rawurldecode($matches[1]));
            return;
        }

        if ($path === '/api/categories') {
            $this->categories($method);
            return;
        }

        if (preg_match('#^/api/categories/([^/]+)$#', $path, $matches) === 1) {
            $this->categoryById($method, rawurldecode($matches[1]));
            return;
        }

        if ($path === '/api/orders') {
            $this->orders($method);
            return;
        }

        if (preg_match('#^/api/orders/([^/]+)$#', $path, $matches) === 1) {
            $this->orderById($method, rawurldecode($matches[1]));
            return;
        }

        if ($path === '/api/quotes') {
            $this->quotes($method);
            return;
        }

        if (preg_match('#^/api/quotes/([^/]+)$#', $path, $matches) === 1) {
            $this->quoteById($method, rawurldecode($matches[1]));
            return;
        }

        if ($path === '/api/settings') {
            $this->settings($method);
            return;
        }

        if ($path === '/api/users') {
            $this->users($method);
            return;
        }

        if (preg_match('#^/api/users/(.+)$#', $path, $matches) === 1) {
            $this->userById($method, rawurldecode($matches[1]));
            return;
        }

        if ($path === '/api/upload') {
            $this->upload($method);
            return;
        }

        if ($path === '/api/cart/count') {
            $this->cartCount($method);
            return;
        }

        $this->json(['error' => 'Not found'], 404);
    }

    private function cartCount(string $method): void
    {
        if ($method !== 'GET') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        $cart = $_SESSION['cart'] ?? [];
        if (!is_array($cart)) {
            $cart = [];
        }

        $count = 0;
        foreach ($cart as $item) {
            if (!is_array($item)) continue;
            $qty = (int) ($item['quantity'] ?? 1);
            $count += max(0, $qty);
        }

        $this->json(['count' => $count]);
    }

    private function authLogin(string $method): void
    {
        if ($method !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        $input = $this->jsonInput();
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $rememberMe = (bool) ($input['rememberMe'] ?? false);

        if ($email === '' || $password === '') {
            $this->json(['error' => 'Неверный email или пароль'], 401);
            return;
        }

        $user = $this->auth->login($email, $password, $rememberMe);
        if (!$user) {
            $this->json(['error' => 'Неверный email или пароль'], 401);
            return;
        }

        $this->json([
            'ok' => true,
            'remember' => $rememberMe,
            'user' => [
                'id' => (string) ($user['id'] ?? ''),
                'email' => (string) ($user['email'] ?? ''),
                'role' => (string) ($user['role'] ?? 'admin'),
            ],
        ]);
    }

    private function authSession(string $method): void
    {
        if ($method !== 'GET') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        $user = $this->auth->user();
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $this->json([
            'ok' => true,
            'user' => [
                'id' => (string) ($user['id'] ?? ''),
                'email' => (string) ($user['email'] ?? ''),
                'role' => (string) ($user['role'] ?? 'admin'),
            ],
        ]);
    }

    private function authLogout(string $method): void
    {
        if ($method !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        $this->auth->logout();
        $this->json(['ok' => true]);
    }

    private function products(string $method): void
    {
        if ($method === 'GET') {
            $this->json($this->store->read('products'));
            return;
        }

        if ($method === 'POST') {
            if (!$this->ensureAdmin()) {
                return;
            }

            $products = $this->store->read('products');
            $payload = $this->normalizeProduct($this->jsonInput());
            $payload['id'] = $this->store->nextId('products');
            $now = gmdate('c');
            $payload['createdAt'] = $now;
            $payload['updatedAt'] = $now;
            $products[] = $payload;
            $this->store->write('products', $products);
            $this->json($payload, 201);
            return;
        }

        $this->json(['error' => 'Method not allowed'], 405);
    }

    private function productById(string $method, string $id): void
    {
        $products = $this->store->read('products');
        $index = $this->findIndexById($products, $id);

        if ($method === 'GET') {
            if ($index < 0) {
                $this->json(['error' => 'Product not found'], 404);
                return;
            }
            $this->json($products[$index]);
            return;
        }

        if (!$this->ensureAdmin()) {
            return;
        }

        if ($method === 'DELETE') {
            if ($index < 0) {
                $this->json(['error' => 'Product not found'], 404);
                return;
            }
            unset($products[$index]);
            $this->store->write('products', array_values($products));
            $this->json(['success' => true, 'id' => $id]);
            return;
        }

        if ($method === 'PUT') {
            if ($index < 0) {
                $this->json(['error' => 'Product not found'], 404);
                return;
            }

            $existing = is_array($products[$index]) ? $products[$index] : [];
            $updated = array_merge($existing, $this->normalizeProduct($this->jsonInput()));
            $updated['id'] = $id;
            $updated['updatedAt'] = gmdate('c');
            if (isset($existing['createdAt']) && (string) $existing['createdAt'] !== '') {
                $updated['createdAt'] = (string) $existing['createdAt'];
            }
            $products[$index] = $updated;
            $this->store->write('products', $products);
            $this->json($updated);
            return;
        }

        $this->json(['error' => 'Method not allowed'], 405);
    }

    private function namedCollection(string $method, string $name, string $label): void
    {
        if ($method === 'GET') {
            $rows = $this->store->read($name);

            if ($name === 'colors' || $name === 'sizes') {
                $products = $this->store->read('products');
                $normalized = [];
                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $valueName = (string) ($row['name'] ?? '');
                    $count = 0;
                    foreach ($products as $product) {
                        if (!is_array($product)) {
                            continue;
                        }
                        $items = $name === 'colors' ? ($product['colors'] ?? []) : ($product['sizes'] ?? []);
                        if (is_array($items) && in_array($valueName, $items, true)) {
                            $count += 1;
                        }
                    }
                    // Keep legacy key but also provide the key used in React admin.
                    $row['productsCount'] = $count;
                    $row['productCount'] = $count;
                    $normalized[] = $row;
                }
                $this->json($normalized);
                return;
            }

            $this->json($rows);
            return;
        }

        if ($method === 'POST') {
            if (!$this->ensureAdmin()) {
                return;
            }

            $payload = $this->jsonInput();
            $nameValue = trim((string) ($payload['name'] ?? ''));
            if ($nameValue === '') {
                $this->json(['error' => $label . ' name is required'], 400);
                return;
            }

            $rows = $this->store->read($name);
            $newRow = [
                'id' => $this->store->nextId($name),
                'name' => $nameValue,
            ];
            $rows[] = $newRow;
            $this->store->write($name, $rows);
            $this->json($newRow, 201);
            return;
        }

        $this->json(['error' => 'Method not allowed'], 405);
    }

    private function namedById(string $method, string $name, string $label, string $id): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        $rows = $this->store->read($name);
        $index = $this->findIndexById($rows, $id);
        if ($index < 0) {
            $this->json(['error' => $label . ' not found'], 404);
            return;
        }

        if ($method === 'DELETE') {
            unset($rows[$index]);
            $this->store->write($name, array_values($rows));
            $this->json(['success' => true]);
            return;
        }

        if ($method === 'PUT') {
            $payload = $this->jsonInput();
            $nameValue = trim((string) ($payload['name'] ?? ''));
            if ($nameValue === '') {
                $this->json(['error' => $label . ' name is required'], 400);
                return;
            }

            $rows[$index]['name'] = $nameValue;
            $this->store->write($name, $rows);
            $this->json($rows[$index]);
            return;
        }

        $this->json(['error' => 'Method not allowed'], 405);
    }

    private function categories(string $method): void
    {
        if ($method === 'GET') {
            $categories = $this->store->read('categories');
            $products = $this->store->read('products');

            $result = [];
            foreach ($categories as $category) {
                if (!is_array($category)) {
                    continue;
                }
                $name = (string) ($category['name'] ?? '');
                $count = 0;
                foreach ($products as $product) {
                    if (!is_array($product)) {
                        continue;
                    }
                    if ((string) ($product['category'] ?? '') === $name) {
                        $count += 1;
                    }
                }
                $category['productsCount'] = $count;
                $category['productCount'] = $count;
                $result[] = $category;
            }
            $this->json($result);
            return;
        }

        if ($method !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        if (!$this->ensureAdmin()) {
            return;
        }

        $payload = $this->jsonInput();
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            $this->json(['error' => 'Category name is required'], 400);
            return;
        }

        $parentId = $payload['parentId'] ?? null;
        $parentName = null;

        $categories = $this->store->read('categories');
        if ($parentId !== null && $parentId !== '') {
            $parentId = (string) $parentId;
            foreach ($categories as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if ((string) ($item['id'] ?? '') === $parentId) {
                    $parentName = (string) ($item['name'] ?? null);
                    break;
                }
            }
        }

        $newCategory = [
            'id' => $this->store->nextId('categories'),
            'name' => $name,
            'parentId' => $parentId !== '' ? $parentId : null,
            'parentName' => $parentName,
        ];

        $categories[] = $newCategory;
        $this->store->write('categories', $categories);
        $this->json($newCategory, 201);
    }

    private function categoryById(string $method, string $id): void
    {
        $categories = $this->store->read('categories');
        $index = $this->findIndexById($categories, $id);

        if ($method === 'GET') {
            if ($index < 0) {
                $this->json(['error' => 'Category not found'], 404);
                return;
            }
            $this->json($categories[$index]);
            return;
        }

        if (!$this->ensureAdmin()) {
            return;
        }

        if ($index < 0) {
            $this->json(['error' => 'Category not found'], 404);
            return;
        }

        if ($method === 'DELETE') {
            if ((string) ($categories[$index]['id'] ?? '') === '1') {
                $this->json(['error' => 'Default category cannot be deleted'], 400);
                return;
            }
            if (count($categories) <= 1) {
                $this->json(['error' => 'At least one category is required'], 400);
                return;
            }
            unset($categories[$index]);
            $this->store->write('categories', array_values($categories));
            $this->json(['success' => true, 'id' => $id]);
            return;
        }

        if ($method === 'PUT') {
            $payload = $this->jsonInput();
            $name = trim((string) ($payload['name'] ?? ''));
            if ($name === '') {
                $this->json(['error' => 'Category name is required'], 400);
                return;
            }

            $parentId = $payload['parentId'] ?? null;
            if ($parentId === '') {
                $parentId = null;
            }
            $parentName = null;
            if (is_string($parentId)) {
                foreach ($categories as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    if ((string) ($item['id'] ?? '') === $parentId) {
                        $parentName = (string) ($item['name'] ?? null);
                        break;
                    }
                }
            }

            $categories[$index]['name'] = $name;
            $categories[$index]['parentId'] = $parentId;
            $categories[$index]['parentName'] = $parentName;
            $this->store->write('categories', $categories);
            $this->json($categories[$index]);
            return;
        }

        $this->json(['error' => 'Method not allowed'], 405);
    }

    private function orders(string $method): void
    {
        if ($method === 'GET') {
            if (!$this->ensureAdmin()) {
                return;
            }
            $this->json($this->store->read('orders'));
            return;
        }

        if ($method !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        $payload = $this->jsonInput();
        $items = $payload['items'] ?? null;
        if (!is_array($items) || count($items) === 0) {
            $this->json(['error' => 'Invalid order payload'], 400);
            return;
        }

        $customerName = trim((string) ($payload['customerName'] ?? ''));
        $customerEmail = trim((string) ($payload['customerEmail'] ?? ''));
        $customerPhone = trim((string) ($payload['customerPhone'] ?? ''));
        $deliveryAddress = trim((string) ($payload['deliveryAddress'] ?? ''));
        $paymentMethod = trim((string) ($payload['paymentMethod'] ?? ''));

        if ($customerName === '' || $customerEmail === '' || $customerPhone === '' || $deliveryAddress === '' || $paymentMethod === '') {
            $this->json(['error' => 'Invalid order payload'], 400);
            return;
        }

        $normalizedItems = [];
        $totalAmount = 0.0;
        foreach ($items as $item) {
            if (!is_array($item)) {
                $this->json(['error' => 'Invalid order payload'], 400);
                return;
            }

            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            $productId = trim((string) ($item['productId'] ?? ''));
            $productName = trim((string) ($item['productName'] ?? ''));
            $color = trim((string) ($item['color'] ?? ''));
            $size = trim((string) ($item['size'] ?? ''));

            if ($quantity < 1 || $price < 0 || $productId === '' || $productName === '' || $color === '' || $size === '') {
                $this->json(['error' => 'Invalid order payload'], 400);
                return;
            }

            $normalizedItems[] = [
                'productId' => $productId,
                'productName' => $productName,
                'quantity' => $quantity,
                'price' => $price,
                'color' => $color,
                'size' => $size,
            ];
            $totalAmount += $quantity * $price;
        }

        $status = (string) ($payload['status'] ?? 'new');
        if (!in_array($status, self::ORDER_STATUSES, true)) {
            $status = 'new';
        }

        $orders = $this->store->read('orders');
        $order = [
            'id' => $this->store->nextId('orders'),
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'customerPhone' => $customerPhone,
            'items' => $normalizedItems,
            'totalAmount' => (float) ($payload['totalAmount'] ?? $totalAmount),
            'status' => $status,
            'deliveryAddress' => $deliveryAddress,
            'paymentMethod' => $paymentMethod,
            'createdAt' => gmdate('c'),
        ];

        $orders[] = $order;
        $this->store->write('orders', $orders);
        $this->json($order, 201);
    }

    private function quotes(string $method): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        if ($method === 'GET') {
            $quotes = $this->store->read('quotes');
            $this->json(is_array($quotes) ? $quotes : []);
            return;
        }

        $this->json(['error' => 'Method not allowed'], 405);
    }

    private function quoteById(string $method, string $id): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        $quotes = $this->store->read('quotes');
        $list = is_array($quotes) ? $quotes : [];
        $index = -1;
        foreach ($list as $i => $row) {
            if (!is_array($row)) {
                continue;
            }
            if ((string) ($row['id'] ?? '') === (string) $id) {
                $index = (int) $i;
                break;
            }
        }

        if ($index < 0) {
            $this->json(['error' => 'Not found'], 404);
            return;
        }

        if ($method === 'GET') {
            $this->json($list[$index]);
            return;
        }

        if ($method === 'DELETE') {
            unset($list[$index]);
            $this->store->write('quotes', array_values($list));
            $this->json(['ok' => true]);
            return;
        }

        $this->json(['error' => 'Method not allowed'], 405);
    }

    private function orderById(string $method, string $id): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        $orders = $this->store->read('orders');
        $index = $this->findIndexById($orders, $id);

        if ($method === 'GET') {
            if ($index < 0) {
                $this->json(['error' => 'Order not found'], 404);
                return;
            }
            $this->json($orders[$index]);
            return;
        }

        if ($method === 'DELETE') {
            if ($index < 0) {
                $this->json(['error' => 'Order not found'], 404);
                return;
            }
            unset($orders[$index]);
            $this->store->write('orders', array_values($orders));
            $this->json(['success' => true, 'id' => $id]);
            return;
        }

        if ($method === 'PUT') {
            if ($index < 0) {
                $this->json(['error' => 'Order not found'], 404);
                return;
            }
            $payload = $this->jsonInput();
            $status = (string) ($payload['status'] ?? '');
            if (!in_array($status, self::ORDER_STATUSES, true)) {
                $this->json(['error' => 'Invalid status'], 400);
                return;
            }
            $orders[$index]['status'] = $status;
            $this->store->write('orders', $orders);
            $this->json($orders[$index]);
            return;
        }

        $this->json(['error' => 'Method not allowed'], 405);
    }

    private function settings(string $method): void
    {
        if ($method === 'GET') {
            $this->json(SettingsDefaults::merge($this->store->read('settings')));
            return;
        }

        if ($method !== 'PUT') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        if (!$this->ensureAdmin()) {
            return;
        }

        $payload = $this->jsonInput();
        $cleanPayload = $this->sanitizeSettingsPayload(is_array($payload) ? $payload : []);
        $normalized = SettingsDefaults::merge($cleanPayload);

        $this->store->write('settings', $normalized);
        $this->clearPublicPageCache();
        $this->json($normalized);
    }

    private function sanitizeSettingsPayload(array $payload): array
    {
        $sanitize = static function (mixed $v) use (&$sanitize): mixed {
            if (is_string($v)) {
                // Remove copy/paste artifacts and normalize whitespace.
                $s = str_replace("\r\n", "\n", $v);
                $s = str_replace("\r", "\n", $s);
                $s = str_replace("\xC2\xA0", ' ', $s); // NBSP
                // Zero-width: U+200B..U+200D and BOM U+FEFF
                $s = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $s) ?? $s;

                // Trim each line and drop trailing spaces.
                $lines = explode("\n", $s);
                foreach ($lines as $i => $line) {
                    $lines[$i] = rtrim($line);
                }
                $s = implode("\n", $lines);

                // Collapse 3+ newlines to максимум 2 (чтобы не раздувать блоки).
                $s = preg_replace("/\n{3,}/", "\n\n", $s) ?? $s;
                return trim($s);
            }

            if (is_array($v)) {
                $out = [];
                foreach ($v as $k => $vv) {
                    $out[$k] = $sanitize($vv);
                }
                return $out;
            }

            return $v;
        };

        return is_array($sanitize($payload)) ? $sanitize($payload) : $payload;
    }

    private function clearPublicPageCache(): void
    {
        $dir = rtrim($this->baseDir, '/') . '/storage/cache/pages';
        if (!is_dir($dir)) {
            return;
        }
        $files = glob($dir . '/*.html') ?: [];
        foreach ($files as $file) {
            if (is_string($file)) {
                @unlink($file);
            }
        }
    }

    private function users(string $method): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        if ($method === 'GET') {
            $admins = $this->store->read('users');
            $orders = $this->store->read('orders');

            $result = [];
            foreach ($admins as $admin) {
                if (!is_array($admin)) {
                    continue;
                }
                $result[] = $this->mapAdminToClient($admin);
            }

            $customers = $this->aggregateCustomers($orders);
            $result = array_merge($result, $customers);

            usort($result, static function (array $a, array $b): int {
                $aAdmin = ($a['userType'] ?? '') === 'admin';
                $bAdmin = ($b['userType'] ?? '') === 'admin';
                if ($aAdmin && !$bAdmin) {
                    return -1;
                }
                if (!$aAdmin && $bAdmin) {
                    return 1;
                }

                $aDate = strtotime((string) ($a['lastOrderAt'] ?? '1970-01-01')) ?: 0;
                $bDate = strtotime((string) ($b['lastOrderAt'] ?? '1970-01-01')) ?: 0;
                return $bDate <=> $aDate;
            });

            $this->json($result);
            return;
        }

        if ($method !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        $payload = $this->jsonInput();
        $name = trim((string) ($payload['name'] ?? ''));
        $email = mb_strtolower(trim((string) ($payload['email'] ?? '')));
        $role = trim((string) ($payload['role'] ?? 'Администратор'));
        $password = (string) ($payload['password'] ?? '');

        if ($name === '' || $email === '' || $role === '' || $password === '') {
            $this->json(['error' => 'Заполните имя, email, роль и пароль'], 400);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['error' => 'Некорректный email'], 400);
            return;
        }
        if (!in_array($role, self::ADMIN_ROLES, true)) {
            $this->json(['error' => 'Недопустимая роль'], 400);
            return;
        }
        if (mb_strlen($password) < 8) {
            $this->json(['error' => 'Пароль должен быть минимум 8 символов'], 400);
            return;
        }

        $users = $this->store->read('users');
        $existingIndex = -1;
        foreach ($users as $i => $user) {
            if (!is_array($user)) {
                continue;
            }
            $existingEmail = mb_strtolower(trim((string) ($user['email'] ?? '')));
            if ($existingEmail === $email) {
                $existingIndex = (int) $i;
                break;
            }
        }

        // Admin panel UX: allow "create or update by email" (like setup_admin.php).
        // This keeps onboarding simple on shared hosting without DB access.
        if ($existingIndex >= 0) {
            $now = gmdate('c');
            $users[$existingIndex]['name'] = $name;
            $users[$existingIndex]['email'] = $email;
            $users[$existingIndex]['phone'] = trim((string) ($payload['phone'] ?? ($users[$existingIndex]['phone'] ?? '')));
            $users[$existingIndex]['address'] = trim((string) ($payload['address'] ?? ($users[$existingIndex]['address'] ?? '')));
            $users[$existingIndex]['role'] = $role;
            $users[$existingIndex]['userType'] = 'admin';
            $users[$existingIndex]['passwordHash'] = password_hash($password, PASSWORD_DEFAULT);
            $users[$existingIndex]['updatedAt'] = $now;

            $this->store->write('users', $users);
            $this->json($this->mapAdminToClient($users[$existingIndex]));
            return;
        }

        $newUser = [
            'id' => $this->store->nextId('users'),
            'name' => $name,
            'email' => $email,
            'phone' => trim((string) ($payload['phone'] ?? '')),
            'address' => trim((string) ($payload['address'] ?? '')),
            'role' => $role,
            'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
            'userType' => 'admin',
            'createdAt' => gmdate('c'),
        ];

        $users[] = $newUser;
        $this->store->write('users', $users);
        $this->json($this->mapAdminToClient($newUser), 201);
    }

    private function userById(string $method, string $id): void
    {
        if (!$this->ensureAdmin()) {
            return;
        }

        if (str_starts_with($id, 'admin:')) {
            $adminId = substr($id, strlen('admin:'));
            $users = $this->store->read('users');
            $index = $this->findIndexById($users, $adminId);

            if ($index < 0) {
                $this->json(['error' => 'Not found'], 404);
                return;
            }

            if ($method === 'GET') {
                $this->json($this->mapAdminToClient($users[$index]));
                return;
            }

            if ($method === 'DELETE') {
                if (count($users) <= 1) {
                    $this->json(['error' => 'At least one admin is required'], 400);
                    return;
                }
                unset($users[$index]);
                $this->store->write('users', array_values($users));
                $this->json(['ok' => true]);
                return;
            }

            if ($method === 'PUT') {
                $payload = $this->jsonInput();
                $name = trim((string) ($payload['name'] ?? ''));
                $email = mb_strtolower(trim((string) ($payload['email'] ?? '')));
                $role = trim((string) ($payload['role'] ?? ''));
                if ($name === '' || $email === '' || $role === '') {
                    $this->json(['error' => 'Заполните имя, email и роль'], 400);
                    return;
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->json(['error' => 'Некорректный email'], 400);
                    return;
                }
                if (!in_array($role, self::ADMIN_ROLES, true)) {
                    $this->json(['error' => 'Недопустимая роль'], 400);
                    return;
                }

                foreach ($users as $i => $user) {
                    if (!is_array($user) || $i === $index) {
                        continue;
                    }
                    $existingEmail = mb_strtolower(trim((string) ($user['email'] ?? '')));
                    if ($existingEmail === $email) {
                        $this->json(['error' => 'User already exists'], 409);
                        return;
                    }
                }

                $users[$index]['name'] = $name;
                $users[$index]['email'] = $email;
                $users[$index]['phone'] = trim((string) ($payload['phone'] ?? ($users[$index]['phone'] ?? '')));
                $users[$index]['address'] = trim((string) ($payload['address'] ?? ($users[$index]['address'] ?? '')));
                $users[$index]['role'] = $role;

                $password = (string) ($payload['password'] ?? '');
                if ($password !== '') {
                    if (mb_strlen($password) < 8) {
                        $this->json(['error' => 'Пароль должен быть минимум 8 символов'], 400);
                        return;
                    }
                    $users[$index]['passwordHash'] = password_hash($password, PASSWORD_DEFAULT);
                }

                $this->store->write('users', $users);
                $this->json($this->mapAdminToClient($users[$index]));
                return;
            }

            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        if ($method === 'GET') {
            $customers = $this->aggregateCustomers($this->store->read('orders'));
            foreach ($customers as $customer) {
                if ((string) ($customer['id'] ?? '') === $id) {
                    $this->json($customer);
                    return;
                }
            }
            $this->json(['error' => 'Not found'], 404);
            return;
        }

        $this->json(['error' => 'Editing customer is not supported'], 400);
    }

    private function upload(string $method): void
    {
        if ($method === 'GET') {
            $key = trim((string) ($_GET['key'] ?? ''));
            $key = ltrim($key, '/');
            $w = (int) ($_GET['w'] ?? 0);

            if ($key === '' || preg_match('/\.\.|\\\\/', $key) === 1) {
                $this->json(['error' => 'Missing key'], 400);
                return;
            }

            $fullPath = $this->store->uploadsPath($key);
            if (!is_file($fullPath)) {
                $this->json(['error' => 'Not found'], 404);
                return;
            }

            // Responsive derivatives (cached on disk).
            // Only for raster images and only when width is requested.
            if ($w > 0 && $w <= 2400) {
                $contentType = mime_content_type($fullPath) ?: '';
                $isRaster = str_starts_with($contentType, 'image/')
                    && !str_contains($contentType, 'svg')
                    && !str_contains($contentType, 'gif');

                if ($isRaster && function_exists('imagecreatetruecolor')) {
                    $base = pathinfo($key, PATHINFO_FILENAME);
                    $dir = trim((string) pathinfo($key, PATHINFO_DIRNAME));
                    $dir = ($dir === '.' ? '' : $dir);

                    $derivRel = ($dir !== '' ? $dir . '/' : '') . 'derivatives/' . $base . '-w' . $w . '.webp';
                    $derivPath = $this->store->uploadsPath($derivRel);

                    if (!is_file($derivPath)) {
                        $derivDir = dirname($derivPath);
                        if (!is_dir($derivDir)) {
                            @mkdir($derivDir, 0775, true);
                        }
                        // Best-effort: if resize fails, we will fall back to the original.
                        $this->resizeToWebp($fullPath, $derivPath, $w);
                    }

                    if (is_file($derivPath)) {
                        header('Cache-Control: public, max-age=31536000, immutable');
                        header('Content-Type: image/webp');
                        readfile($derivPath);
                        return;
                    }
                }
            }

            $contentType = mime_content_type($fullPath) ?: 'application/octet-stream';
            header('Cache-Control: public, max-age=31536000, immutable');
            header('Content-Type: ' . $contentType);
            readfile($fullPath);
            return;
        }

        if ($method !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        if (!$this->ensureAdmin()) {
            return;
        }

        if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
            $this->json(['error' => 'No file provided'], 400);
            return;
        }

        $file = $_FILES['file'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->json(['error' => 'Upload failed'], 500);
            return;
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            $this->json(['error' => 'Invalid file'], 400);
            return;
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size > 8 * 1024 * 1024) {
            $this->json(['error' => 'File is too large'], 413);
            return;
        }

        $mime = mime_content_type($tmpPath) ?: '';
        $allowedMimes = ['image/webp', 'image/jpeg', 'image/png'];
        if (!in_array($mime, $allowedMimes, true)) {
            $this->json(['error' => 'Only WebP, JPEG, and PNG images are allowed'], 415);
            return;
        }

        $folderRaw = trim((string) ($_POST['folder'] ?? 'uploads'));
        $folder = preg_replace('/[^a-z0-9_\/-]+/i', '', $folderRaw);
        $folder = trim((string) $folder, '/');
        if ($folder === '') {
            $folder = 'uploads';
        }

        $baseName = gmdate('YmdHis') . '-' . bin2hex(random_bytes(6));

        // Prefer optimized WebP output. If GD is missing or optimization fails, keep the original format.
        $extByMime = [
            'image/webp' => 'webp',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];
        $fallbackExt = $extByMime[$mime] ?? 'bin';

        $relativePath = $folder . '/' . $baseName . '.webp';
        $targetPath = $this->store->uploadsPath($relativePath);
        $targetDir = dirname($targetPath);

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        // Оптимизация изображения: resize + compress + convert to WebP (if possible).
        $optimized = $this->optimizeImage($tmpPath, $targetPath);
        if (!$optimized) {
            $relativePath = $folder . '/' . $baseName . '.' . $fallbackExt;
            $targetPath = $this->store->uploadsPath($relativePath);
            if (!move_uploaded_file($tmpPath, $targetPath)) {
                $this->json(['error' => 'Upload failed'], 500);
                return;
            }
        }

        $this->json([
            'url' => '/api/upload?key=' . rawurlencode($relativePath),
            'key' => $relativePath,
        ], 201);
    }

    private function normalizeProduct(array $payload): array
    {
        $images = $this->normalizeStringList($payload['images'] ?? []);
        $colors = $this->normalizeStringList($payload['colors'] ?? []);
        $sizes = $this->normalizeStringList($payload['sizes'] ?? []);
        $care = $this->normalizeStringList($payload['care'] ?? []);

        if (count($images) === 0) {
            $images = ['/images/product-placeholder.svg'];
        }

        return [
            'id' => (string) ($payload['id'] ?? ''),
            'name' => trim((string) ($payload['name'] ?? 'Новый товар')),
            'category' => trim((string) ($payload['category'] ?? 'Свитера')),
            'mainCategory' => trim((string) ($payload['mainCategory'] ?? 'ЖЕНСКАЯ')),
            'article' => trim((string) ($payload['article'] ?? '')),
            'price' => (float) ($payload['price'] ?? 0),
            'stock' => (int) ($payload['stock'] ?? 0),
            'inStock' => !isset($payload['inStock']) ? true : (bool) $payload['inStock'],
            'images' => $images,
            'colors' => $colors,
            'sizes' => $sizes,
            'description' => trim((string) ($payload['description'] ?? '')),
            'material' => trim((string) ($payload['material'] ?? '')),
            'care' => $care,
            'createdAt' => (string) ($payload['createdAt'] ?? ''),
            'updatedAt' => (string) ($payload['updatedAt'] ?? ''),
        ];
    }

    // normalizeSettings() removed in favor of SettingsDefaults::merge().

    private function aggregateCustomers(array $orders): array
    {
        $map = [];
        foreach ($orders as $order) {
            if (!is_array($order)) {
                continue;
            }

            $email = mb_strtolower(trim((string) ($order['customerEmail'] ?? '')));
            if ($email === '') {
                continue;
            }

            if (!isset($map[$email])) {
                $createdAt = (string) ($order['createdAt'] ?? gmdate('c'));
                $map[$email] = [
                    'id' => 'customer:' . md5($email),
                    'name' => trim((string) ($order['customerName'] ?? '')),
                    'email' => $email,
                    'phone' => trim((string) ($order['customerPhone'] ?? '')),
                    'address' => trim((string) ($order['deliveryAddress'] ?? '')),
                    'role' => 'customer',
                    'userType' => 'customer',
                    'orderCount' => 0,
                    // Backwards compatibility.
                    'ordersCount' => 0,
                    'totalSpent' => 0,
                    'firstOrderAt' => $createdAt,
                    'lastOrderAt' => $createdAt,
                ];
            }

            $map[$email]['orderCount'] += 1;
            $map[$email]['ordersCount'] += 1;
            $map[$email]['totalSpent'] += (float) ($order['totalAmount'] ?? 0);

            $createdAt = (string) ($order['createdAt'] ?? '');
            $firstOrderAt = (string) ($map[$email]['firstOrderAt'] ?? '');
            $lastOrderAt = (string) ($map[$email]['lastOrderAt'] ?? '');
            if ($firstOrderAt === '' || strtotime($createdAt) < strtotime($firstOrderAt)) {
                $map[$email]['firstOrderAt'] = $createdAt;
            }
            if (strtotime($createdAt) > strtotime($lastOrderAt)) {
                $map[$email]['lastOrderAt'] = $createdAt;
            }
        }

        return array_values($map);
    }

    private function mapAdminToClient(array $admin): array
    {
        $createdAt = (string) ($admin['createdAt'] ?? '');
        $role = trim((string) ($admin['role'] ?? ''));
        if ($role === '' || mb_strtolower($role) === 'admin') {
            $role = 'Администратор';
        }
        return [
            'id' => 'admin:' . (string) ($admin['id'] ?? ''),
            'name' => (string) ($admin['name'] ?? ''),
            'email' => (string) ($admin['email'] ?? ''),
            'phone' => (string) ($admin['phone'] ?? ''),
            'address' => (string) ($admin['address'] ?? ''),
            'role' => $role,
            'userType' => 'admin',
            'orderCount' => 0,
            'ordersCount' => 0,
            'totalSpent' => 0,
            'firstOrderAt' => $createdAt,
            'lastOrderAt' => $createdAt,
        ];
    }

    private function ensureAdmin(): bool
    {
        if ($this->auth->check()) {
            return true;
        }

        $this->json(['error' => 'Unauthorized'], 401);
        return false;
    }

    private function normalizeAssoc(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $key => $item) {
            if (!is_string($key)) {
                continue;
            }
            if (is_scalar($item)) {
                $result[$key] = trim((string) $item);
            }
        }
        return $result;
    }

    private function normalizeStringList(mixed $value, int $limit = 20): array
    {
        $result = [];
        if (!is_array($value)) {
            return $result;
        }

        foreach ($value as $item) {
            $text = trim((string) $item);
            if ($text === '') {
                continue;
            }
            $result[] = $text;
            if (count($result) >= $limit) {
                break;
            }
        }

        return $result;
    }

    private function findIndexById(array $rows, string $id): int
    {
        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }
            if ((string) ($row['id'] ?? '') === $id) {
                return $index;
            }
        }
        return -1;
    }

    private function jsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function json(mixed $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Оптимизация изображения: изменение размера и сжатие
     * 
     * @param string $sourcePath Путь к исходному файлу
     * @param string $targetPath Путь для сохранения оптимизированного файла
     * @return bool Успешность операции
     */
    private function optimizeImage(string $sourcePath, string $targetPath): bool
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        // Загрузка изображения
        $image = @imagecreatefromwebp($sourcePath);
        if ($image === false) {
            // Попробуем другие форматы на случай если mime определился неверно
            $image = @imagecreatefromjpeg($sourcePath);
            if ($image === false) {
                $image = @imagecreatefrompng($sourcePath);
            }
            if ($image === false) {
                return false;
            }
        }

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        // Максимальные размеры для товаров (можно настроить)
        $maxWidth = 1200;
        $maxHeight = 1600;

        // Вычисление новых размеров с сохранением пропорций
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        
        // Если изображение меньше максимальных размеров, не увеличиваем
        if ($ratio >= 1) {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        } else {
            $newWidth = (int) round($originalWidth * $ratio);
            $newHeight = (int) round($originalHeight * $ratio);
        }

        // Создание нового изображения с измененным размером
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        if ($resized === false) {
            imagedestroy($image);
            return false;
        }

        // Сохранение прозрачности для PNG
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        // Изменение размера с высоким качеством
        $success = imagecopyresampled(
            $resized,
            $image,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        imagedestroy($image);

        if (!$success) {
            imagedestroy($resized);
            return false;
        }

        // Сохранение в WebP с качеством 85 (баланс между размером и качеством)
        $saved = imagewebp($resized, $targetPath, 85);
        imagedestroy($resized);

        return $saved;
    }

    private function resizeToWebp(string $sourcePath, string $targetPath, int $maxWidth): bool
    {
        if ($maxWidth <= 0) {
            return false;
        }
        if (!extension_loaded('gd')) {
            return false;
        }

        $image = @imagecreatefromwebp($sourcePath);
        if ($image === false) {
            $image = @imagecreatefromjpeg($sourcePath);
            if ($image === false) {
                $image = @imagecreatefrompng($sourcePath);
            }
            if ($image === false) {
                return false;
            }
        }

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        if ($originalWidth <= 0 || $originalHeight <= 0) {
            imagedestroy($image);
            return false;
        }

        $ratio = min($maxWidth / $originalWidth, 1);
        $newWidth = (int) max(1, round($originalWidth * $ratio));
        $newHeight = (int) max(1, round($originalHeight * $ratio));

        // If already small enough, just save a WebP copy (cheap and keeps Content-Type consistent).
        if ($newWidth === $originalWidth && $newHeight === $originalHeight) {
            $saved = @imagewebp($image, $targetPath, 82);
            imagedestroy($image);
            return (bool) $saved;
        }

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        if ($resized === false) {
            imagedestroy($image);
            return false;
        }

        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        $ok = imagecopyresampled(
            $resized,
            $image,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );
        imagedestroy($image);

        if (!$ok) {
            imagedestroy($resized);
            return false;
        }

        $saved = imagewebp($resized, $targetPath, 82);
        imagedestroy($resized);
        return (bool) $saved;
    }
}
