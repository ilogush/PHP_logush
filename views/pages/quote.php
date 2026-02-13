<?php
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="page-header">
  <h1>Запрос коммерческого предложения</h1>
</section>

<?php if (!empty($sent)): ?>
  <div class="success-box">
    <p>Заявка отправлена. Мы свяжемся с вами.</p>
  </div>
<?php endif; ?>

<form class="stack card-soft narrow" method="post" action="/quote">
  <label>Имя
    <input type="text" name="name" required>
  </label>
  <label>Email
    <input type="email" name="email" required>
  </label>
  <label>Телефон
    <input type="text" name="phone" required>
  </label>
  <label>Комментарий
    <textarea name="message" rows="4" placeholder="Нужный объем, сроки, требования"></textarea>
  </label>
  <button class="btn btn-dark" type="submit">Отправить</button>
</form>
