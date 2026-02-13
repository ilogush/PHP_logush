<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$title = (string) ($page['title'] ?? 'Страница');
$subtitle = (string) ($page['subtitle'] ?? '');
$p1 = (string) ($page['paragraph1'] ?? '');
$p2 = (string) ($page['paragraph2'] ?? '');
?>
<div class="min-h-[calc(100vh-96px)] bg-white pb-16">
  <div class="py-8">
    <div class="max-w-4xl">
      <div class="mb-8">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">ИП Логуш</p>
        <h1 class="text-3xl font-bold text-black tracking-wider"><?= $e($title) ?></h1>
        <?php if ($subtitle !== ''): ?>
          <p class="text-gray-700 mt-3"><?= $e($subtitle) ?></p>
        <?php endif; ?>
      </div>

      <div class="space-y-4 border border-gray-500 p-6">
        <?php if ($p1 !== ''): ?>
          <p class="text-gray-700 leading-relaxed"><?= $e($p1) ?></p>
        <?php endif; ?>
        <?php if ($p2 !== ''): ?>
          <p class="text-gray-700 leading-relaxed"><?= $e($p2) ?></p>
        <?php endif; ?>
      </div>

      <div class="border border-gray-500 p-6 mt-8">
        <h2 class="text-xl font-bold text-black mb-2 tracking-wider">Нужен расчет партии?</h2>
        <p class="text-gray-700 mb-6">Оставьте запрос, и мы подготовим предложение по срокам и цене.</p>
        <a class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-auto min-w-[220px] bg-black text-white hover:bg-orange-400 hover:text-black shadow-sm hover:shadow-md" href="/quote">
          <span>Запросить предложение</span>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path>
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path>
          </svg>
        </a>
      </div>
    </div>
  </div>
</div>
