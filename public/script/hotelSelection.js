(function () {
    'use strict';

    const roomSelect = document.getElementById('room-type');
    if (!roomSelect) return;

    const BASE_PRICE_PER_PERSON = 60_000;
    const state = {
        adults: parseInt(document.querySelector('[data-counter="adults"]')?.textContent) || 2,
        children: parseInt(document.querySelector('[data-counter="children"]')?.textContent) || 0,
        roomPrice: 0,
        extrasPrice: 0
    };

    const el = {
        adults: document.querySelector('[data-counter="adults"]'),
        children: document.querySelector('[data-counter="children"]'),
        buttons: document.querySelectorAll('.counter-btn'),
        checkboxes: document.querySelectorAll('.service-checkbox input[type="checkbox"]'),
        total: document.getElementById('total-tourists'),
        base: document.getElementById('base-cost'),
        extras: document.getElementById('extras-cost'),
        totalCost: document.getElementById('total-cost')
    };

    function fmt(v) { return `${v.toLocaleString('ru-RU')} ₽`; }
    function update() {
        const p = state.adults + state.children;
        const base = p * BASE_PRICE_PER_PERSON;
        const extra = state.roomPrice + state.extrasPrice;
        const total = base + extra;

        if (el.total) el.total.textContent = p;
        if (el.base) el.base.textContent = fmt(base);
        if (el.extras) el.extras.textContent = `${extra >= 0 ? '+' : ''}${fmt(Math.abs(extra))}`;
        if (el.totalCost) el.totalCost.textContent = fmt(total);
    }

    el.buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const t = btn.dataset.type;
            if (btn.dataset.action === 'increase') {
                state[t]++;
            } else if (btn.dataset.action === 'decrease' && state[t] > (t === 'adults' ? 1 : 0)) {
                state[t]--;
            }
            if (el[t]) el[t].textContent = state[t];
            update();
        });
    });

    roomSelect.addEventListener('change', () => {
        state.roomPrice = +roomSelect.value || 0;
        update();
    });

    el.checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const p = +cb.dataset.price || 0;
            state.extrasPrice += cb.checked ? p : -p;
            update();
        });
    });

    update();
})();