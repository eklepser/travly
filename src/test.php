<?php
require_once __DIR__ . 'connect.php';

try {
    $pdo = getPDO();
    if (!$pdo) {
        throw new Exception('Failed to connect to database');
    }

    // Получаем JSON-данные
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $phone = trim($data['phone'] ?? '');
    $name = trim($data['name'] ?? '');

    if (!$phone || !$name) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'phone and name are required']);
        exit;
    }

    // Проверяем длину — защита от переполнения
    if (strlen($phone) > 20 || strlen($name) > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'phone or name too long']);
        exit;
    }

    // Вставляем в PostgreSQL
    $stmt = $pdo->prepare("
        INSERT INTO test
        VALUES (1, 'Allo')
        RETURNING id
    ");

    $stmt->execute([
        ':phone' => $phone,
        ':name' => $name
    ]);

    $result = $stmt->fetch();
    $id = $result['id'];

    echo json_encode([
        'success' => true,
        'id' => (int)$id,
        'phone' => $phone,
        'name' => $name
    ]);

} catch (Exception $e) {
    error_log("User insert error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>