<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="min-h-[calc(100vh-96px)] bg-white pb-16">
  <div class="py-8">
    <div class="max-w-3xl">
      <h1 class="text-3xl font-bold text-black mb-8 tracking-wider">ЗАПРОС КОММЕРЧЕСКОГО ПРЕДЛОЖЕНИЯ</h1>

      <?php if (!empty($sent)): ?>
        <div class="border border-gray-500 p-6 mb-8">
          <p class="text-black">Заявка отправлена. Мы свяжемся с вами.</p>
        </div>
      <?php endif; ?>

      <form class="space-y-6 border border-gray-500 p-6" method="post" action="/quote">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-black mb-2">Имя *</label>
            <input type="text" name="name" class="w-full border border-gray-500 px-4 py-2 focus:outline-none focus:border-black" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-black mb-2">Email *</label>
            <input type="email" name="email" class="w-full border border-gray-500 px-4 py-2 focus:outline-none focus:border-black" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-black mb-2">Телефон *</label>
            <input type="text" name="phone" class="w-full border border-gray-500 px-4 py-2 focus:outline-none focus:border-black" required>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-black mb-2">Комментарий</label>
          <textarea name="message" rows="4" class="w-full border border-gray-500 px-4 py-2 focus:outline-none focus:border-black" placeholder="Нужный объем, сроки, требования"></textarea>
        </div>

        <div class="flex flex-col sm:flex-row gap-4">
          <button class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-full sm:w-auto min-w-[180px] bg-black text-white hover:bg-orange-400 hover:text-black shadow-sm hover:shadow-md" type="submit">
            <span>Отправить</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path>
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path>
            </svg>
          </button>
          <a class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-full sm:w-auto min-w-[180px] bg-white text-black border border-black hover:bg-black hover:text-white shadow-sm hover:shadow-md" href="/sale">
            <span>В магазин</span>
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
