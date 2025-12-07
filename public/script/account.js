(function () {
    'use strict';

    const editBtn = document.getElementById('editToggle');
    const modal = document.getElementById('cancelModal');
    if (!editBtn && !modal) return;

    if (editBtn) {
        const saveBtn = document.querySelector('.save-btn');
        const cancelBtn = document.querySelector('.cancel-btn');
        const textVals = Array.from(document.querySelectorAll('.text-value'));
        const inputs = Array.from(document.querySelectorAll('.edit-input'));

        const toggleEditMode = (enable) => {
            textVals.forEach(el => el.style.display = enable ? 'none' : 'block');
            inputs.forEach(el => el.style.display = enable ? 'block' : 'none');
            editBtn.style.display = enable ? 'none' : 'flex';
            saveBtn.style.display = enable ? 'flex' : 'none';
            cancelBtn.style.display = enable ? 'flex' : 'none';
        };

        editBtn.addEventListener('click', () => toggleEditMode(true));
        cancelBtn?.addEventListener('click', () => toggleEditMode(false));
        saveBtn?.addEventListener('click', () => {
            textVals.forEach((el, i) => {
                const val = inputs[i]?.value?.trim();
                if (val) el.textContent = val;
            });
            toggleEditMode(false);
        });
    }

    if (modal) {
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
            if (!id || id === 'unknown') {
                alert('Ошибка: не указан ID бронирования');
                modal.style.display = 'none';
                return;
            }
            
            // Отправляем запрос на удаление бронирования
            fetch(`?action=cancel-booking&id=${id}`, {
                method: 'GET'
            })
            .then(async response => {
                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    throw new Error('Ошибка парсинга ответа: ' + text);
                }
                
                if (result.success) {
                    // Удаляем карточку бронирования из DOM
                    const card = document.querySelector(`.booking-hero[data-booking-id="${id}"]`);
                    if (card) {
                        card.style.transition = 'opacity 0.3s';
                        card.style.opacity = '0';
                        setTimeout(() => {
                            card.remove();
                        }, 300);
                    }
                    
                    // Если нет активных бронирований, показываем сообщение
                    const activeBookings = document.querySelectorAll('.tours-section .booking-hero');
                    if (activeBookings.length === 0) {
                        const section = document.querySelector('.tours-section');
                        if (section) {
                            const message = document.createElement('p');
                            message.style.cssText = 'text-align: center; padding: 40px; color: #666;';
                            message.textContent = 'У вас нет активных бронирований';
                            section.appendChild(message);
                        }
                    }
                    
                    alert('✅ Бронирование успешно отменено');
                } else {
                    alert('Ошибка: ' + (result.message || 'Не удалось отменить бронирование'));
                }
                
                modal.style.display = 'none';
            })
            .catch(error => {
                console.error('Ошибка при отмене бронирования:', error);
                alert('Ошибка сети: ' + error.message);
                modal.style.display = 'none';
            });
        });
    }
})();