<?php
/**
 * @var array{
 *   id: string,
 *   name: string,
 *   category: string,
 *   price: float,
 *   images: string[],
 *   colors: string[],
 *   sizes: string[],
 *   description: string,
 *   material: string,
 *   care: string[]
 * } $product
 */

$e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$productId = $e($product['id'] ?? '');
$name = $e($product['name'] ?? '');
$category = $e($product['category'] ?? '');
$price = (float) ($product['price'] ?? 0);
$images = $product['images'] ?? [];
$colors = $product['colors'] ?? [];
$sizes = $product['sizes'] ?? [];
$description = $e($product['description'] ?? '');
$material = $e($product['material'] ?? '');
$care = $product['care'] ?? [];

$priceFormatted = number_format($price, 0, '.', ' ');

// Normalize image URLs
$normalizeImage = static function(string $url): string {
    $trimmed = trim($url);
    if (empty($trimmed)) return '';
    if (str_starts_with($trimmed, '/api/upload?key=')) return $trimmed;
    
    if (preg_match('~^https?://(?:www\.)?logush\.ru/(.+)$~i', $trimmed, $m)) {
        return '/api/upload?key=' . rawurlencode($m[1]);
    }
    
    return $trimmed;
};

$normalizedImages = array_map($normalizeImage, $images);
$hasImages = !empty($normalizedImages) && !empty($normalizedImages[0]);
?>

<div class="min-h-screen bg-white pb-16">
    <!-- Breadcrumbs -->
    <div class="text-sm text-gray-600">
        <a href="/" class="hover:text-black">Главная</a>
        <span class="mx-4">/</span>
        <a href="/sale" class="hover:text-black">Магазин</a>
        <span class="mx-4">/</span>
        <span class="text-black"><?= $name ?></span>
    </div>

    <div class="grid md:grid-cols-2 gap-12 py-8">
        <!-- Image Gallery -->
        <div>
            <div id="main-image" class="mb-4 bg-gray-100 aspect-[3/4] overflow-hidden">
                <?php if ($hasImages): ?>
                    <img src="<?= $e($normalizedImages[0]) ?>" alt="<?= $name ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="flex h-full w-full items-center justify-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-20 w-20">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.159 2.159m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v10.5a1.5 1.5 0 0 0 1.5 1.5Z" />
                        </svg>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($hasImages && count($normalizedImages) > 1): ?>
                <div class="grid grid-cols-3 gap-4">
                    <?php foreach ($normalizedImages as $idx => $image): ?>
                        <button
                            onclick="selectImage(<?= $idx ?>)"
                            class="thumbnail bg-gray-100 aspect-square overflow-hidden <?= $idx === 0 ? 'ring-2 ring-black' : '' ?>"
                            data-index="<?= $idx ?>"
                        >
                            <img src="<?= $e($image) ?>" alt="<?= $name ?> <?= $idx + 1 ?>" class="w-full h-full object-cover">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-2"><?= $category ?></p>
            <h1 class="text-3xl font-bold text-black tracking-wider mb-4"><?= $name ?></h1>
            <p class="text-2xl font-bold text-black mb-6"><?= $priceFormatted ?> ₽</p>
            
            <p class="text-gray-700 mb-8"><?= $description ?></p>

            <form method="post" action="/cart/add" id="add-to-cart-form">
                <input type="hidden" name="productId" value="<?= $productId ?>">

                <!-- Color Selection -->
                <?php if (!empty($colors)): ?>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-black uppercase tracking-wider mb-3">Цвет</label>
                        <div class="flex flex-wrap gap-3">
                            <?php foreach ($colors as $color): ?>
                                <button
                                    type="button"
                                    onclick="selectColor('<?= $e($color) ?>')"
                                    class="color-btn px-4 py-2 text-sm border transition-colors bg-white text-black border-black hover:bg-black hover:text-white"
                                    data-color="<?= $e($color) ?>"
                                >
                                    <?= $e($color) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="color" id="selected-color" required>
                    </div>
                <?php endif; ?>

                <!-- Size Selection -->
                <?php if (!empty($sizes)): ?>
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-black uppercase tracking-wider">Размер</label>
                            <a href="/size-table" class="text-xs text-gray-600 hover:text-black underline">Таблица размеров</a>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <?php foreach ($sizes as $size): ?>
                                <button
                                    type="button"
                                    onclick="selectSize('<?= $e($size) ?>')"
                                    class="size-btn px-4 py-2 text-sm border transition-colors bg-white text-black border-black hover:bg-black hover:text-white"
                                    data-size="<?= $e($size) ?>"
                                >
                                    <?= $e($size) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="size" id="selected-size" required>
                    </div>
                <?php endif; ?>

                <!-- Quantity -->
                <div class="mb-8">
                    <label class="block text-sm font-medium text-black uppercase tracking-wider mb-3">Количество</label>
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            onclick="changeQuantity(-1)"
                            class="w-12 h-12 border border-black text-black hover:bg-black hover:text-white transition-colors flex items-center justify-center"
                        >
                            -
                        </button>
                        <span id="quantity-display" class="text-lg font-medium w-12 text-center">1</span>
                        <button
                            type="button"
                            onclick="changeQuantity(1)"
                            class="w-12 h-12 border border-black text-black hover:bg-black hover:text-white transition-colors flex items-center justify-center"
                        >
                            +
                        </button>
                        <input type="hidden" name="quantity" id="quantity-input" value="1">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 mb-8">
                    <button
                        type="submit"
                        class="group flex-1 inline-flex items-center justify-center gap-x-2 py-4 px-6 font-light transition-all duration-300 bg-black text-white hover:bg-orange-400 hover:text-black focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black shadow-sm hover:shadow-md"
                    >
                        <span>Добавить в корзину</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path>
                        </svg>
                    </button>
                    <a
                        href="/quote"
                        class="group flex-1 inline-flex items-center justify-center gap-x-2 py-4 px-6 font-light transition-all duration-300 bg-white text-black border-2 border-black hover:bg-black hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black"
                    >
                        <span>Оптовый заказ</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path>
                        </svg>
                    </a>
                </div>
            </form>

            <!-- Additional Info -->
            <div class="space-y-6">
                <?php if ($material): ?>
                    <div>
                        <h3 class="text-sm font-medium text-black uppercase tracking-wider mb-2">Материал</h3>
                        <p class="text-sm text-gray-600"><?= $material ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($care)): ?>
                    <div>
                        <h3 class="text-sm font-medium text-black uppercase tracking-wider mb-2">Уход</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <?php foreach ($care as $instruction): ?>
                                <li>• <?= $e($instruction) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const images = <?= json_encode($normalizedImages) ?>;
let currentQuantity = 1;

function selectImage(index) {
    const mainImage = document.getElementById('main-image');
    const thumbnails = document.querySelectorAll('.thumbnail');
    
    mainImage.innerHTML = `<img src="${images[index]}" alt="<?= $name ?>" class="w-full h-full object-cover">`;
    
    thumbnails.forEach((thumb, idx) => {
        if (idx === index) {
            thumb.classList.add('ring-2', 'ring-black');
        } else {
            thumb.classList.remove('ring-2', 'ring-black');
        }
    });
}

function selectColor(color) {
    document.getElementById('selected-color').value = color;
    document.querySelectorAll('.color-btn').forEach(btn => {
        if (btn.dataset.color === color) {
            btn.classList.remove('bg-white', 'text-black');
            btn.classList.add('bg-black', 'text-white');
        } else {
            btn.classList.remove('bg-black', 'text-white');
            btn.classList.add('bg-white', 'text-black');
        }
    });
}

function selectSize(size) {
    document.getElementById('selected-size').value = size;
    document.querySelectorAll('.size-btn').forEach(btn => {
        if (btn.dataset.size === size) {
            btn.classList.remove('bg-white', 'text-black');
            btn.classList.add('bg-black', 'text-white');
        } else {
            btn.classList.remove('bg-black', 'text-white');
            btn.classList.add('bg-white', 'text-black');
        }
    });
}

function changeQuantity(delta) {
    currentQuantity = Math.max(1, currentQuantity + delta);
    document.getElementById('quantity-display').textContent = currentQuantity;
    document.getElementById('quantity-input').value = currentQuantity;
}

document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
    const color = document.getElementById('selected-color').value;
    const size = document.getElementById('selected-size').value;
    
    if (!color || !size) {
        e.preventDefault();
        if (typeof window.showToast === 'function') {
            window.showToast('Пожалуйста, выберите цвет и размер', 'error');
        }
    }
});
</script>
