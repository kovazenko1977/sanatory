<?php
namespace Sanatorium;

/**
 * Room - Управление номерами (классы и экземпляры)
 */
class Room {
    private Database $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Создать класс номера
     */
    public function createRoomClass(array $data): array {
        $roomClass = [
            'name' => $data['name'],
            'level' => $data['level'] ?? 'standard', // standard, deluxe, suite, presidential
            'rooms_count' => (int)($data['rooms_count'] ?? 1),
            'max_guests' => (int)($data['max_guests'] ?? 2),
            'base_price' => (float)($data['base_price'] ?? 0),
            'description' => $data['description'] ?? '',
            'amenities' => $data['amenities'] ?? [],
            'photos' => $data['photos'] ?? [],
            'area' => (float)($data['area'] ?? 0),
            'active' => true
        ];
        
        $result = $this->db->insert('rooms.json', $roomClass);
        logAction('room_class_created', ['id' => $result['id'], 'name' => $result['name']]);
        
        return $result;
    }
    
    /**
     * Создать экземпляр номера
     */
    public function createRoomInstance(array $data): array {
        $instance = [
            'room_class_id' => (int)$data['room_class_id'],
            'room_number' => $data['room_number'],
            'floor' => (int)($data['floor'] ?? 1),
            'status' => $data['status'] ?? 'active', // active, maintenance, blocked
            'notes' => $data['notes'] ?? ''
        ];
        
        $result = $this->db->insert('room_instances.json', $instance);
        logAction('room_instance_created', ['id' => $result['id'], 'number' => $result['room_number']]);
        
        return $result;
    }
    
    /**
     * Получить все классы номеров
     */
    public function getAllRoomClasses(): array {
        return $this->db->read('rooms.json');
    }
    
    /**
     * Получить все экземпляры номеров
     */
    public function getAllRoomInstances(): array {
        $instances = $this->db->read('room_instances.json');
        $classes = $this->getAllRoomClasses();
        
        // Добавить информацию о классе к каждому экземпляру
        foreach ($instances as &$instance) {
            foreach ($classes as $class) {
                if ($class['id'] === $instance['room_class_id']) {
                    $instance['room_class'] = $class;
                    break;
                }
            }
        }
        
        return $instances;
    }
    
    /**
     * Получить доступные номера на даты
     */
    public function getAvailableRooms(string $checkIn, string $checkOut): array {
        $allInstances = $this->getAllRoomInstances();
        $bookings = $this->db->read('bookings.json');
        
        $available = [];
        
        foreach ($allInstances as $instance) {
            // Пропустить неактивные номера
            if ($instance['status'] !== 'active') {
                continue;
            }
            
            // Проверить, не занят ли номер
            $isAvailable = true;
            foreach ($bookings as $booking) {
                if ($booking['room_instance_id'] === $instance['id'] && 
                    $booking['status'] !== 'cancelled') {
                    
                    // Проверка пересечения дат
                    if ($this->datesOverlap(
                        $checkIn, $checkOut,
                        $booking['check_in'], $booking['check_out']
                    )) {
                        $isAvailable = false;
                        break;
                    }
                }
            }
            
            if ($isAvailable) {
                $available[] = $instance;
            }
        }
        
        return $available;
    }
    
    /**
     * Обновить класс номера
     */
    public function updateRoomClass(int $id, array $data): bool {
        $result = $this->db->update('rooms.json', $id, $data);
        if ($result) {
            logAction('room_class_updated', ['id' => $id]);
        }
        return $result;
    }
    
    /**
     * Обновить экземпляр номера
     */
    public function updateRoomInstance(int $id, array $data): bool {
        $result = $this->db->update('room_instances.json', $id, $data);
        if ($result) {
            logAction('room_instance_updated', ['id' => $id]);
        }
        return $result;
    }
    
    /**
     * Удалить класс номера
     */
    public function deleteRoomClass(int $id): bool {
        // Проверить, есть ли экземпляры этого класса
        $instances = $this->db->findWhere('room_instances.json', function($inst) use ($id) {
            return $inst['room_class_id'] === $id;
        });
        
        if (!empty($instances)) {
            throw new \Exception('Cannot delete room class with existing instances');
        }
        
        $result = $this->db->delete('rooms.json', $id);
        if ($result) {
            logAction('room_class_deleted', ['id' => $id]);
        }
        return $result;
    }
    
    /**
     * Удалить экземпляр номера
     */
    public function deleteRoomInstance(int $id): bool {
        // Проверить, нет ли активных броней
        $bookings = $this->db->findWhere('bookings.json', function($booking) use ($id) {
            return $booking['room_instance_id'] === $id && 
                   in_array($booking['status'], ['confirmed', 'checked_in']);
        });
        
        if (!empty($bookings)) {
            throw new \Exception('Cannot delete room instance with active bookings');
        }
        
        $result = $this->db->delete('room_instances.json', $id);
        if ($result) {
            logAction('room_instance_deleted', ['id' => $id]);
        }
        return $result;
    }
    
    /**
     * Блокировать номер (ремонт, карантин и т.д.)
     */
    public function blockRoom(int $instanceId, string $reason, string $from, string $to): bool {
        return $this->updateRoomInstance($instanceId, [
            'status' => 'blocked',
            'block_reason' => $reason,
            'block_from' => $from,
            'block_to' => $to
        ]);
    }
    
    /**
     * Разблокировать номер
     */
    public function unblockRoom(int $instanceId): bool {
        return $this->updateRoomInstance($instanceId, [
            'status' => 'active',
            'block_reason' => null,
            'block_from' => null,
            'block_to' => null
        ]);
    }
    
    /**
     * Получить статистику по номерам
     */
    public function getRoomStatistics(): array {
        $instances = $this->getAllRoomInstances();
        $bookings = $this->db->read('bookings.json');
        
        $stats = [
            'total_rooms' => count($instances),
            'active_rooms' => 0,
            'blocked_rooms' => 0,
            'maintenance_rooms' => 0,
            'occupied_today' => 0,
            'available_today' => 0
        ];
        
        $today = date('Y-m-d');
        
        foreach ($instances as $instance) {
            // Подсчет по статусам
            switch ($instance['status']) {
                case 'active':
                    $stats['active_rooms']++;
                    break;
                case 'blocked':
                    $stats['blocked_rooms']++;
                    break;
                case 'maintenance':
                    $stats['maintenance_rooms']++;
                    break;
            }
            
            // Проверка занятости сегодня
            $isOccupied = false;
            foreach ($bookings as $booking) {
                if ($booking['room_instance_id'] === $instance['id'] &&
                    $booking['status'] === 'checked_in' &&
                    $booking['check_in'] <= $today &&
                    $booking['check_out'] > $today) {
                    $isOccupied = true;
                    break;
                }
            }
            
            if ($isOccupied) {
                $stats['occupied_today']++;
            } elseif ($instance['status'] === 'active') {
                $stats['available_today']++;
            }
        }
        
        $stats['occupancy_rate'] = $stats['total_rooms'] > 0 
            ? round(($stats['occupied_today'] / $stats['total_rooms']) * 100, 2)
            : 0;
        
        return $stats;
    }
    
    /**
     * Проверка пересечения дат
     */
    private function datesOverlap(string $start1, string $end1, string $start2, string $end2): bool {
        return ($start1 < $end2) && ($end1 > $start2);
    }
}
