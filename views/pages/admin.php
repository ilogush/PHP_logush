<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$inputBase = 'w-full h-10 px-3 py-2 border border-gray-300 bg-white rounded-lg focus:outline-none focus:border-gray-300 focus:ring-0 disabled:bg-gray-100 disabled:cursor-not-allowed';
$btnPrimary = 'flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed bg-blue-600 text-white border border-transparent hover:bg-blue-700 font-medium px-3 sm:px-4 py-2 text-xs sm:text-sm rounded-lg';
$btnSecondaryIcon = 'flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed bg-blue-400 text-white border border-transparent hover:bg-blue-500 font-medium rounded-lg h-8 w-8 p-0';

$tableWrap = 'w-full max-w-full bg-white shadow-sm border border-gray-100 overflow-hidden rounded-xl touch-pan-y';
$table = 'w-full table-fixed divide-y divide-gray-100';
$thead = 'bg-gray-50';
$tbody = 'bg-white divide-y divide-gray-100';
$th = 'px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider';
$td = 'px-6 py-4 text-sm';

$eyeIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>';
$trashIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path></svg>';

$resolveColorHex = static function (string $value): string {
  $trimmed = trim($value);
  $normalized = function_exists('mb_strtolower')
    ? (string) mb_strtolower($trimmed, 'UTF-8')
    : strtolower($trimmed);
  if ($normalized === '') return '#9ca3af';
  if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $normalized) === 1) return $normalized;
  if (preg_match('/^(rgb|hsl)a?\\(/i', $normalized) === 1) return $normalized;
  if (preg_match('/^[a-z-]+$/', $normalized) === 1) return $normalized;
  $map = [
    'черный' => '#111827',
    'белый' => '#ffffff',
    'серый' => '#9ca3af',
    'синий' => '#2563eb',
    'голубой' => '#38bdf8',
    'зеленый' => '#22c55e',
    'красный' => '#ef4444',
    'розовый' => '#ec4899',
    'фиолетовый' => '#8b5cf6',
    'желтый' => '#eab308',
    'оранжевый' => '#f97316',
    'бежевый' => '#d6bfa6',
    'коричневый' => '#8b5e3c',
  ];
  return $map[$normalized] ?? '#9ca3af';
};

$formatDate = static function (string $value): string {
  if ($value === '') return '—';
  $ts = strtotime($value);
  if ($ts === false) return '—';
  return date('d.m.Y H:i', $ts);
};

$orderStatusLabels = [
  'new' => 'Новый',
  'processing' => 'В обработке',
  'shipped' => 'Отправлен',
  'delivered' => 'Доставлен',
  'cancelled' => 'Отменен',
];
$orderStatusDot = [
  'new' => 'bg-orange-500',
  'processing' => 'bg-blue-500',
  'shipped' => 'bg-purple-500',
  'delivered' => 'bg-green-500',
  'cancelled' => 'bg-red-500',
];

$pageTitle = match ($section) {
  'products', 'product-new', 'product-edit' => 'Товары',
  'categories' => 'Категории',
  'colors' => 'Цвета',
  'sizes' => 'Размеры',
  'orders', 'order-show' => 'Заказы',
  'users', 'users-edit' => 'Пользователи',
  'settings' => 'Настройки',
  default => 'Админ панель',
};
?>
<?php if ($section === 'products'): ?>
  <div>
    <div class="mb-6 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
      <h2 class="text-2xl font-semibold text-gray-900"><?= $e($pageTitle) ?></h2>
      <div class="flex w-full flex-col gap-3 md:flex-row xl:w-auto">
        <div class="w-full md:w-[28rem]">
          <input
            data-table-search-input
            type="text"
            class="<?= $e($inputBase) ?>"
            placeholder="Поиск по названию, категории, цвету, размеру"
            value=""
          >
        </div>
        <a href="/admin/products/new" class="<?= $e($btnPrimary) ?> md:whitespace-nowrap">+ Добавить товар</a>
      </div>
    </div>

    <div class="<?= $e($tableWrap) ?>">
      <table class="<?= $e($table) ?>">
        <thead class="<?= $e($thead) ?>">
          <tr>
            <th class="<?= $e($th) ?>">Название</th>
            <th class="<?= $e($th) ?>">Артикул</th>
            <th class="<?= $e($th) ?>">Цена</th>
            <th class="<?= $e($th) ?>">Категория</th>
            <th class="<?= $e($th) ?>">Цвета</th>
            <th class="<?= $e($th) ?>">Размеры</th>
            <th class="<?= $e($th) ?> w-20"><span class="sr-only">Наличие</span></th>
            <th class="<?= $e($th) ?>">Действия</th>
          </tr>
        </thead>
        <tbody class="<?= $e($tbody) ?>">
          <?php if (!is_array($products) || count($products) === 0): ?>
            <tr>
              <td class="<?= $e($td) ?> text-center text-gray-500 py-8" colspan="8">Нет товаров</td>
            </tr>
          <?php else: ?>
            <?php foreach ($products as $item): ?>
              <?php if (!is_array($item)) { continue; } ?>
              <?php
                $id = (string) ($item['id'] ?? '');
                $name = (string) ($item['name'] ?? '');
                $category = (string) ($item['category'] ?? '');
                $article = (string) ($item['article'] ?? '');
                $price = (float) ($item['price'] ?? 0);
                $images = is_array($item['images'] ?? null) ? $item['images'] : [];
                $firstImage = (string) (($images[0] ?? '') ?: '');
                $colorsList = is_array($item['colors'] ?? null) ? $item['colors'] : [];
                $sizesList = is_array($item['sizes'] ?? null) ? $item['sizes'] : [];
                $inStock = isset($item['inStock']) ? (bool) $item['inStock'] : true;
                $searchText = implode(' ', array_filter([
                  $name,
                  $article,
                  $category,
                  implode(' ', array_map('strval', $colorsList)),
                  implode(' ', array_map('strval', $sizesList)),
                ]));
              ?>
              <tr class="hover:bg-gray-50 transition-colors" data-table-search-row data-search-text="<?= $e($searchText) ?>">
                <td class="<?= $e($td) ?> font-medium text-gray-900">
                  <div class="flex items-center gap-3">
                    <?php if ($firstImage !== ''): ?>
                      <img src="<?= $e($firstImage) ?>" alt="<?= $e($name) ?>" class="h-10 w-10 rounded-lg object-cover border border-gray-200">
                    <?php else: ?>
                      <div class="h-10 w-10 rounded-lg bg-gray-100 border border-gray-200"></div>
                    <?php endif; ?>
                    <span><?= $e($name) ?></span>
                  </div>
                </td>
                <td class="<?= $e($td) ?> text-gray-600"><?= $e($article !== '' ? $article : '—') ?></td>
                <td class="<?= $e($td) ?> whitespace-nowrap font-medium text-gray-900"><?= $e(number_format($price, 0, '.', ' ')) ?> ₽</td>
                <td class="<?= $e($td) ?> text-gray-600"><?= $e($category) ?></td>
                <td class="<?= $e($td) ?> text-gray-600 text-sm">
                  <div class="flex flex-wrap items-center gap-2">
                    <?php if (count($colorsList) > 0): ?>
                      <?php foreach ($colorsList as $idx => $color): ?>
                        <?php $colorStr = (string) $color; ?>
                        <span
                          class="inline-block h-3 w-3 rounded-full border border-gray-300"
                          style="background-color: <?= $e($resolveColorHex($colorStr)) ?>;"
                          title="<?= $e($colorStr) ?>"
                        ></span>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <span>—</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="<?= $e($td) ?> text-gray-600 text-sm"><?= $e(implode(', ', array_map('strval', $sizesList))) ?></td>
                <td class="<?= $e($td) ?>">
                  <span class="inline-flex items-center gap-2" title="<?= $e($inStock ? 'Статус: В наличии' : 'Статус: Нет в наличии') ?>">
                    <span class="inline-block h-3 w-3 rounded-full <?= $e($inStock ? 'bg-green-500' : 'bg-orange-500') ?>"></span>
                  </span>
                </td>
                <td class="<?= $e($td) ?> whitespace-nowrap">
                  <div class="flex gap-2 justify-start">
                    <a
                      href="/admin/products/<?= $e(rawurlencode($id)) ?>/edit"
                      class="<?= $e($btnSecondaryIcon) ?>"
                      title="Просмотр товара"
                      aria-label="Просмотр товара"
                    ><?= $eyeIcon ?></a>
                    <button
                      type="button"
                      data-delete-product-id="<?= $e($id) ?>"
                      class="<?= $e($btnSecondaryIcon) ?>"
                      title="Удалить товар"
                      aria-label="Удалить товар"
                    ><?= $trashIcon ?></button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php elseif ($section === 'product-new' || $section === 'product-edit'): ?>
  <?php
    $productsList = is_array($products) ? $products : [];
    $categoriesList = is_array($categories) ? $categories : [];
    $colorsList = is_array($colors) ? $colors : [];
    $sizesList = is_array($sizes) ? $sizes : [];

    $availableColors = [];
    foreach ($colorsList as $c) {
      if (!is_array($c)) continue;
      $name = trim((string) ($c['name'] ?? ''));
      if ($name !== '') $availableColors[] = $name;
    }

    $availableSizes = [];
    foreach ($sizesList as $s) {
      if (!is_array($s)) continue;
      $name = trim((string) ($s['name'] ?? ''));
      if ($name !== '') $availableSizes[] = $name;
    }

    $sizeOrder = ["XXS", "XS", "S", "M", "L", "XL", "XXL", "XXXL"];
    usort($availableSizes, static function (string $a, string $b) use ($sizeOrder): int {
      $normalize = static fn (string $v): string => strtoupper(trim($v));
      $an = $normalize($a);
      $bn = $normalize($b);

      $ai = array_search($an, $sizeOrder, true);
      $bi = array_search($bn, $sizeOrder, true);
      $ai = ($ai === false) ? -1 : (int) $ai;
      $bi = ($bi === false) ? -1 : (int) $bi;
      if ($ai !== -1 || $bi !== -1) {
        return ($ai === -1 ? count($sizeOrder) : $ai) <=> ($bi === -1 ? count($sizeOrder) : $bi);
      }

      $aNum = (float) str_replace(',', '.', $an);
      $bNum = (float) str_replace(',', '.', $bn);
      $aHas = is_numeric(str_replace(',', '.', $an));
      $bHas = is_numeric(str_replace(',', '.', $bn));
      if ($aHas && $bHas) return $aNum <=> $bNum;
      if ($aHas) return -1;
      if ($bHas) return 1;
      return $an <=> $bn;
    });

    $visibleCategory = static fn (string $name): bool => mb_strtolower(trim($name)) !== 'без категории';

    $categoryById = [];
    foreach ($categoriesList as $cat) {
      if (!is_array($cat)) continue;
      $categoryById[(string) ($cat['id'] ?? '')] = $cat;
    }

    $editingProduct = null;
    if ($section === 'product-edit') {
      foreach ($productsList as $p) {
        if (!is_array($p)) continue;
        if ((string) ($p['id'] ?? '') === $entityId) {
          $editingProduct = $p;
          break;
        }
      }
    }

    $formDefaults = [
      'name' => '',
      'category' => '',
      'article' => '',
      'price' => 0,
      'stock' => 0,
      'colors' => [],
      'sizes' => [],
      'description' => '',
      'material' => '',
      'care' => '',
      'inStock' => true,
      'images' => [],
    ];

    if (is_array($editingProduct)) {
      $formDefaults['name'] = (string) ($editingProduct['name'] ?? '');
      $formDefaults['category'] = (string) ($editingProduct['category'] ?? '');
      $formDefaults['article'] = (string) ($editingProduct['article'] ?? '');
      $formDefaults['price'] = (float) ($editingProduct['price'] ?? 0);
      $formDefaults['stock'] = (int) ($editingProduct['stock'] ?? 0);
      $formDefaults['colors'] = is_array($editingProduct['colors'] ?? null) ? $editingProduct['colors'] : [];
      $formDefaults['sizes'] = is_array($editingProduct['sizes'] ?? null) ? $editingProduct['sizes'] : [];
      $formDefaults['description'] = (string) ($editingProduct['description'] ?? '');
      $formDefaults['material'] = (string) ($editingProduct['material'] ?? '');
      $formDefaults['care'] = is_array($editingProduct['care'] ?? null)
        ? implode("\n", $editingProduct['care'])
        : (string) ($editingProduct['care'] ?? '');
      $formDefaults['inStock'] = !isset($editingProduct['inStock']) ? true : (bool) $editingProduct['inStock'];
      $formDefaults['images'] = is_array($editingProduct['images'] ?? null) ? $editingProduct['images'] : [];
    }

    $imageMax = 6;
  ?>

  <div>
    <div class="mb-6 flex items-center gap-4">
      <a href="/admin/products" class="inline-flex items-center justify-center p-2 rounded-lg bg-white hover:bg-gray-200 transition-all duration-200 shadow-sm border border-gray-200" aria-label="Назад">
        <svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
        </svg>
      </a>
      <h2 class="text-2xl font-semibold text-gray-900"><?= $e($section === 'product-edit' ? 'Редактировать товар' : 'Добавить товар') ?></h2>
    </div>

    <?php if ($section === 'product-edit' && !is_array($editingProduct)): ?>
      <div class="rounded-3xl bg-white p-6 text-gray-600 shadow-sm">Товар не найден</div>
    <?php else: ?>
      <form
        data-product-form
        data-product-mode="<?= $e($section === 'product-edit' ? 'edit' : 'create') ?>"
        data-product-id="<?= $e($entityId) ?>"
        class="bg-white rounded-3xl shadow-sm p-6 space-y-6"
      >
        <div>
          <label class="block text-xs text-gray-600 mb-1">Фотографии товара <span class="text-gray-500">*</span></label>
          <div class="space-y-4">
            <div class="grid gap-4 grid-cols-6" data-product-images-grid>
              <?php foreach ((array) $formDefaults['images'] as $idx => $url): ?>
                <?php $u = trim((string) $url); ?>
                <?php if ($u === '') continue; ?>
                <div class="relative bg-gray-100 rounded-lg overflow-hidden aspect-[3/4]" data-image-item="<?= $e($u) ?>">
                  <img src="<?= $e($u) ?>" alt="Product <?= $e((string) ($idx + 1)) ?>" class="w-full h-full object-cover">
                  <button
                    type="button"
                    data-remove-image="<?= $e($u) ?>"
                    class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-lg hover:bg-red-700"
                    aria-label="Удалить"
                  >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>
              <?php endforeach; ?>

            <?php if (count((array) $formDefaults['images']) < $imageMax): ?>
                <label class="aspect-[3/4] border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-gray-400 transition-colors" data-upload-tile>
                  <svg class="w-8 h-8 text-gray-400 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V6.75A2.25 2.25 0 0 1 5.25 4.5h13.5A2.25 2.25 0 0 1 21 6.75v9.75A2.25 2.25 0 0 1 18.75 18.75H5.25A2.25 2.25 0 0 1 3 16.5Z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="m3 14 4.5-4.5a2.25 2.25 0 0 1 3.182 0L15 13.5"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.25 12.75 1.5-1.5a2.25 2.25 0 0 1 3.182 0L21 13.5"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 7.5h.008v.008h-.008V7.5Z"></path>
                  </svg>
                  <span class="text-sm text-gray-500">Добавить фото</span>
                  <input type="file" accept="image/webp" multiple data-upload-images class="hidden">
                </label>
            <?php endif; ?>
            </div>

            <p class="text-xs text-gray-500">Загружено <?= $e((string) count((array) $formDefaults['images'])) ?> из <?= $e((string) $imageMax) ?> фото. Только WebP, до 8MB.</p>
            <input type="hidden" name="images" data-images-value value="<?= $e(json_encode((array) $formDefaults['images'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">
          </div>
        </div>

        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4">
            <div>
              <label class="block text-xs text-gray-600 mb-1">Название товара <span class="text-gray-500">*</span></label>
              <input name="name" type="text" required class="<?= $e($inputBase) ?>" value="<?= $e((string) $formDefaults['name']) ?>">
            </div>

            <div>
              <label class="block text-xs text-gray-600 mb-1">Категория <span class="text-gray-500">*</span></label>
              <select name="category" required class="<?= $e($inputBase) ?>">
                <option value="">Выберите категорию</option>
                <?php foreach ($categoriesList as $main): ?>
                  <?php
                    if (!is_array($main)) continue;
                    $mainId = (string) ($main['id'] ?? '');
                    $mainName = trim((string) ($main['name'] ?? ''));
                    $mainParentId = $main['parentId'] ?? null;
                    if ($mainName === '' || $mainParentId !== null) continue;
                    if (!$visibleCategory($mainName)) continue;
                  ?>
                  <optgroup label="<?= $e($mainName) ?>">
                    <option value="<?= $e($mainName) ?>" <?= ((string) $formDefaults['category'] === $mainName) ? 'selected' : '' ?>><?= $e($mainName) ?></option>
                    <?php foreach ($categoriesList as $sub): ?>
                      <?php
                        if (!is_array($sub)) continue;
                        $subName = trim((string) ($sub['name'] ?? ''));
                        $subParentId = $sub['parentId'] ?? null;
                        if ($subName === '' || $subParentId === null) continue;
                        if ((string) $subParentId !== $mainId) continue;
                        if (!$visibleCategory($subName)) continue;
                      ?>
                      <option value="<?= $e($subName) ?>" <?= ((string) $formDefaults['category'] === $subName) ? 'selected' : '' ?>><?= $e($subName) ?></option>
                    <?php endforeach; ?>
                  </optgroup>
                <?php endforeach; ?>
                <?php foreach ($categoriesList as $orphan): ?>
                  <?php
                    if (!is_array($orphan)) continue;
                    $orphanName = trim((string) ($orphan['name'] ?? ''));
                    $orphanParentId = $orphan['parentId'] ?? null;
                    if ($orphanName === '' || $orphanParentId === null) continue;
                    if (!$visibleCategory($orphanName)) continue;
                    if (isset($categoryById[(string) $orphanParentId])) continue;
                  ?>
                  <option value="<?= $e($orphanName) ?>" <?= ((string) $formDefaults['category'] === $orphanName) ? 'selected' : '' ?>><?= $e($orphanName) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block text-xs text-gray-600 mb-1">Артикул <span class="text-gray-500">*</span></label>
              <input name="article" type="text" required class="<?= $e($inputBase) ?>" value="<?= $e((string) $formDefaults['article']) ?>" placeholder="Артикул товара">
            </div>

            <div>
              <label class="block text-xs text-gray-600 mb-1">Цена (₽) <span class="text-gray-500">*</span></label>
              <input name="price" type="number" required class="<?= $e($inputBase) ?>" value="<?= $e((string) $formDefaults['price']) ?>">
            </div>

            <div>
              <label class="block text-xs text-gray-600 mb-1">Остатки <span class="text-gray-500">*</span></label>
              <input name="stock" type="number" required class="<?= $e($inputBase) ?>" value="<?= $e((string) $formDefaults['stock']) ?>" placeholder="0">
            </div>

            <div>
              <label class="block text-xs text-gray-600 mb-1">Материал <span class="text-gray-500">*</span></label>
              <input name="material" type="text" required class="<?= $e($inputBase) ?>" value="<?= $e((string) $formDefaults['material']) ?>" placeholder="100% шерсть мериноса">
            </div>
          </div>

          <div>
            <label class="block text-xs text-gray-600 mb-1">Цвета <span class="text-gray-500">*</span></label>
            <div class="space-y-3">
              <div class="flex flex-wrap gap-3" data-colors>
                <?php foreach ($availableColors as $colorName): ?>
                  <?php
                    $selected = in_array($colorName, (array) $formDefaults['colors'], true);
                    $cls = $selected
                      ? 'bg-blue-600 border-blue-600 text-white'
                      : 'bg-white border-gray-300 text-gray-700 hover:border-blue-400';
                  ?>
                  <button
                    type="button"
                    data-toggle-color="<?= $e($colorName) ?>"
                    class="px-4 py-2 rounded-lg border-2 font-medium transition-colors <?= $cls ?>"
                  ><?= $e($colorName) ?></button>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-xs text-gray-600 mb-1">Размеры <span class="text-gray-500">*</span></label>
            <div class="space-y-3">
              <div class="flex flex-wrap gap-3" data-sizes>
                <?php foreach ($availableSizes as $sz): ?>
                  <?php
                    $selected = in_array($sz, (array) $formDefaults['sizes'], true);
                    $cls = $selected
                      ? 'bg-blue-600 border-blue-600 text-white'
                      : 'bg-white border-gray-300 text-gray-700 hover:border-blue-400';
                  ?>
                  <button
                    type="button"
                    data-toggle-size="<?= $e($sz) ?>"
                    class="px-4 py-2 rounded-lg border-2 font-medium transition-colors <?= $cls ?>"
                  ><?= $e($sz) ?></button>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs text-gray-600 mb-1">Описание <span class="text-gray-500">*</span></label>
          <input
            name="description"
            value="<?= $e((string) $formDefaults['description']) ?>"
            class="w-full h-10 px-3 py-2 border border-gray-300 bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            required
          >
        </div>

        <div>
          <label class="block text-xs text-gray-600 mb-1">Уход (каждая инструкция с новой строки) <span class="text-gray-500">*</span></label>
          <input
            name="care"
            value="<?= $e((string) $formDefaults['care']) ?>"
            class="w-full h-10 px-3 py-2 border border-gray-300 bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Ручная стирка при 30°C, Не отбеливать, Сушить горизонтально"
            required
          >
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 pt-4" data-instock="<?= $e($formDefaults['inStock'] ? '1' : '0') ?>">
          <div class="flex items-center gap-3 text-sm font-medium text-gray-700">
            <span>В наличии</span>
            <button
              type="button"
              data-toggle-instock
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors <?= $formDefaults['inStock'] ? 'bg-blue-600' : 'bg-gray-200' ?>"
              aria-label="В наличии"
            >
              <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform <?= $formDefaults['inStock'] ? 'translate-x-6' : 'translate-x-1' ?>" data-instock-dot></span>
            </button>
          </div>
          <button type="submit" class="<?= $e($btnPrimary) ?> h-10 whitespace-nowrap" data-submit>
            <?= $e($section === 'product-edit' ? 'Сохранить изменения' : 'Создать') ?>
          </button>
        </div>
      </form>
    <?php endif; ?>
  </div>
<?php elseif ($section === 'categories'): ?>
  <?php
    $productsList = is_array($products) ? $products : [];
    $categoriesList = is_array($categories) ? $categories : [];

    $categoriesById = [];
    foreach ($categoriesList as $c) {
      if (!is_array($c)) continue;
      $categoriesById[(string) ($c['id'] ?? '')] = $c;
    }

    $mainCategories = [];
    $rows = [];
    foreach ($categoriesList as $c) {
      if (!is_array($c)) continue;
      $id = (string) ($c['id'] ?? '');
      $name = (string) ($c['name'] ?? '');
      $parentId = $c['parentId'] ?? null;
      $parentIdStr = ($parentId === null || $parentId === '') ? null : (string) $parentId;
      $parentName = null;
      if ($parentIdStr !== null && isset($categoriesById[$parentIdStr])) {
        $parentName = (string) ($categoriesById[$parentIdStr]['name'] ?? null);
      } else {
        $parentName = isset($c['parentName']) ? (string) ($c['parentName'] ?? null) : null;
      }

      if ($parentIdStr === null && mb_strtolower(trim($name)) !== 'без категории') {
        $mainCategories[] = ['id' => $id, 'name' => $name];
      }

      $count = 0;
      foreach ($productsList as $p) {
        if (!is_array($p)) continue;
        if ((string) ($p['category'] ?? '') === $name) $count++;
      }

      $rows[] = [
        'id' => $id,
        'name' => $name,
        'parentId' => $parentIdStr,
        'parentName' => $parentName,
        'productCount' => $count,
      ];
    }
  ?>
  <div>
    <div class="mb-6 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
      <h2 class="text-2xl font-semibold text-gray-900"><?= $e($pageTitle) ?></h2>
      <div class="flex w-full flex-col gap-3 md:flex-row xl:w-auto">
        <div class="w-full md:w-96">
          <input data-table-search-input type="text" class="<?= $e($inputBase) ?>" placeholder="Поиск по категории и типу одежды" value="">
        </div>
        <button type="button" data-crud-open="category-create" class="<?= $e($btnPrimary) ?> md:whitespace-nowrap">+ Добавить категорию</button>
      </div>
    </div>
    <div class="<?= $e($tableWrap) ?>">
      <table class="<?= $e($table) ?>">
        <thead class="<?= $e($thead) ?>">
          <tr>
            <th class="<?= $e($th) ?>">Категория</th>
            <th class="<?= $e($th) ?>">Тип одежды</th>
            <th class="<?= $e($th) ?>">Товаров</th>
            <th class="<?= $e($th) ?>">Действия</th>
          </tr>
        </thead>
        <tbody class="<?= $e($tbody) ?>">
          <?php if (count($rows) === 0): ?>
            <tr><td class="<?= $e($td) ?> text-center text-gray-500 py-8" colspan="4">Нет категорий</td></tr>
          <?php else: ?>
            <?php foreach ($rows as $cat): ?>
              <?php
                $catId = (string) ($cat['id'] ?? '');
                $catName = (string) ($cat['name'] ?? '');
                $parentName = (string) ($cat['parentName'] ?? '');
                $parentId = $cat['parentId'] ?? null;
                $searchText = trim(($parentName ? $parentName . ' ' : '') . $catName);
                $productCount = (int) ($cat['productCount'] ?? 0);
              ?>
              <tr class="hover:bg-gray-50 transition-colors" data-table-search-row data-search-text="<?= $e($searchText) ?>">
                <td class="<?= $e($td) ?> font-medium text-gray-900"><?= $e($parentName !== '' ? $parentName : $catName) ?></td>
                <td class="<?= $e($td) ?> text-gray-600"><?= $e($parentName !== '' ? $catName : '—') ?></td>
                <td class="<?= $e($td) ?> text-gray-600"><?= $e((string) $productCount) ?></td>
                <td class="<?= $e($td) ?> whitespace-nowrap">
                  <div class="flex gap-2 justify-start">
                    <button
                      type="button"
                      data-crud-open="category-edit"
                      data-id="<?= $e($catId) ?>"
                      data-name="<?= $e($catName) ?>"
                      data-parent-id="<?= $e((string) ($parentId ?? '')) ?>"
                      class="<?= $e($btnSecondaryIcon) ?>"
                      title="Просмотр категории"
                      aria-label="Просмотр категории"
                    ><?= $eyeIcon ?></button>
                    <button
                      type="button"
                      data-crud-delete="category"
                      data-id="<?= $e($catId) ?>"
                      class="<?= $e($btnSecondaryIcon) ?>"
                      title="Удалить категорию"
                      aria-label="Удалить категорию"
                    ><?= $trashIcon ?></button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <script type="application/json" id="adminCategoryParents"><?= json_encode($mainCategories, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
  </div>
<?php elseif ($section === 'colors'): ?>
  <?php
    $productsList = is_array($products) ? $products : [];
    $colorRows = [];
    if (is_array($colors)) {
      foreach ($colors as $c) {
        if (!is_array($c)) continue;
        $name = (string) ($c['name'] ?? '');
        $count = 0;
        foreach ($productsList as $p) {
          if (!is_array($p)) continue;
          $items = $p['colors'] ?? [];
          if (is_array($items) && in_array($name, $items, true)) $count++;
        }
        $colorRows[] = [
          'id' => (string) ($c['id'] ?? ''),
          'name' => $name,
          'productCount' => $count,
        ];
      }
    }
  ?>
  <div>
    <div class="mb-6 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
      <h2 class="text-2xl font-semibold text-gray-900"><?= $e($pageTitle) ?></h2>
      <div class="flex w-full flex-col gap-3 md:flex-row xl:w-auto">
        <div class="w-full md:w-96">
          <input data-table-search-input type="text" class="<?= $e($inputBase) ?>" placeholder="Поиск по названию цвета" value="">
        </div>
        <button type="button" data-crud-open="color-create" class="<?= $e($btnPrimary) ?> md:whitespace-nowrap">+ Добавить цвет</button>
      </div>
    </div>
    <div class="<?= $e($tableWrap) ?>">
      <table class="<?= $e($table) ?>">
        <thead class="<?= $e($thead) ?>">
          <tr>
            <th class="<?= $e($th) ?>">Название</th>
            <th class="<?= $e($th) ?>">Товаров</th>
            <th class="<?= $e($th) ?>">Действия</th>
          </tr>
        </thead>
        <tbody class="<?= $e($tbody) ?>">
          <?php if (count($colorRows) === 0): ?>
            <tr><td class="<?= $e($td) ?> text-center text-gray-500 py-8" colspan="3">Нет цветов</td></tr>
          <?php else: ?>
            <?php foreach ($colorRows as $color): ?>
              <?php
                $id = (string) ($color['id'] ?? '');
                $name = (string) ($color['name'] ?? '');
                $productCount = (int) ($color['productCount'] ?? 0);
              ?>
              <tr class="hover:bg-gray-50 transition-colors" data-table-search-row data-search-text="<?= $e($name) ?>">
                <td class="<?= $e($td) ?> font-medium text-gray-900"><?= $e($name) ?></td>
                <td class="<?= $e($td) ?> text-gray-600"><?= $e((string) $productCount) ?></td>
                <td class="<?= $e($td) ?> whitespace-nowrap">
                  <div class="flex gap-2 justify-start">
                    <button
                      type="button"
                      data-crud-open="color-edit"
                      data-id="<?= $e($id) ?>"
                      data-name="<?= $e($name) ?>"
                      class="<?= $e($btnSecondaryIcon) ?>"
                      title="Просмотр цвета"
                      aria-label="Просмотр цвета"
                    ><?= $eyeIcon ?></button>
                    <button
                      type="button"
                      data-crud-delete="color"
                      data-id="<?= $e($id) ?>"
                      class="<?= $e($btnSecondaryIcon) ?>"
                      title="Удалить цвет"
                      aria-label="Удалить цвет"
                    ><?= $trashIcon ?></button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php elseif ($section === 'sizes'): ?>
  <?php
    $productsList = is_array($products) ? $products : [];
    $sizeRows = [];
    if (is_array($sizes)) {
      foreach ($sizes as $s) {
        if (!is_array($s)) continue;
        $name = (string) ($s['name'] ?? '');
        $count = 0;
        foreach ($productsList as $p) {
          if (!is_array($p)) continue;
          $items = $p['sizes'] ?? [];
          if (is_array($items) && in_array($name, $items, true)) $count++;
        }
        $sizeRows[] = [
          'id' => (string) ($s['id'] ?? ''),
          'name' => $name,
          'productCount' => $count,
        ];
      }
    }
  ?>
  <div>
    <div class="mb-6 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
      <h2 class="text-2xl font-semibold text-gray-900"><?= $e($pageTitle) ?></h2>
      <div class="flex w-full flex-col gap-3 md:flex-row xl:w-auto">
        <div class="w-full md:w-96">
          <input data-table-search-input type="text" class="<?= $e($inputBase) ?>" placeholder="Поиск по размеру" value="">
        </div>
        <button type="button" data-crud-open="size-create" class="<?= $e($btnPrimary) ?> md:whitespace-nowrap">+ Добавить размер</button>
      </div>
    </div>
    <div class="<?= $e($tableWrap) ?>">
      <table class="<?= $e($table) ?>">
        <thead class="<?= $e($thead) ?>">
          <tr>
            <th class="<?= $e($th) ?>">Название</th>
            <th class="<?= $e($th) ?>">Товаров</th>
            <th class="<?= $e($th) ?>">Действия</th>
          </tr>
        </thead>
        <tbody class="<?= $e($tbody) ?>">
          <?php if (count($sizeRows) === 0): ?>
            <tr><td class="<?= $e($td) ?> text-center text-gray-500 py-8" colspan="3">Нет размеров</td></tr>
          <?php else: ?>
            <?php foreach ($sizeRows as $size): ?>
              <?php
                $id = (string) ($size['id'] ?? '');
                $name = (string) ($size['name'] ?? '');
                $productCount = (int) ($size['productCount'] ?? 0);
              ?>
              <tr class="hover:bg-gray-50 transition-colors" data-table-search-row data-search-text="<?= $e($name) ?>">
                <td class="<?= $e($td) ?> font-medium text-gray-900"><?= $e($name) ?></td>
                <td class="<?= $e($td) ?> text-gray-600"><?= $e((string) $productCount) ?></td>
                <td class="<?= $e($td) ?> whitespace-nowrap">
                  <div class="flex gap-2 justify-start">
                    <button
                      type="button"
                      data-crud-open="size-edit"
                      data-id="<?= $e($id) ?>"
                      data-name="<?= $e($name) ?>"
                      class="<?= $e($btnSecondaryIcon) ?>"
                      title="Просмотр размера"
                      aria-label="Просмотр размера"
                    ><?= $eyeIcon ?></button>
                    <button
                      type="button"
                      data-crud-delete="size"
                      data-id="<?= $e($id) ?>"
                      class="<?= $e($btnSecondaryIcon) ?>"
                      title="Удалить размер"
                      aria-label="Удалить размер"
                    ><?= $trashIcon ?></button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php elseif ($section === 'orders'): ?>
  <div>
    <div class="mb-6 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
      <div>
        <h2 class="text-2xl font-semibold text-gray-900"><?= $e($pageTitle) ?></h2>
        <p class="mt-1 text-sm text-gray-600">Всего заказов: <?= $e((string) (is_array($orders) ? count($orders) : 0)) ?></p>
      </div>
      <div class="flex w-full flex-col gap-3 md:flex-row xl:w-auto">
        <div class="w-full md:w-[28rem]">
          <input data-table-search-input type="text" class="<?= $e($inputBase) ?>" placeholder="Поиск по номеру, имени, email, телефону" value="">
        </div>
      </div>
    </div>

    <div class="<?= $e($tableWrap) ?>">
      <table class="<?= $e($table) ?>">
        <thead class="<?= $e($thead) ?>">
          <tr>
            <th class="<?= $e($th) ?> w-28">№ Заказа</th>
            <th class="<?= $e($th) ?> w-52">Клиент</th>
            <th class="<?= $e($th) ?>">Товары</th>
            <th class="<?= $e($th) ?> w-36">Сумма</th>
            <th class="<?= $e($th) ?> w-20"><span class="sr-only">Статус</span></th>
            <th class="<?= $e($th) ?> w-44">Дата</th>
            <th class="<?= $e($th) ?> w-32">Действия</th>
          </tr>
        </thead>
        <tbody class="<?= $e($tbody) ?>">
          <?php if (!is_array($orders) || count($orders) === 0): ?>
            <tr><td class="<?= $e($td) ?> text-center text-gray-500 py-8" colspan="7">Нет заказов</td></tr>
          <?php else: ?>
            <?php foreach ($orders as $order): ?>
              <?php if (!is_array($order)) { continue; } ?>
              <?php
                $id = (string) ($order['id'] ?? '');
                $customerName = (string) ($order['customerName'] ?? '');
                $customerEmail = (string) ($order['customerEmail'] ?? '');
                $customerPhone = (string) ($order['customerPhone'] ?? '');
                $items = is_array($order['items'] ?? null) ? $order['items'] : [];
                $totalAmount = (float) ($order['totalAmount'] ?? 0);
                $status = (string) ($order['status'] ?? 'new');
                $createdAt = (string) ($order['createdAt'] ?? '');
                $searchText = implode(' ', array_filter([$id, $customerName, $customerEmail, $customerPhone]));
              ?>
              <tr class="hover:bg-gray-50 transition-colors" data-table-search-row data-search-text="<?= $e($searchText) ?>">
                <td class="<?= $e($td) ?> font-medium text-gray-900">#<?= $e(substr($id, -6)) ?></td>
                <td class="<?= $e($td) ?>">
                  <div class="text-sm">
                    <div class="font-medium text-gray-900"><?= $e($customerName) ?></div>
                    <div class="text-gray-500"><?= $e($customerPhone) ?></div>
                  </div>
                </td>
                <td class="<?= $e($td) ?> max-w-0 text-sm text-gray-600">
                  <div class="space-y-1 break-words">
                    <?php
                      $shown = 0;
                      foreach ($items as $idx => $it) {
                        if ($shown >= 2) break;
                        if (!is_array($it)) continue;
                        $line = (string) ($it['productName'] ?? '');
                        $line .= ' (' . (string) ($it['color'] ?? '') . '/' . (string) ($it['size'] ?? '') . ')';
                        $line .= ' × ' . (string) ((int) ($it['quantity'] ?? 0));
                        $shown++;
                        echo '<div class="truncate">' . $e($line) . '</div>';
                      }
                      $rest = max(0, count($items) - $shown);
                      if ($rest > 0) {
                        echo '<div class="text-xs text-gray-500">+ еще ' . $e((string) $rest) . '</div>';
                      }
                    ?>
                  </div>
                </td>
                <td class="<?= $e($td) ?> whitespace-nowrap font-medium text-gray-900"><?= $e(number_format($totalAmount, 0, '.', ' ')) ?> ₽</td>
                <td class="<?= $e($td) ?>">
                  <span class="inline-flex items-center gap-2" title="<?= $e('Статус: ' . ($orderStatusLabels[$status] ?? $status)) ?>">
                    <span class="inline-block h-3 w-3 rounded-full <?= $e($orderStatusDot[$status] ?? 'bg-orange-500') ?>"></span>
                  </span>
                </td>
                <td class="<?= $e($td) ?> text-sm text-gray-600"><?= $e($formatDate($createdAt)) ?></td>
                <td class="<?= $e($td) ?> whitespace-nowrap">
                  <div class="flex justify-start gap-2">
                    <a href="/admin/orders/<?= $e(rawurlencode($id)) ?>" class="<?= $e($btnSecondaryIcon) ?>" title="Просмотр заказа" aria-label="Просмотр заказа"><?= $eyeIcon ?></a>
                    <button type="button" data-delete-order-id="<?= $e($id) ?>" class="<?= $e($btnSecondaryIcon) ?>" title="Удалить заказ" aria-label="Удалить заказ"><?= $trashIcon ?></button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php elseif ($section === 'order-show'): ?>
  <?php
    $order = null;
    $ordersList = is_array($orders) ? $orders : [];
    foreach ($ordersList as $o) {
      if (!is_array($o)) continue;
      if ((string) ($o['id'] ?? '') === $entityId) {
        $order = $o;
        break;
      }
    }
    $orderId = is_array($order) ? (string) ($order['id'] ?? '') : '';
    $status = is_array($order) ? (string) ($order['status'] ?? 'new') : 'new';
    $items = is_array($order) && is_array($order['items'] ?? null) ? $order['items'] : [];
    $formatDateFull = static function (string $value): string {
      $ts = strtotime($value);
      if ($ts === false) return '—';
      return date('d.m.Y H:i', $ts);
    };
  ?>

  <?php if (!is_array($order)): ?>
    <div class="space-y-4">
      <a href="/admin/orders" class="inline-flex items-center justify-center p-2 rounded-lg bg-white hover:bg-gray-200 transition-all duration-200 shadow-sm border border-gray-200" aria-label="Назад">
        <svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
        </svg>
      </a>
      <div class="rounded-3xl bg-white p-6 text-gray-600 shadow-sm">Заказ не найден</div>
    </div>
  <?php else: ?>
    <div class="space-y-6" data-order-details data-order-id="<?= $e($orderId) ?>">
      <div class="flex items-center gap-4">
        <a href="/admin/orders" class="inline-flex items-center justify-center p-2 rounded-lg bg-white hover:bg-gray-200 transition-all duration-200 shadow-sm border border-gray-200" aria-label="Назад">
          <svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
          </svg>
        </a>
        <h2 class="text-2xl font-semibold text-gray-900">Заказ #<?= $e(substr($orderId, -6)) ?></h2>
      </div>

      <div class="rounded-3xl bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
          <div>
            <p class="text-xs text-gray-500">Клиент</p>
            <p class="font-medium text-gray-900"><?= $e((string) ($order['customerName'] ?? '')) ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Email</p>
            <p class="font-medium text-gray-900"><?= $e((string) ($order['customerEmail'] ?? '')) ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Телефон</p>
            <p class="font-medium text-gray-900"><?= $e((string) ($order['customerPhone'] ?? '')) ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Создан</p>
            <p class="font-medium text-gray-900"><?= $e($formatDateFull((string) ($order['createdAt'] ?? ''))) ?></p>
          </div>
          <div class="md:col-span-2 lg:col-span-4">
            <p class="text-xs text-gray-500">Адрес доставки</p>
            <p class="font-medium text-gray-900"><?= $e((string) ($order['deliveryAddress'] ?? '')) ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Способ оплаты</p>
            <p class="font-medium text-gray-900"><?= $e((string) ($order['paymentMethod'] ?? '')) ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Текущий статус</p>
            <span class="inline-flex items-center gap-2" title="<?= $e('Статус: ' . ($orderStatusLabels[$status] ?? $status)) ?>">
              <span class="inline-block h-3 w-3 rounded-full <?= $e($orderStatusDot[$status] ?? 'bg-orange-500') ?>"></span>
              <span class="text-sm text-gray-700"><?= $e($orderStatusLabels[$status] ?? $status) ?></span>
            </span>
          </div>
          <div>
            <label class="block text-xs text-gray-600 mb-1">Статус заказа <span class="text-gray-500">*</span></label>
            <select name="status" data-order-status class="<?= $e($inputBase) ?>">
              <?php foreach ($orderStatusLabels as $value => $label): ?>
                <option value="<?= $e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= $e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="flex items-end">
            <button type="button" class="<?= $e($btnPrimary) ?> h-10 w-full whitespace-nowrap" data-order-save>Сохранить</button>
          </div>
        </div>
      </div>

      <div class="rounded-3xl bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Товары</h3>
        <div class="w-full max-w-full bg-white overflow-hidden rounded-xl touch-pan-y border-0 shadow-none">
          <table class="w-full table-fixed divide-y divide-gray-100">
            <thead class="<?= $e($thead) ?>">
              <tr>
                <th class="<?= $e($th) ?>">Товар</th>
                <th class="<?= $e($th) ?>">ID товара</th>
                <th class="<?= $e($th) ?>">Цвет</th>
                <th class="<?= $e($th) ?>">Размер</th>
                <th class="<?= $e($th) ?>">Кол-во</th>
                <th class="<?= $e($th) ?>">Цена за шт.</th>
                <th class="<?= $e($th) ?>">Сумма</th>
              </tr>
            </thead>
            <tbody class="<?= $e($tbody) ?>">
              <?php foreach ($items as $idx => $it): ?>
                <?php if (!is_array($it)) continue; ?>
                <?php
                  $qty = (int) ($it['quantity'] ?? 0);
                  $price = (float) ($it['price'] ?? 0);
                  $sum = $qty * $price;
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="<?= $e($td) ?> font-medium text-gray-900"><?= $e((string) ($it['productName'] ?? '')) ?></td>
                  <td class="<?= $e($td) ?> max-w-[220px] truncate text-xs text-gray-500" title="<?= $e((string) ($it['productId'] ?? '')) ?>"><?= $e((string) ($it['productId'] ?? '')) ?></td>
                  <td class="<?= $e($td) ?> text-gray-600"><?= $e((string) ($it['color'] ?? '')) ?></td>
                  <td class="<?= $e($td) ?> text-gray-600"><?= $e((string) ($it['size'] ?? '')) ?></td>
                  <td class="<?= $e($td) ?> text-gray-600"><?= $e((string) $qty) ?></td>
                  <td class="<?= $e($td) ?> whitespace-nowrap text-gray-600"><?= $e(number_format($price, 0, '.', ' ')) ?> ₽</td>
                  <td class="<?= $e($td) ?> whitespace-nowrap font-medium text-gray-900"><?= $e(number_format($sum, 0, '.', ' ')) ?> ₽</td>
                </tr>
              <?php endforeach; ?>
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="<?= $e($td) ?> text-right font-semibold text-gray-900" colspan="6">Итого</td>
                <td class="<?= $e($td) ?> whitespace-nowrap font-semibold text-gray-900"><?= $e(number_format((float) ($order['totalAmount'] ?? 0), 0, '.', ' ')) ?> ₽</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php elseif ($section === 'users' || $section === 'users-edit'): ?>
  <?php
    $formatDateUi = static function (string $value): string {
      if ($value === '') return '—';
      $ts = strtotime($value);
      if ($ts === false) return '—';
      return date('d.m.Y H:i', $ts);
    };

    $adminsList = is_array($users) ? $users : [];
    $ordersList = is_array($orders) ? $orders : [];

    $mapAdminToClient = static function (array $admin): array {
      $createdAt = (string) ($admin['createdAt'] ?? '');
      $role = trim((string) ($admin['role'] ?? ''));
      if ($role === '' || mb_strtolower($role) === 'admin') {
        $role = 'Администратор';
      }
      return [
        'id' => 'admin:' . (string) ($admin['id'] ?? ''),
        'name' => (string) ($admin['name'] ?? ''),
        'email' => (string) ($admin['email'] ?? ''),
        'phone' => (string) ($admin['phone'] ?? ''),
        'address' => (string) ($admin['address'] ?? ''),
        'role' => $role,
        'userType' => 'admin',
        'orderCount' => 0,
        'totalSpent' => 0,
        'firstOrderAt' => $createdAt,
        'lastOrderAt' => $createdAt,
      ];
    };

    $aggregateCustomers = static function (array $orders): array {
      $map = [];
      foreach ($orders as $order) {
        if (!is_array($order)) continue;
        $email = mb_strtolower(trim((string) ($order['customerEmail'] ?? '')));
        if ($email === '') continue;

        if (!isset($map[$email])) {
          $createdAt = (string) ($order['createdAt'] ?? gmdate('c'));
          $map[$email] = [
            'id' => 'customer:' . md5($email),
            'name' => trim((string) ($order['customerName'] ?? '')),
            'email' => $email,
            'phone' => trim((string) ($order['customerPhone'] ?? '')),
            'address' => trim((string) ($order['deliveryAddress'] ?? '')),
            'role' => 'Клиент',
            'userType' => 'customer',
            'orderCount' => 0,
            'totalSpent' => 0,
            'firstOrderAt' => $createdAt,
            'lastOrderAt' => $createdAt,
          ];
        }

        $map[$email]['orderCount'] += 1;
        $map[$email]['totalSpent'] += (float) ($order['totalAmount'] ?? 0);

        $createdAt = (string) ($order['createdAt'] ?? '');
        $first = (string) ($map[$email]['firstOrderAt'] ?? '');
        $last = (string) ($map[$email]['lastOrderAt'] ?? '');
        if ($first === '' || strtotime($createdAt) < strtotime($first)) $map[$email]['firstOrderAt'] = $createdAt;
        if (strtotime($createdAt) > strtotime($last)) $map[$email]['lastOrderAt'] = $createdAt;
      }
      return array_values($map);
    };

    $clients = [];
    foreach ($adminsList as $admin) {
      if (!is_array($admin)) continue;
      $clients[] = $mapAdminToClient($admin);
    }
    $clients = array_merge($clients, $aggregateCustomers($ordersList));
    usort($clients, static function (array $a, array $b): int {
      $aAdmin = ((string) ($a['userType'] ?? '')) === 'admin';
      $bAdmin = ((string) ($b['userType'] ?? '')) === 'admin';
      if ($aAdmin && !$bAdmin) return -1;
      if (!$aAdmin && $bAdmin) return 1;
      $aDate = strtotime((string) ($a['lastOrderAt'] ?? '1970-01-01')) ?: 0;
      $bDate = strtotime((string) ($b['lastOrderAt'] ?? '1970-01-01')) ?: 0;
      return $bDate <=> $aDate;
    });

    $selectedUser = null;
    if ($section === 'users-edit') {
      $raw = (string) $entityId;
      $candidates = [];
      if ($raw !== '') {
        $candidates[] = $raw;
        if (!str_starts_with($raw, 'admin:')) {
          $candidates[] = 'admin:' . $raw;
        }
      }
      foreach ($candidates as $cand) {
        foreach ($clients as $c) {
          if ((string) ($c['id'] ?? '') === (string) $cand) {
            $selectedUser = $c;
            break 2;
          }
        }
      }
    }
  ?>

  <?php if ($section === 'users'): ?>
    <div>
      <div class="mb-6 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
        <div>
          <h2 class="text-2xl font-semibold text-gray-900"><?= $e($pageTitle) ?></h2>
          <p class="mt-1 text-sm text-gray-600">Всего пользователей: <?= $e((string) count($clients)) ?></p>
        </div>
        <div class="w-full xl:w-96">
          <input data-table-search-input type="text" class="<?= $e($inputBase) ?>" placeholder="Поиск по имени, телефону, email, роли" value="">
        </div>
      </div>
      <div class="<?= $e($tableWrap) ?>">
        <table class="<?= $e($table) ?>">
          <thead class="<?= $e($thead) ?>">
            <tr>
              <th class="<?= $e($th) ?>">Пользователь</th>
              <th class="<?= $e($th) ?>">Контакты</th>
              <th class="<?= $e($th) ?>">Заказов</th>
              <th class="<?= $e($th) ?>">Сумма покупок</th>
              <th class="<?= $e($th) ?>">Последний заказ</th>
              <th class="<?= $e($th) ?>">Действия</th>
            </tr>
          </thead>
          <tbody class="<?= $e($tbody) ?>">
            <?php if (count($clients) === 0): ?>
              <tr><td class="<?= $e($td) ?> text-center text-gray-500 py-8" colspan="6">Нет клиентов</td></tr>
            <?php else: ?>
              <?php foreach ($clients as $u): ?>
                <?php if (!is_array($u)) continue; ?>
                <?php
                  $id = (string) ($u['id'] ?? '');
                  $name = (string) ($u['name'] ?? '');
                  $email = (string) ($u['email'] ?? '');
                  $phone = (string) ($u['phone'] ?? '');
                  $role = (string) ($u['role'] ?? '');
                  $orderCount = (int) ($u['orderCount'] ?? 0);
                  $totalSpent = (float) ($u['totalSpent'] ?? 0);
                  $lastOrderAt = (string) ($u['lastOrderAt'] ?? '');
                  $searchText = implode(' ', array_filter([$name, $email, $phone, $role]));
                ?>
                <tr class="hover:bg-gray-50 transition-colors" data-table-search-row data-search-text="<?= $e($searchText) ?>">
                  <td class="<?= $e($td) ?>">
                    <div class="text-sm">
                      <div class="font-medium text-gray-900"><?= $e($name !== '' ? $name : ($email !== '' ? explode('@', $email)[0] : 'Пользователь')) ?></div>
                      <div class="text-xs text-gray-500"><?= $e($role !== '' ? $role : 'Клиент') ?></div>
                    </div>
                  </td>
                  <td class="<?= $e($td) ?>">
                    <div class="text-sm">
                      <div class="text-gray-900"><?= $e($phone !== '' ? $phone : '—') ?></div>
                      <div class="text-gray-500"><?= $e($email !== '' ? $email : '—') ?></div>
                    </div>
                  </td>
                  <td class="<?= $e($td) ?> text-gray-600"><?= $e((string) $orderCount) ?></td>
                  <td class="<?= $e($td) ?> whitespace-nowrap font-medium text-gray-900"><?= $e(number_format($totalSpent, 0, '.', ' ')) ?> ₽</td>
                  <td class="<?= $e($td) ?> text-sm text-gray-600"><?= $e($formatDateUi($lastOrderAt)) ?></td>
                  <td class="<?= $e($td) ?> whitespace-nowrap">
                    <div class="flex gap-2 justify-start">
                      <a
                        href="/admin/users/edit?id=<?= $e(rawurlencode($id)) ?>"
                        class="<?= $e($btnSecondaryIcon) ?> h-8 w-8 p-0"
                        title="Редактировать пользователя"
                        aria-label="Редактировать пользователя"
                      ><?= $eyeIcon ?></a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <?php
      $isAdminUser = is_array($selectedUser) && ((string) ($selectedUser['userType'] ?? '')) === 'admin';
    ?>
    <div class="space-y-4">
      <div class="flex items-center gap-4">
        <a href="/admin/users" class="inline-flex items-center justify-center p-2 rounded-lg bg-white hover:bg-gray-200 transition-all duration-200 shadow-sm border border-gray-200" aria-label="Назад">
          <svg class="w-5 h-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
          </svg>
        </a>
        <h2 class="text-2xl font-semibold text-gray-900">Редактирование пользователя</h2>
      </div>

      <?php if (!is_array($selectedUser)): ?>
        <div class="rounded-3xl border border-gray-100 bg-white p-6 text-gray-600 shadow-sm">Пользователь не найден.</div>
      <?php else: ?>
        <?php
          $uid = (string) ($selectedUser['id'] ?? '');
          $name = (string) ($selectedUser['name'] ?? '');
          $email = (string) ($selectedUser['email'] ?? '');
          $phone = (string) ($selectedUser['phone'] ?? '');
          $address = (string) ($selectedUser['address'] ?? '');
          $role = (string) ($selectedUser['role'] ?? ($isAdminUser ? 'Администратор' : 'Клиент'));
          $orderCount = (int) ($selectedUser['orderCount'] ?? 0);
          $totalSpent = (float) ($selectedUser['totalSpent'] ?? 0);
          $firstOrderAt = (string) ($selectedUser['firstOrderAt'] ?? '');
          $lastOrderAt = (string) ($selectedUser['lastOrderAt'] ?? '');
        ?>

        <form
          data-user-edit-form
          data-user-id="<?= $e($uid) ?>"
          data-user-type="<?= $e($isAdminUser ? 'admin' : 'customer') ?>"
          class="space-y-6 rounded-3xl bg-white p-6 shadow-sm"
        >
          <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
              <label class="block text-xs text-gray-600 mb-1">Имя</label>
              <input name="name" class="<?= $e($inputBase) ?>" value="<?= $e($name) ?>" <?= $isAdminUser ? '' : 'disabled' ?> required>
            </div>
            <div>
              <label class="block text-xs text-gray-600 mb-1">Телефон</label>
              <input name="phone" class="<?= $e($inputBase) ?>" value="<?= $e($phone) ?>" <?= $isAdminUser ? '' : 'disabled' ?>>
            </div>
            <div>
              <label class="block text-xs text-gray-600 mb-1">Email</label>
              <input name="email" type="email" class="<?= $e($inputBase) ?>" value="<?= $e($email) ?>" <?= $isAdminUser ? '' : 'disabled' ?> required>
            </div>
            <div>
              <label class="block text-xs text-gray-600 mb-1">Адрес</label>
              <input name="address" class="<?= $e($inputBase) ?>" value="<?= $e($address) ?>" <?= $isAdminUser ? '' : 'disabled' ?>>
            </div>
            <div class="md:col-span-1">
              <label class="block text-xs text-gray-600 mb-1">Роль</label>
              <select name="role" class="<?= $e($inputBase) ?>" <?= $isAdminUser ? '' : 'disabled' ?>>
                <?php if ($role !== '' && !in_array($role, ['Администратор','Менеджер','Контент-менеджер'], true)): ?>
                  <option value="<?= $e($role) ?>" selected><?= $e($role) ?></option>
                <?php endif; ?>
                <option value="Администратор" <?= $role === 'Администратор' ? 'selected' : '' ?>>Администратор</option>
                <option value="Менеджер" <?= $role === 'Менеджер' ? 'selected' : '' ?>>Менеджер</option>
                <option value="Контент-менеджер" <?= $role === 'Контент-менеджер' ? 'selected' : '' ?>>Контент-менеджер</option>
              </select>
            </div>
          </div>

          <?php if ($isAdminUser): ?>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
              <div>
                <label class="block text-xs text-gray-600 mb-1">Новый пароль</label>
                <div class="flex rounded-lg" data-password-field>
                  <input name="password" type="password" class="<?= $e($inputBase) ?> rounded-r-none" placeholder="Оставьте пустым, чтобы не менять" data-password-input>
                  <button
                    type="button"
                    class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-200 bg-gray-50 text-gray-500 hover:text-gray-700 transition-colors"
                    data-password-toggle
                    aria-label="Показать пароль"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                  </button>
                </div>
              </div>
              <div>
                <label class="block text-xs text-gray-600 mb-1">Подтверждение пароля</label>
                <div class="flex rounded-lg" data-password-field>
                  <input name="confirmPassword" type="password" class="<?= $e($inputBase) ?> rounded-r-none" placeholder="Повторите новый пароль" data-password-input>
                  <button
                    type="button"
                    class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-gray-200 bg-gray-50 text-gray-500 hover:text-gray-700 transition-colors"
                    data-password-toggle
                    aria-label="Показать пароль"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="rounded-xl border border-blue-300 bg-blue-50 p-4 text-sm text-blue-700">
              Для клиентов редактирование недоступно: данные формируются автоматически из заказов.
            </div>
          <?php endif; ?>

          <div class="grid grid-cols-1 gap-4 text-sm text-gray-600 md:grid-cols-2">
            <div>Заказов: <?= $e((string) $orderCount) ?></div>
            <div>Сумма покупок: <?= $e(number_format($totalSpent, 0, '.', ' ')) ?> ₽</div>
            <div>Первый заказ: <?= $e($formatDateUi($firstOrderAt)) ?></div>
            <div>Последний заказ: <?= $e($formatDateUi($lastOrderAt)) ?></div>
          </div>

          <?php if ($isAdminUser): ?>
            <div class="flex justify-end gap-3">
              <button type="button" class="flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed bg-blue-400 text-white border border-transparent hover:bg-blue-500 font-medium px-3 sm:px-4 py-2 text-xs sm:text-sm rounded-lg" data-user-delete>Удалить пользователя</button>
              <button type="submit" class="<?= $e($btnPrimary) ?>" data-user-save>Сохранить</button>
            </div>
          <?php endif; ?>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>
<?php elseif ($section === 'settings'): ?>
  <?php
    $settingsArr = is_array($settings) ? $settings : [];
    $pageContent = is_array($settingsArr['pageContent'] ?? null) ? $settingsArr['pageContent'] : [];
    $seo = is_array($settingsArr['seo'] ?? null) ? $settingsArr['seo'] : [];
    $pageBlocks = is_array($settingsArr['pageBlocks'] ?? null) ? $settingsArr['pageBlocks'] : [];

    $textareaClass = "w-full min-h-[120px] px-3 py-2 border border-gray-300 bg-white rounded-lg focus:outline-none focus:border-gray-300 focus:ring-0";

    $collectStringFields = static function (mixed $source, array $path = []) use (&$collectStringFields): array {
      if (is_string($source)) return [['path' => $path, 'value' => $source]];
      if (is_array($source)) {
        $result = [];
        $isList = array_keys($source) === range(0, count($source) - 1);
        foreach ($source as $k => $v) {
          $nextPath = $path;
          $nextPath[] = $isList ? (int) $k : (string) $k;
          $result = array_merge($result, $collectStringFields($v, $nextPath));
        }
        return $result;
      }
      return [];
    };

    $pathLabel = static function (array $path): string {
      $parts = [];
      foreach ($path as $seg) {
        if (is_int($seg)) $parts[] = (string) ($seg + 1);
        else $parts[] = (string) $seg;
      }
      return implode(' / ', $parts);
    };

    $pathName = static function (string $root, array $path): string {
      $name = $root;
      foreach ($path as $seg) {
        $name .= '[' . (string) $seg . ']';
      }
      return $name;
    };

    $tabs = [
      ['key' => 'home', 'label' => 'Главная'],
      ['key' => 'about', 'label' => 'О нас'],
      ['key' => 'services', 'label' => 'Услуги'],
      ['key' => 'vacancies', 'label' => 'Вакансии'],
      ['key' => 'slider', 'label' => 'Слайдер'],
      ['key' => 'contacts', 'label' => 'Контакты'],
    ];

    $pageLabelMap = [
      'home' => 'Главная',
      'about' => 'О нас',
      'services' => 'Услуги',
      'vacancies' => 'Вакансии',
    ];

    $getSeo = static function (string $page, string $field) use ($seo): string {
      $p = is_array($seo[$page] ?? null) ? $seo[$page] : [];
      return (string) ($p[$field] ?? '');
    };
  ?>

  <div>
    <div class="mb-6 flex items-center justify-between">
      <h2 class="text-2xl font-semibold text-gray-900"><?= $e($pageTitle) ?></h2>
      <div class="w-full max-w-md">
        <input
          type="text"
          class="<?= $e($inputBase) ?>"
          placeholder="Поиск по настройкам"
          value=""
          data-settings-search
        >
      </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
      <?php foreach ($tabs as $idx => $tab): ?>
        <?php
          $active = ($tab['key'] ?? '') === 'contacts';
          $cls = $active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-blue-400 hover:text-white';
        ?>
        <button
          type="button"
          data-settings-tab-btn="<?= $e((string) $tab['key']) ?>"
          class="rounded-lg px-4 py-2 text-sm font-medium transition-colors <?= $e($cls) ?>"
        ><?= $e((string) $tab['label']) ?></button>
      <?php endforeach; ?>
    </div>

    <form data-settings-form data-settings-default-tab="contacts" class="bg-white rounded-3xl shadow-sm p-6 space-y-6">
      <div data-settings-tab-pane="contacts">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Контакты</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-gray-600 mb-1">Телефон <span class="text-gray-500">*</span></label>
            <input name="phone" type="tel" class="<?= $e($inputBase) ?>" value="<?= $e((string) ($settingsArr['phone'] ?? '')) ?>" required>
          </div>
          <div>
            <label class="block text-xs text-gray-600 mb-1">Email <span class="text-gray-500">*</span></label>
            <input name="email" type="email" class="<?= $e($inputBase) ?>" value="<?= $e((string) ($settingsArr['email'] ?? '')) ?>" required>
          </div>
          <div>
            <label class="block text-xs text-gray-600 mb-1">WhatsApp <span class="text-gray-500">*</span></label>
            <input name="whatsapp" class="<?= $e($inputBase) ?>" value="<?= $e((string) ($settingsArr['whatsapp'] ?? '')) ?>" required>
          </div>
          <div>
            <label class="block text-xs text-gray-600 mb-1">Telegram <span class="text-gray-500">*</span></label>
            <input name="telegram" class="<?= $e($inputBase) ?>" value="<?= $e((string) ($settingsArr['telegram'] ?? '')) ?>" required>
          </div>
        </div>
      </div>

      <?php foreach (['home','about','services','vacancies'] as $pageKey): ?>
        <?php
          $pc = is_array($pageContent[$pageKey] ?? null) ? $pageContent[$pageKey] : [];
          $blockFields = $collectStringFields(is_array($pageBlocks[$pageKey] ?? null) ? $pageBlocks[$pageKey] : []);
        ?>
        <div class="hidden space-y-4" data-settings-tab-pane="<?= $e($pageKey) ?>">
          <h3 class="text-lg font-semibold text-gray-900">Страница: <?= $e($pageLabelMap[$pageKey] ?? $pageKey) ?></h3>

          <div class="rounded-3xl border border-gray-200 p-4 space-y-4">
            <h4 class="text-sm font-semibold text-gray-900">Контент страницы: <?= $e($pageLabelMap[$pageKey] ?? $pageKey) ?></h4>
            <?php if ($pageKey === 'home'): ?>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                  <label class="block text-xs text-gray-600 mb-1">Текст главной — Абзац 1 <span class="text-gray-500">*</span></label>
                  <textarea name="pageContent[home][heroParagraph1]" class="<?= $e($textareaClass) ?>" required><?= $e((string) ($pc['heroParagraph1'] ?? '')) ?></textarea>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-xs text-gray-600 mb-1">Текст главной — Абзац 2 <span class="text-gray-500">*</span></label>
                  <textarea name="pageContent[home][heroParagraph2]" class="<?= $e($textareaClass) ?>" required><?= $e((string) ($pc['heroParagraph2'] ?? '')) ?></textarea>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-xs text-gray-600 mb-1">Текст главной — Абзац 3 <span class="text-gray-500">*</span></label>
                  <textarea name="pageContent[home][heroParagraph3]" class="<?= $e($textareaClass) ?>" required><?= $e((string) ($pc['heroParagraph3'] ?? '')) ?></textarea>
                </div>
                <div>
                  <label class="block text-xs text-gray-600 mb-1">Текст кнопки на главной <span class="text-gray-500">*</span></label>
                  <input name="pageContent[home][heroButtonText]" class="<?= $e($inputBase) ?>" value="<?= $e((string) ($pc['heroButtonText'] ?? '')) ?>" required>
                </div>
              </div>
            <?php else: ?>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-600 mb-1">Заголовок <span class="text-gray-500">*</span></label>
                  <input name="pageContent[<?= $e($pageKey) ?>][title]" class="<?= $e($inputBase) ?>" value="<?= $e((string) ($pc['title'] ?? '')) ?>" required>
                </div>
                <div>
                  <label class="block text-xs text-gray-600 mb-1">Подзаголовок <span class="text-gray-500">*</span></label>
                  <input name="pageContent[<?= $e($pageKey) ?>][subtitle]" class="<?= $e($inputBase) ?>" value="<?= $e((string) ($pc['subtitle'] ?? '')) ?>" required>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-xs text-gray-600 mb-1">Абзац 1 <span class="text-gray-500">*</span></label>
                  <textarea name="pageContent[<?= $e($pageKey) ?>][paragraph1]" class="<?= $e($textareaClass) ?>" required><?= $e((string) ($pc['paragraph1'] ?? '')) ?></textarea>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-xs text-gray-600 mb-1">Абзац 2 <span class="text-gray-500">*</span></label>
                  <textarea name="pageContent[<?= $e($pageKey) ?>][paragraph2]" class="<?= $e($textareaClass) ?>" required><?= $e((string) ($pc['paragraph2'] ?? '')) ?></textarea>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <div class="rounded-3xl border border-gray-200 p-4 space-y-4">
            <h4 class="text-sm font-semibold text-gray-900">SEO: <?= $e($pageLabelMap[$pageKey] ?? $pageKey) ?></h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-xs text-gray-600 mb-1">Meta title <span class="text-gray-500">*</span></label>
                <input name="seo[<?= $e($pageKey) ?>][title]" class="<?= $e($inputBase) ?>" value="<?= $e($getSeo($pageKey, 'title')) ?>" required>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs text-gray-600 mb-1">Meta description <span class="text-gray-500">*</span></label>
                <textarea name="seo[<?= $e($pageKey) ?>][description]" class="<?= $e($textareaClass) ?>" required><?= $e($getSeo($pageKey, 'description')) ?></textarea>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs text-gray-600 mb-1">Meta keywords <span class="text-gray-500">*</span></label>
                <textarea name="seo[<?= $e($pageKey) ?>][keywords]" class="<?= $e($textareaClass) ?>" required><?= $e($getSeo($pageKey, 'keywords')) ?></textarea>
              </div>
            </div>
          </div>

          <div class="rounded-3xl border border-gray-200 p-4 space-y-4">
            <h4 class="text-sm font-semibold text-gray-900">Текстовые блоки: <?= $e($pageLabelMap[$pageKey] ?? $pageKey) ?></h4>
            <div class="grid grid-cols-1 gap-4">
              <?php foreach ($blockFields as $idx => $field): ?>
                <?php
                  $path = is_array($field['path'] ?? null) ? $field['path'] : [];
                  $value = (string) ($field['value'] ?? '');
                  $label = $pathLabel($path);
                  $name = $pathName('pageBlocks[' . $pageKey . ']', $path);
                ?>
                <div>
                  <label class="block text-xs text-gray-600 mb-1"><?= $e($label) ?> <span class="text-gray-500">*</span></label>
                  <textarea name="<?= $e($name) ?>" class="<?= $e($textareaClass) ?>" required><?= $e($value) ?></textarea>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <?php
        $slider1Images = is_array($settingsArr['slider1Images'] ?? null) ? $settingsArr['slider1Images'] : [];
        $slider2Images = is_array($settingsArr['slider2Images'] ?? null) ? $settingsArr['slider2Images'] : [];
        $slider1Json = json_encode($slider1Images, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $slider2Json = json_encode($slider2Images, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
      ?>
      <div class="hidden space-y-4" data-settings-tab-pane="slider">
        <h3 class="text-lg font-semibold text-gray-900">Слайдер</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-gray-600 mb-2">Изображения слайдера 1 <span class="text-gray-500">*</span></label>
            <div class="space-y-2" data-settings-images="slider1">
              <div class="grid gap-4 grid-cols-4">
                <?php foreach ($slider1Images as $url): ?>
                  <?php $u = trim((string) $url); if ($u === '') continue; ?>
                  <div class="relative bg-gray-100 rounded-lg overflow-hidden aspect-[3/4]" data-image-item="<?= $e($u) ?>">
                    <img src="<?= $e($u) ?>" alt="Slide" class="w-full h-full object-cover">
                    <button type="button" data-remove-image="<?= $e($u) ?>" class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-lg hover:bg-red-700" aria-label="Удалить">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                      </svg>
                    </button>
                  </div>
                <?php endforeach; ?>
                <label class="aspect-[3/4] border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-gray-400 transition-colors" data-settings-upload-tile>
                  <span class="text-sm text-gray-500">Добавить</span>
                  <input type="file" accept="image/webp" multiple class="hidden" data-settings-upload>
                </label>
              </div>
              <input type="hidden" value="<?= $e((string) $slider1Json) ?>" data-settings-images-json="slider1">
            </div>
          </div>
          <div>
            <label class="block text-xs text-gray-600 mb-2">Изображения слайдера 2 <span class="text-gray-500">*</span></label>
            <div class="space-y-2" data-settings-images="slider2">
              <div class="grid gap-4 grid-cols-4">
                <?php foreach ($slider2Images as $url): ?>
                  <?php $u = trim((string) $url); if ($u === '') continue; ?>
                  <div class="relative bg-gray-100 rounded-lg overflow-hidden aspect-[3/4]" data-image-item="<?= $e($u) ?>">
                    <img src="<?= $e($u) ?>" alt="Slide" class="w-full h-full object-cover">
                    <button type="button" data-remove-image="<?= $e($u) ?>" class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-lg hover:bg-red-700" aria-label="Удалить">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                      </svg>
                    </button>
                  </div>
                <?php endforeach; ?>
                <label class="aspect-[3/4] border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-gray-400 transition-colors" data-settings-upload-tile>
                  <span class="text-sm text-gray-500">Добавить</span>
                  <input type="file" accept="image/webp" multiple class="hidden" data-settings-upload>
                </label>
              </div>
              <input type="hidden" value="<?= $e((string) $slider2Json) ?>" data-settings-images-json="slider2">
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <button type="submit" class="<?= $e($btnPrimary) ?>" data-settings-save>Сохранить</button>
      </div>
    </form>
  </div>
<?php else: ?>
  <div class="bg-white rounded-3xl shadow-sm p-6">
    <h2 class="text-2xl font-semibold text-gray-900 mb-2"><?= $e($pageTitle) ?></h2>
    <p class="text-sm text-gray-600">Раздел в процессе переноса.</p>
  </div>
<?php endif; ?>
