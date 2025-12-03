<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../src/config/database.php';

$pdo = createPDO();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Получаем параметры фильтров
$country = $_GET['country'] ?? null;
$minPrice = isset($_GET['min_price']) ? (int)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : null;
$minNights = isset($_GET['min_nights']) ? (int)$_GET['min_nights'] : null;
$maxNights = isset($_GET['max_nights']) ? (int)$_GET['max_nights'] : null;
$minGuests = isset($_GET['min_guests']) ? (int)$_GET['min_guests'] : null;
$minRating = isset($_GET['min_rating']) ? (float)$_GET['min_rating'] : null;
$hotelName = $_GET['hotel'] ?? null;

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
    
    $sql .= " ORDER BY t.id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Фильтруем по количеству ночей (вычисляемое поле)
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
    
    echo json_encode(['tours' => $tours], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("[API] Filter tours failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch tours']);
}

