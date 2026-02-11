# Sanatorium Management System

Профессиональная система управления санаторием с файловым хранилищем (JSON).

## 🎯 Возможности

### Административная панель
- 📊 Дашборд с графиками (Chart.js)
- 🏨 Управление номерным фондом (классы и экземпляры)
- 📅 Визуальный календарь (FullCalendar) с drag & drop
- 📝 Бронирования и заселения (CRUD)
- 👥 Карточки гостей с документами
- 💰 Финансовые отчеты и налоги
- 🎁 Промокоды, лояльность, сертификаты
- 📱 Telegram-интеграция
- 📄 Генерация PDF-договоров
- 🌙 Тёмная тема
- 🎓 Onboarding для новых пользователей

### Публичная часть (WordPress)
- Форма бронирования через шорткод
- Публичный календарь занятости
- Лист ожидания

## 📋 Требования

- PHP 8.0+
- Composer (для зависимостей)
- WordPress 5.0+ (для шорткодов)

## 🚀 Установка

### 1. Загрузка файлов

```bash
# Скопируйте папку sanatorium-system на ваш хостинг
# Например: /var/www/html/sanatorium-system/
```

### 2. Установка зависимостей

```bash
cd sanatorium-system
composer install
```

### 3. Настройка прав доступа

```bash
chmod 755 admin/
chmod 777 data/
chmod 777 data/logs/
chmod 777 uploads/
```

### 4. Конфигурация

Отредактируйте `config/config.php`:

```php
// HTTP-авторизация для админки
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'your_secure_password');

// Telegram Bot
define('TELEGRAM_BOT_TOKEN', 'your_bot_token');
define('TELEGRAM_ADMIN_CHAT_ID', 'your_chat_id');
```

### 5. WordPress интеграция

Скопируйте файл `wordpress/sanatorium-plugin.php` в:
```
/wp-content/plugins/sanatorium-booking/sanatorium-plugin.php
```

Активируйте плагин в админке WordPress.

## 📁 Структура проекта

```
sanatorium-system/
├── admin/                      # Административная панель
│   ├── index.php              # Главная страница (дашборд)
│   ├── auth.php               # Авторизация
│   ├── rooms.php              # Управление номерами
│   ├── bookings.php           # Бронирования
│   ├── guests.php             # Гости
│   ├── calendar.php           # Календарь
│   ├── services.php           # Услуги
│   ├── finances.php           # Финансы
│   ├── marketing.php          # Маркетинг
│   ├── settings.php           # Настройки
│   └── assets/                # CSS, JS, изображения
├── api/                       # API endpoints
│   ├── bookings.php
│   ├── rooms.php
│   ├── guests.php
│   └── ...
├── classes/                   # PHP классы
│   ├── Database.php           # JSON handler
│   ├── Room.php
│   ├── Booking.php
│   ├── Guest.php
│   ├── Service.php
│   ├── Finance.php
│   ├── Telegram.php
│   └── PDF.php
├── config/
│   └── config.php             # Конфигурация
├── data/                      # JSON хранилище
│   ├── rooms.json
│   ├── room_instances.json
│   ├── bookings.json
│   ├── guests.json
│   ├── services.json
│   ├── prices.json
│   ├── promocodes.json
│   ├── waitlist.json
│   ├── taxes.json
│   ├── contracts.json
│   ├── loyalty.json
│   ├── certificates.json
│   ├── telegram.json
│   ├── settings.json
│   └── logs/
├── templates/                 # PDF шаблоны
│   ├── contract_booking.html
│   ├── contract_checkin.html
│   └── act.html
├── uploads/                   # Загруженные файлы
│   ├── documents/
│   └── contracts/
├── wordpress/                 # WordPress плагин
│   └── sanatorium-plugin.php
├── telegram/                  # Telegram бот
│   └── bot.php
├── composer.json
└── README.md
```

## 🎨 Использование

### Админ-панель

Откройте в браузере:
```
https://your-domain.com/sanatorium-system/admin/
```

Логин: `admin` (или ваш из config.php)

### WordPress шорткоды

**Форма бронирования:**
```
[sanatorium_booking]
```

**Календарь занятости:**
```
[sanatorium_calendar]
```

### Telegram бот

Настройте webhook:
```bash
curl -F "url=https://your-domain.com/sanatorium-system/telegram/bot.php" \
     https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook
```

Команды бота:
- `/start` - Приветствие
- `/today` - Брони на сегодня
- `/confirm 123` - Подтвердить бронь
- `/stats` - Статистика

## 📊 Демо-данные

При первом запуске система создаст демо-данные:
- 3 класса номеров (Стандарт, Люкс, Президентский)
- 10 экземпляров номеров
- 5 тестовых броней
- Базовые услуги и налоги

## 🔒 Безопасность

- HTTP Basic Authentication для админки
- Защита от SQL-инъекций (не используется БД)
- Валидация всех входных данных
- Защищенная папка для документов
- Логирование всех действий

## 📈 Отчеты

Доступные отчеты:
- Загрузка номеров (%)
- Выручка по периодам
- ADR (Average Daily Rate)
- RevPAR (Revenue Per Available Room)
- Топ популярных номеров
- Экспорт в Excel/PDF/CSV

## 🛠️ Техническая поддержка

При возникновении проблем проверьте:
1. Права доступа к папкам `data/` и `uploads/`
2. Версию PHP (должна быть 8.0+)
3. Логи в `data/logs/`

## 📝 Лицензия

Proprietary - Все права защищены

## 🔄 Обновления

Версия: 1.0.0
Дата: 2026-02-11
