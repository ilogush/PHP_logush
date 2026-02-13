<?php
$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$nav = [
  '/' => 'Главная',
  '/about' => 'О нас',
  '/services' => 'Услуги',
  '/contact' => 'Контакты',
  '/vacancies' => 'Вакансии',
  '/sale' => 'Опт',
  '/colors' => 'Цвета',
  '/size-table' => 'Размеры',
  '/cart' => 'Корзина',
];
?>
<header class="site-header">
  <div class="container nav-wrap">
    <a class="brand" href="/">LOGUSH</a>
    <nav class="nav-links">
      <?php foreach ($nav as $href => $label): ?>
        <?php $active = ($currentPath === $href) ? 'active' : ''; ?>
        <a class="<?= $active ?>" href="<?= $e($href) ?>"><?= $e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="header-actions">
      <?php if ($authUser): ?>
        <a class="btn btn-light" href="/admin/products">Админ</a>
      <?php else: ?>
        <a class="btn btn-light" href="/login">Вход</a>
      <?php endif; ?>
    </div>
  </div>
</header>
