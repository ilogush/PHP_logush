<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="min-h-screen flex items-center justify-center bg-white py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full">
    <div class="">
      <div class="mb-8">
        <a class="inline-flex items-center mb-8 text-gray-800 hover:text-black transition-colors text-sm" href="/">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
          </svg>
          НАЗАД
        </a>
        <h1 class="text-3xl font-bold text-black tracking-wider mb-2">ВХОД</h1>
        <p class="text-sm text-gray-600">Административная панель</p>
      </div>

      <?php if ($error !== ''): ?>
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          <?= $e($error) ?>
        </div>
      <?php endif; ?>

      <form class="space-y-6" method="post" action="/login">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-800 mb-2 uppercase tracking-wide">Email / Логин</label>
          <input
            id="email"
            type="text"
            autocomplete="username"
            required
            class="appearance-none block w-full px-4 py-3 border border-gray-600 placeholder-gray-400 focus:outline-none focus:border-black transition-colors"
            placeholder="ilogush@icloud.com"
            name="email"
            value="<?= $e($email) ?>"
          >
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-800 mb-2 uppercase tracking-wide">Пароль</label>
          <div class="relative">
            <input
              id="password"
              type="password"
              data-password-input
              autocomplete="current-password"
              required
              class="appearance-none block w-full px-4 py-3 pr-12 border border-gray-600 placeholder-gray-400 focus:outline-none focus:border-black transition-colors"
              placeholder="••••••••"
              name="password"
              value=""
            >
            <button type="button" data-toggle-password class="absolute top-1/2 -translate-y-1/2 right-4 text-gray-600 hover:text-black transition-colors" aria-label="Показать пароль">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </button>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input id="remember-me" type="checkbox" class="h-4 w-4 text-black focus:ring-0 border-gray-600 cursor-pointer" name="remember-me">
            <label for="remember-me" class="ml-2 block text-sm text-gray-700 cursor-pointer">Запомнить меня</label>
          </div>
          <div class="text-sm">
            <a class="text-gray-800 hover:text-black transition-colors border-b border-transparent hover:border-black" href="/contact">Забыли пароль?</a>
          </div>
        </div>

        <div>
          <button type="submit" class="group flex items-center justify-center gap-x-2 px-4 py-3 w-full text-base font-light transition-colors duration-300 bg-black text-white hover:bg-orange-400 hover:text-black disabled:opacity-50 disabled:cursor-not-allowed">
            <span>Войти</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 transition-colors">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path>
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path>
            </svg>
          </button>
        </div>
      </form>

      <div class="mt-8">
        <p class="text-xs text-gray-500 text-center">Доступ только для авторизованных сотрудников</p>
      </div>
    </div>

    <div class="mt-8 text-center">
      <p class="text-sm text-gray-600">Нужна помощь? <a class="text-black hover:text-gray-800 border-b border-black" href="/contact">Свяжитесь с нами</a></p>
    </div>
  </div>
</div>
