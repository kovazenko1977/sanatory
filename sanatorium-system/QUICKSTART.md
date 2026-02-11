# 🚀 Quick Start Guide

## Что уже готово

### ✅ Базовая инфраструктура
- Структура проекта
- Конфигурация (config/config.php)
- Composer зависимости
- HTTP-авторизация

### ✅ Классы (PHP)
- **Database.php** - JSON хранилище с атомарными операциями
- **Room.php** - Управление номерами (классы и экземпляры)
- **Booking.php** - Полная система бронирований
- **Guest.php** - Управление гостями с документами

### ✅ Админ-панель
- Дашборд с графиками (Chart.js)
- Навигация и меню
- Адаптивный дизайн
- Тёмная тема (переключатель)
- Профессиональные стили

## 📦 Установка за 5 минут

### 1. Скопируйте файлы

```bash
# Загрузите папку sanatorium-system на ваш сервер
# Например: /var/www/html/sanatorium-system/
```

### 2. Установите зависимости

```bash
cd sanatorium-system
composer install
```

### 3. Настройте права

```bash
chmod 755 admin/
chmod 777 data/
chmod 777 uploads/
mkdir -p data/logs uploads/documents uploads/contracts
chmod 777 data/logs uploads/documents uploads/contracts
```

### 4. Настройте конфигурацию

Отредактируйте `config/config.php`:

```php
// Измените пароль администратора
define('ADMIN_PASSWORD', 'ваш_надежный_пароль');

// Добавьте токен Telegram бота (опционально)
define('TELEGRAM_BOT_TOKEN', 'ваш_токен');
```

### 5. Откройте админ-панель

```
https://ваш-домен.com/sanatorium-system/admin/
```

**Логин:** admin  
**Пароль:** тот, что указали в config.php

## 🎯 Что работает прямо сейчас

### Дашборд
- ✅ Виджеты статистики (выручка, загрузка, брони)
- ✅ График выручки по месяцам
- ✅ Круговая диаграмма статуса номеров
- ✅ Таблица последних бронирований
- ✅ Переключатель темы (светлая/тёмная)

### API (через классы)
Все классы готовы к использованию:

```php
// Пример создания номера
$roomManager = new \Sanatorium\Room();
$roomClass = $roomManager->createRoomClass([
    'name' => 'Люкс',
    'level' => 'suite',
    'base_price' => 5000,
    'max_guests' => 2
]);

// Пример создания бронирования
$bookingManager = new \Sanatorium\Booking();
$booking = $bookingManager->create([
    'guest_id' => 1,
    'room_instance_id' => 1,
    'check_in' => '2026-03-01',
    'check_out' => '2026-03-05'
]);
```

## 📋 Что нужно доделать

### Приоритет 1 (MVP) - 1-2 дня
- [ ] API endpoints (api/rooms.php, api/bookings.php, api/guests.php)
- [ ] Страница управления номерами (admin/rooms.php)
- [ ] Страница управления бронированиями (admin/bookings.php)
- [ ] Страница управления гостями (admin/guests.php)
- [ ] Базовый календарь (admin/calendar.php)

### Приоритет 2 (PRO) - 2-3 дня
- [ ] Service.php класс
- [ ] Finance.php класс
- [ ] Страница услуг (admin/services.php)
- [ ] Страница финансов (admin/finances.php)
- [ ] Marketing.php класс (промокоды, лояльность)
- [ ] Страница маркетинга (admin/marketing.php)

### Приоритет 3 (PREMIUM) - 3-5 дней
- [ ] Telegram.php интеграция
- [ ] PDF.php генерация договоров
- [ ] FullCalendar с drag & drop
- [ ] WordPress плагин и шорткоды
- [ ] Onboarding система
- [ ] Split-бронирования
- [ ] Waitlist функционал

## 🛠️ Как продолжить разработку

### Создание API endpoint

Пример `api/rooms.php`:

```php
<?php
require_once __DIR__ . '/../config/config.php';
checkAuth();

use Sanatorium\Room;

header('Content-Type: application/json');

$roomManager = new Room();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $rooms = $roomManager->getAllRoomClasses();
        echo json_encode($rooms);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $room = $roomManager->createRoomClass($data);
        echo json_encode($room);
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        unset($data['id']);
        $result = $roomManager->updateRoomClass($id, $data);
        echo json_encode(['success' => $result]);
        break;
        
    case 'DELETE':
        $id = $_GET['id'];
        $result = $roomManager->deleteRoomClass($id);
        echo json_encode(['success' => $result]);
        break;
}
```

### Создание страницы админки

Пример `admin/rooms.php`:

```php
<?php
require_once __DIR__ . '/../config/config.php';
checkAuth();

use Sanatorium\Room;

$roomManager = new Room();
$rooms = $roomManager->getAllRoomClasses();
$instances = $roomManager->getAllRoomInstances();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление номерами</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <h1>Управление номерами</h1>
                
                <!-- Ваш контент здесь -->
                
            </main>
        </div>
    </div>
</body>
</html>
```

## 📚 Документация классов

### Database
- `read($filename)` - Чтение JSON файла
- `write($filename, $data)` - Запись JSON файла
- `insert($filename, $record)` - Добавить запись
- `update($filename, $id, $updates)` - Обновить запись
- `delete($filename, $id)` - Удалить запись
- `findById($filename, $id)` - Найти по ID
- `findWhere($filename, $condition)` - Найти по условию

### Room
- `createRoomClass($data)` - Создать класс номера
- `createRoomInstance($data)` - Создать экземпляр номера
- `getAllRoomClasses()` - Получить все классы
- `getAllRoomInstances()` - Получить все экземпляры
- `getAvailableRooms($checkIn, $checkOut)` - Доступные номера
- `updateRoomClass($id, $data)` - Обновить класс
- `updateRoomInstance($id, $data)` - Обновить экземпляр
- `blockRoom($id, $reason, $from, $to)` - Заблокировать
- `unblockRoom($id)` - Разблокировать
- `getRoomStatistics()` - Статистика

### Booking
- `create($data)` - Создать бронирование
- `update($id, $data)` - Обновить бронирование
- `changeStatus($id, $status)` - Изменить статус
- `addPayment($id, $payment)` - Добавить оплату
- `checkIn($id)` - Заселить
- `checkOut($id)` - Выселить
- `cancel($id, $reason)` - Отменить
- `getAll($filters)` - Получить все с фильтрами
- `getToday()` - Брони на сегодня

### Guest
- `create($data)` - Создать гостя
- `update($id, $data)` - Обновить гостя
- `delete($id)` - Удалить гостя
- `getAll($filters)` - Получить всех с фильтрами
- `getById($id)` - Получить по ID
- `uploadDocument($guestId, $file, $type)` - Загрузить документ
- `deleteDocument($guestId, $filename)` - Удалить документ
- `addToBlacklist($id, $reason)` - В черный список
- `removeFromBlacklist($id)` - Из черного списка
- `updateStatistics($id)` - Обновить статистику
- `getBookingHistory($id)` - История бронирований
- `findByContact($contact)` - Найти по контакту

## 🔧 Troubleshooting

### Ошибка: Cannot write to data/
```bash
chmod 777 data/
chmod 777 data/logs/
```

### Ошибка: Class not found
```bash
composer dump-autoload
```

### Ошибка: 401 Unauthorized
Проверьте логин/пароль в `config/config.php`

### Графики не отображаются
Проверьте подключение к интернету (Chart.js загружается с CDN)

## 💡 Советы

1. **Начните с демо-данных**: Создайте несколько тестовых номеров и броней через PHP скрипт
2. **Используйте логи**: Все действия логируются в `data/logs/`
3. **Тестируйте на локальном сервере**: XAMPP, MAMP, или встроенный PHP сервер
4. **Делайте бэкапы**: Регулярно копируйте папку `data/`

## 🎓 Следующие шаги

1. Создайте демо-данные (номера, гости, брони)
2. Реализуйте API endpoints
3. Создайте страницы управления
4. Добавьте FullCalendar
5. Интегрируйте с WordPress
6. Настройте Telegram бота

## 📞 Поддержка

При возникновении проблем:
1. Проверьте логи в `data/logs/`
2. Убедитесь, что все права доступа настроены
3. Проверьте версию PHP (должна быть 8.0+)

---

**Версия:** 1.0.0 (MVP Foundation)  
**Дата:** 2026-02-11  
**Статус:** Готов к разработке 🚀
