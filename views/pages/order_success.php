<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="min-h-[calc(100vh-96px)] bg-white flex items-center justify-center pb-16">
  <div class="text-center py-16">
    <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full border border-gray-500">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8 text-green-600" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
      </svg>
    </div>
    <h1 class="text-3xl md:text-4xl font-bold text-black mb-4 tracking-wider">ЗАКАЗ ПРИНЯТ</h1>
    <?php if ($orderId !== ''): ?>
      <p class="text-gray-700 mb-8">Номер заказа: <strong class="text-black"><?= $e($orderId) ?></strong></p>
    <?php else: ?>
      <p class="text-gray-700 mb-8">Мы свяжемся с вами в ближайшее время.</p>
    <?php endif; ?>
    <a class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-auto min-w-[180px] bg-black text-white hover:bg-orange-400 hover:text-black shadow-sm hover:shadow-md" href="/">
      <span>На главную</span>
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path>
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path>
      </svg>
    </a>
  </div>
</div>
