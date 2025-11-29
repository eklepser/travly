// account.js — логика страницы личного кабинета
(function () {
    'use strict';

    // 🔹 Регистрируем инициализатор в глобальном реестре
    if (typeof window.pageModules !== 'undefined') {
        window.pageModules['js/pages/account.js'] = init;
    }

    function init() {
        // === Редактирование профиля ===
        const editBtn = document.getElementById('editToggle');
        if (!editBtn) return; // не на этой странице

        const saveBtn = document.querySelector('.save-btn');
        const cancelBtn = document.querySelector('.cancel-btn');
        const textVals = Array.from(document.querySelectorAll('.text-value'));
        const inputs = Array.from(document.querySelectorAll('.edit-input'));

        const toggleEditMode = (enable) => {
            textVals.forEach(el => el.style.display = enable ? 'none' : 'block');
            inputs.forEach(el => el.style.display = enable ? 'block' : 'none');
            editBtn.style.display = enable ? 'none' : 'inline-block';
            saveBtn.style.display = enable ? 'inline-block' : 'none';
            cancelBtn.style.display = enable ? 'inline-block' : 'none';
        };

        editBtn.addEventListener('click', () => toggleEditMode(true));
        cancelBtn.addEventListener('click', () => toggleEditMode(false));
        saveBtn.addEventListener('click', () => {
            textVals.forEach((el, i) => {
                const val = inputs[i]?.value?.trim();
                if (val) el.textContent = val;
            });
            toggleEditMode(false);
        });

        // === Модальное окно отмены ===
        const modal = document.getElementById('cancelModal');
        if (!modal) return;

        document.querySelectorAll('[data-action="cancel-booking"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                modal.style.display = 'flex';
                modal.dataset.bookingId = btn.closest('.booking-hero')?.dataset.bookingId || 'unknown';
            });
        });

        document.getElementById('cancelNo')?.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        document.getElementById('cancelYes')?.addEventListener('click', () => {
            const id = modal.dataset.bookingId;
            alert(`✅ Бронирование ${id} успешно отменено`);
            modal.style.display = 'none';

            // Опционально: затемнить карточку
            const card = document.querySelector(`.booking-hero[data-booking-id="${id}"]`);
            if (card) card.style.opacity = '0.5';
        });
    }
})();