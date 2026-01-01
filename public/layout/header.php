<?php
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
$pageTitle = $pageTitle ?? 'Travly';
?>
<header>
    <div class="logo" onclick="location.href='/'">
        <span class="logo-text">Trav<span class="logo-text-highlight">ly</span></span>
        <div class="logo-icon"></div>
    </div>
    <nav class="nav">
    <button class="nav-button" onclick="location.href='/'">Главная</button>
    <button class="nav-button" onclick="location.href='?page=search'">Поиск тура</button>
    <button class="nav-button" onclick="location.href='?page=about'">О нас</button>
    <button class="nav-button" onclick="location.href='?page=help'">Помощь</button>
   <?php 
   if (isset($_SESSION['user_id'])): 
       require_once __DIR__ . '/../../src/config/database.php';
       require_once __DIR__ . '/../../src/repositories/UserRepository.php';
       $userRepo = new UserRepository();
       $user = $userRepo->findById($_SESSION['user_id']);
       $userName = $user ? $user['full_name'] : 'Пользователь';
   ?>
    <div class="account" style="cursor:pointer; position: relative;" id="userAccount" onclick="if(!event.target.closest('.account-dropdown')) { window.location.href='?page=me'; }">
        <div class="account-icon" id="accountIcon" style="cursor:pointer;"></div>
        <span class="account-text" style="cursor:pointer;"><?= htmlspecialchars($userName) ?></span>
        <div class="account-dropdown" id="accountDropdown" style="display: none; position: absolute; top: 100%; right: 0; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin-top: 8px; min-width: 150px; z-index: 1000;">
            <a href="?page=me" style="display: block; padding: 12px 16px; text-decoration: none; color: #1E1E1E; border-bottom: 1px solid #E0E0E0;">Кабинет</a>
            <a href="?action=logout" style="display: block; padding: 12px 16px; text-decoration: none; color: #1E1E1E;">Выйти</a>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const account = document.getElementById('userAccount');
        const dropdown = document.getElementById('accountDropdown');
        if (account && dropdown) {
            account.addEventListener('click', function(e) {
                if (e.target.closest('.account-dropdown')) {
                    e.stopPropagation();
                    return;
                }
                window.location.href = '?page=me';
            });
            document.addEventListener('click', function(e) {
                if (!account.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        }
    });
    </script>
    <?php else: ?>
    <div class="account" onclick="location.href='?page=auth'" style="cursor:pointer">
        <div class="account-icon"></div>
        <span class="account-text">Войти</span>
    </div>
    <?php endif; ?>
    </nav>
</header>