<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$title = (string) ($page['title'] ?? 'Страница');
$subtitle = (string) ($page['subtitle'] ?? '');
$p1 = (string) ($page['paragraph1'] ?? '');
$p2 = (string) ($page['paragraph2'] ?? '');
?>
<section class="page-header">
  <span class="eyebrow">ИП Логуш</span>
  <h1><?= $e($title) ?></h1>
  <?php if ($subtitle !== ''): ?>
    <p class="subtitle"><?= $e($subtitle) ?></p>
  <?php endif; ?>
</section>

<section class="content-block">
  <p><?= $e($p1) ?></p>
  <p><?= $e($p2) ?></p>
</section>

<section class="cta-box">
  <h2>Нужен расчет партии?</h2>
  <p>Оставьте запрос, и мы подготовим предложение по срокам и цене.</p>
  <a class="btn btn-dark" href="/quote">Запросить предложение</a>
</section>
