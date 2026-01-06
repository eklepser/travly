<?php
require_once __DIR__ . '/../core/View.php';

class AuthView extends View {
    public function render() {
        ?>
<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Вход</h1>

            <form class="auth-form" id="loginForm">
                <div class="form-group">
                    <input type="text" name="email" id="auth-email" placeholder="E-mail или номер телефона" required>
                </div>

                <div class="form-group">
                    <input type="password" name="password" id="auth-password" placeholder="Пароль" required>
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
                <p>Нет аккаунта? <a href="?page=registration">Зарегистрироваться</a></p>
            </div>

        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="script/validation.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = form.querySelector('.submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Вход...';
        
        const formData = new FormData(form);
        formData.append('action', 'login');
        
        try {
            const response = await fetch('?action=login', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = '/';
            } else {
                alert(result.message || 'Ошибка входа');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        } catch (error) {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при входе');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
});
</script>
        <?php
    }
}

