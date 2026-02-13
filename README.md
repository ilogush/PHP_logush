# PHP_logush

Интернет-магазин трикотажных изделий на PHP.

## Требования

- PHP 8.1+
- MySQL 5.7+ / 8.0+
- Composer (опционально)

## Установка

1. Клонируйте репозиторий
2. Скопируйте `.env.example` в `.env` и настройте параметры БД
3. Создайте базу данных MySQL
4. Запустите миграции: `php scripts/migrate.php`
5. Заполните тестовыми данными: `php scripts/seed.php`

## Разработка

Запустите dev сервер:

```bash
php -S localhost:8000 -t public scripts/dev_router.php
```

Откройте http://localhost:8000 в браузере.

## Структура

- `/public` - публичная директория (точка входа)
- `/src` - основной код приложения
- `/views` - шаблоны страниц
- `/storage` - данные, логи, загрузки
  - `/storage/data` - JSON данные (если БД недоступна)
  - `/storage/uploads` - загруженные изображения из админки
  - `/storage/backups` - бэкапы базы данных
  - `/storage/ssr` - pre-rendered HTML для SEO
- `/scripts` - утилиты для миграций и деплоя

## Полезные команды

### Разработка
```bash
# Запуск dev сервера
php -S localhost:8000 -t public scripts/dev_router.php

# Миграции БД
php scripts/migrate.php

# Заполнение тестовыми данными
php scripts/seed.php

# Проверка здоровья системы
php scripts/health_check.php
```

### Синхронизация с продакшеном
```bash
# Скачать данные из удаленной БД
php scripts/sync_from_remote.php

# Создать бэкап БД
php scripts/backup_db.php

# Загрузить файлы на FTP
php scripts/ftp_upload.php
```

## Админка

Доступ к админке: http://localhost:8000/admin

Создание администратора:
```bash
php scripts/create_admin.php
```

## Загрузка изображений

Изображения из админки сохраняются в `storage/uploads/`
- Формат: только WebP
- Максимальный размер: 8 МБ
- Доступ: `/api/upload?key=uploads/filename.webp`

## Деплой на WMRS.ru

Подробная инструкция в файле [HOSTING_SETUP.md](HOSTING_SETUP.md)
