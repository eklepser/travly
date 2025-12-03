<?php
require_once __DIR__ . '/../config/database.php';

function getFilterOptions() {
    $pdo = createPDO();
    if (!$pdo) {
        return [
            'countries' => [],
            'hotels' => [],
            'maxCapacity' => 4
        ];
    }
    
    try {
        // Получаем уникальные страны
        $stmt = $pdo->query("SELECT DISTINCT country FROM tours ORDER BY country");
        $countries = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Получаем уникальные отели
        $stmt = $pdo->query("SELECT DISTINCT name FROM hotels ORDER BY name");
        $hotels = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Получаем максимальную вместимость
        $stmt = $pdo->query("SELECT MAX(max_capacity_per_room) FROM hotels");
        $maxCapacity = (int)$stmt->fetchColumn();
        
        return [
            'countries' => $countries,
            'hotels' => $hotels,
            'maxCapacity' => $maxCapacity
        ];
        
    } catch (Exception $e) {
        error_log("[Handler] Filter options failed: " . $e->getMessage());
        return [
            'countries' => [],
            'hotels' => [],
            'maxCapacity' => 4
        ];
    }
}

