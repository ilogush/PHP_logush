<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="min-h-[calc(100vh-96px)] bg-white pb-16">
  <div class="py-8">
    <div class="max-w-5xl">
      <h1 class="text-3xl font-bold text-black mb-8 tracking-wider">ТАБЛИЦА РАЗМЕРОВ</h1>

      <div class="border border-gray-500 overflow-x-auto">
        <table class="min-w-full text-left">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Размер</th>
              <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Обхват груди</th>
              <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Обхват талии</th>
              <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider">Обхват бедер</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php
            $mock = [
              'XS' => ['80-84', '60-64', '86-90'],
              'S' => ['84-88', '64-68', '90-94'],
              'M' => ['88-92', '68-72', '94-98'],
              'L' => ['92-96', '72-78', '98-104'],
              'XL' => ['96-102', '78-84', '104-110'],
            ];
            foreach ($mock as $size => $values):
            ?>
              <tr class="bg-white">
                <td class="px-4 py-3 text-sm text-black font-medium"><?= $e($size) ?></td>
                <td class="px-4 py-3 text-sm text-gray-700"><?= $e($values[0]) ?></td>
                <td class="px-4 py-3 text-sm text-gray-700"><?= $e($values[1]) ?></td>
                <td class="px-4 py-3 text-sm text-gray-700"><?= $e($values[2]) ?></td>
              </tr>
            <?php endforeach; ?>

            <?php foreach ($sizes as $size): ?>
              <?php if (!is_array($size)) { continue; } ?>
              <?php $name = (string) ($size['name'] ?? ''); ?>
              <?php if (isset($mock[$name]) || $name === '') { continue; } ?>
              <tr class="bg-white">
                <td class="px-4 py-3 text-sm text-black font-medium"><?= $e($name) ?></td>
                <td class="px-4 py-3 text-sm text-gray-700">-</td>
                <td class="px-4 py-3 text-sm text-gray-700">-</td>
                <td class="px-4 py-3 text-sm text-gray-700">-</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="pt-6">
        <a class="group inline-flex items-center justify-center gap-x-2 font-light transition-all duration-300 h-12 px-4 text-base w-auto min-w-[180px] bg-black text-white hover:bg-orange-400 hover:text-black shadow-sm hover:shadow-md" href="/sale">
          <span>В магазин</span>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-5 h-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6"></path>
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12"></path>
          </svg>
        </a>
      </div>
    </div>
  </div>
</div>
