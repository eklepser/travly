function loadPage(pagePath) {
    fetch(pagePath)
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
            }
        })
        .catch(error => {
            console.error('Content loading error:', error);
            const pageContent = document.getElementById('page-content');
            if (pageContent) {
                pageContent.innerHTML = '<p>Не удалось загрузить содержимое страницы.</p>';
            }
        });
}

// Делегирование кликов на весь документ (или на #page-content)
document.addEventListener('click', (event) => {
    // Ищем ближайший элемент с data-page (включая сам target)
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