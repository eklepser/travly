// navigation.js — загрузка страниц и их JS-логики
(function () {
    'use strict';

    const container = document.getElementById('page-content');
    if (!container) return;

    // 🖱️ Делегирование кликов по data-page
    document.addEventListener('click', function (e) {
        const target = e.target.closest('[data-page]');
        if (target) {
            const path = target.getAttribute('data-page');
            loadPage(path);
            e.preventDefault();
            window.scrollTo(0, 0);
        }
    });

    // 🔄 Загрузка при старте и изменении хеша
    function loadFromHash() {
        const hash = location.hash.slice(1).trim();
        loadPage(hash || 'layout/main.html');
    }
    window.addEventListener('hashchange', loadFromHash);
    window.addEventListener('load', loadFromHash);

    // 🔁 Основной метод загрузки
    window.loadPage = async function (path) {
        if (!path) return;

        container.innerHTML = `<div style="padding:40px;text-align:center"><p>Загрузка...</p></div>`;

        // Формируем URL (без ./ и //)
        let url = path.replace(/^\.?\//, '').replace(/^\/+/, '');

        // 🔹 Добавляем timestamp для страниц с личными данными (защита от кэша)
        const noCachePages = ['account', 'booking'];
        if (noCachePages.some(p => path.includes(p))) {
            url += (url.includes('?') ? '&' : '?') + 't=' + Date.now();
        }

        console.log('➡️ Запрос к:', url);

        try {
            // 🔹 Запрещаем кэширование на уровне fetch
            const res = await fetch(url, {
                cache: 'no-store',
                headers: { 'Pragma': 'no-cache' }
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const html = await res.text();
            container.innerHTML = html;

            // 🔹 Определяем JS-файл для страницы
            const jsPath = getJsPathForPage(path);
            if (jsPath) {
                await loadScript(jsPath);
                // Запускаем инициализацию, если зарегистрирована
                if (window.pageModules?.[jsPath]) {
                    window.pageModules[jsPath]();
                }
            }

            updatePageTitle(path);
        } catch (err) {
            console.error('❌ Ошибка:', err);
            container.innerHTML = `
                <div style="padding:40px;text-align:center;color:#c00">
                    <h3>Ошибка загрузки</h3>
                    <p>Не удалось загрузить: <code>${url}</code></p>
                    <button onclick="loadPage('layout/main.html')" style="margin-top:10px;padding:8px 16px">
                        На главную
                    </button>
                </div>
            `;
        }
    };

    // 🗺️ Сопоставление страницы → JS-файла
    function getJsPathForPage(path) {
        if (path.includes('hotel-selection')) return 'js/pages/hotelSelection.js';
        if (path.includes('account')) return 'js/pages/account.js';
        return null;
    }

    // ⏳ Загрузка скрипта (без дублей)
    function loadScript(src) {
        return new Promise((resolve, reject) => {
            // Проверяем, не загружен ли уже
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }
            const script = document.createElement('script');
            script.src = src;
            script.defer = true;
            script.onload = resolve;
            script.onerror = () => reject(new Error(`Не удалось загрузить ${src}`));
            document.head.appendChild(script);
        });
    }

    // 🏷️ Обновление заголовка
    function updatePageTitle(path) {
        const titles = {
            'layout/main.html': 'Travly — Главная',
            'layout/account.html': 'Travly — Личный кабинет',
            'layout/hotel-selection.html': 'Travly — Выбор отеля',
            'layout/booking.html': 'Travly — Бронирование'
        };
        document.title = titles[path] || 'Travly';
    }

    // 📦 Реестр инициализаторов (заполняется из account.js / hotelSelection.js)
    window.pageModules = window.pageModules || {};
})();