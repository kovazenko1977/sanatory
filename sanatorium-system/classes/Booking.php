<?php
namespace Sanatorium;

/**
 * Booking - Управление бронированиями
 */
class Booking {
    private Database $db;
    
    // Статусы бронирования
    const STATUS_NEW = 'new';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PAID = 'paid';
    const STATUS_CHECKED_IN = 'checked_in';
    const STATUS_CHECKED_OUT = 'checked_out';
    const STATUS_CANCELLED = 'cancelled';
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Создать бронирование
     */
    public function create(array $data): array {
        // Валидация
        $this->validateBooking($data);
        
        // Проверка доступности номера
        if (!$this->isRoomAvailable(
            $data['room_instance_id'],
            $data['check_in'],
            $data['check_out']
        )) {
            throw new \Exception('Room is not available for selected dates');
        }
        
        // Расчет цены
        $totalPrice = $this->calculatePrice(
            $data['room_instance_id'],
            $data['check_in'],
            $data['check_out'],
            $data['services'] ?? [],
            $data['promocode'] ?? null
        );
        
        $booking = [
            'guest_id' => (int)$data['guest_id'],
            'room_instance_id' => (int)$data['room_instance_id'],
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'guests_count' => (int)($data['guests_count'] ?? 1),
            'status' => self::STATUS_NEW,
            'total_price' => $totalPrice['total'],
            'base_price' => $totalPrice['base'],
            'services_price' => $totalPrice['services'],
            'taxes' => $totalPrice['taxes'],
            'discount' => $totalPrice['discount'],
            'services' => $data['services'] ?? [],
            'promocode' => $data['promocode'] ?? null,
            'notes' => $data['notes'] ?? '',
            'payments' => [],
            'paid_amount' => 0,
            'source' => $data['source'] ?? 'admin' // admin, website, phone
        ];
        
        $result = $this->db->insert('bookings.json', $booking);
        logAction('booking_created', ['id' => $result['id'], 'guest_id' => $result['guest_id']]);
        
        // Отправить уведомление
        $this->sendNotification($result);
        
        return $result;
    }
    
    /**
     * Обновить бронирование
     */
    public function update(int $id, array $data): bool {
        $booking = $this->db->findById('bookings.json', $id);
        if (!$booking) {
            throw new \Exception('Booking not found');
        }
        
        // Если меняются даты или номер, проверить доступность
        if (isset($data['check_in']) || isset($data['check_out']) || isset($data['room_instance_id'])) {
            $checkIn = $data['check_in'] ?? $booking['check_in'];
            $checkOut = $data['check_out'] ?? $booking['check_out'];
            $roomId = $data['room_instance_id'] ?? $booking['room_instance_id'];
            
            if (!$this->isRoomAvailable($roomId, $checkIn, $checkOut, $id)) {
                throw new \Exception('Room is not available for selected dates');
            }
            
            // Пересчитать цену
            $totalPrice = $this->calculatePrice(
                $roomId,
                $checkIn,
                $checkOut,
                $data['services'] ?? $booking['services'],
                $data['promocode'] ?? $booking['promocode']
            );
            
            $data['total_price'] = $totalPrice['total'];
            $data['base_price'] = $totalPrice['base'];
            $data['services_price'] = $totalPrice['services'];
            $data['taxes'] = $totalPrice['taxes'];
            $data['discount'] = $totalPrice['discount'];
        }
        
        $result = $this->db->update('bookings.json', $id, $data);
        if ($result) {
            logAction('booking_updated', ['id' => $id]);
        }
        return $result;
    }
    
    /**
     * Изменить статус бронирования
     */
    public function changeStatus(int $id, string $status): bool {
        $validStatuses = [
            self::STATUS_NEW,
            self::STATUS_CONFIRMED,
            self::STATUS_PAID,
            self::STATUS_CHECKED_IN,
            self::STATUS_CHECKED_OUT,
            self::STATUS_CANCELLED
        ];
        
        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Invalid status');
        }
        
        $result = $this->db->update('bookings.json', $id, ['status' => $status]);
        if ($result) {
            logAction('booking_status_changed', ['id' => $id, 'status' => $status]);
        }
        return $result;
    }
    
    /**
     * Добавить оплату
     */
    public function addPayment(int $id, array $payment): bool {
        $booking = $this->db->findById('bookings.json', $id);
        if (!$booking) {
            throw new \Exception('Booking not found');
        }
        
        $payment['date'] = date('Y-m-d H:i:s');
        $payment['amount'] = (float)$payment['amount'];
        $payment['method'] = $payment['method'] ?? 'cash'; // cash, card, transfer
        
        $booking['payments'][] = $payment;
        $booking['paid_amount'] = array_sum(array_column($booking['payments'], 'amount'));
        
        // Если оплачено полностью, изменить статус
        if ($booking['paid_amount'] >= $booking['total_price']) {
            $booking['status'] = self::STATUS_PAID;
        }
        
        $result = $this->db->update('bookings.json', $id, [
            'payments' => $booking['payments'],
            'paid_amount' => $booking['paid_amount'],
            'status' => $booking['status']
        ]);
        
        if ($result) {
            logAction('payment_added', ['booking_id' => $id, 'amount' => $payment['amount']]);
        }
        
        return $result;
    }
    
    /**
     * Заселить гостя
     */
    public function checkIn(int $id): bool {
        $booking = $this->db->findById('bookings.json', $id);
        if (!$booking) {
            throw new \Exception('Booking not found');
        }
        
        if ($booking['status'] !== self::STATUS_PAID && $booking['status'] !== self::STATUS_CONFIRMED) {
            throw new \Exception('Booking must be paid or confirmed before check-in');
        }
        
        $result = $this->db->update('bookings.json', $id, [
            'status' => self::STATUS_CHECKED_IN,
            'actual_check_in' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            logAction('guest_checked_in', ['booking_id' => $id]);
        }
        
        return $result;
    }
    
    /**
     * Выселить гостя
     */
    public function checkOut(int $id): bool {
        $booking = $this->db->findById('bookings.json', $id);
        if (!$booking) {
            throw new \Exception('Booking not found');
        }
        
        if ($booking['status'] !== self::STATUS_CHECKED_IN) {
            throw new \Exception('Guest must be checked in before check-out');
        }
        
        $result = $this->db->update('bookings.json', $id, [
            'status' => self::STATUS_CHECKED_OUT,
            'actual_check_out' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            logAction('guest_checked_out', ['booking_id' => $id]);
        }
        
        return $result;
    }
    
    /**
     * Отменить бронирование
     */
    public function cancel(int $id, string $reason = ''): bool {
        $result = $this->db->update('bookings.json', $id, [
            'status' => self::STATUS_CANCELLED,
            'cancellation_reason' => $reason,
            'cancelled_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            logAction('booking_cancelled', ['id' => $id, 'reason' => $reason]);
        }
        
        return $result;
    }
    
    /**
     * Получить все бронирования
     */
    public function getAll(array $filters = []): array {
        $bookings = $this->db->read('bookings.json');
        
        // Применить фильтры
        if (!empty($filters)) {
            $bookings = array_filter($bookings, function($booking) use ($filters) {
                if (isset($filters['status']) && $booking['status'] !== $filters['status']) {
                    return false;
                }
                if (isset($filters['guest_id']) && $booking['guest_id'] !== $filters['guest_id']) {
                    return false;
                }
                if (isset($filters['room_instance_id']) && $booking['room_instance_id'] !== $filters['room_instance_id']) {
                    return false;
                }
                if (isset($filters['date_from']) && $booking['check_in'] < $filters['date_from']) {
                    return false;
                }
                if (isset($filters['date_to']) && $booking['check_out'] > $filters['date_to']) {
                    return false;
                }
                return true;
            });
        }
        
        // Добавить информацию о госте и номере
        $guests = $this->db->read('guests.json');
        $roomInstances = $this->db->read('room_instances.json');
        $roomClasses = $this->db->read('rooms.json');
        
        foreach ($bookings as &$booking) {
            // Добавить гостя
            foreach ($guests as $guest) {
                if ($guest['id'] === $booking['guest_id']) {
                    $booking['guest'] = $guest;
                    break;
                }
            }
            
            // Добавить номер
            foreach ($roomInstances as $instance) {
                if ($instance['id'] === $booking['room_instance_id']) {
                    $booking['room_instance'] = $instance;
                    
                    // Добавить класс номера
                    foreach ($roomClasses as $class) {
                        if ($class['id'] === $instance['room_class_id']) {
                            $booking['room_class'] = $class;
                            break;
                        }
                    }
                    break;
                }
            }
        }
        
        return array_values($bookings);
    }
    
    /**
     * Получить бронирования на сегодня
     */
    public function getToday(): array {
        $today = date('Y-m-d');
        return $this->db->findWhere('bookings.json', function($booking) use ($today) {
            return $booking['check_in'] === $today || 
                   $booking['check_out'] === $today ||
                   ($booking['check_in'] < $today && $booking['check_out'] > $today && $booking['status'] === self::STATUS_CHECKED_IN);
        });
    }
    
    /**
     * Проверить доступность номера
     */
    private function isRoomAvailable(int $roomInstanceId, string $checkIn, string $checkOut, ?int $excludeBookingId = null): bool {
        $bookings = $this->db->read('bookings.json');
        
        foreach ($bookings as $booking) {
            // Пропустить текущее бронирование при обновлении
            if ($excludeBookingId && $booking['id'] === $excludeBookingId) {
                continue;
            }
            
            // Пропустить отмененные
            if ($booking['status'] === self::STATUS_CANCELLED) {
                continue;
            }
            
            // Проверить пересечение
            if ($booking['room_instance_id'] === $roomInstanceId) {
                if ($this->datesOverlap($checkIn, $checkOut, $booking['check_in'], $booking['check_out'])) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Расчет цены бронирования
     */
    private function calculatePrice(int $roomInstanceId, string $checkIn, string $checkOut, array $services = [], ?string $promocode = null): array {
        // Получить базовую цену номера
        $roomInstance = $this->db->findById('room_instances.json', $roomInstanceId);
        $roomClass = $this->db->findById('rooms.json', $roomInstance['room_class_id']);
        
        // Количество ночей
        $nights = (strtotime($checkOut) - strtotime($checkIn)) / 86400;
        
        // Базовая цена
        $basePrice = $roomClass['base_price'] * $nights;
        
        // Цена услуг
        $servicesPrice = 0;
        if (!empty($services)) {
            $allServices = $this->db->read('services.json');
            foreach ($services as $serviceId) {
                foreach ($allServices as $service) {
                    if ($service['id'] === $serviceId) {
                        $servicesPrice += $service['price'] * $nights;
                        break;
                    }
                }
            }
        }
        
        // Промокод
        $discount = 0;
        if ($promocode) {
            $promocodes = $this->db->read('promocodes.json');
            foreach ($promocodes as $promo) {
                if ($promo['code'] === $promocode && $promo['active']) {
                    if ($promo['type'] === 'percent') {
                        $discount = ($basePrice + $servicesPrice) * ($promo['value'] / 100);
                    } else {
                        $discount = $promo['value'];
                    }
                    break;
                }
            }
        }
        
        // Налоги
        $taxes = $this->calculateTaxes($basePrice + $servicesPrice - $discount);
        
        return [
            'base' => $basePrice,
            'services' => $servicesPrice,
            'discount' => $discount,
            'taxes' => $taxes,
            'total' => $basePrice + $servicesPrice - $discount + $taxes
        ];
    }
    
    /**
     * Расчет налогов
     */
    private function calculateTaxes(float $amount): float {
        $taxes = $this->db->read('taxes.json');
        $totalTax = 0;
        
        foreach ($taxes as $tax) {
            if ($tax['active']) {
                $totalTax += $amount * ($tax['rate'] / 100);
            }
        }
        
        return $totalTax;
    }
    
    /**
     * Валидация данных бронирования
     */
    private function validateBooking(array $data): void {
        if (empty($data['guest_id'])) {
            throw new \Exception('Guest ID is required');
        }
        if (empty($data['room_instance_id'])) {
            throw new \Exception('Room instance ID is required');
        }
        if (empty($data['check_in']) || empty($data['check_out'])) {
            throw new \Exception('Check-in and check-out dates are required');
        }
        if (strtotime($data['check_in']) >= strtotime($data['check_out'])) {
            throw new \Exception('Check-out must be after check-in');
        }
    }
    
    /**
     * Проверка пересечения дат
     */
    private function datesOverlap(string $start1, string $end1, string $start2, string $end2): bool {
        return ($start1 < $end2) && ($end1 > $start2);
    }
    
    /**
     * Отправить уведомление о бронировании
     */
    private function sendNotification(array $booking): void {
        // TODO: Реализовать отправку через Telegram/Email
        logAction('notification_sent', ['booking_id' => $booking['id']]);
    }
}
