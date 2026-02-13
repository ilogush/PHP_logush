<?php

declare(strict_types=1);

namespace Logush;

final class Auth
{
    public function __construct(private readonly DataStore $store)
    {
    }

    public function user(): ?array
    {
        $sessionUser = $_SESSION['admin_user'] ?? null;
        return is_array($sessionUser) ? $sessionUser : null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function login(string $email, string $password, bool $remember): ?array
    {
        $users = $this->store->read('users');
        $found = null;

        foreach ($users as $index => $user) {
            if (!is_array($user)) {
                continue;
            }
            $userEmail = mb_strtolower(trim((string) ($user['email'] ?? '')));
            if ($userEmail === mb_strtolower(trim($email))) {
                $found = ['index' => $index, 'user' => $user];
                break;
            }
        }

        if (!$found) {
            return null;
        }

        $user = $found['user'];
        $hash = (string) ($user['passwordHash'] ?? '');

        if ($hash === '') {
            return null;
        }

        if (!password_verify($password, $hash)) {
            return null;
        }

        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $users[$found['index']]['passwordHash'] = password_hash($password, PASSWORD_DEFAULT);
            $this->store->write('users', $users);
        }

        $safeUser = [
            'id' => (string) ($user['id'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'role' => (string) ($user['role'] ?? 'admin'),
            'name' => (string) ($user['name'] ?? ''),
        ];

        session_regenerate_id(true);
        $_SESSION['admin_user'] = $safeUser;

        if ($remember) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            setcookie(session_name(), session_id(), [
                'expires' => time() + (60 * 60 * 24 * 30),
                'path' => '/',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        return $safeUser;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
        }

        session_destroy();
    }
}
