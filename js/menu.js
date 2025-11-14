// menu.js
function loadPage(pagePath) {
    return fetch(pagePath)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network error: ' + response.status);
            }
            return response.text();
        })
        .then(data => {
            const pageContent = document.getElementById('page-content');
            if (pageContent) {
                pageContent.innerHTML = data;

                // Вызываем колбэк после загрузки контента
                if (window.onPageContentLoaded) {
                    window.onPageContentLoaded();
                }

                // Инициализируем валидацию для нового контента
                if (window.formValidator) {
                    setTimeout(() => {
                        window.formValidator.setupValidation();
                    }, 50);
                }
            }
            return data;
        })
        .catch(error => {
            console.error('Content loading error:', error);
            const pageContent = document.getElementById('page-content');
            if (pageContent) {
                pageContent.innerHTML = '<p>Не удалось загрузить содержимое страницы.</p>';
            }
            throw error;
        });
}

// Делегирование кликов на весь документ
document.addEventListener('click', (event) => {
    const pageElement = event.target.closest('[data-page]');
    if (pageElement) {
        const page = pageElement.getAttribute('data-page');
        loadPage(page);
        event.preventDefault();
        window.scrollTo(0, 0);
    }
});

// Загрузка главной страницы при старте
document.addEventListener('DOMContentLoaded', () => {
    const pageContent = document.getElementById('page-content');
    if (pageContent && pageContent.innerHTML.trim() === '') {
        loadPage('layout/account.html');
    }
});