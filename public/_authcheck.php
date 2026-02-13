<?php

declare(strict_types=1);

// Temporary debug endpoint. Delete after use.

const TOKEN = 'JRmatVdAHzBTrdrcA7kcYpPNHB3bODLScVi1sXB6MlE';

if (!isset($_GET['token']) || !hash_equals(TOKEN, (string) $_GET['token'])) {
    http_response_code(404);
    exit;
}

$baseDir = dirname(__DIR__);
require $baseDir . '/src/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

$store = new Logush\DataStore($baseDir);

echo "db=" . ($store->usesDatabase() ? "ready\n" : "not_ready\n");

$users = $store->read('users');
echo "users_count=" . (is_array($users) ? count($users) : 0) . "\n";

if (is_array($users)) {
    foreach ($users as $u) {
        if (!is_array($u)) continue;
        $email = (string)($u['email'] ?? '');
        $hash = (string)($u['passwordHash'] ?? '');
        echo "email=" . $email . "\n";
        echo "hash_prefix=" . substr($hash, 0, 7) . "\n";
        echo "verify_admin123=" . (password_verify('admin123', $hash) ? "1" : "0") . "\n";
    }
}
