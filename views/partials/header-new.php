<?php
$currentPath = $currentPath ?? '/';
$authUser = $authUser ?? null;
?>
<header class="fixed top-0 left-0 w-full z-60 bg-white flex items-center justify-between px-4 md:px-8 lg:px-12 py-3 overflow-visible">
    <!-- Logo -->
    <a class="inline-block" href="/">
        <div class="text-md font-bold text-black tracking-wider border-2 border-gray-500 px-3 py-1 hover:text-gray-600 transition-colors">LOGUSH</div>
    </a>
    
    <!-- Desktop Navigation -->
    <nav class="hidden md:flex w-1/2 justify-between items-center overflow-visible">
        <a class="text-base text-gray-800 inline-block border-b-2 <?= $currentPath === '/' ? 'border-black' : 'border-transparent' ?> hover:border-black transition-colors" href="/">ГЛАВНАЯ</a>
        <a class="text-base text-gray-800 inline-block border-b-2 <?= $currentPath === '/about' ? 'border-black' : 'border-transparent' ?> hover:border-black transition-colors" href="/about">О НАС</a>
        <a class="text-base text-gray-800 inline-block border-b-2 <?= $currentPath === '/services' ? 'border-black' : 'border-transparent' ?> hover:border-black transition-colors" href="/services">УСЛУГИ</a>
        <a class="text-base text-gray-800 inline-block border-b-2 <?= $currentPath === '/sale' ? 'border-black' : 'border-transparent' ?> hover:border-black transition-colors" href="/sale">МАГАЗИН</a>
        <a class="text-base text-gray-800 inline-block border-b-2 <?= $currentPath === '/vacancies' ? 'border-black' : 'border-transparent' ?> hover:border-black transition-colors" href="/vacancies">ВАКАНСИИ</a>
        <a class="text-base text-gray-800 inline-block border-b-2 <?= $currentPath === '/contact' ? 'border-black' : 'border-transparent' ?> hover:border-black transition-colors" href="/contact">КОНТАКТЫ</a>
        
        <!-- Cart Icon -->
        <a class="text-gray-800 hover:text-black transition-colors relative inline-block p-2 -m-2" href="/cart" aria-label="Корзина">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"></path>
            </svg>
            <span data-cart-count class="absolute -top-1 -right-1 hidden bg-black text-white text-xs rounded-full w-5 h-5 items-center justify-center"></span>
        </a>
        
        <!-- User Icon -->
        <a class="text-gray-800 hover:text-black transition-colors" href="<?= $authUser ? '/admin/products' : '/login' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
            </svg>
        </a>
    </nav>
    
    <!-- Mobile Icons -->
    <div class="md:hidden flex items-center gap-4 overflow-visible">
        <a class="relative inline-block p-2 -m-2" href="/cart" aria-label="Корзина">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-black">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"></path>
            </svg>
            <span data-cart-count class="absolute -top-1 -right-1 hidden bg-black text-white text-xs rounded-full w-5 h-5 items-center justify-center"></span>
        </a>
        
        <a href="<?= $authUser ? '/admin/products' : '/login' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-black">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
            </svg>
        </a>
        
        <!-- Mobile Menu Button -->
        <button class="w-6 h-6 flex items-center justify-center" aria-label="Открыть меню" id="mobile-menu-button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-black">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12H12m-8.25 5.25h16.5"></path>
            </svg>
        </button>
    </div>
    
    <!-- Mobile Menu Overlay -->
    <div class="md:hidden fixed top-0 left-0 w-full h-screen bg-black z-50 transition-transform duration-300 ease-in-out translate-x-full" id="mobile-menu">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between px-4 md:px-8 lg:px-12 py-5">
                <a class="text-md font-bold text-white tracking-wider border-2 border-gray-500 px-3 py-1 hover:text-gray-600 transition-colors" href="/">LOGUSH</a>
                <button class="w-6 h-6 flex items-center justify-center" aria-label="Закрыть меню" id="mobile-menu-close">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="flex-1 flex items-center justify-center">
                <nav class="flex flex-col items-center space-y-8">
                    <a class="text-xl text-white hover:text-gray-600 transition-colors" href="/">ГЛАВНАЯ</a>
                    <a class="text-xl text-white hover:text-gray-600 transition-colors" href="/about">О НАС</a>
                    <a class="text-xl text-white hover:text-gray-600 transition-colors" href="/services">УСЛУГИ</a>
                    <a class="text-xl text-white hover:text-gray-600 transition-colors" href="/sale">МАГАЗИН</a>
                    <a class="text-xl text-white hover:text-gray-600 transition-colors" href="/vacancies">ВАКАНСИИ</a>
                    <a class="text-xl text-white hover:text-gray-600 transition-colors" href="/contact">КОНТАКТЫ</a>
                </nav>
            </div>
        </div>
    </div>
</header>
