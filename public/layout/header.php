<?php
$pageTitle = $pageTitle ?? 'Travly';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style/styles.css">
    <title><?= htmlspecialchars($pageTitle) ?></title>
</head>
<body>

<header>
    <div class="logo">
        <span class="logo-text">Trav<span class="logo-text-highlight">ly</span></span>
        <div class="logo-icon"></div>
    </div>
    <nav class="nav">
    <button class="nav-button" onclick="location.href='/'">Главная</button>
    <button class="nav-button" onclick="location.href='?page=search'">Поиск тура</button>
    <button class="nav-button" onclick="location.href='?page=about'">О нас</button>
    <button class="nav-button" onclick="location.href='?page=help'">Помощь</button>
   <?php if (isset($_SESSION['user_id'])): ?>
    <div class="account" onclick="location.href='?page=me'" style="cursor:pointer">
        <div class="account-icon"></div>
        <span class="account-text">Кабинет</span>
    </div>
    <?php else: ?>
    <div class="account" onclick="location.href='?page=auth'" style="cursor:pointer">
        <div class="account-icon"></div>
        <span class="account-text">Войти</span>
    </div>
    <?php endif; ?>
    </nav>
</header>