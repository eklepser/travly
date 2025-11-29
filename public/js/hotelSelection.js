// public/js/hotelSelection.js
(function () {
    'use strict';

    // Инициализация — вызывается из navigation.js ПОСЛЕ загрузки HTML
    window.initHotelSelectionPage = function () {
        const roomSelect = document.getElementById('room-type');
        if (!roomSelect) return; // не на этой странице

        const BASE_PRICE_PER_PERSON = 60_000;

        const state = {
            adults: parseInt(document.querySelector('[data-counter="adults"]')?.textContent) || 2,
            children: parseInt(document.querySelector('[data-counter="children"]')?.textContent) || 0,
            roomPrice: 0,
            extrasPrice: 0
        };

        // Элементы
        const counterValues = {
            adults: document.querySelector('[data-counter="adults"]'),
            children: document.querySelector('[data-counter="children"]')
        };
        const counterButtons = document.querySelectorAll('.counter-btn');
        const checkboxes = document.querySelectorAll('.service-checkbox input[type="checkbox"]');
        const touristsTotalEl = document.getElementById('total-tourists');
        const baseCostEl = document.getElementById('base-cost');
        const extrasCostEl = document.getElementById('extras-cost');
        const totalCostEl = document.getElementById('total-cost');

        // Функции
        function updateDisplay() {
            if (counterValues.adults) counterValues.adults.textContent = state.adults;
            if (counterValues.children) counterValues.children.textContent = state.children;
            if (touristsTotalEl) touristsTotalEl.textContent = state.adults + state.children;
        }

        function formatCurrency(value) {
            return `${value.toLocaleString('ru-RU')} ₽`;
        }

        function calculate() {
            const totalPeople = state.adults + state.children;
            const base = totalPeople * BASE_PRICE_PER_PERSON;
            const total = base + state.roomPrice + state.extrasPrice;

            if (baseCostEl) baseCostEl.textContent = formatCurrency(base);
            if (extrasCostEl) extrasCostEl.textContent = `+${formatCurrency(state.extrasPrice)}`;
            if (totalCostEl) totalCostEl.textContent = formatCurrency(total);
        }

        // Обработчики
        counterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.dataset.type;
                const action = btn.dataset.action;

                if (action === 'increase') {
                    state[type]++;
                } else if (action === 'decrease' && state[type] > (type === 'adults' ? 1 : 0)) {
                    state[type]--;
                }

                updateDisplay();
                calculate();
            });
        });

        roomSelect.addEventListener('change', () => {
            state.roomPrice = parseInt(roomSelect.value) || 0;
            calculate();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const price = parseInt(cb.dataset.price) || 0;
                state.extrasPrice += cb.checked ? price : -price;
                calculate();
            });
        });

        // Первый расчёт
        updateDisplay();
        calculate();
    };
})();