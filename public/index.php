<?php
session_start();
require_once '../src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'create-booking') {
    require_once '../src/handlers/booking.php';
    handleCreateBooking();
}

$page = $_GET['page'] ?? 'main';
$allowedPages = ['main', 'search', 'about', 'help', 'auth', 'registration', 'me', 'tour', 'booking']; 
$page = in_array($page, $allowedPages) ? $page : 'main';
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