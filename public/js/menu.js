// menu.js — загрузка страниц из public/layout/
(function() {
    'use strict';

    const container = document.getElementById('page-content');
    if (!container) return;

    // 🖱️ Обработка кликов по data-page (делегирование)
    document.addEventListener('click', function(e) {
        const target = e.target.closest('[data-page]');
        if (target) {
            const path = target.getAttribute('data-page');
            loadPage(path);
            e.preventDefault();
            window.scrollTo(0, 0);
        }
    });

    // 🔄 Обработка F5, прямого URL и кнопок "Назад/Вперёд"
    function loadFromHash() {
        const hash = location.hash.slice(1).trim();
        loadPage(hash || 'layout/main.html');
    }

    window.addEventListener('hashchange', loadFromHash);
    window.addEventListener('load', loadFromHash);

    // 🔁 Основной метод загрузки
    window.loadPage = async function(path) {
        if (!path) return;

        // Показ загрузки
        container.innerHTML = `
            <div style="padding: 40px; text-align: center;">
                <p>Загрузка...</p>
            </div>
        `;

        // ⚡️ Формируем путь: layout/X.html → layout/X.html (остаётся как есть)
        let url = path
            .replace(/^\.?\//, '')   // убираем ./
            .replace(/^\/+/, '');    // убираем ведущие /

        console.log('➡️ Запрос к:', url);

        try {
            const res = await fetch(url);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const html = await res.text();
            container.innerHTML = html;

            // Обновляем title (опционально)
            updatePageTitle(path);

        } catch (err) {
            console.error('❌ Ошибка загрузки:', err);
            container.innerHTML = `
                <div style="padding: 40px; text-align: center; color: #c00;">
                    <h3>Ошибка загрузки</h3>
                    <p>Не удалось загрузить: <code>${url}</code></p>
                    <button onclick="loadPage('layout/main.html')" 
                            style="margin-top: 10px; padding: 8px 16px;">
                        На главную
                    </button>
                </div>
            `;
        }
    };

    // 🏷️ Обновление title (опционально)
    function updatePageTitle(path) {
        const titles = {
            'layout/main.html': 'Travly — Главная',
            'layout/search.html': 'Travly — Поиск тура',
            'layout/about.html': 'Travly — О нас',
            'layout/help.html': 'Travly — Помощь',
            'layout/auth.html': 'Travly — Вход',
            'layout/registration.html': 'Travly — Регистрация',
            'layout/hotel-selection.html': 'Travly — Выбор отеля',
            'layout/booking.html': 'Travly — Бронирование'
        };
        document.title = titles[path] || 'Travly';
    }

    // Экспорт для отладки (не обязательно)
    window.pageLoader = { loadPage };
})();