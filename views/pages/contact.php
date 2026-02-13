<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$phone = (string) ($settings['phone'] ?? '');
$email = (string) ($settings['email'] ?? '');
$telegram = (string) ($settings['telegram'] ?? '');
$whatsapp = (string) ($settings['whatsapp'] ?? '');
?>
<section class="page-header">
  <h1>Контакты</h1>
</section>

<div class="contact-grid">
  <article class="card-soft">
    <h2>Телефон</h2>
    <p><?= $e($phone) ?></p>
  </article>
  <article class="card-soft">
    <h2>Email</h2>
    <p><?= $e($email) ?></p>
  </article>
  <article class="card-soft">
    <h2>Telegram</h2>
    <p><a href="<?= $e($telegram) ?>" target="_blank" rel="noopener">Связаться</a></p>
  </article>
  <article class="card-soft">
    <h2>WhatsApp</h2>
    <p><a href="<?= $e($whatsapp) ?>" target="_blank" rel="noopener">Написать</a></p>
  </article>
</div>
