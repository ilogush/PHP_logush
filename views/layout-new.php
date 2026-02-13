<?php
$title = $title ?? 'ИП Логуш';
$content = $content ?? '';
$currentPath = $currentPath ?? '/';
$authUser = $authUser ?? null;
$metaDescription = $metaDescription ?? 'Швейное и вязальное производство трикотажной одежды';
$metaKeywords = $metaKeywords ?? 'швейное производство, вязальное производство, трикотажная одежда';
$canonicalUrl = $canonicalUrl ?? null;
$ogTitle = $ogTitle ?? null;
$ogDescription = $ogDescription ?? null;
$ogUrl = $ogUrl ?? null;
$ogImage = $ogImage ?? null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta name="author" content="ИП Логуш">
    <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords, ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>

    <?php if (is_string($canonicalUrl) && $canonicalUrl !== ''): ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <?php if (is_string($ogTitle) && $ogTitle !== ''): ?>
    <meta property="og:title" content="<?= htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if (is_string($ogDescription) && $ogDescription !== ''): ?>
    <meta property="og:description" content="<?= htmlspecialchars($ogDescription, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if (is_string($ogUrl) && $ogUrl !== ''): ?>
    <meta property="og:url" content="<?= htmlspecialchars($ogUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if (is_string($ogImage) && $ogImage !== ''): ?>
    <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:site_name" content="ИП Логуш">
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="/assets/root-DXB_3M8-.css">
    
    <!-- Animations CSS -->
    <link rel="stylesheet" href="/css/animations.css">
    
    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    
    <!-- Preload images -->
    <link rel="preload" as="image" href="/images/logush_slide_1.jpg">
</head>
<body class="__className_f367f3">
    <?php require __DIR__ . '/partials/header-new.php'; ?>
    
    <main class="pt-24 px-4 md:px-8 lg:px-12">
        <?= $content ?>
    </main>
    
    <?php require __DIR__ . '/partials/footer-new.php'; ?>
    
    <!-- Minimal JavaScript for interactivity -->
    <script src="/js/app.js" defer></script>
</body>
</html>
