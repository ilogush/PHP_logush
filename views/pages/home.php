<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$home = is_array($settings['pageContent']['home'] ?? null) ? $settings['pageContent']['home'] : [];
$hero1 = (string) ($home['heroParagraph1'] ?? 'Производство полного цикла для опта.');
$hero2 = (string) ($home['heroParagraph2'] ?? 'Швейный и вязальный цех в Смоленской области.');
$hero3 = (string) ($home['heroParagraph3'] ?? 'Выпускаем партии с контролем качества на каждом этапе.');
$heroButton = (string) ($home['heroButtonText'] ?? 'Узнать больше');
$slider1 = $settings['slider1Images'] ?? ['/images/logush_slide_1.jpg'];
$slider2 = $settings['slider2Images'] ?? ['/images/logush_slide_3.jpg'];
if (!is_array($slider1) || count($slider1) === 0) {
    $slider1 = ['/images/logush_slide_1.jpg'];
}
if (!is_array($slider2) || count($slider2) === 0) {
    $slider2 = ['/images/logush_slide_3.jpg'];
}
?>
<section class="hero">
  <div class="hero-text">
    <span class="eyebrow">Производство</span>
    <h1>ИП ЛОГУШ</h1>
    <p><?= $e($hero1) ?></p>
    <p><?= $e($hero2) ?></p>
    <p><?= $e($hero3) ?></p>
    <a class="btn btn-dark" href="/contact">
      <?= $e($heroButton) ?>
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon-sm" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
      </svg>
    </a>
  </div>
  <div class="hero-media">
    <img src="<?= $e((string) $slider1[0]) ?>" alt="Производство Логуш">
  </div>
</section>

<section class="media-strip">
  <img src="<?= $e((string) $slider1[min(1, count($slider1) - 1)]) ?>" alt="Швейное производство">
  <img src="<?= $e((string) $slider2[0]) ?>" alt="Вязальное производство">
</section>

<section class="section-head">
  <h2>Популярные товары</h2>
  <a href="/services">Смотреть услуги</a>
</section>

<section class="cards-grid">
  <?php foreach ($products as $product): ?>
    <?php if (!is_array($product)) { continue; } ?>
    <?php
      $productId = (string) ($product['id'] ?? '');
      $name = (string) ($product['name'] ?? 'Товар');
      $price = (float) ($product['price'] ?? 0);
      $category = (string) ($product['category'] ?? '');
      $img = (string) (($product['images'][0] ?? '/images/product-placeholder.svg'));
    ?>
    <article class="card">
      <a href="/product/<?= rawurlencode($productId) ?>" class="card-image-wrap">
        <img src="<?= $e($img) ?>" alt="<?= $e($name) ?>">
      </a>
      <div class="card-body">
        <span class="tag"><?= $e($category) ?></span>
        <h3><a href="/product/<?= rawurlencode($productId) ?>"><?= $e($name) ?></a></h3>
        <div class="card-row">
          <strong><?= $e(number_format($price, 0, '.', ' ')) ?> ₽</strong>
          <a class="link-arrow" href="/product/<?= rawurlencode($productId) ?>">
            Детали
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon-sm" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
            </svg>
          </a>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</section>
