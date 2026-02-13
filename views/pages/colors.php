<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="min-h-[calc(100vh-96px)] bg-white pb-16">
  <div class="py-8">
    <div class="max-w-5xl">
      <h1 class="text-3xl font-bold text-black mb-8 tracking-wider">ЦВЕТА</h1>

      <div class="flex flex-wrap gap-3">
        <?php foreach ($colors as $color): ?>
          <?php if (!is_array($color)) { continue; } ?>
          <?php $name = (string) ($color['name'] ?? ''); ?>
          <?php if ($name === '') { continue; } ?>
          <span class="inline-flex items-center rounded-full border border-gray-500 px-4 py-2 text-sm text-black"><?= $e($name) ?></span>
        <?php endforeach; ?>
      </div>

      <div class="pt-6">
        <a class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-auto min-w-[180px] bg-white text-black border border-black hover:bg-black hover:text-white shadow-sm hover:shadow-md" href="/sale">
          <span>В магазин</span>
        </a>
      </div>
    </div>
  </div>
</div>
