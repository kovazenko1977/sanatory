<?php
namespace Sanatorium;

/**
 * Database - JSON File Handler with atomic operations
 */
class Database {
    private string $dataPath;
    
    public function __construct() {
        $this->dataPath = DATA_PATH;
        $this->initializeDataFiles();
    }
    
    /**
     * Инициализация файлов данных
     */
    private function initializeDataFiles(): void {
        $files = [
            'rooms.json' => [],
            'room_instances.json' => [],
            'bookings.json' => [],
            'guests.json' => [],
            'services.json' => [],
            'prices.json' => [],
            'promocodes.json' => [],
            'waitlist.json' => [],
            'taxes.json' => [],
            'contracts.json' => [],
            'loyalty.json' => ['levels' => [], 'points' => []],
            'certificates.json' => [],
            'telegram.json' => ['users' => [], 'settings' => []],
            'settings.json' => $this->getDefaultSettings()
        ];
        
        // Создать папку data если не существует
        if (!is_dir($this->dataPath)) {
            mkdir($this->dataPath, 0777, true);
        }
        
        // Создать папку logs
        if (!is_dir($this->dataPath . '/logs')) {
            mkdir($this->dataPath . '/logs', 0777, true);
        }
        
        // Создать файлы если не существуют
        foreach ($files as $filename => $defaultData) {
            $filepath = $this->dataPath . '/' . $filename;
            if (!file_exists($filepath)) {
                $this->writeFile($filename, $defaultData);
            }
        }
    }
    
    /**
     * Чтение данных из JSON файла
     */
    public function read(string $filename): array {
        $filepath = $this->dataPath . '/' . $filename;
        
        if (!file_exists($filepath)) {
            return [];
        }
        
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            throw new \Exception("Cannot open file: $filename");
        }
        
        // Блокировка для чтения
        flock($handle, LOCK_SH);
        $content = fread($handle, filesize($filepath) ?: 1);
        flock($handle, LOCK_UN);
        fclose($handle);
        
        $data = json_decode($content, true);
        return $data ?? [];
    }
    
    /**
     * Запись данных в JSON файл (атомарная операция)
     */
    public function write(string $filename, array $data): bool {
        $filepath = $this->dataPath . '/' . $filename;
        $tempFile = $filepath . '.tmp';
        
        // Записать во временный файл
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new \Exception("JSON encoding error: " . json_last_error_msg());
        }
        
        $handle = fopen($tempFile, 'w');
        if (!$handle) {
            throw new \Exception("Cannot create temp file: $tempFile");
        }
        
        // Эксклюзивная блокировка
        flock($handle, LOCK_EX);
        fwrite($handle, $json);
        flock($handle, LOCK_UN);
        fclose($handle);
        
        // Атомарное переименование
        return rename($tempFile, $filepath);
    }
    
    /**
     * Добавить запись
     */
    public function insert(string $filename, array $record): array {
        $data = $this->read($filename);
        
        // Генерация ID
        $record['id'] = $this->generateId($data);
        $record['created_at'] = date('Y-m-d H:i:s');
        $record['updated_at'] = date('Y-m-d H:i:s');
        
        $data[] = $record;
        $this->write($filename, $data);
        
        return $record;
    }
    
    /**
     * Обновить запись
     */
    public function update(string $filename, int $id, array $updates): bool {
        $data = $this->read($filename);
        $found = false;
        
        foreach ($data as &$record) {
            if ($record['id'] === $id) {
                $record = array_merge($record, $updates);
                $record['updated_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        
        if ($found) {
            $this->write($filename, $data);
        }
        
        return $found;
    }
    
    /**
     * Удалить запись
     */
    public function delete(string $filename, int $id): bool {
        $data = $this->read($filename);
        $initialCount = count($data);
        
        $data = array_filter($data, function($record) use ($id) {
            return $record['id'] !== $id;
        });
        
        if (count($data) < $initialCount) {
            $this->write($filename, array_values($data));
            return true;
        }
        
        return false;
    }
    
    /**
     * Найти запись по ID
     */
    public function findById(string $filename, int $id): ?array {
        $data = $this->read($filename);
        
        foreach ($data as $record) {
            if ($record['id'] === $id) {
                return $record;
            }
        }
        
        return null;
    }
    
    /**
     * Найти записи по условию
     */
    public function findWhere(string $filename, callable $condition): array {
        $data = $this->read($filename);
        return array_filter($data, $condition);
    }
    
    /**
     * Генерация уникального ID
     */
    private function generateId(array $data): int {
        if (empty($data)) {
            return 1;
        }
        
        $maxId = max(array_column($data, 'id'));
        return $maxId + 1;
    }
    
    /**
     * Получить настройки по умолчанию
     */
    private function getDefaultSettings(): array {
        return [
            'sanatorium_name' => 'Санаторий',
            'sanatorium_address' => '',
            'sanatorium_phone' => '',
            'sanatorium_email' => '',
            'sanatorium_inn' => '',
            'check_in_time' => '14:00',
            'check_out_time' => '12:00',
            'default_currency' => 'RUB',
            'enable_telegram' => false,
            'enable_email' => false,
            'theme' => 'light',
            'language' => 'ru'
        ];
    }
    
    /**
     * Вспомогательный метод для записи файла
     */
    private function writeFile(string $filename, array $data): void {
        $filepath = $this->dataPath . '/' . $filename;
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($filepath, $json, LOCK_EX);
    }
}
