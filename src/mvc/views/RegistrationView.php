<?php
require_once __DIR__ . '/../View.php';

class RegistrationView extends View {
    public function render() {
        ?>
<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Регистрация</h1>

            <form class="auth-form" id="registrationForm">

                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="last_name" id="reg-lastname" placeholder="Фамилия" required>
                    </div>

                    <div class="form-group">
                        <input type="text" name="first_name" id="reg-firstname" placeholder="Имя" required>
                    </div>
                </div>

                <div class="form-group">
                    <input type="text" name="email" id="reg-email" placeholder="E-mail или номер телефона" required>
                </div>

                <div class="form-group">
                    <input type="password" name="password" id="reg-password" placeholder="Пароль" required>
                </div>

                <div class="form-group">
                    <input type="password" name="confirm_password" id="reg-confirm-password" placeholder="Подтвердите пароль" required>
                </div>

                <div class="logo">
                    <span class="logo-text">Trav<span class="logo-text-highlight">ly</span></span>
                    <div class="logo-icon"></div>
                </div>

                <button type="submit" class="submit-btn">Зарегистрироваться</button>
            </form>

            <div class="auth-footer">
                <p>Уже есть аккаунт? <a href="?page=auth">Войти</a></p>
            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="script/validation.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = form.querySelector('.submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Регистрация...';
        
        const formData = new FormData(form);
        formData.append('action', 'register');
        
        try {
            const response = await fetch('?action=register', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = '/';
            } else {
                alert(result.message || 'Ошибка регистрации');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        } catch (error) {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при регистрации');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
});
</script>
        <?php
    }
}

