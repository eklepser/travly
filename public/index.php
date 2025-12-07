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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'create-booking') {
    require_once '../src/config/database.php';
    require_once '../src/handlers/booking.php';
    handleCreateBooking();
    exit; // Важно: выходим, чтобы не выполнять код ниже
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