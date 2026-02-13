<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="page-header">
  <h1>Таблица размеров</h1>
</section>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Размер</th>
        <th>Обхват груди</th>
        <th>Обхват талии</th>
        <th>Обхват бедер</th>
      </tr>
    </thead>
    <tbody>
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
      <tr>
        <td><?= $e($size) ?></td>
        <td><?= $e($values[0]) ?></td>
        <td><?= $e($values[1]) ?></td>
        <td><?= $e($values[2]) ?></td>
      </tr>
      <?php endforeach; ?>

      <?php foreach ($sizes as $size): ?>
        <?php if (!is_array($size)) { continue; } ?>
        <?php $name = (string) ($size['name'] ?? ''); ?>
        <?php if (isset($mock[$name]) || $name === '') { continue; } ?>
        <tr>
          <td><?= $e($name) ?></td>
          <td>-</td>
          <td>-</td>
          <td>-</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
