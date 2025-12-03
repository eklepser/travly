<?php
require_once __DIR__ . '/../config/database.php';

function getFilteredTours($filters = []) {
    $pdo = createPDO();
    if (!$pdo) {
        return [];
    }
    
    $country = $filters['country'] ?? null;
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
        
        // Сначала получаем все туры без сортировки (сортировка будет применена после фильтрации по ночам)
        $sql .= " ORDER BY t.id ASC";
        
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
        
        // Применяем сортировку после фильтрации
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
                // По популярности - по id (меньше id = популярнее)
                usort($tours, function($a, $b) {
                    return (int)$a['tour_id'] - (int)$b['tour_id'];
                });
                break;
        }
        
        return $tours;
        
    } catch (Exception $e) {
        error_log("[Handler] Filter tours failed: " . $e->getMessage());
        return [];
    }
}

