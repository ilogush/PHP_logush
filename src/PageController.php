<?php

declare(strict_types=1);

namespace Logush;

final class PageController
{
    public function __construct(
        private readonly DataStore $store,
        private readonly Auth $auth,
        private readonly View $view,
        private readonly SnapshotRenderer $snapshots
    ) {
    }

    public function handle(string $method, string $path): void
    {
        $isReadRequest = $method === 'GET' || $method === 'HEAD';

        // /sale must be dynamic (products come from admin/DB). Snapshot is static.
        if ($isReadRequest && $path === '/sale') {
            $this->renderSale();
            return;
        }

        // Stateful pages must be dynamic (depend on session / POST flow).
        if ($isReadRequest && $path === '/cart') {
            $this->renderCart();
            return;
        }
        if ($isReadRequest && $path === '/checkout') {
            $this->renderCheckout();
            return;
        }
        if ($isReadRequest && $path === '/quote') {
            $this->renderQuote();
            return;
        }
        if ($isReadRequest && $path === '/order-success') {
            $this->renderSite(
                'Заказ оформлен',
                'pages/order_success.php',
                ['orderId' => (string) ($_GET['id'] ?? '')],
                'Заказ оформлен',
                ''
            );
            return;
        }

        if ($path === '/robots.txt') {
            $this->sendRobots();
            return;
        }

        if ($path === '/sitemap.xml') {
            $this->sendSitemap();
            return;
        }

        // Skip snapshots for product pages - always render dynamically
        $isProductPage = preg_match('#^/product/([^/]+)$#', $path) === 1;
        
        if ($isReadRequest && !$isProductPage && $this->renderSnapshotPage($path)) {
            return;
        }

        if ($path === '/logout' && $method === 'POST') {
            $this->auth->logout();
            $this->redirect('/login');
            return;
        }

        if ($path === '/login') {
            $this->handleLogin($method);
            return;
        }

        if (str_starts_with($path, '/admin')) {
            $this->handleAdminPage($path);
            return;
        }

        if ($path === '/cart/add' && $method === 'POST') {
            $this->addToCart();
            return;
        }

        if ($path === '/cart/remove' && $method === 'POST') {
            $this->removeFromCart();
            return;
        }

        if ($path === '/checkout' && $method === 'POST') {
            $this->checkout();
            return;
        }

        if ($path === '/quote' && $method === 'POST') {
            $this->saveQuote();
            return;
        }

        if ($isReadRequest && $this->handleDynamicPublicFallback($path)) {
            return;
        }

        if ($path === '/404') {
            $this->renderNotFound();
            return;
        }

        $this->renderNotFound();
    }

    /**
     * Dynamic fallback only for stateful pages when SSR snapshot is missing.
     * Public content pages are snapshot-first by design.
     */
    private function handleDynamicPublicFallback(string $path): bool
    {
        if (preg_match('#^/product/([^/]+)$#', $path, $matches) === 1) {
            $this->renderProduct(rawurldecode($matches[1]));
            return true;
        }

        if ($path === '/cart') {
            $this->renderCart();
            return true;
        }

        if ($path === '/checkout') {
            $this->renderCheckout();
            return true;
        }

        if ($path === '/quote') {
            $this->renderQuote();
            return true;
        }

        if ($path === '/order-success') {
            $this->renderSite(
                'Заказ оформлен',
                'pages/order_success.php',
                ['orderId' => (string) ($_GET['id'] ?? '')]
            );
            return true;
        }

        return false;
    }

    private function renderProduct(string $id): void
    {
        $products = $this->store->read('products');
        $product = null;

        foreach ($products as $item) {
            if ((string) ($item['id'] ?? '') === $id) {
                $product = $item;
                break;
            }
        }

        if (!$product) {
            $this->renderNotFound();
            return;
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $baseUrl = rtrim((string) getenv('APP_PUBLIC_URL'), '/');
        if ($baseUrl === '') {
            $baseUrl = $scheme . '://' . $host;
        }
        $canonicalUrl = $baseUrl . '/product/' . rawurlencode($id);

        $name = (string) ($product['name'] ?? 'Товар');
        $category = (string) ($product['category'] ?? '');
        $description = trim((string) ($product['description'] ?? ''));
        $metaDescription = $description !== '' ? $description : ('Купить ' . $name . ' в магазине ИП Логуш.');
        $metaKeywords = trim($name . ($category !== '' ? (', ' . $category) : '') . ', купить трикотаж');
        $ogTitle = $name . ' | Магазин ИП Логуш';
        $ogDescription = $metaDescription;
        $ogUrl = $canonicalUrl;
        $ogImage = rtrim($baseUrl, '/') . '/apple-touch-icon.png';

        $this->renderSite(
            $ogTitle,
            'pages/product.php',
            ['product' => $product],
            $metaDescription,
            $metaKeywords,
            $canonicalUrl,
            $ogTitle,
            $ogDescription,
            $ogUrl,
            $ogImage
        );
    }

    private function renderCart(): void
    {
        [$items, $total] = $this->buildCartItems();
        $this->renderSite('Корзина', 'pages/cart.php', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    private function renderCheckout(): void
    {
        [$items, $total] = $this->buildCartItems();
        $this->renderSite('Оформление заказа', 'pages/checkout.php', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    private function renderQuote(): void
    {
        $sent = ($_GET['sent'] ?? '') === '1';
        $this->renderSite('Запрос предложения', 'pages/quote.php', ['sent' => $sent]);
    }

    private function handleLogin(string $method): void
    {
        if ($this->auth->check()) {
            $this->redirect('/admin/products');
            return;
        }

        $error = '';
        if ($method === 'POST') {
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $remember = isset($_POST['rememberMe']) || isset($_POST['remember-me']);
            $user = $this->auth->login($email, $password, $remember);
            if ($user) {
                $this->redirect('/admin/products');
                return;
            }
            $error = 'Неверный email или пароль';
        }

        $this->renderAdminLayout('Вход', 'pages/login.php', [
            'error' => $error,
            'email' => (string) ($_POST['email'] ?? ''),
        ]);
    }

    private function handleAdminPage(string $path): void
    {
        if (!$this->auth->check()) {
            $this->redirect('/login');
            return;
        }

        $products = $this->store->read('products');
        $orders = $this->store->read('orders');
        $categories = $this->store->read('categories');
        $colors = $this->store->read('colors');
        $sizes = $this->store->read('sizes');
        $users = $this->store->read('users');
        $settings = SettingsDefaults::merge($this->store->read('settings'));

        $section = 'products';
        $entityId = '';

        if ($path === '/admin' || $path === '/admin/products') {
            $section = 'products';
        } elseif ($path === '/admin/products/new') {
            $section = 'product-new';
        } elseif (preg_match('#^/admin/products/([^/]+)/edit$#', $path, $matches) === 1) {
            $section = 'product-edit';
            $entityId = rawurldecode($matches[1]);
        } elseif ($path === '/admin/categories') {
            $section = 'categories';
        } elseif ($path === '/admin/colors') {
            $section = 'colors';
        } elseif ($path === '/admin/sizes') {
            $section = 'sizes';
        } elseif ($path === '/admin/orders') {
            $section = 'orders';
        } elseif (preg_match('#^/admin/orders/([^/]+)$#', $path, $matches) === 1) {
            $section = 'order-show';
            $entityId = rawurldecode($matches[1]);
        } elseif ($path === '/admin/users') {
            $section = 'users';
        } elseif ($path === '/admin/users/edit') {
            $section = 'users-edit';
            $entityId = (string) ($_GET['id'] ?? '');
        } elseif ($path === '/admin/settings') {
            $section = 'settings';
        } else {
            $this->renderNotFound();
            return;
        }

        $this->renderAdminLayout('Админ-панель', 'pages/admin.php', [
            'section' => $section,
            'entityId' => $entityId,
            'products' => $products,
            'orders' => $orders,
            'categories' => $categories,
            'colors' => $colors,
            'sizes' => $sizes,
            'users' => $users,
            'settings' => $settings,
            'currentPath' => $path,
            'authUser' => $this->auth->user(),
        ]);
    }

    private function addToCart(): void
    {
        $productId = trim((string) ($_POST['productId'] ?? ''));
        $color = trim((string) ($_POST['color'] ?? ''));
        $size = trim((string) ($_POST['size'] ?? ''));
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

        if ($productId === '' || $color === '' || $size === '') {
            $this->redirect('/cart');
            return;
        }

        $cart = $_SESSION['cart'] ?? [];
        if (!is_array($cart)) {
            $cart = [];
        }

        $updated = false;
        foreach ($cart as $index => $item) {
            if (!is_array($item)) {
                continue;
            }
            $sameProduct = (string) ($item['productId'] ?? '') === $productId;
            $sameColor = (string) ($item['color'] ?? '') === $color;
            $sameSize = (string) ($item['size'] ?? '') === $size;
            if ($sameProduct && $sameColor && $sameSize) {
                $cart[$index]['quantity'] = ((int) ($item['quantity'] ?? 0)) + $quantity;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $cart[] = [
                'productId' => $productId,
                'color' => $color,
                'size' => $size,
                'quantity' => $quantity,
            ];
        }

        $_SESSION['cart'] = $cart;
        $this->redirect('/cart');
    }

    private function removeFromCart(): void
    {
        $index = (int) ($_POST['index'] ?? -1);
        $cart = $_SESSION['cart'] ?? [];
        if (is_array($cart) && isset($cart[$index])) {
            unset($cart[$index]);
            $_SESSION['cart'] = array_values($cart);
        }

        $this->redirect('/cart');
    }

    private function checkout(): void
    {
        [$items, $total] = $this->buildCartItems();
        if (count($items) === 0) {
            $this->redirect('/cart');
            return;
        }

        $customerName = trim((string) ($_POST['customerName'] ?? ''));
        $customerEmail = trim((string) ($_POST['customerEmail'] ?? ''));
        $customerPhone = trim((string) ($_POST['customerPhone'] ?? ''));
        $deliveryAddress = trim((string) ($_POST['deliveryAddress'] ?? ''));
        $paymentMethod = trim((string) ($_POST['paymentMethod'] ?? ''));

        if ($customerName === '' || $customerEmail === '' || $customerPhone === '' || $deliveryAddress === '' || $paymentMethod === '') {
            $this->redirect('/checkout');
            return;
        }

        $orders = $this->store->read('orders');
        $id = $this->store->nextId('orders');

        $orderItems = array_map(static fn(array $item): array => [
            'productId' => (string) $item['productId'],
            'productName' => (string) $item['name'],
            'quantity' => (int) $item['quantity'],
            'price' => (float) $item['price'],
            'color' => (string) $item['color'],
            'size' => (string) $item['size'],
        ], $items);

        $order = [
            'id' => $id,
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'customerPhone' => $customerPhone,
            'items' => $orderItems,
            'totalAmount' => $total,
            'status' => 'new',
            'deliveryAddress' => $deliveryAddress,
            'paymentMethod' => $paymentMethod,
            'createdAt' => gmdate('c'),
        ];

        $orders[] = $order;
        $this->store->write('orders', $orders);
        $_SESSION['cart'] = [];

        $this->redirect('/order-success?id=' . rawurlencode($id));
    }

    private function saveQuote(): void
    {
        $quotes = $this->store->read('quotes');
        $id = $this->store->nextId('quotes');

        $quotes[] = [
            'id' => $id,
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'message' => trim((string) ($_POST['message'] ?? '')),
            'createdAt' => gmdate('c'),
        ];

        $this->store->write('quotes', $quotes);
        $this->redirect('/quote?sent=1');
    }

    private function buildCartItems(): array
    {
        $products = $this->store->read('products');
        $byId = [];
        foreach ($products as $product) {
            if (!is_array($product)) {
                continue;
            }
            $byId[(string) ($product['id'] ?? '')] = $product;
        }

        $cart = $_SESSION['cart'] ?? [];
        $items = [];
        $total = 0.0;

        if (!is_array($cart)) {
            return [[], 0.0];
        }

        foreach ($cart as $item) {
            if (!is_array($item)) {
                continue;
            }

            $productId = (string) ($item['productId'] ?? '');
            $product = $byId[$productId] ?? null;
            if (!is_array($product)) {
                continue;
            }

            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $price = (float) ($product['price'] ?? 0);
            $subtotal = $price * $quantity;
            $total += $subtotal;

            $items[] = [
                'productId' => $productId,
                'name' => (string) ($product['name'] ?? ''),
                'price' => $price,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'color' => (string) ($item['color'] ?? ''),
                'size' => (string) ($item['size'] ?? ''),
                'image' => (string) (($product['images'][0] ?? '/images/product-placeholder.svg')),
            ];
        }

        return [$items, $total];
    }

    private function renderSite(
        string $title,
        string $template,
        array $vars = [],
        string $metaDescription = '',
        string $metaKeywords = '',
        ?string $canonicalUrl = null,
        ?string $ogTitle = null,
        ?string $ogDescription = null,
        ?string $ogUrl = null,
        ?string $ogImage = null
    ): void
    {
        $content = $this->view->render($template, $vars);
        echo $this->view->render('layout-new.php', [
            'title' => $title,
            'content' => $content,
            'currentPath' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
            'authUser' => $this->auth->user(),
            'metaDescription' => $metaDescription,
            'metaKeywords' => $metaKeywords,
            'canonicalUrl' => $canonicalUrl,
            'ogTitle' => $ogTitle,
            'ogDescription' => $ogDescription,
            'ogUrl' => $ogUrl,
            'ogImage' => $ogImage,
        ]);
    }

    private function renderAdminLayout(string $title, string $template, array $vars = []): void
    {
        $content = $this->view->render($template, $vars);
        echo $this->view->render('layout-admin.php', [
            'title' => $title,
            'content' => $content,
            'currentPath' => (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'),
            'authUser' => $this->auth->user(),
        ]);
    }

    private function renderNotFound(): void
    {
        http_response_code(404);
        $content = $this->view->render('pages/not_found.php');
        $this->renderSite('Страница не найдена', 'pages/not_found.php', [], 'Страница не найдена', '', null);
    }

    private function sendRobots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\nAllow: /\nSitemap: /sitemap.xml\n";
    }

    private function sendSitemap(): void
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = $scheme . '://' . $host;

        $urls = [
            '/', '/about', '/services', '/contact', '/vacancies', '/sale', '/colors', '/size-table', '/quote',
        ];

        foreach ($this->store->read('products') as $product) {
            if (!is_array($product)) {
                continue;
            }
            $id = (string) ($product['id'] ?? '');
            if ($id !== '') {
                $urls[] = '/product/' . rawurlencode($id);
            }
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $url) {
            echo "  <url><loc>" . htmlspecialchars($base . $url, ENT_XML1, 'UTF-8') . "</loc></url>\n";
        }
        echo "</urlset>\n";
    }

    private function redirect(string $location): void
    {
        header('Location: ' . $location);
        http_response_code(302);
    }

    private function renderSnapshotPage(string $path): bool
    {
        return $this->snapshots->render($path);
    }

    private function renderSale(): void
    {
        $baseDir = dirname(__DIR__);
        $snapshotPath = $baseDir . '/storage/ssr/sale.html';
        if (!is_file($snapshotPath)) {
            $this->renderNotFound();
            return;
        }

        $html = (string) (file_get_contents($snapshotPath) ?: '');
        if ($html === '') {
            $this->renderNotFound();
            return;
        }

        $products = $this->store->read('products');
        $categories = $this->store->read('categories');
        $cardsHtml = $this->renderSaleProductCards($products, $categories);

        // Replace the product grid content from the snapshot with dynamic cards.
        $pattern = '~(<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">)(.*?)(</div><div class="mt-16)~s';
        $replacement = '$1' . $cardsHtml . '$3';
        $next = preg_replace($pattern, $replacement, $html, 1);
        if (is_string($next) && $next !== '') {
            $html = $next;
        }

        // Minimal JS for category filtering (no React).
        $script = '<script>(function(){' .
            'var tabs=document.querySelectorAll(".mb-12.flex.flex-wrap.gap-4 button");' .
            'var cards=document.querySelectorAll("[data-sale-card]");' .
            'var setActive=function(btn){tabs.forEach(function(b){b.classList.remove("bg-black","text-white");b.classList.add("bg-white","text-black");b.classList.add("hover:bg-black","hover:text-white");});btn.classList.add("bg-black","text-white");btn.classList.remove("bg-white","text-black");};' .
            'var apply=function(cat){cards.forEach(function(c){var mc=c.getAttribute("data-main-category")||"";c.style.display=(cat==="Все"||mc===cat)?"":"none";});};' .
            'tabs.forEach(function(btn){btn.addEventListener("click",function(){var t=(btn.textContent||"").trim();setActive(btn);apply(t);});});' .
            'var first=tabs[0];if(first){setActive(first);apply("Все");}' .
        '})();</script>';

        if (stripos($html, '</body>') !== false) {
            $html = preg_replace('~</body>~i', $script . '</body>', $html, 1) ?: ($html . $script);
        } else {
            $html .= $script;
        }

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    private function renderSaleProductCards(array $products, array $categories): string
    {
        $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

        $mainTabs = ['ЖЕНСКАЯ', 'МУЖСКАЯ', 'ДЕТСКАЯ'];
        $normalize = static function (string $value): string {
            $trim = trim($value);
            $up = function_exists('mb_strtoupper') ? mb_strtoupper($trim, 'UTF-8') : strtoupper($trim);
            return (string) $up;
        };

        // Build category->mainCategory map (like React buildMainCategoryResolver).
        $byId = [];
        foreach ($categories as $c) {
            if (!is_array($c)) {
                continue;
            }
            $id = (string) ($c['id'] ?? '');
            if ($id !== '') {
                $byId[$id] = $c;
            }
        }

        $map = [];
        foreach ($categories as $c) {
            if (!is_array($c)) {
                continue;
            }
            $name = $normalize((string) ($c['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $parentName = '';
            $parentId = $c['parentId'] ?? null;
            if ($parentId !== null && $parentId !== '') {
                $pid = (string) $parentId;
                if (isset($byId[$pid]) && is_array($byId[$pid])) {
                    $parentName = $normalize((string) ($byId[$pid]['name'] ?? ''));
                }
            } elseif (isset($c['parentName'])) {
                $parentName = $normalize((string) ($c['parentName'] ?? ''));
            }

            if (in_array($name, $mainTabs, true)) {
                $map[$name] = $name;
            }
            if ($parentName !== '' && in_array($parentName, $mainTabs, true)) {
                $map[$name] = $parentName;
            }
        }

        $resolveMain = static function (string $category) use ($map, $mainTabs, $normalize): ?string {
            $key = $normalize($category);
            if ($key === '') {
                return null;
            }
            if (in_array($key, $mainTabs, true)) {
                return $key;
            }
            return $map[$key] ?? null;
        };

        $getProductImage = static function (array $images): string {
            $first = '';
            if (isset($images[0])) {
                $first = trim((string) $images[0]);
            }
            if ($first === '') {
                return '';
            }
            if (str_starts_with($first, '/api/upload?key=')) {
                return $first;
            }
            if (preg_match('~^https?://(?:www\\.)?logush\\.ru/(.+)$~i', $first, $m) === 1) {
                return '/api/upload?key=' . rawurlencode((string) ($m[1] ?? ''));
            }
            return $first;
        };

        $getColorSwatch = static function (string $color): string {
            $value = trim($color);
            if ($value === '') {
                return '#d1d5db';
            }
            if (str_starts_with($value, '#') || str_starts_with($value, 'rgb') || str_starts_with($value, 'hsl')) {
                return $value;
            }
            $normalized = function_exists('mb_strtolower')
                ? (string) mb_strtolower($value, 'UTF-8')
                : strtolower($value);
            $palette = [
                'черный' => '#000000',
                'белый' => '#ffffff',
                'серый' => '#808080',
                'светло-серый' => '#d1d5db',
                'темно-серый' => '#374151',
                'бежевый' => '#d2b48c',
                'синий' => '#1d4ed8',
                'голубой' => '#38bdf8',
                'красный' => '#dc2626',
                'зеленый' => '#16a34a',
                'желтый' => '#facc15',
                'оранжевый' => '#f97316',
                'розовый' => '#ec4899',
                'фиолетовый' => '#9333ea',
                'коричневый' => '#7c3f00',
                'бордовый' => '#7f1d1d',
                'хаки' => '#78716c',
                'молочный' => '#f8fafc',
                'ivory' => '#fffff0',
                'black' => '#000000',
                'white' => '#ffffff',
                'gray' => '#808080',
                'grey' => '#808080',
                'beige' => '#d2b48c',
                'blue' => '#1d4ed8',
                'red' => '#dc2626',
                'green' => '#16a34a',
                'yellow' => '#facc15',
                'orange' => '#f97316',
                'pink' => '#ec4899',
                'purple' => '#9333ea',
                'brown' => '#7c3f00',
            ];
            return $palette[$normalized] ?? '#d1d5db';
        };

        $saleProducts = [];
        foreach ($products as $p) {
            if (!is_array($p)) {
                continue;
            }
            $inStock = !isset($p['inStock']) ? true : (bool) $p['inStock'];
            $stock = (int) ($p['stock'] ?? 0);
            if (!$inStock || $stock <= 0) {
                continue;
            }
            $main = $resolveMain((string) ($p['category'] ?? ''));
            if ($main === null) {
                continue;
            }
            $saleProducts[] = ['p' => $p, 'main' => $main];
        }

        if (count($saleProducts) === 0) {
            return '<div class="md:col-span-2 lg:col-span-3 rounded-lg border border-gray-200 p-8 text-center text-gray-600">Товары пока не добавлены в админке или отсутствуют в наличии.</div>';
        }

        $out = '';
        foreach ($saleProducts as $row) {
            $p = $row['p'];
            $main = (string) $row['main'];

            $id = (string) ($p['id'] ?? '');
            $name = (string) ($p['name'] ?? '');
            $category = (string) ($p['category'] ?? '');
            $price = (float) ($p['price'] ?? 0);
            $images = is_array($p['images'] ?? null) ? $p['images'] : [];
            $colors = is_array($p['colors'] ?? null) ? $p['colors'] : [];

            $img = $getProductImage($images);
            $colorDots = '';
            $shown = 0;
            foreach ($colors as $c) {
                if ($shown >= 5) {
                    break;
                }
                $color = (string) $c;
                $swatch = $getColorSwatch($color);
                $colorDots .= '<span title="' . $e($color) . '" class="inline-block h-4 w-4 rounded-full border border-gray-400" style="background-color: ' . $e($swatch) . '"></span>';
                $shown++;
            }
            if (count($colors) > 5) {
                $colorDots .= '<span class="ml-1 text-xs text-gray-500">+' . $e((string) (count($colors) - 5)) . '</span>';
            }

            $priceText = number_format($price, 0, '.', ' ');
            $productUrl = '/product/' . rawurlencode($id);

            $imageBlock = '';
            if ($img !== '') {
                $imageBlock = '<img src="' . $e($img) . '" alt="' . $e($name) . '" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">';
            } else {
                // Heroicons PhotoIcon outline.
                $imageBlock = '<div class="absolute inset-0 flex items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-16 w-16"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.159 2.159m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v10.5a1.5 1.5 0 0 0 1.5 1.5Z" /></svg></div>';
            }

            $out .=
                '<div class="group" data-sale-card data-main-category="' . $e($main) . '">' .
                    '<a href="' . $e($productUrl) . '">' .
                        '<div class="relative overflow-hidden bg-gray-100 mb-4 aspect-[3/4]">' .
                            $imageBlock .
                            '<div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>' .
                        '</div>' .
                    '</a>' .
                    '<div class="space-y-2">' .
                        '<p class="text-xs text-gray-500 uppercase tracking-wider">' . $e($category) . '</p>' .
                        '<a href="' . $e($productUrl) . '">' .
                            '<h3 class="text-lg font-medium text-black hover:text-gray-600 transition-colors">' . $e($name) . '</h3>' .
                        '</a>' .
                        '<div class="flex items-center gap-2">' .
                            '<span class="text-sm text-gray-600">Цвета:</span>' .
                            '<div class="flex items-center gap-1.5">' . $colorDots . '</div>' .
                        '</div>' .
                        '<div class="flex items-center justify-between pt-2">' .
                            '<span class="text-xl font-bold text-black">' . $e($priceText) . ' ₽</span>' .
                            '<a href="' . $e($productUrl) . '" class="group flex items-center gap-x-2 px-4 py-3 text-sm font-light transition-colors duration-300 bg-black text-white hover:bg-orange-400 hover:text-black">' .
                                '<span>Подробнее</span>' .
                                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4 transition-colors"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path></svg>' .
                            '</a>' .
                        '</div>' .
                    '</div>' .
                '</div>';
        }

        return $out;
    }
}
