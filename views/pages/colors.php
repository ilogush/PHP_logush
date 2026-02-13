<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="page-header">
  <h1>Доступные цвета</h1>
</section>

<div class="chips-wrap">
  <?php foreach ($colors as $color): ?>
    <?php if (!is_array($color)) { continue; } ?>
    <span class="chip"><?= $e((string) ($color['name'] ?? '')) ?></span>
  <?php endforeach; ?>
</div>
