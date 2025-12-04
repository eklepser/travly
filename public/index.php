<?php
// 1. Загрузка зависимостей (если нужно)
require_once '../src/config/database.php';

// 2. Безопасное определение страницы — whitelist
$page = $_GET['page'] ?? 'main';
$allowedPages = ['main', 'search', 'about', 'help', 'auth', 'registration', 'me', 'tour', 'booking']; 
$page = in_array($page, $allowedPages) ? $page : 'main';

// 3. Начало HTML
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Travly</title>
  <link rel="stylesheet" href="style/styles.css">
</head>
<body>

<?php require_once 'layout/header.php'; ?>

<?php include "layout/{$page}.php"; ?>

<?php require_once 'layout/footer.php'; ?>

</body>
</html>