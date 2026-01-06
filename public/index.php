<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

require_once '../src/utils/session-helper.php';
require_once '../src/utils/auth-helper.php';

ensureSessionStarted();
$isAdmin = checkIsAdmin();
if ($isAdmin && isset($_GET['action'])) {
    require_once '../src/core/autoload.php';
    
    if (in_array($_GET['action'], ['add-tour', 'add-hotel', 'update-tour', 'delete-tour', 'get-hotels', 'get-tour'])) {
        $controllerClass = 'AdminController';
        $controller = new $controllerClass();
        $controller->handle();
        exit;
    }
}

if (isset($_GET['action']) && in_array($_GET['action'], ['register', 'login', 'logout'])) {
    require_once '../src/core/autoload.php';
    $controller = new AuthController();
    $controller->handle();
    exit;
}
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
    require_once '../src/core/database.php';
    require_once '../src/handlers/booking.php';
    handleCreateBooking();
    exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'cancel-booking') {
    require_once '../src/core/database.php';
    require_once '../src/models/repositories/BookingRepository.php';
    
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

require_once '../src/core/database.php';

$page = $_GET['page'] ?? 'main';
$allowedPages = ['main', 'search', 'about', 'help', 'auth', 'registration', 'me', 'tour', 'booking']; 
$page = in_array($page, $allowedPages) ? $page : 'main';

if ($page === 'me' && !isset($_SESSION['user_id'])) {
    header('Location: ?page=auth');
    exit;
}

require_once '../src/core/autoload.php';

$controllers = [
    'main' => 'MainController',
    'search' => 'SearchController',
    'about' => 'AboutController',
    'help' => 'HelpController',
    'auth' => 'AuthController',
    'registration' => 'RegistrationController',
    'me' => 'MeController',
    'tour' => 'TourController',
    'booking' => 'BookingController'
];

$controllerClass = $controllers[$page] ?? 'MainController';

$pageTitle = 'Travly';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="style/styles.css">
</head>
<body class="main-page-body<?= $isAdmin ? ' admin-mode' : '' ?>">

<?php require_once 'layout/header.php'; ?>

<?php if ($isAdmin) { require_once 'layout/components/admin-panel.php'; } ?>

<?php
try {
    $controller = new $controllerClass();
    $controller->handle();
} catch (Exception $e) {
    error_log("[index.php] Error in controller {$controllerClass}: " . $e->getMessage());
    error_log("[index.php] Stack trace: " . $e->getTraceAsString());
    if (ini_get('display_errors')) {
        echo '<main><div style="padding: 40px; text-align: center;"><h1>Ошибка</h1><p>' . htmlspecialchars($e->getMessage()) . '</p><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre></div></main>';
    } else {
        echo '<main><div style="padding: 40px; text-align: center;"><h1>Ошибка</h1><p>Произошла ошибка при загрузке страницы.</p></div></main>';
    }
} catch (Error $e) {
    error_log("[index.php] Fatal error in controller {$controllerClass}: " . $e->getMessage());
    error_log("[index.php] Stack trace: " . $e->getTraceAsString());
    if (ini_get('display_errors')) {
        echo '<main><div style="padding: 40px; text-align: center;"><h1>Критическая ошибка</h1><p>' . htmlspecialchars($e->getMessage()) . '</p><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre></div></main>';
    } else {
        echo '<main><div style="padding: 40px; text-align: center;"><h1>Ошибка</h1><p>Произошла ошибка при загрузке страницы.</p></div></main>';
    }
}
?>

<?php require_once 'layout/footer.php'; ?>

</body>
</html>
