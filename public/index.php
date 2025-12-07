<?php
// Настраиваем обработку ошибок
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Начинаем сессию только если она еще не начата
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Обработка регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'register') {
    require_once '../src/config/database.php';
    require_once '../src/handlers/auth.php';
    handleRegister();
    exit; // Важно: выходим, чтобы не выполнять код ниже
}

// Обработка авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'login') {
    require_once '../src/config/database.php';
    require_once '../src/handlers/auth.php';
    handleLogin();
    exit; // Важно: выходим, чтобы не выполнять код ниже
}

// Обработка выхода
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    require_once '../src/handlers/auth.php';
    handleLogout();
    exit; // Важно: выходим, чтобы не выполнять код ниже
}

// Обработка сохранения данных бронирования в сессию
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save-booking-data') {
    header('Content-Type: application/json');
    
    $tourId = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
    $adults = isset($_POST['adults']) ? (int)$_POST['adults'] : 1;
    $children = isset($_POST['children']) ? (int)$_POST['children'] : 0;
    $roomPrice = isset($_POST['room_price']) ? (int)$_POST['room_price'] : 0;
    
    $servicesJson = $_POST['services'] ?? '[]';
    $services = json_decode($servicesJson, true);
    if (!is_array($services)) {
        $services = [];
    }
    
    $_SESSION['booking_data'] = [
        'tour_id' => $tourId,
        'adults' => $adults,
        'children' => $children,
        'room_price' => $roomPrice,
        'services' => $services
    ];
    
    echo json_encode(['success' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'create-booking') {
    require_once '../src/config/database.php';
    require_once '../src/handlers/booking.php';
    handleCreateBooking();
    exit; // Важно: выходим, чтобы не выполнять код ниже
}

// Обработка отмены бронирования
if (isset($_GET['action']) && $_GET['action'] === 'cancel-booking') {
    require_once '../src/config/database.php';
    require_once '../src/repositories/BookingRepository.php';
    
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
        exit;
    }
    
    $bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $userId = (int)$_SESSION['user_id'];
    
    if ($bookingId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Неверный ID бронирования']);
        exit;
    }
    
    try {
        error_log("[cancel-booking] Attempting to delete booking {$bookingId} for user {$userId}");
        
        $bookingRepo = new BookingRepository();
        $result = $bookingRepo->delete($bookingId, $userId);
        
        error_log("[cancel-booking] Delete result: " . ($result ? 'true' : 'false'));
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Бронирование успешно отменено']);
        } else {
            error_log("[cancel-booking] Failed to delete booking {$bookingId}");
            echo json_encode(['success' => false, 'message' => 'Не удалось отменить бронирование. Проверьте логи сервера.']);
        }
    } catch (Exception $e) {
        error_log("[cancel-booking] Exception: " . $e->getMessage());
        error_log("[cancel-booking] Stack trace: " . $e->getTraceAsString());
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
    
    exit;
}

// Обычная загрузка страницы
require_once '../src/config/database.php';

$page = $_GET['page'] ?? 'main';
$allowedPages = ['main', 'search', 'about', 'help', 'auth', 'registration', 'me', 'tour', 'booking']; 
$page = in_array($page, $allowedPages) ? $page : 'main';

// Проверка авторизации для страницы кабинета
if ($page === 'me' && !isset($_SESSION['user_id'])) {
    header('Location: ?page=auth');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Travly</title>
  <link rel="stylesheet" href="style/styles.css">
</head>
<body class="main-page-body">

<?php require_once 'layout/header.php'; ?>

<?php include "layout/{$page}.php"; ?>

<?php require_once 'layout/footer.php'; ?>

</body>
</html>