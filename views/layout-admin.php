<?php
$title = isset($title) ? (string) $title : 'Админ';
$content = isset($content) ? (string) $content : '';
$currentPath = isset($currentPath) ? (string) $currentPath : '';
$authUser = isset($authUser) && is_array($authUser) ? $authUser : null;
$isAdminShell = str_starts_with($currentPath, '/admin');
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$assetUrl = static function (string $path): string {
  $full = dirname(__DIR__) . '/public' . $path;
  $v = is_file($full) ? (string) @filemtime($full) : '1';
  return $path . '?v=' . rawurlencode($v);
};

$isActive = static function (string $path) use ($currentPath): bool {
  if ($path === '') return false;
  return $currentPath === $path || str_starts_with($currentPath, $path . '/');
};

$menuItems = [
  [
    'name' => 'Товары',
    'path' => '/admin/products',
    'icon' => '<svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>',
  ],
  [
    'name' => 'Категории',
    'path' => '/admin/categories',
    'icon' => '<svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>',
  ],
  [
    'name' => 'Цвета',
    'path' => '/admin/colors',
    'icon' => '<svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z" /></svg>',
  ],
  [
    'name' => 'Размеры',
    'path' => '/admin/sizes',
    'icon' => '<svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>',
  ],
  [
    'name' => 'Заказы',
    'path' => '/admin/orders',
    'icon' => '<svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>',
  ],
  [
    'name' => 'Заявки',
    'path' => '/admin/quotes',
    'icon' => '<svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5A2.25 2.25 0 0119.5 19.5h-15A2.25 2.25 0 012.25 17.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.909A2.25 2.25 0 012.25 6.993V6.75" /></svg>',
  ],
  [
    'name' => 'Пользователи',
    'path' => '/admin/users',
    'icon' => '<svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>',
  ],
  [
    'name' => 'Настройки',
    'path' => '/admin/settings',
    'icon' => '<svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12a7.5 7.5 0 00-.09-1.16l1.72-1.33a.75.75 0 00.17-.94l-1.64-2.84a.75.75 0 00-.91-.33l-2.02.81a7.5 7.5 0 00-2-1.16l-.31-2.16a.75.75 0 00-.74-.64h-3.28a.75.75 0 00-.74.64l-.31 2.16a7.5 7.5 0 00-2 1.16l-2.02-.81a.75.75 0 00-.91.33L2.7 8.57a.75.75 0 00.17.94l1.72 1.33A7.5 7.5 0 004.5 12c0 .39.03.78.09 1.16l-1.72 1.33a.75.75 0 00-.17.94l1.64 2.84c.2.35.61.5.98.33l2.02-.81c.62.5 1.29.9 2 1.16l.31 2.16c.06.36.37.64.74.64h3.28c.37 0 .68-.28.74-.64l.31-2.16c.71-.26 1.38-.66 2-1.16l2.02.81c.37.17.78.02.98-.33l1.64-2.84a.75.75 0 00-.17-.94l-1.72-1.33c.06-.38.09-.77.09-1.16z" /></svg>',
  ],
];
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= $e($title) ?></title>
  <link rel="stylesheet" href="/assets/root-DXB_3M8-.css">
  <link rel="stylesheet" href="<?= $e($assetUrl('/css/ui.css')) ?>">
</head>
	<body class="__className_f367f3 text-black">
    <?php if (isset($_SESSION['flash_success']) && is_string($_SESSION['flash_success']) && $_SESSION['flash_success'] !== ''): ?>
      <?php $flashSuccess = (string) $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
      <script>
        window.addEventListener('DOMContentLoaded', function () {
          if (typeof window.showToast === 'function') {
            window.showToast(<?= json_encode($flashSuccess, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>, 'success');
          }
        });
      </script>
    <?php endif; ?>
	  <?php if (!$isAdminShell): ?>
	    <?= $content ?>
	    <script src="<?= $e($assetUrl('/js/toast.js')) ?>" defer></script>
	    <script src="<?= $e($assetUrl('/js/admin.js')) ?>" defer></script>
	  <?php else: ?>
	    <div class="flex h-screen overflow-hidden bg-gray-100" data-admin-shell="1">
      <div id="adminOverlay" class="fixed inset-0 z-40 bg-black/50 lg:hidden hidden" aria-hidden="true"></div>

	      <aside id="adminSidebar" class="fixed left-0 top-0 z-50 flex h-screen w-56 -translate-x-full flex-col overflow-y-hidden bg-gray-50 duration-300 lg:static lg:overflow-hidden lg:w-0">
        <div class="flex items-center justify-between gap-2 px-6 pt-8 pb-7">
          <a href="/" class="flex items-center">
            <span class="text-xl font-bold text-gray-900">Logush</span>
          </a>
        </div>

        <nav class="flex h-full flex-col overflow-y-auto p-4">
          <div>
            <ul class="flex flex-col gap-2 mb-6">
              <?php foreach ($menuItems as $item): ?>
                <?php
                  $path = (string) ($item['path'] ?? '');
                  $active = $isActive($path);
                  $linkClasses = $active
                    ? 'bg-blue-600 text-white'
                    : 'text-gray-700 hover:bg-blue-400 hover:text-white hover:shadow-sm';
                ?>
                <li>
                  <a
                    href="<?= $e($path) ?>"
                    class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-colors <?= $linkClasses ?>"
                  >
                    <?= (string) ($item['icon'] ?? '') ?>
                    <span><?= $e((string) ($item['name'] ?? '')) ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <div class="mt-auto border-t border-gray-100 pt-4">
            <button
              type="button"
              id="adminSidebarLogout"
              class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-blue-400 hover:text-white hover:shadow-sm"
            >
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
              </svg>
              <span>Выйти</span>
            </button>
          </div>
        </nav>
      </aside>

      <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden lg:ml-0">
        <header class="flex w-full">
          <div class="flex grow items-center justify-between px-4 py-3 lg:px-6 lg:py-4">
            <div class="flex items-center gap-4">
              <button
                type="button"
                id="adminSidebarToggle"
                class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50"
                aria-label="Открыть меню"
              >
                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M3.25 6C3.25 5.58579 3.58579 5.25 4 5.25L20 5.25C20.4142 5.25 20.75 5.58579 20.75 6C20.75 6.41421 20.4142 6.75 20 6.75L4 6.75C3.58579 6.75 3.25 6.41422 3.25 6ZM3.25 18C3.25 17.5858 3.58579 17.25 4 17.25L20 17.25C20.4142 17.25 20.75 17.5858 20.75 18C20.75 18.4142 20.4142 18.75 20 18.75L4 18.75C3.58579 18.75 3.25 18.4142 3.25 18ZM4 11.25C3.58579 11.25 3.25 11.5858 3.25 12C3.25 12.4142 3.58579 12.75 4 12.75L12 12.75C12.4142 12.75 12.75 12.4142 12.75 12C12.75 11.5858 12.4142 11.25 12 11.25L4 11.25Z" />
                </svg>
              </button>
            </div>

            <div class="flex items-center gap-3">
              <div class="relative">
	                <button
	                  type="button"
	                  id="adminNotifBtn"
	                  class="relative flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-900"
	                  aria-label="Уведомления"
	                >
                  <span id="adminNotifBadge" class="absolute -top-1 -right-1 z-10 hidden min-h-5 min-w-5 items-center justify-center rounded-full bg-orange-400 px-1 text-xs font-semibold text-white"></span>
                  <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z" />
                  </svg>
                </button>

                <div id="adminNotifPanel" class="absolute right-0 mt-4 hidden w-[350px] rounded-2xl border border-gray-200 bg-white p-3 shadow-lg">
                  <div class="mb-3 flex items-center justify-between border-b border-gray-100 pb-3">
                    <h5 class="text-lg font-semibold text-gray-900">Уведомления</h5>
                    <button type="button" id="adminNotifClose" class="text-gray-600 hover:text-gray-900" aria-label="Закрыть">
                      <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z" />
                      </svg>
                    </button>
                  </div>
                  <div id="adminNotifBody" class="text-sm text-gray-600">Загрузка...</div>
                </div>
              </div>

	              <button
	                type="button"
	                id="adminHeaderLogout"
	                class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-900"
	                title="Выйти"
	              >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
              </button>
            </div>
          </div>
        </header>

        <main class="flex-1">
          <div class="mx-auto max-w-screen-2xl p-4 md:p-6">
            <?= $content ?>
          </div>
        </main>
      </div>
	    </div>
	    <script src="<?= $e($assetUrl('/js/toast.js')) ?>" defer></script>
	    <script src="<?= $e($assetUrl('/js/admin.js')) ?>" defer></script>
	  <?php endif; ?>
	</body>
	</html>
