<?php
require_once __DIR__ . '/../config/database.php';

class HotelRepository {
    private $pdo;
    
    public function __construct() {
        $this->pdo = createPDO();
    }
    
    /**
     * Получить все уникальные названия отелей
     * @return array
     */
    public function findAllNames() {
        if (!$this->pdo) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->query("SELECT DISTINCT name FROM hotels ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("[HotelRepository] findAllNames failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить отели по стране
     * @param string $country Название страны
     * @return array
     */
    public function findByCountry($country) {
        if (!$country || !$this->pdo) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT h.name 
                FROM hotels h
                INNER JOIN tours t ON h.id = t.hotel_id
                WHERE t.country = :country
                ORDER BY h.name
            ");
            $stmt->execute(['country' => $country]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("[HotelRepository] findByCountry failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить максимальную вместимость отелей
     * @return int
     */
    public function getMaxCapacity() {
        if (!$this->pdo) {
            return 4;
        }
        
        try {
            $stmt = $this->pdo->query("SELECT MAX(max_capacity_per_room) FROM hotels");
            $maxCapacity = (int)$stmt->fetchColumn();
            return $maxCapacity > 0 ? $maxCapacity : 4;
        } catch (Exception $e) {
            error_log("[HotelRepository] getMaxCapacity failed: " . $e->getMessage());
            return 4;
        }
    }
    
    /**
     * Получить все отели с полной информацией
     * @return array
     */
    public function findAll() {
        if (!$this->pdo) {
            return [];
        }
        
        try {
            // Пробуем получить с country и city
            try {
                $stmt = $this->pdo->query("
                    SELECT 
                        h.id AS hotel_id,
                        h.name AS hotel_name,
                        h.rating AS hotel_rating,
                        h.max_capacity_per_room,
                        h.country,
                        h.city
                    FROM hotels h
                    ORDER BY h.name
                ");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                // Если полей country и city нет, получаем без них
                error_log("[HotelRepository] findAll: country/city not found, trying without them");
                $stmt = $this->pdo->query("
                    SELECT 
                        h.id AS hotel_id,
                        h.name AS hotel_name,
                        h.rating AS hotel_rating,
                        h.max_capacity_per_room,
                        NULL AS country,
                        NULL AS city
                    FROM hotels h
                    ORDER BY h.name
                ");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("[HotelRepository] findAll failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Создать новый отель
     * @param array $data Данные отеля
     * @return int|false ID созданного отеля или false при ошибке
     */
    public function create($data) {
        if (!$this->pdo) {
            return false;
        }
        
        try {
            // Создаем отель только с обязательными полями
            // country и city могут отсутствовать в таблице
            $stmt = $this->pdo->prepare("
                INSERT INTO hotels (name, rating, max_capacity_per_room)
                VALUES (:name, :rating, :max_capacity_per_room)
            ");
            $result = $stmt->execute([
                'name' => trim($data['name'] ?? ''),
                'rating' => (float)($data['rating'] ?? 4),
                'max_capacity_per_room' => (int)($data['max_capacity_per_room'] ?? 4)
            ]);
            
            if (!$result) {
                error_log("[HotelRepository] create: execute returned false");
                return false;
            }
            
            $hotelId = (int)$this->pdo->lastInsertId();
            if ($hotelId > 0) {
                return $hotelId;
            } else {
                error_log("[HotelRepository] create: lastInsertId returned 0");
                return false;
            }
        } catch (PDOException $e) {
            error_log("[HotelRepository] create failed: " . $e->getMessage());
            error_log("[HotelRepository] SQL State: " . $e->getCode());
            error_log("[HotelRepository] Data: " . print_r($data, true));
            return false;
        } catch (Exception $e) {
            error_log("[HotelRepository] create failed (general): " . $e->getMessage());
            return false;
        }
    }
}

