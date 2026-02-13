<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$phone = (string) ($settings['phone'] ?? '');
$email = (string) ($settings['email'] ?? '');
$telegram = (string) ($settings['telegram'] ?? '');
$whatsapp = (string) ($settings['whatsapp'] ?? '');
?>
<div class="min-h-[calc(100vh-96px)] bg-white pb-16">
  <div class="py-8">
    <div class="max-w-5xl">
      <h1 class="text-3xl font-bold text-black mb-8 tracking-wider">КОНТАКТЫ /</h1>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <article class="border border-gray-500 p-6">
          <h2 class="text-sm font-medium text-black uppercase tracking-wider mb-2">Телефон</h2>
          <p class="text-lg text-black"><?= $e($phone) ?></p>
        </article>

        <article class="border border-gray-500 p-6">
          <h2 class="text-sm font-medium text-black uppercase tracking-wider mb-2">Email</h2>
          <p class="text-lg text-black"><?= $e($email) ?></p>
        </article>

        <article class="border border-gray-500 p-6">
          <h2 class="text-sm font-medium text-black uppercase tracking-wider mb-2">Telegram</h2>
          <?php if ($telegram !== ''): ?>
            <a class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-auto min-w-[180px] bg-black text-white hover:bg-orange-400 hover:text-black shadow-sm hover:shadow-md" href="<?= $e($telegram) ?>" target="_blank" rel="noopener">
              <span>Связаться</span>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path>
              </svg>
            </a>
          <?php else: ?>
            <p class="text-gray-600">Не указан</p>
          <?php endif; ?>
        </article>

        <article class="border border-gray-500 p-6">
          <h2 class="text-sm font-medium text-black uppercase tracking-wider mb-2">WhatsApp</h2>
          <?php if ($whatsapp !== ''): ?>
            <a class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-auto min-w-[180px] bg-black text-white hover:bg-orange-400 hover:text-black shadow-sm hover:shadow-md" href="<?= $e($whatsapp) ?>" target="_blank" rel="noopener">
              <span>Написать</span>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path>
              </svg>
            </a>
          <?php else: ?>
            <p class="text-gray-600">Не указан</p>
          <?php endif; ?>
        </article>
      </div>
    </div>
  </div>
</div>
