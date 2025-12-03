<?php
require_once __DIR__ . '/../config/database.php';

function getHotelsByCountry($country) {
    if (!$country) {
        return [];
    }
    
    $pdo = createPDO();
    if (!$pdo) {
        return [];
    }
    
    try {
        // Получаем отели для выбранной страны
        $stmt = $pdo->prepare("
            SELECT DISTINCT h.name 
            FROM hotels h
            INNER JOIN tours t ON h.id = t.hotel_id
            WHERE t.country = :country
            ORDER BY h.name
        ");
        $stmt->execute(['country' => $country]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
        
    } catch (Exception $e) {
        error_log("[Handler] Hotels by country failed: " . $e->getMessage());
        return [];
    }
}

