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
}

