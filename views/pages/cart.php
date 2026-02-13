<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$items = isset($items) && is_array($items) ? $items : [];
$total = isset($total) ? (float) $total : 0.0;
?>
<?php if (count($items) === 0): ?>
	  <div class="min-h-[calc(100vh-96px)] bg-white flex items-center justify-center">
	    <div class="text-center py-16">
	      <h1 class="text-3xl md:text-4xl font-bold text-black mb-4 tracking-wider">КОРЗИНА ПУСТА</h1>
	      <p class="text-gray-600 mb-8">Добавьте товары в корзину, чтобы продолжить покупки</p>
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
      <h1 class="text-3xl font-bold text-black mb-8 tracking-wider">КОРЗИНА</h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <div class="space-y-6">
            <?php foreach ($items as $index => $item): ?>
              <?php
                $name = (string) ($item['name'] ?? '');
                $image = (string) ($item['image'] ?? '/images/product-placeholder.svg');
                $qty = (int) ($item['quantity'] ?? 1);
                $price = (float) ($item['price'] ?? 0);
                $subtotal = (float) ($item['subtotal'] ?? ($qty * $price));
              ?>
              <div class="border border-gray-500 p-6 flex gap-6">
                <div class="w-24 h-24 bg-gray-100 flex-shrink-0 overflow-hidden">
                  <img
                    src="<?= $e($image) ?>"
                    <?php if (str_starts_with($image, '/api/upload?key=')): ?>
                      srcset="<?= $e($image) ?>&w=240 240w, <?= $e($image) ?>&w=480 480w"
                      sizes="96px"
                    <?php endif; ?>
                    alt="<?= $e($name) ?>"
                    class="w-full h-full object-cover"
                    loading="lazy"
                    decoding="async"
                  >
                </div>

                <div class="flex-1">
                  <div class="flex items-start justify-between gap-4">
                    <div>
                      <h3 class="text-lg font-bold text-black mb-2"><?= $e($name) ?></h3>
                      <div class="text-sm text-gray-600 space-y-1">
                        <p>Цвет: <?= $e((string) ($item['color'] ?? '')) ?></p>
                        <p>Размер: <?= $e((string) ($item['size'] ?? '')) ?></p>
                      </div>
                    </div>

                    <form method="post" action="/cart/remove">
                      <input type="hidden" name="index" value="<?= $e((string) $index) ?>">
                      <button type="submit" class="text-gray-400 hover:text-black transition-colors flex-shrink-0" aria-label="Удалить">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </form>
                  </div>

                  <div class="flex items-center justify-between mt-4 gap-4">
                    <div class="flex items-center gap-4">
                      <div class="flex items-center border border-gray-500">
                        <button type="button" class="px-3 py-1 text-black hover:bg-gray-100" data-qty-dec>-</button>
                        <span class="px-4 py-1 text-black font-medium" data-qty-value><?= $e((string) $qty) ?></span>
                        <button type="button" class="px-3 py-1 text-black hover:bg-gray-100" data-qty-inc>+</button>
                      </div>
                      <p class="text-sm text-gray-600"><?= $e(number_format($price, 0, '.', ' ')) ?> ₽</p>
                    </div>
                    <p class="text-lg font-bold text-black min-w-[120px] text-right"><?= $e(number_format($subtotal, 0, '.', ' ')) ?> ₽</p>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="lg:col-span-1">
          <div class="border border-gray-500 p-6 sticky top-4">
            <h2 class="text-xl font-bold text-black mb-4 tracking-wider">ИТОГО</h2>

            <div class="space-y-2 mb-6">
              <div class="flex justify-between text-gray-600">
                <span>Товары (<?= $e((string) count($items)) ?>)</span>
                <span><?= $e(number_format($total, 0, '.', ' ')) ?> ₽</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Доставка</span>
                <span>Рассчитывается при оформлении</span>
              </div>
            </div>

            <div class="border-t border-gray-500 pt-4 mb-6">
              <div class="flex justify-between text-xl font-bold text-black">
                <span>Всего</span>
                <span><?= $e(number_format($total, 0, '.', ' ')) ?> ₽</span>
              </div>
            </div>

            <a class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-full bg-black text-white hover:bg-orange-400 hover:text-black shadow-sm hover:shadow-md" href="/checkout">
              <span>Оформить заказ</span>
            </a>

            <div class="mt-4">
              <a class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-full bg-white text-black border border-black hover:bg-black hover:text-white shadow-sm hover:shadow-md" href="/sale">
                <span>Продолжить покупки</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/../partials/benefits.php'; ?>

  <script>
    // UI-only quantity control (server-side quantity update isn't implemented yet).
    (function () {
      const cards = document.querySelectorAll('[data-qty-value]');
      if (!cards.length) return;
      document.querySelectorAll('[data-qty-inc]').forEach((btn) => {
        btn.addEventListener('click', () => {
          const wrap = btn.closest('.border');
          const val = wrap?.querySelector('[data-qty-value]');
          if (!val) return;
          const n = Math.max(1, parseInt(val.textContent || '1', 10) + 1);
          val.textContent = String(n);
          window.showToast?.('Изменение количества пока не доступно', 'info');
        });
      });
      document.querySelectorAll('[data-qty-dec]').forEach((btn) => {
        btn.addEventListener('click', () => {
          const wrap = btn.closest('.border');
          const val = wrap?.querySelector('[data-qty-value]');
          if (!val) return;
          const n = Math.max(1, parseInt(val.textContent || '1', 10) - 1);
          val.textContent = String(n);
          window.showToast?.('Изменение количества пока не доступно', 'info');
        });
      });
    })();
  </script>
<?php endif; ?>
