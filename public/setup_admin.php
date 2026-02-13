<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$baseDir = dirname(__DIR__);

$token = trim((string) getenv('APP_SETUP_TOKEN'));
if ($token === '') {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$provided = (string) ($_GET['token'] ?? '');
if (!hash_equals($token, $provided)) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$message = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $name = trim((string) ($_POST['name'] ?? 'Администратор'));

    if ($email === '' || $password === '') {
        $error = 'Email и пароль обязательны';
    } else {
        try {
            $store = new Logush\DataStore($baseDir);
            $users = $store->read('users');
            if (!is_array($users)) {
                $users = [];
            }

            $emailNorm = function_exists('mb_strtolower')
                ? (string) mb_strtolower($email, 'UTF-8')
                : strtolower($email);

            $foundIndex = -1;
            foreach ($users as $idx => $u) {
                if (!is_array($u)) {
                    continue;
                }
                $uEmail = trim((string) ($u['email'] ?? ''));
                $uEmailNorm = function_exists('mb_strtolower')
                    ? (string) mb_strtolower($uEmail, 'UTF-8')
                    : strtolower($uEmail);
                if ($uEmailNorm === $emailNorm) {
                    $foundIndex = (int) $idx;
                    break;
                }
            }

            $now = gmdate('c');
            $payload = [
                'name' => $name,
                'email' => $email,
                'phone' => '',
                'address' => '',
                'role' => 'admin',
                'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
                'userType' => 'admin',
                'updatedAt' => $now,
            ];

            if ($foundIndex >= 0) {
                $existing = is_array($users[$foundIndex]) ? $users[$foundIndex] : [];
                $payload['id'] = (string) ($existing['id'] ?? '1');
                $payload['createdAt'] = (string) (($existing['createdAt'] ?? '') ?: $now);
                $users[$foundIndex] = array_merge($existing, $payload);
                $store->write('users', $users);
                $message = 'Пользователь обновлен';
            } else {
                $maxId = 0;
                foreach ($users as $u) {
                    if (!is_array($u)) continue;
                    $id = (int) ($u['id'] ?? 0);
                    if ($id > $maxId) $maxId = $id;
                }
                $payload['id'] = (string) max(1, $maxId + 1);
                $payload['createdAt'] = $now;
                $users[] = $payload;
                $store->write('users', $users);
                $message = 'Пользователь создан';
            }
        } catch (Throwable $t) {
            $error = 'Ошибка: ' . $t->getMessage();
        }
    }
}

?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Setup Admin</title>
  <link rel="stylesheet" href="/assets/root-DXB_3M8-.css">
  <script src="/js/toast.js" defer></script>
</head>
<body class="__className_f367f3 bg-gray-100 text-black">
  <main class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
      <h1 class="text-xl font-semibold text-gray-900 mb-2">Создать/обновить администратора</h1>
      <p class="text-sm text-gray-600 mb-6">После настройки удалите файл <code class="font-mono">public/setup_admin.php</code> или очистите <code class="font-mono">APP_SETUP_TOKEN</code>.</p>

      <form method="post" class="space-y-4">
        <div>
          <label class="block text-xs text-gray-600 mb-1">Имя</label>
          <input name="name" type="text" class="w-full h-10 px-3 py-2 border border-gray-300 rounded-lg" value="<?= $e((string) ($_POST['name'] ?? 'Администратор')) ?>">
        </div>
        <div>
          <label class="block text-xs text-gray-600 mb-1">Email</label>
          <input name="email" type="email" required class="w-full h-10 px-3 py-2 border border-gray-300 rounded-lg" value="<?= $e((string) ($_POST['email'] ?? 'ilogush@icloud.com')) ?>">
        </div>
        <div>
          <label class="block text-xs text-gray-600 mb-1">Пароль</label>
          <input name="password" type="text" required class="w-full h-10 px-3 py-2 border border-gray-300 rounded-lg" value="<?= $e((string) ($_POST['password'] ?? '')) ?>">
        </div>
        <button type="submit" class="bg-black text-white px-4 h-10 rounded-lg hover:bg-orange-400 hover:text-black transition-colors">Сохранить</button>
      </form>
    </div>
  </main>

  <?php if ($message !== ''): ?>
    <script>
      window.addEventListener('DOMContentLoaded', function () {
        window.showToast?.(<?= json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>, 'success');
      });
    </script>
  <?php endif; ?>
  <?php if ($error !== ''): ?>
    <script>
      window.addEventListener('DOMContentLoaded', function () {
        window.showToast?.(<?= json_encode($error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>, 'error');
      });
    </script>
  <?php endif; ?>
</body>
</html>

