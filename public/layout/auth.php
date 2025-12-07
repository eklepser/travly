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
        
        try {
            // Формируем URL правильно, учитывая текущие параметры
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('action', 'login');
            const url = currentUrl.pathname + '?' + currentUrl.searchParams.toString();
            
            console.log('Sending request to:', url);
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            
            // Читаем ответ как текст один раз
            const responseText = await response.text();
            
            if (!response.ok) {
                console.error('Server error:', response.status, responseText);
                showNotification('Ошибка сервера: ' + response.status, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                return;
            }
            
            // Пытаемся распарсить как JSON
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError, 'Response:', responseText);
                showNotification('Ошибка обработки ответа сервера', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                return;
            }
            
            if (result.success) {
                showNotification('Вход выполнен успешно! Перенаправление...', 'success');
                setTimeout(() => {
                    window.location.href = '/';
                }, 1500);
            } else {
                showNotification(result.message || 'Ошибка входа', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        } catch (error) {
            console.error('Fetch error:', error);
            showNotification('Ошибка соединения с сервером: ' + error.message, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
});

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        background: ${type === 'success' ? '#4CAF50' : '#f44336'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>
<style>
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
</style>