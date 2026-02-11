<?php
/**
 * Demo Data Generator
 * Создает тестовые данные для системы
 */

require_once __DIR__ . '/config/config.php';

use Sanatorium\Database;
use Sanatorium\Room;
use Sanatorium\Booking;
use Sanatorium\Guest;

echo "🚀 Установка демо-данных...\n\n";

$db = new Database();
$roomManager = new Room();
$bookingManager = new Booking();
$guestManager = new Guest();

// 1. Создать классы номеров
echo "📦 Создание классов номеров...\n";

$roomClasses = [
    [
        'name' => 'Стандарт',
        'level' => 'standard',
        'rooms_count' => 1,
        'max_guests' => 2,
        'base_price' => 3000,
        'description' => 'Уютный номер с базовыми удобствами',
        'amenities' => ['Wi-Fi', 'Телевизор', 'Кондиционер', 'Холодильник'],
        'area' => 18
    ],
    [
        'name' => 'Комфорт',
        'level' => 'deluxe',
        'rooms_count' => 2,
        'max_guests' => 2,
        'base_price' => 4500,
        'description' => 'Просторный номер повышенной комфортности',
        'amenities' => ['Wi-Fi', 'Телевизор', 'Кондиционер', 'Холодильник', 'Балкон', 'Сейф'],
        'area' => 25
    ],
    [
        'name' => 'Люкс',
        'level' => 'suite',
        'rooms_count' => 2,
        'max_guests' => 3,
        'base_price' => 6500,
        'description' => 'Роскошный двухкомнатный номер',
        'amenities' => ['Wi-Fi', 'Телевизор', 'Кондиционер', 'Холодильник', 'Балкон', 'Сейф', 'Мини-бар', 'Джакузи'],
        'area' => 40
    ],
    [
        'name' => 'Президентский',
        'level' => 'presidential',
        'rooms_count' => 3,
        'max_guests' => 4,
        'base_price' => 12000,
        'description' => 'Эксклюзивный трехкомнатный номер с панорамным видом',
        'amenities' => ['Wi-Fi', 'Телевизор', 'Кондиционер', 'Холодильник', 'Балкон', 'Сейф', 'Мини-бар', 'Джакузи', 'Кухня', 'Гостиная'],
        'area' => 80
    ]
];

$createdRoomClasses = [];
foreach ($roomClasses as $roomClass) {
    $created = $roomManager->createRoomClass($roomClass);
    $createdRoomClasses[] = $created;
    echo "  ✓ {$created['name']} (ID: {$created['id']})\n";
}

// 2. Создать экземпляры номеров
echo "\n🏨 Создание экземпляров номеров...\n";

$roomInstances = [];
$roomNumber = 101;

foreach ($createdRoomClasses as $roomClass) {
    $count = $roomClass['level'] === 'presidential' ? 2 : 5;
    
    for ($i = 0; $i < $count; $i++) {
        $instance = $roomManager->createRoomInstance([
            'room_class_id' => $roomClass['id'],
            'room_number' => (string)$roomNumber,
            'floor' => (int)($roomNumber / 100),
            'status' => 'active'
        ]);
        $roomInstances[] = $instance;
        echo "  ✓ Номер {$instance['room_number']} ({$roomClass['name']})\n";
        $roomNumber++;
    }
}

// 3. Создать гостей
echo "\n👥 Создание гостей...\n";

$guests = [
    [
        'first_name' => 'Иван',
        'last_name' => 'Петров',
        'middle_name' => 'Сергеевич',
        'phone' => '+79161234567',
        'email' => 'ivan.petrov@example.com',
        'passport_series' => '4512',
        'passport_number' => '123456',
        'birth_date' => '1985-05-15'
    ],
    [
        'first_name' => 'Мария',
        'last_name' => 'Иванова',
        'middle_name' => 'Александровна',
        'phone' => '+79167654321',
        'email' => 'maria.ivanova@example.com',
        'passport_series' => '4513',
        'passport_number' => '654321',
        'birth_date' => '1990-08-22'
    ],
    [
        'first_name' => 'Алексей',
        'last_name' => 'Смирнов',
        'middle_name' => 'Дмитриевич',
        'phone' => '+79169876543',
        'email' => 'alexey.smirnov@example.com',
        'passport_series' => '4514',
        'passport_number' => '987654',
        'birth_date' => '1978-12-10'
    ],
    [
        'first_name' => 'Елена',
        'last_name' => 'Козлова',
        'middle_name' => 'Викторовна',
        'phone' => '+79165432109',
        'email' => 'elena.kozlova@example.com',
        'passport_series' => '4515',
        'passport_number' => '543210',
        'birth_date' => '1995-03-18'
    ],
    [
        'first_name' => 'Дмитрий',
        'last_name' => 'Новиков',
        'middle_name' => 'Андреевич',
        'phone' => '+79162109876',
        'email' => 'dmitry.novikov@example.com',
        'passport_series' => '4516',
        'passport_number' => '210987',
        'birth_date' => '1982-07-25'
    ]
];

$createdGuests = [];
foreach ($guests as $guest) {
    $created = $guestManager->create($guest);
    $createdGuests[] = $created;
    echo "  ✓ {$created['first_name']} {$created['last_name']} (ID: {$created['id']})\n";
}

// 4. Создать бронирования
echo "\n📅 Создание бронирований...\n";

$bookings = [
    [
        'guest_id' => $createdGuests[0]['id'],
        'room_instance_id' => $roomInstances[0]['id'],
        'check_in' => date('Y-m-d', strtotime('+2 days')),
        'check_out' => date('Y-m-d', strtotime('+7 days')),
        'guests_count' => 2,
        'services' => [],
        'notes' => 'Раннее заселение'
    ],
    [
        'guest_id' => $createdGuests[1]['id'],
        'room_instance_id' => $roomInstances[5]['id'],
        'check_in' => date('Y-m-d', strtotime('+5 days')),
        'check_out' => date('Y-m-d', strtotime('+10 days')),
        'guests_count' => 2,
        'services' => [],
        'notes' => ''
    ],
    [
        'guest_id' => $createdGuests[2]['id'],
        'room_instance_id' => $roomInstances[10]['id'],
        'check_in' => date('Y-m-d', strtotime('+1 day')),
        'check_out' => date('Y-m-d', strtotime('+4 days')),
        'guests_count' => 2,
        'services' => [],
        'notes' => 'VIP гость'
    ],
    [
        'guest_id' => $createdGuests[3]['id'],
        'room_instance_id' => $roomInstances[2]['id'],
        'check_in' => date('Y-m-d'),
        'check_out' => date('Y-m-d', strtotime('+3 days')),
        'guests_count' => 1,
        'services' => [],
        'notes' => ''
    ],
    [
        'guest_id' => $createdGuests[4]['id'],
        'room_instance_id' => $roomInstances[15]['id'],
        'check_in' => date('Y-m-d', strtotime('+10 days')),
        'check_out' => date('Y-m-d', strtotime('+17 days')),
        'guests_count' => 3,
        'services' => [],
        'notes' => 'Семейный отдых'
    ]
];

foreach ($bookings as $booking) {
    $created = $bookingManager->create($booking);
    
    // Подтвердить и частично оплатить некоторые брони
    if (rand(0, 1)) {
        $bookingManager->changeStatus($created['id'], 'confirmed');
        
        if (rand(0, 1)) {
            $bookingManager->addPayment($created['id'], [
                'amount' => $created['total_price'] * 0.5,
                'method' => 'card',
                'notes' => 'Предоплата 50%'
            ]);
        }
    }
    
    echo "  ✓ Бронь #{$created['id']} - {$createdGuests[array_search($booking['guest_id'], array_column($createdGuests, 'id'))]['first_name']} {$createdGuests[array_search($booking['guest_id'], array_column($createdGuests, 'id'))]['last_name']}\n";
}

// 5. Создать услуги
echo "\n🛎️ Создание услуг...\n";

$services = [
    [
        'name' => 'Завтрак',
        'category' => 'meal',
        'price' => 500,
        'description' => 'Шведский стол',
        'active' => true
    ],
    [
        'name' => 'Полупансион',
        'category' => 'meal',
        'price' => 1200,
        'description' => 'Завтрак + ужин',
        'active' => true
    ],
    [
        'name' => 'Полный пансион',
        'category' => 'meal',
        'price' => 1800,
        'description' => 'Завтрак + обед + ужин',
        'active' => true
    ],
    [
        'name' => 'Массаж',
        'category' => 'spa',
        'price' => 2000,
        'description' => 'Классический массаж 60 мин',
        'active' => true
    ],
    [
        'name' => 'Бассейн',
        'category' => 'spa',
        'price' => 800,
        'description' => 'Посещение бассейна',
        'active' => true
    ],
    [
        'name' => 'Трансфер',
        'category' => 'transport',
        'price' => 1500,
        'description' => 'Трансфер от/до аэропорта',
        'active' => true
    ]
];

foreach ($services as $service) {
    $db->insert('services.json', $service);
    echo "  ✓ {$service['name']} - {$service['price']} ₽\n";
}

// 6. Создать налоги
echo "\n💰 Создание налогов...\n";

$taxes = [
    [
        'name' => 'НДС',
        'rate' => 20,
        'description' => 'Налог на добавленную стоимость',
        'active' => false
    ],
    [
        'name' => 'Курортный сбор',
        'rate' => 2,
        'description' => 'Курортный сбор',
        'active' => true
    ]
];

foreach ($taxes as $tax) {
    $db->insert('taxes.json', $tax);
    echo "  ✓ {$tax['name']} - {$tax['rate']}%\n";
}

// 7. Создать промокоды
echo "\n🎁 Создание промокодов...\n";

$promocodes = [
    [
        'code' => 'WELCOME2026',
        'type' => 'percent',
        'value' => 10,
        'description' => 'Скидка 10% для новых гостей',
        'valid_from' => date('Y-m-d'),
        'valid_to' => date('Y-m-d', strtotime('+1 year')),
        'max_uses' => 100,
        'used_count' => 0,
        'active' => true
    ],
    [
        'code' => 'SUMMER500',
        'type' => 'fixed',
        'value' => 500,
        'description' => 'Скидка 500 рублей на летний отдых',
        'valid_from' => date('Y-m-d'),
        'valid_to' => date('Y-m-d', strtotime('+6 months')),
        'max_uses' => 50,
        'used_count' => 0,
        'active' => true
    ]
];

foreach ($promocodes as $promo) {
    $db->insert('promocodes.json', $promo);
    echo "  ✓ {$promo['code']} - {$promo['description']}\n";
}

// 8. Создать уровни лояльности
echo "\n⭐ Создание уровней лояльности...\n";

$loyaltyData = $db->read('loyalty.json');
$loyaltyData['levels'] = [
    [
        'name' => 'Базовый',
        'min_bookings' => 0,
        'min_spent' => 0,
        'discount' => 0,
        'benefits' => ['Стандартные условия']
    ],
    [
        'name' => 'Серебряный',
        'min_bookings' => 2,
        'min_spent' => 20000,
        'discount' => 5,
        'benefits' => ['Скидка 5%', 'Ранний заезд']
    ],
    [
        'name' => 'Золотой',
        'min_bookings' => 5,
        'min_spent' => 50000,
        'discount' => 10,
        'benefits' => ['Скидка 10%', 'Ранний заезд', 'Поздний выезд', 'Бесплатный апгрейд']
    ],
    [
        'name' => 'Платиновый',
        'min_bookings' => 10,
        'min_spent' => 100000,
        'discount' => 15,
        'benefits' => ['Скидка 15%', 'Ранний заезд', 'Поздний выезд', 'Бесплатный апгрейд', 'Персональный менеджер']
    ]
];

$db->write('loyalty.json', $loyaltyData);
echo "  ✓ Создано 4 уровня лояльности\n";

echo "\n✅ Демо-данные успешно установлены!\n\n";
echo "📊 Статистика:\n";
echo "  - Классов номеров: " . count($createdRoomClasses) . "\n";
echo "  - Экземпляров номеров: " . count($roomInstances) . "\n";
echo "  - Гостей: " . count($createdGuests) . "\n";
echo "  - Бронирований: " . count($bookings) . "\n";
echo "  - Услуг: " . count($services) . "\n";
echo "  - Налогов: " . count($taxes) . "\n";
echo "  - Промокодов: " . count($promocodes) . "\n";
echo "\n🚀 Теперь можете открыть админ-панель: /admin/\n";
