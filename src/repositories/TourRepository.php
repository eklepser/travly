<?php
require_once __DIR__ . '/../config/database.php';

class TourRepository {
    private $pdo;
    
    public function __construct() {
        $this->pdo = createPDO();
    }
    
    /**
     * Получить все туры с информацией об отелях
     * @param int $limit Ограничение количества результатов
     * @return array
     */
    public function findAll($limit = null) {
        if (!$this->pdo) {
            return [];
        }
        
        try {
            $sql = "
                SELECT 
                    t.id AS tour_id,
                    t.country,
                    t.location AS city,
                    t.base_price,
                    t.arrival_date,
                    t.return_date,
                    t.image_url,  
                    h.name AS hotel_name,
                    h.rating AS hotel_rating,
                    h.max_capacity_per_room
                FROM tours t
                INNER JOIN hotels h ON t.hotel_id = h.id
                ORDER BY t.id
            ";
            
            if ($limit !== null) {
                $sql .= " LIMIT :limit";
            }
            
            $stmt = $this->pdo->prepare($sql);
            if ($limit !== null) {
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[TourRepository] findAll failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить туры с применением фильтров
     * @param array $filters Массив фильтров
     * @return array
     */
    public function findByFilters($filters = []) {
        if (!$this->pdo) {
            return [];
        }
        
        $country = $filters['country'] ?? null;
        $tourType = $filters['vacation_type'] ?? $filters['tour_type'] ?? null;
        $minPrice = isset($filters['min_price']) ? (int)$filters['min_price'] : null;
        $maxPrice = isset($filters['max_price']) ? (int)$filters['max_price'] : null;
        $minNights = isset($filters['min_nights']) ? (int)$filters['min_nights'] : null;
        $maxNights = isset($filters['max_nights']) ? (int)$filters['max_nights'] : null;
        $minGuests = isset($filters['min_guests']) ? (int)$filters['min_guests'] : null;
        $minRating = isset($filters['min_rating']) ? (float)$filters['min_rating'] : null;
        $hotelName = $filters['hotel'] ?? null;
        $sortBy = $filters['sort'] ?? 'popularity';
        
        try {
            $sql = "
                SELECT 
                    t.id AS tour_id,
                    t.country,
                    t.location AS city,
                    t.base_price,
                    t.arrival_date,
                    t.return_date,
                    t.image_url,  
                    h.name AS hotel_name,
                    h.rating AS hotel_rating,
                    h.max_capacity_per_room
                FROM tours t
                INNER JOIN hotels h ON t.hotel_id = h.id
                WHERE 1=1
            ";
            
            $params = [];
            
            if ($country) {
                $sql .= " AND t.country = :country";
                $params['country'] = $country;
            }
            
            if ($tourType) {
                $sql .= " AND t.tour_type = :tour_type";
                $params['tour_type'] = $tourType;
            }
            
            if ($minPrice !== null) {
                $sql .= " AND t.base_price >= :min_price";
                $params['min_price'] = $minPrice;
            }
            
            if ($maxPrice !== null) {
                $sql .= " AND t.base_price <= :max_price";
                $params['max_price'] = $maxPrice;
            }
            
            if ($minRating !== null) {
                $sql .= " AND h.rating >= :min_rating";
                $params['min_rating'] = $minRating;
            }
            
            if ($hotelName) {
                $sql .= " AND h.name = :hotel_name";
                $params['hotel_name'] = $hotelName;
            }
            
            if ($minGuests !== null) {
                $sql .= " AND h.max_capacity_per_room >= :min_guests";
                $params['min_guests'] = $minGuests;
            }
            
            $sql .= " ORDER BY t.id ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($minNights !== null || $maxNights !== null) {
                $filteredTours = [];
                foreach ($tours as $tour) {
                    $arrival = new DateTime($tour['arrival_date']);
                    $return = new DateTime($tour['return_date']);
                    $nights = max(1, $arrival->diff($return)->days);
                    
                    if ($minNights !== null && $nights < $minNights) {
                        continue;
                    }
                    if ($maxNights !== null && $nights > $maxNights) {
                        continue;
                    }
                    
                    $filteredTours[] = $tour;
                }
                $tours = $filteredTours;
            }
            
            $tours = $this->sortTours($tours, $sortBy);
            
            return $tours;
            
        } catch (Exception $e) {
            error_log("[TourRepository] findByFilters failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить тур по ID
     * @param int $id ID тура
     * @return array|null Данные тура или null если не найден
     */
    public function findById($id) {
        if (!$this->pdo || !$id) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    t.id,
                    t.country,
                    t.location,
                    t.departure_point,
                    t.departure_date,
                    t.arrival_point,
                    t.arrival_date,
                    t.return_point,
                    t.return_date,
                    t.base_price,
                    t.additional_services,
                    t.image_url,
                    h.name AS hotel_name,
                    h.rating AS hotel_rating,
                    h.max_capacity_per_room,
                    h.beach_description,
                    h.amenities,
                    h.meal_plan
                FROM tours t
                INNER JOIN hotels h ON t.hotel_id = h.id
                WHERE t.id = :id
            ");
            $stmt->execute(['id' => (int)$id]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);
            return $tour ?: null;
        } catch (Exception $e) {
            error_log("[TourRepository] findById failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получить уникальные страны из туров
     * @return array
     */
    public function getDistinctCountries() {
        if (!$this->pdo) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->query("SELECT DISTINCT country FROM tours ORDER BY country");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("[TourRepository] getDistinctCountries failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получить уникальные типы туров
     * @return array
     */
    public function getDistinctTourTypes() {
        if (!$this->pdo) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->query("SELECT DISTINCT tour_type FROM tours WHERE tour_type IS NOT NULL ORDER BY tour_type");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("[TourRepository] getDistinctTourTypes failed: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Сортировка туров
     * @param array $tours Массив туров
     * @param string $sortBy Тип сортировки
     * @return array
     */
    private function sortTours($tours, $sortBy) {
        switch ($sortBy) {
            case 'price_asc':
                usort($tours, function($a, $b) {
                    return (int)$a['base_price'] - (int)$b['base_price'];
                });
                break;
            case 'price_desc':
                usort($tours, function($a, $b) {
                    return (int)$b['base_price'] - (int)$a['base_price'];
                });
                break;
            case 'rating_desc':
                usort($tours, function($a, $b) {
                    return (float)$b['hotel_rating'] <=> (float)$a['hotel_rating'];
                });
                break;
            case 'rating_asc':
                usort($tours, function($a, $b) {
                    return (float)$a['hotel_rating'] <=> (float)$b['hotel_rating'];
                });
                break;
            case 'popularity':
            default:
                usort($tours, function($a, $b) {
                    return (int)$a['tour_id'] - (int)$b['tour_id'];
                });
                break;
        }
        
        return $tours;
    }
    
    /**
     * Создать новый тур
     * @param array $data Данные тура
     * @return int|false ID созданного тура или false при ошибке
     */
    public function create($data) {
        if (!$this->pdo) {
            error_log("[TourRepository] create: PDO connection is null");
            return false;
        }
        
        try {
            $imageUrl = !empty($data['image_url']) ? trim($data['image_url']) : null;
            $tourType = !empty($data['vacation_type']) ? $data['vacation_type'] : null;
            
            $country = trim($data['country'] ?? '');
            $city = trim($data['city'] ?? '');
            $arrivalDate = $data['arrival_date'] ?? '';
            $returnDate = $data['return_date'] ?? '';
            
            if (empty($country)) {
                error_log("[TourRepository] create: country is required");
                return false;
            }
            
            if (empty($city)) {
                error_log("[TourRepository] create: city is required");
                return false;
            }
            
            if (empty($arrivalDate)) {
                error_log("[TourRepository] create: arrival_date is required");
                return false;
            }
            
            if (empty($returnDate)) {
                error_log("[TourRepository] create: return_date is required");
                return false;
            }
            
            $hotelId = (int)($data['hotel_id'] ?? 0);
            if ($hotelId <= 0) {
                error_log("[TourRepository] create: hotel_id is invalid: " . $hotelId);
                return false;
            }
            
            $params = [
                'country' => $country,
                'location' => $city,
                'hotel_id' => $hotelId,
                'base_price' => (int)($data['base_price'] ?? 0),
                'departure_point' => $data['departure_point'] ?? 'Москва',
                'departure_date' => $data['departure_date'] ?? $arrivalDate,
                'arrival_point' => $data['arrival_point'] ?? $city,
                'arrival_date' => $arrivalDate,
                'return_point' => $data['return_point'] ?? ($data['departure_point'] ?? 'Москва'),
                'return_date' => $returnDate,
                'image_url' => $imageUrl,
                'tour_type' => $tourType
            ];
            
            error_log("[TourRepository] create: Params: " . print_r($params, true));
            
            $sql = "
                INSERT INTO tours (
                    country, location, hotel_id, base_price, 
                    departure_point, departure_date,
                    arrival_point, arrival_date,
                    return_point, return_date,
                    image_url, tour_type
                )
                VALUES (
                    :country, :location, :hotel_id, :base_price,
                    :departure_point, :departure_date,
                    :arrival_point, :arrival_date,
                    :return_point, :return_date,
                    :image_url, :tour_type
                )
            ";
            
            error_log("[TourRepository] create: Executing SQL");
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                $errorMessage = isset($errorInfo[2]) ? $errorInfo[2] : 'Unknown PDO error';
                $errorCode = isset($errorInfo[0]) ? $errorInfo[0] : 'Unknown';
                error_log("[TourRepository] create: execute returned false");
                error_log("[TourRepository] create: PDO Error Info: " . print_r($errorInfo, true));
                error_log("[TourRepository] create: SQL: " . $sql);
                error_log("[TourRepository] create: Params: " . print_r($params, true));
                
                // Выбрасываем исключение с детальной информацией
                throw new Exception("Ошибка выполнения SQL: [$errorCode] $errorMessage");
            }
            
            $tourId = (int)$this->pdo->lastInsertId();
            error_log("[TourRepository] create: lastInsertId = " . $tourId);
            
            if ($tourId > 0) {
                return $tourId;
            }
            
            error_log("[TourRepository] create: lastInsertId returned 0 or invalid value");
            throw new Exception("Не удалось получить ID созданного тура. lastInsertId вернул: $tourId");
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            error_log("[TourRepository] create failed (PDOException): " . $errorMessage);
            error_log("[TourRepository] create failed: SQL State: " . $errorCode);
            error_log("[TourRepository] create failed: Data: " . print_r($data, true));
            error_log("[TourRepository] create failed: Stack trace: " . $e->getTraceAsString());
            
            // Обработка ошибки дублирования ключа (PostgreSQL)
            if ($errorCode == '23505' || strpos($errorMessage, 'duplicate key') !== false) {
                try {
                    $maxStmt = $this->pdo->query("SELECT MAX(id) FROM tours");
                    $maxId = (int)$maxStmt->fetchColumn();
                    $nextVal = $maxId + 1;
                    $this->pdo->query("SELECT setval('tours_id_seq', $nextVal, false)");
                    
                    $result = $stmt->execute($params);
                    if ($result) {
                        $tourId = (int)$this->pdo->lastInsertId();
                        if ($tourId > 0) {
                            return $tourId;
                        }
                    }
                } catch (Exception $e2) {
                    error_log("[TourRepository] Failed to fix sequence: " . $e2->getMessage());
                }
            }
            
            // Пробрасываем исключение дальше с детальной информацией
            throw new Exception("Ошибка базы данных при создании тура: [$errorCode] $errorMessage", 0, $e);
        } catch (Exception $e) {
            error_log("[TourRepository] create failed (general): " . $e->getMessage());
            error_log("[TourRepository] create failed: Stack trace: " . $e->getTraceAsString());
            // Пробрасываем исключение дальше
            throw $e;
        }
    }
    
    /**
     * Удалить тур по ID
     * @param int $id ID тура
     * @return bool true при успешном удалении, false при ошибке
     */
    public function delete($id) {
        if (!$this->pdo || !$id || $id <= 0) {
            error_log("[TourRepository] delete: Invalid ID or PDO connection");
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("DELETE FROM tours WHERE id = :id");
            $result = $stmt->execute(['id' => (int)$id]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("[TourRepository] delete: execute returned false");
                error_log("[TourRepository] delete: PDO Error Info: " . print_r($errorInfo, true));
                return false;
            }
            
            $rowCount = $stmt->rowCount();
            error_log("[TourRepository] delete: Deleted $rowCount row(s) for tour ID $id");
            
            return $rowCount > 0;
        } catch (PDOException $e) {
            error_log("[TourRepository] delete failed (PDOException): " . $e->getMessage());
            error_log("[TourRepository] delete failed: SQL State: " . $e->getCode());
            error_log("[TourRepository] delete failed: Stack trace: " . $e->getTraceAsString());
            throw new Exception("Ошибка базы данных при удалении тура: [" . $e->getCode() . "] " . $e->getMessage(), 0, $e);
        } catch (Exception $e) {
            error_log("[TourRepository] delete failed (general): " . $e->getMessage());
            error_log("[TourRepository] delete failed: Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
}

