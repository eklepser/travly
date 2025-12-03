<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../src/config/database.php';

$pdo = createPDO();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
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
    
    echo json_encode([
        'countries' => $countries,
        'hotels' => $hotels,
        'maxCapacity' => $maxCapacity
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("[API] Filter options failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch filter options']);
}

