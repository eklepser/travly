document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('button[data-page]');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const page = button.getAttribute('data-page');
            loadPage(page);
        });
    });

    if (document.getElementById('page-content').innerHTML.trim() === '') {
        loadPage('main.html');
    }
});

function loadPage(pagePath) {
    fetch(pagePath)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network error: ' + response.status);
            }
            return response.text();
        })
        .then(data => {
            document.getElementById('page-content').innerHTML = data;
        })
        .catch(error => {
            console.error('Content loading error:', error);
            document.getElementById('page-content').innerHTML = '<p>Не удалось загрузить содержимое страницы.</p>';
        });
}