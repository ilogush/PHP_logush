<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$settings = $settings ?? [];
$products = $products ?? [];
$home = is_array($settings['pageContent']['home'] ?? null) ? $settings['pageContent']['home'] : [];
$hero1 = (string) ($home['heroParagraph1'] ?? 'Мы — надежный производственный партнер для оптовиков и розничных сетей по всей России. За 20 лет научились создавать трикотаж премиального качества.');
$hero2 = (string) ($home['heroParagraph2'] ?? 'Команда из 100+ специалистов выпускает до 10 000 изделий ежемесячно. Швейный цех в Рославле, вязальное производство в Смоленске.');
$hero3 = (string) ($home['heroParagraph3'] ?? 'Полный цикл — от разработки лекал до отгрузки готовой продукции. Помогаем оптовым компаниям и федеральным сетям реализовывать амбициозные проекты.');
$heroButton = (string) ($home['heroButtonText'] ?? 'Узнать больше');
?>

<article class="font-sans bg-white text-black">
    <!-- Hero Section -->
    <section class="relative flex py-16 md:py-20 lg:py-24" aria-label="Главная информация о компании">
        <div class="hidden md:block absolute top-1/2 left-0 pointer-events-none select-none" style="transform:translateY(calc(-50% - 60px))">
            <span class="font-extrabold text-black leading-none tracking-wider block" style="font-size:min(80vw, 80vh);line-height:0.8">L</span>
        </div>
        <div class="flex flex-col md:ml-[50vw] pl-8 md:pl-0 relative z-10">
            <div class="flex-1 flex flex-col justify-center pt-0">
                <div class="max-w-lg text-lg md:-ml-8">
                    <p class="text-lg text-gray-700 mb-6"><?= $e($hero1) ?></p>
                    <p class="text-lg text-gray-700 mb-6"><?= $e($hero2) ?></p>
                    <p class="text-lg text-gray-700 mb-8"><?= $e($hero3) ?></p>
                    <a class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 py-3 px-4 text-base w-auto min-w-[180px] bg-black text-white hover:bg-orange-400 hover:text-black shadow-sm hover:shadow-md" href="/contact">
                        <span><?= $e($heroButton) ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Slider Section -->
    <section id="slider-section" class="relative w-full" style="height:200vh" aria-label="Галерея производства">
        <div class="sticky top-0 h-screen overflow-hidden">
            <div class="flex h-full transition-transform duration-100 ease-out" id="slider-container">
                <div class="relative h-full flex-shrink-0" style="width:100vw">
                    <img src="/images/logush_slide_1.jpg" alt="Швейное производство ИП Логуш" class="object-cover w-full h-full" loading="eager">
                </div>
                <div class="relative h-full flex-shrink-0" style="width:100vw">
                    <img src="/images/logush_slide_2.jpg" alt="Трикотажное производство" class="object-cover w-full h-full" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <!-- Sewing Production Section -->
    <section id="sewing-production" aria-label="Швейное производство" class="py-12 md:py-16 lg:py-20 border-b border-gray-600 md:grid md:grid-cols-6 lg:grid-cols-12">
        <h2 class="tracking-tight text-xl leading-7 mb-4 md:mb-6 md:col-start-1 md:col-end-7 md:text-3xl md:leading-9 lg:col-start-1 lg:col-end-6 font-semibold">
            <span class="text-black block">Швейное</span>
            <span class="text-gray-600 block">производство</span>
        </h2>
        <div class="text-sm leading-4 hidden lg:col-start-1 lg:col-end-3 lg:block">
            <span class="block mb-4 font-semibold text-black">Возможности</span>
            <ul class="list-disc list-inside text-gray-600">
                <li>Прозрачное ценообразование</li>
                <li>Гарантия качества пошива</li>
                <li>Соблюдение сроков</li>
                <li>Индивидуальный подход</li>
                <li>Контроль на каждом этапе</li>
                <li>Без скрытых доплат</li>
            </ul>
        </div>
        <div class="col-start-1 col-end-7 lg:col-start-7 lg:col-end-13">
            <p class="text-base leading-7 tracking-tight mb-8 text-black">
                Швейный цех в Рославле оборудован современной техникой: прямострочные машины, оверлоки, плоскошовные агрегаты, автоматизированные раскройные системы. Мощности позволяют шить до 10 000 единиц в месяц.
            </p>
            <p class="text-base leading-7 tracking-tight mb-8 text-black">
                Шьем крупные партии для оптовиков: женскую, мужскую и детскую одежду. Работаем на давальческом сырье по готовым ТЗ. Минимум — 500 изделий, срок — 14 дней.
            </p>
        </div>
    </section>

    <!-- Products Section -->
    <?php if (count($products) > 0): ?>
    <section class="py-12 md:py-16 lg:py-20">
        <h2 class="text-2xl md:text-3xl font-bold text-black mb-8">Популярные товары</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
                <?php if (!is_array($product)) continue; ?>
                <?php
                    $productId = (string) ($product['id'] ?? '');
                    $name = (string) ($product['name'] ?? 'Товар');
                    $price = (float) ($product['price'] ?? 0);
                    $category = (string) ($product['category'] ?? '');
                    $img = (string) (($product['images'][0] ?? '/images/product-placeholder.svg'));
                ?>
                <article class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                    <a href="/product/<?= rawurlencode($productId) ?>" class="block aspect-square overflow-hidden">
                        <img src="<?= $e($img) ?>" alt="<?= $e($name) ?>" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </a>
                    <div class="p-4">
                        <?php if ($category): ?>
                        <span class="text-xs text-gray-500 uppercase tracking-wide"><?= $e($category) ?></span>
                        <?php endif; ?>
                        <h3 class="text-lg font-semibold text-black mt-2 mb-3">
                            <a href="/product/<?= rawurlencode($productId) ?>" class="hover:text-gray-600"><?= $e($name) ?></a>
                        </h3>
                        <div class="flex items-center justify-between">
                            <strong class="text-xl text-black"><?= $e(number_format($price, 0, '.', ' ')) ?> ₽</strong>
                            <a href="/product/<?= rawurlencode($productId) ?>" class="text-sm text-gray-600 hover:text-black inline-flex items-center gap-1">
                                Подробнее
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Reviews Section -->
    <section aria-label="Отзывы наших клиентов" class="py-12 md:py-16 lg:py-20 border-b border-gray-600 md:grid md:grid-cols-6 lg:grid-cols-12">
        <h2 class="tracking-tight text-xl leading-7 mb-4 md:mb-6 md:col-start-1 md:col-end-7 md:text-3xl md:leading-9 lg:col-start-1 lg:col-end-6 font-semibold">
            <span class="text-black block">Отзывы</span>
            <span class="text-gray-600 block">наших клиентов</span>
        </h2>
        <div class="col-start-1 col-end-7 lg:col-start-7 lg:col-end-13 space-y-8">
            <blockquote class="text-base leading-7 text-black border-b border-gray-600 last:border-b-0 pb-6">
                "Нужно было изготовить 800 женских свитеров к осеннему сезону. LOGUSH сработали безупречно: вязка идеальная, размеры точь-в-точь по лекалам, сроки выдержаны день в день. Цена оказалась на 20% выгоднее, чем у других производств. Брендированная упаковка — вообще огонь!"
                <footer class="mt-4 text-black font-semibold">
                    Анна Соколова
                    <span class="block font-normal text-gray-600">Основатель бренда WOOL&CO</span>
                </footer>
            </blockquote>
            <blockquote class="text-base leading-7 text-black border-b border-gray-600 last:border-b-0 pb-6">
                "Третий сезон работаем с Логуш — и каждый раз убеждаемся, что выбрали правильно. Ребята вникают в каждую мелочь, помогают дорабатывать лекала, предлагают технические решения. Произвели уже больше 15 000 изделий, брак — единичные случаи. Это партнеры, на которых можно положиться."
                <footer class="mt-4 text-black font-semibold">
                    Михаил Петров
                    <span class="block font-normal text-gray-600">Директор по развитию KNIT STORY</span>
                </footer>
            </blockquote>
            <blockquote class="text-base leading-7 text-black border-b border-gray-600 last:border-b-0 pb-6">
                "Запускали детскую линию трикотажа — задача была непростая: многоцветные узоры, размеры от 80 до 140 см, строгие требования к гипоаллергенности. LOGUSH справились блестяще! Профессионализм на каждом этапе, а цены — более чем разумные."
                <footer class="mt-4 text-black font-semibold">
                    Елена Иванова
                    <span class="block font-normal text-gray-600">Создатель бренда SOFT LINE</span>
                </footer>
            </blockquote>
            <blockquote class="text-base leading-7 text-black border-b border-gray-600 last:border-b-0 pb-6">
                "Работаем с ИП Логуш уже второй год. Качество пошива на высоте, сроки всегда соблюдаются. Особенно радует гибкость в работе — всегда идут навстречу и помогают решить любые вопросы."
                <footer class="mt-4 text-black font-semibold">
                    Дмитрий Волков
                    <span class="block font-normal text-gray-600">Владелец сети магазинов TEXTILE PRO</span>
                </footer>
            </blockquote>
            <blockquote class="text-base leading-7 text-black border-b border-gray-600 last:border-b-0 pb-6">
                "Заказывали партию из 1200 толстовок для корпоративного мерча. Результат превзошел ожидания — качество печати отличное, ткань приятная, все размеры точные. Рекомендуем!"
                <footer class="mt-4 text-black font-semibold">
                    Ольга Морозова
                    <span class="block font-normal text-gray-600">Менеджер по закупкам TECH CORP</span>
                </footer>
            </blockquote>
            <div class="pt-4">
                <button class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 py-3 px-4 text-base w-auto min-w-[180px] bg-black text-white hover:bg-orange-400 hover:text-black shadow-sm hover:shadow-md">
                    <span>Показать все отзывы</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-12 md:py-16 lg:py-20 md:grid md:grid-cols-6 lg:grid-cols-12">
        <h2 class="tracking-tight text-xl leading-7 mb-4 md:mb-6 md:col-start-1 md:col-end-7 md:text-3xl md:leading-9 lg:col-start-1 lg:col-end-6 font-semibold">
            <span class="text-black block">Частые вопросы</span>
            <span class="text-gray-600 block">о производстве</span>
        </h2>
        <div class="col-start-1 col-end-7 md:col-start-1 md:col-end-7 lg:col-start-7 lg:col-end-13">
            <?php
            $faqs = [
                ['q' => 'С какого объема можно начать сотрудничество?', 'a' => 'Для вязаных изделий стартовая партия — от 300 единиц, для швейных — от 500. Такой подход гарантирует выгодную цену и стабильное качество.'],
                ['q' => 'За какой срок вы изготовите мою коллекцию?', 'a' => 'Обычно 14-21 рабочий день — точные сроки зависят от технической сложности моделей и размера партии.'],
                ['q' => 'Какие ткани и пряжу вы используете?', 'a' => 'Шерсть мериноса, кашемир, альпака, хлопок, лён и современные синтетические волокна. Можем работать с вашими материалами.'],
            ];
            foreach ($faqs as $faq):
            ?>
            <div class="border-b border-gray-600 py-4 last:border-b-0">
                <button class="flex justify-between w-full text-left text-lg text-black hover:text-gray-700 transition-colors" data-faq-button>
                    <?= $e($faq['q']) ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 text-current flex-shrink-0 ml-4" style="transition: transform 0.3s ease;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                    </svg>
                </button>
                <div style="max-height: 0; opacity: 0; overflow: hidden; transition: max-height 0.3s ease, opacity 0.3s ease; display: none;" class="text-base leading-7 text-gray-600">
                    <div class="mt-4"><?= $e($faq['a']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</article>

<script>
// Parallax slider effect
window.addEventListener('scroll', function() {
    const section = document.getElementById('slider-section');
    if (!section) return;
    
    const rect = section.getBoundingClientRect();
    const sectionHeight = section.offsetHeight;
    const viewportHeight = window.innerHeight;
    
    if (rect.top < viewportHeight && rect.bottom > 0) {
        const scrollProgress = Math.max(0, Math.min(1, (viewportHeight - rect.top) / (sectionHeight + viewportHeight)));
        const container = document.getElementById('slider-container');
        if (container) {
            container.style.transform = `translateX(-${scrollProgress * 50}%)`;
        }
    }
});
</script>
