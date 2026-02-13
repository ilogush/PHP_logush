<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="success-screen">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon-lg" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
  </svg>
  <h1>Заказ принят</h1>
  <?php if ($orderId !== ''): ?>
    <p>Номер заказа: <strong><?= $e($orderId) ?></strong></p>
  <?php endif; ?>
  <a class="btn btn-dark" href="/">Вернуться на главную</a>
</section>
