<?php

declare(strict_types=1);

namespace Logush;

final class App
{
    private DataStore $store;
    private Auth $auth;
    private View $view;
    private SnapshotRenderer $snapshots;
    private ?PageController $pages = null;
    private ?ApiController $api = null;

    public function __construct(private readonly string $baseDir)
    {
        $this->store = new DataStore($this->baseDir);
        // seed() moved to CLI scripts only (scripts/seed.php)
        // Calling it on every HTTP request was a performance bottleneck

        $this->auth = new Auth($this->store);
        $this->view = new View($this->baseDir . '/views');
        $this->snapshots = new SnapshotRenderer($this->baseDir, $this->store);
        // Controllers are now lazy-loaded
    }

    private function getPages(): PageController
    {
        if ($this->pages === null) {
            $this->pages = new PageController($this->store, $this->auth, $this->view, $this->snapshots);
        }
        return $this->pages;
    }

    private function getApi(): ApiController
    {
        if ($this->api === null) {
            $this->api = new ApiController($this->baseDir, $this->store, $this->auth);
        }
        return $this->api;
    }

    public function run(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST') {
            $override = '';
            // Some shared hostings block PUT/DELETE, so we support overrides via header or form field.
            if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $override = (string) $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
            } elseif (isset($_SERVER['HTTP_X_METHOD_OVERRIDE'])) {
                $override = (string) $_SERVER['HTTP_X_METHOD_OVERRIDE'];
            } elseif (isset($_POST['_method'])) {
                $override = (string) $_POST['_method'];
            }
            $override = strtoupper(trim($override));
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }
        $isReadRequest = $method === 'GET' || $method === 'HEAD';
        $rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = is_string($rawPath) ? rtrim($rawPath, '/') : '/';
        if ($path === '') {
            $path = '/';
        }

        // Start session only for routes that need it
        $needsSession = $this->routeNeedsSession($path, $method);
        if ($needsSession && session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Compatibility fallback for stale React Router runtime in browser cache.
        if ($isReadRequest && $path === '/__manifest') {
            http_response_code(204);
            header('X-Remix-Reload-Document: true');
            return;
        }

        // Handle React Router data requests (client-side navigation).
        if ($isReadRequest && str_ends_with($path, '.data')) {
            header('Content-Type: application/json; charset=utf-8');
            echo '{}';
            return;
        }

        if (isset($_GET['_data'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo '{}';
            return;
        }

        if (str_starts_with($path, '/api/')) {
            $this->getApi()->handle($method, $path);
            return;
        }

        $this->getPages()->handle($method, $path);
    }

    private function routeNeedsSession(string $path, string $method): bool
    {
        // Session needed for: cart, checkout, admin, login, logout
        if (str_starts_with($path, '/admin')) {
            return true;
        }
        if (str_starts_with($path, '/cart')) {
            return true;
        }
        if ($path === '/checkout' || $path === '/login' || $path === '/logout') {
            return true;
        }
        if ($path === '/quote' && $method === 'POST') {
            return true;
        }
        if (str_starts_with($path, '/api/auth')) {
            return true;
        }
        if ($path === '/api/cart/count') {
            return true;
        }
        // Admin API endpoints (users/settings/products writes, etc.) rely on session auth.
        // Without a session start, ensureAdmin() always returns Unauthorized.
        if (str_starts_with($path, '/api/')) {
            return true;
        }
        return false;
    }
}
