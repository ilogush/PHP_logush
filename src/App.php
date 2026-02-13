<?php

declare(strict_types=1);

namespace Logush;

final class App
{
    private DataStore $store;
    private Auth $auth;
    private View $view;
    private SnapshotRenderer $snapshots;
    private PageController $pages;
    private ApiController $api;

    public function __construct(private readonly string $baseDir)
    {
        $this->store = new DataStore($this->baseDir);
        $this->store->seed();

        $this->auth = new Auth($this->store);
        $this->view = new View($this->baseDir . '/views');
        $this->snapshots = new SnapshotRenderer($this->baseDir);
        $this->pages = new PageController($this->store, $this->auth, $this->view, $this->snapshots);
        $this->api = new ApiController($this->baseDir, $this->store, $this->auth);
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
            $this->api->handle($method, $path);
            return;
        }

        $this->pages->handle($method, $path);
    }
}
