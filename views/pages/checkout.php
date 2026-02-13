<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$items = isset($items) && is_array($items) ? $items : [];
$total = isset($total) ? (float) $total : 0.0;
?>
<?php if (count($items) === 0): ?>
	  <div class="min-h-[calc(100vh-96px)] bg-white flex items-center justify-center">
	    <div class="text-center py-16">
	      <h1 class="text-3xl md:text-4xl font-bold text-black mb-4 tracking-wider">КОРЗИНА ПУСТА</h1>
	      <p class="text-gray-600 mb-8">Добавьте товары в корзину, чтобы оформить заказ</p>
	      <a class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-auto min-w-[180px] bg-black text-white hover:bg-orange-400 hover:text-black shadow-sm hover:shadow-md" href="/sale">
	        <span>Перейти в магазин</span>
	        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5">
	          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path>
	          <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path>
	        </svg>
	      </a>
	    </div>
	  </div>
<?php else: ?>
  <div class="min-h-screen bg-white pb-16">
    <div class="py-8">
      <h1 class="text-3xl font-bold text-black mb-8 tracking-wider">ОФОРМЛЕНИЕ ЗАКАЗА</h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <form id="checkout-form" class="space-y-6" method="post" action="/checkout">
            <div class="border border-gray-500 p-6">
              <h2 class="text-xl font-bold text-black mb-4 tracking-wider">КОНТАКТЫ</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-black mb-2">Имя *</label>
                  <input type="text" name="firstName" class="w-full border border-gray-500 px-4 py-2 focus:outline-none focus:border-black" required>
                </div>
                <div>
                  <label class="block text-sm font-medium text-black mb-2">Фамилия *</label>
                  <input type="text" name="lastName" class="w-full border border-gray-500 px-4 py-2 focus:outline-none focus:border-black" required>
                </div>
                <div>
                  <label class="block text-sm font-medium text-black mb-2">Email *</label>
                  <input type="email" name="customerEmail" class="w-full border border-gray-500 px-4 py-2 focus:outline-none focus:border-black" required>
                </div>
                <div>
                  <label class="block text-sm font-medium text-black mb-2">Телефон *</label>
                  <input type="tel" name="customerPhone" class="w-full border border-gray-500 px-4 py-2 focus:outline-none focus:border-black" required>
                </div>
              </div>
            </div>

            <div class="border border-gray-500 p-6">
              <h2 class="text-xl font-bold text-black mb-4 tracking-wider">ДОСТАВКА</h2>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-black mb-2">Адрес *</label>
                  <input type="text" name="address" class="w-full border border-gray-500 px-4 py-2 focus:outline-none focus:border-black" required>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-black mb-2">Город *</label>
                    <input type="text" name="city" class="w-full border border-gray-500 px-4 py-2 focus:outline-none focus:border-black" required>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-black mb-2">Индекс *</label>
                    <input type="text" name="postalCode" class="w-full border border-gray-500 px-4 py-2 focus:outline-none focus:border-black" required>
                  </div>
                </div>
              </div>
            </div>

            <div class="border border-gray-500 p-6">
              <h2 class="text-xl font-bold text-black mb-4 tracking-wider">СПОСОБ ДОСТАВКИ</h2>
              <div class="space-y-3">
                <label class="flex items-center gap-3 cursor-pointer">
                  <input type="radio" name="deliveryMethod" value="standard" checked class="sr-only">
                  <span class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors bg-gray-800" data-delivery-pill>
                    <span class="inline-block h-4 w-4 rounded-full bg-white transition-transform translate-x-4" data-delivery-dot></span>
                  </span>
                  <span class="text-sm">Стандартная доставка (5-7 дней) - 300 ₽</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input type="radio" name="deliveryMethod" value="express" class="sr-only">
                  <span class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors bg-gray-200" data-delivery-pill>
                    <span class="inline-block h-4 w-4 rounded-full bg-white transition-transform translate-x-1" data-delivery-dot></span>
                  </span>
                  <span class="text-sm">Экспресс доставка (2-3 дня) - 600 ₽</span>
                </label>
              </div>
            </div>

            <div class="border border-gray-500 p-6">
              <h2 class="text-xl font-bold text-black mb-4 tracking-wider">ОПЛАТА</h2>
              <select name="paymentMethod" class="w-full border border-gray-500 px-4 py-2 focus:outline-none focus:border-black" required>
                <option value="card">Банковская карта</option>
                <option value="invoice">По счету</option>
              </select>
            </div>

            <!-- Legacy backend fields -->
            <input type="hidden" name="customerName" value="">
            <textarea class="hidden" name="deliveryAddress" rows="1"></textarea>
          </form>
        </div>

        <div class="lg:col-span-1">
          <div class="border border-gray-500 p-6 sticky top-4">
            <h2 class="text-xl font-bold text-black mb-4 tracking-wider">ВАШ ЗАКАЗ</h2>

            <div class="space-y-4 mb-6">
              <?php foreach ($items as $item): ?>
                <div class="flex justify-between items-start gap-4 text-sm">
                  <div>
                    <p class="font-medium text-black"><?= $e((string) ($item['name'] ?? '')) ?></p>
                    <p class="text-gray-600"><?= $e((string) ($item['quantity'] ?? 1)) ?> шт.</p>
                  </div>
                  <p class="font-bold text-black"><?= $e(number_format((float) ($item['subtotal'] ?? 0), 0, '.', ' ')) ?> ₽</p>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="border-t border-gray-500 pt-4 space-y-2">
              <div class="flex justify-between text-gray-600">
                <span>Товары</span>
                <span><?= $e(number_format($total, 0, '.', ' ')) ?> ₽</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Доставка</span>
                <span data-delivery-price>300 ₽</span>
              </div>
            </div>

            <div class="border-t border-gray-500 pt-4 mt-4">
              <div class="flex justify-between text-xl font-bold text-black">
                <span>Всего</span>
                <span data-grand-total><?= $e(number_format($total + 300, 0, '.', ' ')) ?> ₽</span>
              </div>
            </div>

            <button form="checkout-form" type="submit" class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-full bg-black text-white hover:bg-orange-400 hover:text-black shadow-sm hover:shadow-md mt-6">
              <span>Подтвердить заказ</span>
            </button>

            <div class="mt-4">
              <a class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-full bg-white text-black border border-black hover:bg-black hover:text-white shadow-sm hover:shadow-md" href="/cart">
                <span>Вернуться в корзину</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/../partials/benefits.php'; ?>

  <script>
    (function () {
      const form = document.getElementById('checkout-form');
      if (!form) return;

      const setDeliveryUi = (method) => {
        const isExpress = method === 'express';
        const price = isExpress ? 600 : 300;
        const priceEl = document.querySelector('[data-delivery-price]');
        if (priceEl) priceEl.textContent = price + ' ₽';

        const goods = <?= json_encode((float) $total) ?>;
        const grand = goods + price;
        const grandEl = document.querySelector('[data-grand-total]');
        if (grandEl) grandEl.textContent = grand.toLocaleString('ru-RU') + ' ₽';

        document.querySelectorAll('input[name="deliveryMethod"]').forEach((inp) => {
          const label = inp.closest('label');
          const pill = label?.querySelector('[data-delivery-pill]');
          const dot = label?.querySelector('[data-delivery-dot]');
          const active = inp.checked;
          if (pill) {
            pill.classList.toggle('bg-gray-800', active);
            pill.classList.toggle('bg-gray-200', !active);
          }
          if (dot) {
            dot.classList.toggle('translate-x-4', active);
            dot.classList.toggle('translate-x-1', !active);
          }
        });
      };

      document.querySelectorAll('input[name="deliveryMethod"]').forEach((inp) => {
        inp.addEventListener('change', () => setDeliveryUi(inp.value));
      });
      setDeliveryUi((document.querySelector('input[name="deliveryMethod"]:checked')?.value) || 'standard');

      form.addEventListener('submit', (e) => {
        const firstName = (form.querySelector('input[name="firstName"]')?.value || '').trim();
        const lastName = (form.querySelector('input[name="lastName"]')?.value || '').trim();
        const email = (form.querySelector('input[name="customerEmail"]')?.value || '').trim();
        const phone = (form.querySelector('input[name="customerPhone"]')?.value || '').trim();
        const address = (form.querySelector('input[name="address"]')?.value || '').trim();
        const city = (form.querySelector('input[name="city"]')?.value || '').trim();
        const postal = (form.querySelector('input[name="postalCode"]')?.value || '').trim();

        if (!firstName || !lastName) { e.preventDefault(); window.showToast?.('Пожалуйста, укажите имя и фамилию', 'warning'); return; }
        if (!email) { e.preventDefault(); window.showToast?.('Пожалуйста, укажите email', 'warning'); return; }
        if (!phone) { e.preventDefault(); window.showToast?.('Пожалуйста, укажите телефон', 'warning'); return; }
        if (!address || !city || !postal) { e.preventDefault(); window.showToast?.('Пожалуйста, заполните адрес доставки', 'warning'); return; }

        const customerName = form.querySelector('input[name="customerName"]');
        if (customerName) customerName.value = (firstName + ' ' + lastName).trim();

        const deliveryAddress = form.querySelector('textarea[name="deliveryAddress"]');
        if (deliveryAddress) deliveryAddress.value = [address, city, postal].filter(Boolean).join(', ');
      });
    })();
  </script>
<?php endif; ?>
