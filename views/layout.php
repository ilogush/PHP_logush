<?php
$title = isset($title) ? (string) $title : 'ИП Логуш';
$content = isset($content) ? (string) $content : '';
$currentPath = isset($currentPath) ? (string) $currentPath : '/';
$authUser = isset($authUser) && is_array($authUser) ? $authUser : null;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="index, follow">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/css/4dc721bd223196f3.css">
  <link rel="icon" href="/favicon.ico">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
</head>
<body>
  <?php require __DIR__ . '/partials/header.php'; ?>
  <main class="container main-content">
    <?= $content ?>
  </main>
  <?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
