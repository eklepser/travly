<?php
require_once __DIR__ . '/../config/database.php';

class HotelRepository {
    private $pdo;
    
    public function __construct() {
        $this->pdo = createPDO();
    }

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

    public function findByCountryWithDetails($country) {
        if (!$country || !$this->pdo) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT
                    h.id AS hotel_id,
                    h.name AS hotel_name,
                    h.rating AS hotel_rating,
                    h.max_capacity_per_room,
                    t.country,
                    t.location AS city
                FROM hotels h
                INNER JOIN tours t ON h.id = t.hotel_id
                WHERE t.country = :country
                ORDER BY h.name
            ");
            $stmt->execute(['country' => $country]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[HotelRepository] findByCountryWithDetails failed: " . $e->getMessage());
            return [];
        }
    }

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

    public function findAll() {
        if (!$this->pdo) {
            return [];
        }
        
        try {
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

    public function create($data) {
        if (!$this->pdo) {
            error_log("[HotelRepository] create: PDO connection is null");
            return false;
        }
        
        try {
            $name = trim($data['name'] ?? '');
            $rating = isset($data['rating']) ? (float)$data['rating'] : null;
            $maxCapacity = (int)($data['max_capacity_per_room'] ?? 4);
            
            if (empty($name)) {
                error_log("[HotelRepository] create: name is required");
                return false;
            }
            
            if ($maxCapacity < 1) {
                error_log("[HotelRepository] create: max_capacity_per_room must be >= 1, got: " . $maxCapacity);
                return false;
            }
            
            $fields = ['name', 'max_capacity_per_room'];
            $values = [':name', ':max_capacity_per_room'];
            $params = [
                'name' => $name,
                'max_capacity_per_room' => $maxCapacity
            ];
            
            if ($rating !== null) {
                $rating = max(0.0, min(9.99, $rating));
                $fields[] = 'rating';
                $values[] = ':rating';
                $params['rating'] = $rating;
            }
            
            $sql = "INSERT INTO hotels (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $values) . ")";
            
            error_log("[HotelRepository] create: Executing SQL: " . $sql);
            error_log("[HotelRepository] create: Params: " . print_r($params, true));
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                $errorMessage = isset($errorInfo[2]) ? $errorInfo[2] : 'Unknown PDO error';
                $errorCode = isset($errorInfo[0]) ? $errorInfo[0] : 'Unknown';
                error_log("[HotelRepository] create: execute returned false");
                error_log("[HotelRepository] create: SQL: " . $sql);
                error_log("[HotelRepository] create: Params: " . print_r($params, true));
                error_log("[HotelRepository] create: PDO Error Info: " . print_r($errorInfo, true));

                throw new Exception("Ошибка выполнения SQL при создании отеля: [$errorCode] $errorMessage");
            }
            
            $hotelId = (int)$this->pdo->lastInsertId();
            error_log("[HotelRepository] create: lastInsertId = " . $hotelId);
            
            if ($hotelId > 0) {
                return $hotelId;
            } else {
                error_log("[HotelRepository] create: lastInsertId returned 0 or invalid value");
                throw new Exception("Не удалось получить ID созданного отеля. lastInsertId вернул: $hotelId");
            }
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            error_log("[HotelRepository] create failed (PDOException): " . $errorMessage);
            error_log("[HotelRepository] SQL State: " . $errorCode);
            error_log("[HotelRepository] Data: " . print_r($data, true));
            error_log("[HotelRepository] Stack trace: " . $e->getTraceAsString());

            throw new Exception("Ошибка базы данных при создании отеля: [$errorCode] $errorMessage", 0, $e);
        } catch (Exception $e) {
            error_log("[HotelRepository] create failed (general): " . $e->getMessage());
            error_log("[HotelRepository] Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
}

