<?php
namespace Sanatorium;

/**
 * Guest - Управление гостями
 */
class Guest {
    private Database $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Создать гостя
     */
    public function create(array $data): array {
        $guest = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'middle_name' => $data['middle_name'] ?? '',
            'phone' => $data['phone'],
            'email' => $data['email'] ?? '',
            'passport_series' => $data['passport_series'] ?? '',
            'passport_number' => $data['passport_number'] ?? '',
            'passport_issued_by' => $data['passport_issued_by'] ?? '',
            'passport_issued_date' => $data['passport_issued_date'] ?? '',
            'birth_date' => $data['birth_date'] ?? '',
            'address' => $data['address'] ?? '',
            'telegram_chat_id' => $data['telegram_chat_id'] ?? null,
            'notes' => $data['notes'] ?? '',
            'documents' => [],
            'blacklisted' => false,
            'blacklist_reason' => null,
            'loyalty_level' => 'base',
            'loyalty_points' => 0,
            'total_bookings' => 0,
            'total_spent' => 0
        ];
        
        $result = $this->db->insert('guests.json', $guest);
        logAction('guest_created', ['id' => $result['id'], 'name' => $result['first_name'] . ' ' . $result['last_name']]);
        
        return $result;
    }
    
    /**
     * Обновить гостя
     */
    public function update(int $id, array $data): bool {
        $result = $this->db->update('guests.json', $id, $data);
        if ($result) {
            logAction('guest_updated', ['id' => $id]);
        }
        return $result;
    }
    
    /**
     * Удалить гостя
     */
    public function delete(int $id): bool {
        // Проверить, нет ли активных броней
        $bookings = $this->db->findWhere('bookings.json', function($booking) use ($id) {
            return $booking['guest_id'] === $id && 
                   in_array($booking['status'], ['confirmed', 'paid', 'checked_in']);
        });
        
        if (!empty($bookings)) {
            throw new \Exception('Cannot delete guest with active bookings');
        }
        
        $result = $this->db->delete('guests.json', $id);
        if ($result) {
            logAction('guest_deleted', ['id' => $id]);
        }
        return $result;
    }
    
    /**
     * Получить всех гостей
     */
    public function getAll(array $filters = []): array {
        $guests = $this->db->read('guests.json');
        
        if (!empty($filters)) {
            $guests = array_filter($guests, function($guest) use ($filters) {
                if (isset($filters['search'])) {
                    $search = strtolower($filters['search']);
                    $fullName = strtolower($guest['first_name'] . ' ' . $guest['last_name']);
                    if (strpos($fullName, $search) === false && 
                        strpos($guest['phone'], $search) === false &&
                        strpos($guest['email'], $search) === false) {
                        return false;
                    }
                }
                if (isset($filters['blacklisted']) && $guest['blacklisted'] !== $filters['blacklisted']) {
                    return false;
                }
                return true;
            });
        }
        
        return array_values($guests);
    }
    
    /**
     * Получить гостя по ID
     */
    public function getById(int $id): ?array {
        return $this->db->findById('guests.json', $id);
    }
    
    /**
     * Загрузить документ
     */
    public function uploadDocument(int $guestId, array $file, string $type): bool {
        $guest = $this->getById($guestId);
        if (!$guest) {
            throw new \Exception('Guest not found');
        }
        
        // Проверка типа файла
        $allowedTypes = ['passport', 'voucher', 'insurance', 'other'];
        if (!in_array($type, $allowedTypes)) {
            throw new \Exception('Invalid document type');
        }
        
        // Проверка расширения
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTENSIONS)) {
            throw new \Exception('File type not allowed');
        }
        
        // Проверка размера
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            throw new \Exception('File too large');
        }
        
        // Создать папку для документов гостя
        $guestDir = UPLOADS_PATH . '/documents/guest_' . $guestId;
        if (!is_dir($guestDir)) {
            mkdir($guestDir, 0777, true);
        }
        
        // Генерация имени файла
        $filename = $type . '_' . time() . '.' . $ext;
        $filepath = $guestDir . '/' . $filename;
        
        // Переместить файл
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new \Exception('Failed to upload file');
        }
        
        // Добавить в список документов
        $guest['documents'][] = [
            'type' => $type,
            'filename' => $filename,
            'original_name' => $file['name'],
            'uploaded_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->db->update('guests.json', $guestId, [
            'documents' => $guest['documents']
        ]);
        
        if ($result) {
            logAction('document_uploaded', ['guest_id' => $guestId, 'type' => $type]);
        }
        
        return $result;
    }
    
    /**
     * Удалить документ
     */
    public function deleteDocument(int $guestId, string $filename): bool {
        $guest = $this->getById($guestId);
        if (!$guest) {
            throw new \Exception('Guest not found');
        }
        
        // Удалить файл
        $filepath = UPLOADS_PATH . '/documents/guest_' . $guestId . '/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        // Удалить из списка
        $guest['documents'] = array_filter($guest['documents'], function($doc) use ($filename) {
            return $doc['filename'] !== $filename;
        });
        
        return $this->db->update('guests.json', $guestId, [
            'documents' => array_values($guest['documents'])
        ]);
    }
    
    /**
     * Добавить в черный список
     */
    public function addToBlacklist(int $id, string $reason): bool {
        $result = $this->db->update('guests.json', $id, [
            'blacklisted' => true,
            'blacklist_reason' => $reason,
            'blacklisted_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            logAction('guest_blacklisted', ['id' => $id, 'reason' => $reason]);
        }
        
        return $result;
    }
    
    /**
     * Удалить из черного списка
     */
    public function removeFromBlacklist(int $id): bool {
        $result = $this->db->update('guests.json', $id, [
            'blacklisted' => false,
            'blacklist_reason' => null,
            'blacklisted_at' => null
        ]);
        
        if ($result) {
            logAction('guest_removed_from_blacklist', ['id' => $id]);
        }
        
        return $result;
    }
    
    /**
     * Обновить статистику гостя
     */
    public function updateStatistics(int $id): void {
        $bookings = $this->db->findWhere('bookings.json', function($booking) use ($id) {
            return $booking['guest_id'] === $id && $booking['status'] !== 'cancelled';
        });
        
        $totalBookings = count($bookings);
        $totalSpent = array_sum(array_column($bookings, 'total_price'));
        
        // Определить уровень лояльности
        $loyaltyLevel = 'base';
        if ($totalBookings >= 10 || $totalSpent >= 100000) {
            $loyaltyLevel = 'platinum';
        } elseif ($totalBookings >= 5 || $totalSpent >= 50000) {
            $loyaltyLevel = 'gold';
        } elseif ($totalBookings >= 2 || $totalSpent >= 20000) {
            $loyaltyLevel = 'silver';
        }
        
        $this->db->update('guests.json', $id, [
            'total_bookings' => $totalBookings,
            'total_spent' => $totalSpent,
            'loyalty_level' => $loyaltyLevel,
            'loyalty_points' => floor($totalSpent / 100) // 1 балл за каждые 100 рублей
        ]);
    }
    
    /**
     * Получить историю бронирований гостя
     */
    public function getBookingHistory(int $id): array {
        return $this->db->findWhere('bookings.json', function($booking) use ($id) {
            return $booking['guest_id'] === $id;
        });
    }
    
    /**
     * Поиск гостя по телефону или email
     */
    public function findByContact(string $contact): ?array {
        $guests = $this->db->read('guests.json');
        
        foreach ($guests as $guest) {
            if ($guest['phone'] === $contact || $guest['email'] === $contact) {
                return $guest;
            }
        }
        
        return null;
    }
}
