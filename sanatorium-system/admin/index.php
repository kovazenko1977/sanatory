<?php
require_once __DIR__ . '/../config/config.php';
checkAuth();

use Sanatorium\Database;
use Sanatorium\Room;
use Sanatorium\Booking;
use Sanatorium\Guest;

$db = new Database();
$roomManager = new Room();
$bookingManager = new Booking();
$guestManager = new Guest();

// Получить статистику
$roomStats = $roomManager->getRoomStatistics();
$todayBookings = $bookingManager->getToday();
$allBookings = $bookingManager->getAll();

// Статистика по выручке
$totalRevenue = 0;
$todayRevenue = 0;
$today = date('Y-m-d');

foreach ($allBookings as $booking) {
    if ($booking['status'] !== 'cancelled') {
        $totalRevenue += $booking['paid_amount'];
        if ($booking['created_at'] && date('Y-m-d', strtotime($booking['created_at'])) === $today) {
            $todayRevenue += $booking['paid_amount'];
        }
    }
}

// Данные для графиков
$monthlyRevenue = [];
$monthlyBookings = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthlyRevenue[$month] = 0;
    $monthlyBookings[$month] = 0;
}

foreach ($allBookings as $booking) {
    if ($booking['status'] !== 'cancelled' && isset($booking['created_at'])) {
        $month = date('Y-m', strtotime($booking['created_at']));
        if (isset($monthlyRevenue[$month])) {
            $monthlyRevenue[$month] += $booking['paid_amount'];
            $monthlyBookings[$month]++;
        }
    }
}

$settings = $db->read('settings.json');
$theme = $settings['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="ru" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления - Санаторий</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-speedometer2"></i> Панель управления</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="themeToggle">
                            <i class="bi bi-moon-fill"></i> Тема
                        </button>
                    </div>
                </div>

                <!-- Виджеты статистики -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Выручка сегодня
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($todayRevenue, 0, ',', ' ') ?> <?= CURRENCY_SYMBOL ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-currency-exchange fs-2 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Загрузка номеров
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= $roomStats['occupancy_rate'] ?>%
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-percent fs-2 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Брони сегодня
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= count($todayBookings) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-calendar-check fs-2 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Свободных номеров
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= $roomStats['available_today'] ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-door-open fs-2 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Графики -->
                <div class="row mb-4">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="bi bi-graph-up"></i> Выручка по месяцам
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="bi bi-pie-chart"></i> Статус номеров
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="roomStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Последние бронирования -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="bi bi-clock-history"></i> Последние бронирования
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Гость</th>
                                        <th>Номер</th>
                                        <th>Заезд</th>
                                        <th>Выезд</th>
                                        <th>Сумма</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $recentBookings = array_slice(array_reverse($allBookings), 0, 10);
                                    foreach ($recentBookings as $booking): 
                                        $statusClass = [
                                            'new' => 'secondary',
                                            'confirmed' => 'info',
                                            'paid' => 'success',
                                            'checked_in' => 'primary',
                                            'checked_out' => 'dark',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusText = [
                                            'new' => 'Новая',
                                            'confirmed' => 'Подтверждена',
                                            'paid' => 'Оплачена',
                                            'checked_in' => 'Заселен',
                                            'checked_out' => 'Выехал',
                                            'cancelled' => 'Отменена'
                                        ];
                                    ?>
                                    <tr>
                                        <td>#<?= $booking['id'] ?></td>
                                        <td><?= htmlspecialchars($booking['guest']['first_name'] ?? '') ?> <?= htmlspecialchars($booking['guest']['last_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($booking['room_instance']['room_number'] ?? '') ?></td>
                                        <td><?= date('d.m.Y', strtotime($booking['check_in'])) ?></td>
                                        <td><?= date('d.m.Y', strtotime($booking['check_out'])) ?></td>
                                        <td><?= number_format($booking['total_price'], 0, ',', ' ') ?> <?= CURRENCY_SYMBOL ?></td>
                                        <td>
                                            <span class="badge bg-<?= $statusClass[$booking['status']] ?>">
                                                <?= $statusText[$booking['status']] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="bookings.php?id=<?= $booking['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // График выручки
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_keys($monthlyRevenue)) ?>,
                datasets: [{
                    label: 'Выручка (₽)',
                    data: <?= json_encode(array_values($monthlyRevenue)) ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });

        // График статуса номеров
        const roomStatusCtx = document.getElementById('roomStatusChart').getContext('2d');
        new Chart(roomStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Занято', 'Свободно', 'Ремонт', 'Блокировка'],
                datasets: [{
                    data: [
                        <?= $roomStats['occupied_today'] ?>,
                        <?= $roomStats['available_today'] ?>,
                        <?= $roomStats['maintenance_rooms'] ?>,
                        <?= $roomStats['blocked_rooms'] ?>
                    ],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(201, 203, 207)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Переключение темы
        document.getElementById('themeToggle').addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Обновить иконку
            const icon = this.querySelector('i');
            icon.className = newTheme === 'light' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
        });

        // Загрузить сохраненную тему
        const savedTheme = localStorage.getItem('theme') || '<?= $theme ?>';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</body>
</html>
