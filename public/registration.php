<?php
$pageTitle = 'Travly — Регистрация';
require_once 'layout/header.php';
?>

<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Регистрация</h1>

            <div class="user-type-selector">
                <button class="tourist">Как турист</button>
                <button class="extra-button organizator">Как организатор</button>
            </div>

            <button class="submit-btn" onclick="location.href='account.php'"> Продолжить с VK </button>

            <div class="divider">Или</div>

            <form class="auth-form">

                <div class="form-row">
                    <div class="form-group">
                        <input type="text" id="reg-lastname" placeholder="Фамилия" required>
                    </div>

                    <div class="form-group">
                        <input type="text" id="reg-firstname" placeholder="Имя" required>
                    </div>
                </div>

                <div class="form-group">
                    <input type="email" id="reg-email" placeholder="E-mail или номер телефона" required>
                </div>

                <div class="form-group">
                    <input type="password" id="reg-password" placeholder="Пароль" required>
                </div>

                <div class="form-group">
                    <input type="password" id="reg-confirm-password" placeholder="Подтвердите пароль" required>
                </div>

                <div class="logo">
                    <span class="logo-text">Trav<span class="logo-text-highlight">ly</span></span>
                    <div class="logo-icon"></div>
                </div>

                <button type="submit" class="submit-btn">Зарегистрироваться</button>
            </form>

            <div class="auth-footer">
                <p>Уже есть аккаунт? <a href='auth.php'>Войти</a></p>
            </div>
        </div>
    </div>
</main>

<?php require_once 'layout/footer.php'; ?>
