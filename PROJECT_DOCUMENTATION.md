# Документация проекта Logush

## Обзор проекта

Logush - интернет-магазин трикотажных изделий, разработанный на чистом PHP без использования фреймворков. Проект использует современные практики PHP 8.1+ и поддерживает работу как с MySQL базой данных, так и с JSON-файлами в качестве fallback-хранилища.

## Технический стек

- **PHP**: 8.1+
- **База данных**: MySQL 5.7+ / 8.0+ (с fallback на JSON)
- **Frontend**: Vanilla JavaScript, CSS
- **Сервер**: Apache/Nginx (поддержка PHP built-in server для разработки)

## Архитектура приложения

### Основные компоненты

#### 1. App.php - Главный класс приложения
Точка входа приложения, управляет маршрутизацией и инициализацией компонентов:
- Обрабатывает HTTP методы (GET, POST, PUT, DELETE)
- Поддерживает method override для shared hosting
- Разделяет запросы на API и страницы
- Обрабатывает React Router data requests

#### 2. DataStore.php - Слой работы с данными
Универсальное хранилище данных с автоматическим переключением между MySQL и JSON:
- Автоматическая миграция данных из JSON в БД
- Fallback на JSON при недоступности БД
- Управление сущностями: products, categories, pages, settings, orders, users

#### 3. Auth.php - Аутентификация
Управление сессиями и авторизацией администраторов:
- Проверка учетных данных
- Управление сессиями
- Поддержка "запомнить меня"

#### 4. PageController.php - Контроллер страниц
Обработка публичных страниц сайта:
- Рендеринг шаблонов через View
- Поддержка SSR (Server-Side Rendering)
- Динамическая маршрутизация

#### 5. ApiController.php - API контроллер
RESTful API для управления данными:
- CRUD операции для всех сущностей
- Загрузка изображений (WebP)
- Аутентификация администраторов
- Управление заказами

#### 6. View.php - Шаблонизатор
Простой PHP-шаблонизатор:
- Рендеринг PHP-шаблонов
- Передача переменных в шаблоны
- Поддержка layout'ов

#### 7. SnapshotRenderer.php - SSR для SEO
Pre-rendering HTML страниц для поисковых систем:
- Кэширование статических версий страниц
- Автоматическая отдача snapshot'ов ботам
- Хранение в `/storage/ssr/`

#### 8. Database.php - Подключение к БД
Управление подключением к MySQL:
- Чтение конфигурации из .env
- PDO с обработкой ошибок
- UTF-8 поддержка

#### 9. DatabaseMigrator.php - Миграции БД
Автоматическое создание и обновление схемы БД:
- Создание таблиц
- Добавление колонок
- Создание индексов

## Структура директорий

```
/
├── public/              # Публичная директория (document root)
│   ├── index.php       # Точка входа приложения
│   ├── css/            # Стили
│   ├── js/             # JavaScript файлы
│   └── images/         # Статические изображения
│
├── src/                # Исходный код приложения
│   ├── App.php         # Главный класс приложения
│   ├── bootstrap.php   # Инициализация окружения
│   ├── DataStore.php   # Слой данных
│   ├── Auth.php        # Аутентификация
│   ├── PageController.php
│   ├── ApiController.php
│   ├── View.php
│   ├── SnapshotRenderer.php
│   ├── Database.php
│   └── DatabaseMigrator.php
│
├── views/              # PHP шаблоны
│   ├── layout.php      # Основной layout
│   ├── layout-admin.php
│   ├── pages/          # Шаблоны страниц
│   └── partials/       # Переиспользуемые части
│
├── storage/            # Данные и логи
│   ├── data/           # JSON данные (fallback)
│   ├── uploads/        # Загруженные изображения
│   ├── backups/        # Бэкапы БД
│   ├── ssr/            # Pre-rendered HTML
│   └── php_errors.log  # Логи ошибок
│
└── scripts/            # Утилиты и скрипты
    ├── migrate.php     # Миграции БД
    ├── seed.php        # Заполнение тестовыми данными
    ├── backup_db.php   # Бэкап базы данных
    └── ...             # Другие утилиты
```

## Установка и настройка

### 1. Требования
- PHP 8.1 или выше
- MySQL 5.7+ / 8.0+
- Расширения PHP: PDO, pdo_mysql, json, mbstring

### 2. Установка

```bash
# Клонировать репозиторий
git clone <repository-url>
cd php_logush

# Настроить окружение
cp .env.example .env
# Отредактировать .env с параметрами БД

# Создать базу данных
mysql -u root -p -e "CREATE DATABASE logush_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Запустить миграции
php scripts/migrate.php

# Заполнить тестовыми данными
php scripts/seed.php
```

### 3. Конфигурация .env

```env
APP_DEBUG=0                    # 1 для разработки, 0 для продакшена
DB_HOST=127.0.0.1             # Хост БД
DB_PORT=3306                  # Порт БД
DB_NAME=logush_prod           # Имя БД
DB_USER=logush_user           # Пользователь БД
DB_PASSWORD=change_me         # Пароль БД
DB_CHARSET=utf8mb4            # Кодировка

# Опционально для продакшена
APP_FORCE_HTTPS=1             # Принудительный HTTPS
APP_CANONICAL_HOST=logush.ru  # Канонический домен
```

## Разработка

### Запуск dev-сервера

```bash
php -S localhost:8000 -t public scripts/dev_router.php
```

Откройте http://localhost:8000 в браузере.

### Полезные команды

```bash
# Миграции БД
php scripts/migrate.php

# Заполнение тестовыми данными
php scripts/seed.php

# Создание администратора
php scripts/create_admin.php

# Проверка здоровья системы
php scripts/health_check.php

# Бэкап базы данных
php scripts/backup_db.php

# Синхронизация с продакшеном
php scripts/sync_from_remote.php
```

## API Endpoints

### Публичные endpoints

```
GET  /                    # Главная страница
GET  /products            # Каталог товаров
GET  /product/{id}        # Страница товара
GET  /cart                # Корзина
GET  /checkout            # Оформление заказа
POST /checkout            # Создание заказа
GET  /contact             # Контакты
GET  /about               # О компании
```

### API endpoints (требуют аутентификации для изменений)

```
# Товары
GET    /api/products           # Список товаров
GET    /api/products/{id}      # Получить товар
POST   /api/products           # Создать товар (admin)
PUT    /api/products/{id}      # Обновить товар (admin)
DELETE /api/products/{id}      # Удалить товар (admin)

# Категории
GET    /api/categories         # Список категорий
POST   /api/categories         # Создать категорию (admin)
PUT    /api/categories/{id}    # Обновить категорию (admin)
DELETE /api/categories/{id}    # Удалить категорию (admin)

# Страницы
GET    /api/pages              # Список страниц
POST   /api/pages              # Создать страницу (admin)
PUT    /api/pages/{id}         # Обновить страницу (admin)
DELETE /api/pages/{id}         # Удалить страницу (admin)

# Настройки
GET    /api/settings           # Получить настройки
PUT    /api/settings           # Обновить настройки (admin)

# Заказы
GET    /api/orders             # Список заказов (admin)
POST   /api/orders             # Создать заказ
PUT    /api/orders/{id}        # Обновить заказ (admin)
DELETE /api/orders/{id}        # Удалить заказ (admin)

# Загрузка файлов
POST   /api/upload             # Загрузить изображение (admin)
GET    /api/upload?key=...     # Получить изображение

# Аутентификация
POST   /api/auth/login         # Вход
POST   /api/auth/logout        # Выход
GET    /api/auth/session       # Проверка сессии
```

## База данных

### Схема таблиц

#### products
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR(255))
- slug (VARCHAR(255), UNIQUE)
- description (TEXT)
- price (DECIMAL(10,2))
- category_id (INT)
- image (VARCHAR(500))
- images (TEXT) - JSON массив
- in_stock (TINYINT(1))
- featured (TINYINT(1))
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### categories
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR(255))
- slug (VARCHAR(255), UNIQUE)
- description (TEXT)
- created_at (DATETIME)
```

#### pages
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- slug (VARCHAR(255), UNIQUE)
- title (VARCHAR(255))
- content (TEXT)
- meta_description (TEXT)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### settings
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- data (TEXT) - JSON объект с настройками
- updated_at (DATETIME)
```

#### orders
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- customer_name (VARCHAR(255))
- customer_email (VARCHAR(255))
- customer_phone (VARCHAR(50))
- items (TEXT) - JSON массив товаров
- total (DECIMAL(10,2))
- status (VARCHAR(50))
- notes (TEXT)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### users
```sql
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- email (VARCHAR(255), UNIQUE)
- password_hash (VARCHAR(255))
- role (VARCHAR(50))
- created_at (DATETIME)
```

## Безопасность

### Реализованные меры

1. **Аутентификация**
   - Хеширование паролей через password_hash()
   - Защита сессий (httponly, samesite)
   - HTTPS поддержка

2. **Защита от атак**
   - Prepared statements для SQL (защита от SQL injection)
   - CSRF токены в формах
   - XSS защита через htmlspecialchars()
   - Валидация загружаемых файлов

3. **HTTP заголовки безопасности**
   - X-Content-Type-Options: nosniff
   - X-Frame-Options: SAMEORIGIN
   - Referrer-Policy: strict-origin-when-cross-origin
   - Permissions-Policy

4. **Загрузка файлов**
   - Только WebP формат
   - Максимальный размер: 8 МБ
   - Проверка MIME типа
   - Безопасные имена файлов

## Деплой

### Подготовка к деплою

1. Установить APP_DEBUG=0 в .env
2. Настроить параметры БД для продакшена
3. Создать бэкап: `php scripts/backup_db.php`

### Деплой на shared hosting (WMRS.ru)

```bash
# Загрузка файлов через FTP
php scripts/ftp_upload.php

# Проверка удаленного окружения
php scripts/check_remote_env.php

# Синхронизация БД
php scripts/remote_seed.php
```

### Настройка веб-сервера

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Администрирование

### Создание администратора

```bash
php scripts/create_admin.php
```

Скрипт запросит email и пароль для нового администратора.

### Доступ к админке

URL: http://your-domain.com/admin

Функционал:
- Управление товарами
- Управление категориями
- Управление страницами
- Просмотр и обработка заказов
- Настройки сайта
- Загрузка изображений

### Бэкапы

Автоматические бэкапы сохраняются в `/storage/backups/`:
- Формат: `backup_YYYYMMDD_HHMMSS.sql`
- Создание: `php scripts/backup_db.php`

## Troubleshooting

### Проблема: Ошибки подключения к БД

**Решение:**
1. Проверить параметры в .env
2. Убедиться, что БД создана
3. Проверить права пользователя БД
4. Приложение автоматически переключится на JSON fallback

### Проблема: 500 Internal Server Error

**Решение:**
1. Проверить логи: `storage/php_errors.log`
2. Включить APP_DEBUG=1 для детальных ошибок
3. Проверить права на директории storage/

### Проблема: Изображения не загружаются

**Решение:**
1. Проверить права на `storage/uploads/` (должно быть 755)
2. Убедиться, что загружается WebP формат
3. Проверить размер файла (макс. 8 МБ)

### Проблема: Сессии не работают

**Решение:**
1. Проверить права на директорию сессий PHP
2. Убедиться, что session_start() вызывается
3. Проверить настройки cookies (secure, httponly)

### Проблема: Не работает кнопка "Показать все отзывы" или аккордеон FAQ

**Решение:**
1. Убедиться, что `public/js/app.js` загружается корректно
2. Проверить консоль браузера на наличие JavaScript ошибок
3. Функции `initReviews()` и `initFAQ()` должны вызываться в `onReady()`
4. Проверить, что элементы имеют правильные data-атрибуты:
   - Кнопки FAQ: `data-faq-button`
   - Секция отзывов: `aria-label="Отзывы наших клиентов"`

## Производительность

### Оптимизации

1. **SSR для SEO**
   - Pre-rendered HTML в `/storage/ssr/`
   - Автоматическая отдача ботам
   - Уменьшение нагрузки на сервер

2. **Кэширование**
   - Статические файлы с долгим cache
   - ETags для изображений
   - Browser caching через headers

3. **База данных**
   - Индексы на часто используемых полях
   - Prepared statements
   - Connection pooling

4. **Изображения**
   - WebP формат (меньший размер)
   - Lazy loading на фронтенде
   - Оптимизация размеров

## Расширение функционала

### Добавление новой сущности

1. Добавить методы в DataStore.php
2. Создать миграцию в DatabaseMigrator.php
3. Добавить API endpoints в ApiController.php
4. Создать шаблоны в views/
5. Обновить маршрутизацию в PageController.php

### Добавление нового API endpoint

```php
// В ApiController.php
if ($path === '/api/custom-endpoint') {
    if ($method === 'GET') {
        // Логика обработки
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
        return;
    }
}
```

## Тестирование

### Проверка здоровья системы

```bash
php scripts/health_check.php
```

Проверяет:
- Подключение к БД
- Права на директории
- Наличие необходимых расширений PHP
- Конфигурацию окружения

## Лицензия и контакты

Проект разработан для интернет-магазина Logush.

---

**Последнее обновление:** 2026-02-13
**Версия документации:** 1.2

## История изменений

### v1.2 (2026-02-13)
- Исправлена работа кнопки "Показать все отзывы"
- Добавлен уникальный ID для кнопки отзывов (`show-all-reviews-btn`)
- Упрощена логика функции `initReviews()` для надежной работы
- Кнопка теперь корректно скрывает/показывает отзывы после третьего

### v1.1 (2026-02-13)
- Исправлена проблема с дублированием вызова `onReady()` в app.js
- Добавлена инициализация `initHomeSliders()` в основной блок инициализации
- Исправлена работа кнопки "Показать все отзывы" и аккордеона FAQ
- Добавлен раздел Troubleshooting для JavaScript проблем

### v1.0 (2026-02-13)
- Первая версия документации
- Описание архитектуры и компонентов
- Инструкции по установке и настройке
- API документация
