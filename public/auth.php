<?php
$pageTitle = 'Travly — Вход';
require_once 'layout/header.php';
?>

<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Вход</h1>
            <button class="submit-btn" onclick="location.href='account.php'"> Продолжить с VK </button>

            <div class="divider">
                <span>Или</span>
            </div>

            <form class="auth-form">
                <div class="form-group">
                    <input type="text" id="auth-email" placeholder="E-mail или номер телефона" required>
                </div>

                <div class="form-group">
                    <input type="password" id="auth-password" placeholder="Пароль" required>
                </div>

                <div class="form-options">
                    <a href="#" class="forgot-password">Забыли пароль?</a>
                </div>

                <div class="logo">
                    <span class="logo-text">Trav<span class="logo-text-highlight">ly</span></span>
                    <div class="logo-icon"></div>
                </div>

                <button type="submit" class="submit-btn">Войти</button>
            </form>

            <div class="auth-footer">
                <p>Нет аккаунта? <a onclick="location.href='registration.php'">Зарегистрироваться</a></p>
            </div>

        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="script/validation.js"></script>

<?php require_once 'layout/footer.php'; ?>