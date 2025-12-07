<?php
require_once __DIR__ . '/../../src/repositories/TourRepository.php';

$tourRepository = new TourRepository();
$tourId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
$tour = null;
$error = null;

if ($tourId === false) {
    $error = "Неверный идентификатор тура";
} else {
    $tour = $tourRepository->findById($tourId);
    if (!$tour) {
        $error = "Тур не найден";
    }
}

$departDate = (new DateTime($tour['departure_date']))->format('d.m.Y');
$returnDate = (new DateTime($tour['return_date']))->format('d.m.Y');

$services = !empty($tour['additional_services']) 
    ? json_decode($tour['additional_services'], true) 
    : [];

$amenities = $tour['amenities'] ?? [];
if (is_string($amenities) && $amenities !== '') {
    $amenities = trim($amenities, '{}');
    $amenities = $amenities === '' ? [] : array_map('trim', explode(',', $amenities));
}

$pageTitle = 'Travly — Выбор отеля';
?>

<main class="selection-main">
    <section class="selection-hero">
        <div class="selection-header">
            <h1>Выбор отеля и условий</h1>
            <h2>
                <?= htmlspecialchars($tour['country']) ?>, 
                <?= htmlspecialchars($tour['location']) ?>. 
                <?= $departDate ?> – <?= $returnDate ?>
            </h2>
        </div>

        <div class="selection-content">
            <div class="hotel-left-section">
                <div class="booking-card">
                    <?php
                    $imageUrl = $tour['image_url'] ?? '';
                    // Проверяем, является ли путь URL из интернета
                    $isExternalUrl = !empty($imageUrl) && (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://'));
                    
                    if (empty($imageUrl)) {
                        $imageUrl = 'resources/images/tours/default_tour.png';
                    } elseif (!$isExternalUrl && !file_exists(__DIR__ . '/../../' . $imageUrl)) {
                        // Для локальных путей проверяем существование файла
                        $imageUrl = 'resources/images/tours/default_tour.png';
                    }
                    ?>
                    <div class="card-image" 
                        style="background-image: url('<?= htmlspecialchars($imageUrl) ?>'); 
                                width: 100%; height: 100%; border-radius: 8px;">
                    </div>
                </div>
                <button class="extra-button" onclick="location.href='search.php'">Сменить отель</button>
            </div>

            <div class="hotel-info">
                <h3>Информация об отеле</h3>
                <div class="info-item">
                    <span class="info-label">Название отеля:</span>
                    <span class="info-value"><?= htmlspecialchars($tour['hotel_name']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Категория:</span>
                    <span class="info-value">
                        <?= str_repeat('★', (int) floor((float) $tour['hotel_rating'])) ?> 
                        (<?= number_format((float) $tour['hotel_rating'], 1) ?> звезды)
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Расположение:</span>
                    <span class="info-value">
                        <?= htmlspecialchars($tour['location']) ?>, 
                        <?= htmlspecialchars($tour['arrival_point']) ?> (аэропорт)
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Пляж:</span>
                    <span class="info-value">
                        <?= htmlspecialchars($tour['beach_description'] ?? 'Информация отсутствует') ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Услуги:</span>
                    <span class="info-value">
                        <?= !empty($amenities) 
                            ? htmlspecialchars(implode(', ', $amenities)) 
                            : 'Информация отсутствует' ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Питание:</span>
                    <span class="info-value">
                        <?= htmlspecialchars($tour['meal_plan'] ?? 'Информация отсутствует') ?>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="tourists-section">
        <h3>Количество участников</h3>
        <div class="tourists-row">
            <div class="tourist-group">
                <label>Взрослые</label>
                <div class="counter-group">
                    <button type="button" class="counter-btn" data-action="decrease" data-type="adults" id="decreaseAdultsBtn">−</button>
                    <span class="counter-value" data-counter="adults">2</span>
                    <button type="button" class="counter-btn" data-action="increase" data-type="adults">+</button>
                </div>
            </div>
            <div class="tourist-group">
                <label>Дети (до 12 лет)</label>
                <div class="counter-group">
                    <button type="button" class="counter-btn" data-action="decrease" data-type="children">−</button>
                    <span class="counter-value" data-counter="children">0</span>
                    <button type="button" class="counter-btn" data-action="increase" data-type="children">+</button>
                </div>
            </div>
        </div>
    </section>

    <section class="extra-services-section">
        <h3>Дополнительные услуги</h3>

        <div class="services-row">
            <div class="service-group">
                <label>Тип номера</label>
                <div class="input-field">
                    <select class="form-select" id="room-type">
                        <option value="0">Стандартный номер</option>
                        <option value="15000">Люкс с видом на море (+15 000 ₽)</option>
                        <option value="20000">Семейный номер (+20 000 ₽)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="services-row">
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $service): ?>
                    <div class="service-group">
                        <label class="service-checkbox">
                            <input type="checkbox" data-price="<?= (int) ($service['price'] ?? 0) ?>">
                            <?= htmlspecialchars($service['service'] ?? 'Доп. услуга') ?> 
                            (+<?= number_format((int) ($service['price'] ?? 0), 0, ' ', ' ') ?> ₽)
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="service-group">
                    <label class="service-checkbox">
                        <input type="checkbox" data-price="5000">
                        Трансфер из аэропорта (+5 000 ₽)
                    </label>
                </div>
                <div class="service-group">
                    <label class="service-checkbox">
                        <input type="checkbox" data-price="3000">
                        Медицинская страховка (+3 000 ₽)
                    </label>
                </div>
                <div class="service-group">
                    <label class="service-checkbox">
                        <input type="checkbox" data-price="8000">
                        Обзорная экскурсия (+8 000 ₽)
                    </label>
                </div>
                <div class="service-group">
                    <label class="service-checkbox">
                        <input type="checkbox" data-price="12000">
                        SPA-пакет на 2 персоны (+12 000 ₽)
                    </label>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="selection-summary">
        <div class="cost-breakdown">
            <div class="cost-item">
                <span>Базовая стоимость (на 1 чел.):</span>
                <span><?= number_format((int) $tour['base_price'], 0, ' ', ' ') ?> ₽</span>
            </div>
            <div class="cost-item">
                <span>Количество туристов:</span>
                <span id="total-tourists">2 (взрослые), 0 (дети)</span>
            </div>
            <div class="cost-item">
                <span>Базовая стоимость тура:</span>
                <span id="base-cost">120 000 ₽</span>
            </div>
            <div class="cost-item">
                <span>Номер и допуслуги:</span>
                <span id="extras-cost">+0 ₽</span>
            </div>
            <div class="cost-total">
                <span>Итого к оплате:</span>
                <span id="total-cost">120 000 ₽</span>
            </div>
        </div>

        <div class="summary-buttons">
            <button class="proceed-btn" id="proceedToBookingBtn">
                Перейти к оформлению
            </button>
        </div>
    </div>
</main>

<script>
(function() {
    const BASE_PRICE_PER_PERSON = <?= (int) $tour['base_price'] ?>;
    let selectedRoomPrice = 0;
    let selectedExtrasPrice = 0;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        const adultsCounter = document.querySelector('[data-counter="adults"]');
        const childrenCounter = document.querySelector('[data-counter="children"]');

        if (!adultsCounter || !childrenCounter) {
            console.error('[Tour] Счётчики не найдены');
            return;
        }
        
        const adultsValue = parseInt(adultsCounter.textContent) || 1;
        if (adultsValue < 1) {
            adultsCounter.textContent = '1';
        }

        const increaseButtons = document.querySelectorAll('[data-action="increase"]');
        const decreaseButtons = document.querySelectorAll('[data-action="decrease"]');
        
        if (increaseButtons.length === 0 || decreaseButtons.length === 0) {
            return;
        }

        if (increaseButtons[0].hasAttribute('data-handler-attached')) {
            return;
        }

        increaseButtons.forEach(btn => {
            btn.setAttribute('data-handler-attached', 'true');
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                const type = btn.dataset.type;
                changeCounter(type, +1);
            }, { once: false });
        });

        decreaseButtons.forEach(btn => {
            btn.setAttribute('data-handler-attached', 'true');
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                const type = btn.dataset.type;
                const currentValue = parseInt(document.querySelector(`[data-counter="${type}"]`)?.textContent || '0');
                if (type === 'adults' && currentValue <= 1) {
                    return;
                }
                changeCounter(type, -1);
            }, { once: false });
        });
        
        const decreaseAdultsBtn = document.querySelector('[data-action="decrease"][data-type="adults"]');
        if (decreaseAdultsBtn) {
            const updateAdultsButtonState = () => {
                const adultsValue = parseInt(document.querySelector('[data-counter="adults"]')?.textContent || '1');
                decreaseAdultsBtn.disabled = adultsValue <= 1;
                decreaseAdultsBtn.style.opacity = adultsValue <= 1 ? '0.5' : '1';
                decreaseAdultsBtn.style.cursor = adultsValue <= 1 ? 'not-allowed' : 'pointer';
            };
            updateAdultsButtonState();
            const adultsCounter = document.querySelector('[data-counter="adults"]');
            if (adultsCounter) {
                const observer = new MutationObserver(updateAdultsButtonState);
                observer.observe(adultsCounter, { childList: true, characterData: true, subtree: true });
            }
        }

        const roomSelect = document.getElementById('room-type');
        if (roomSelect) {
            roomSelect.addEventListener('change', () => {
                selectedRoomPrice = parseInt(roomSelect.value) || 0;
                recalculate();
            });
        }

        document.querySelectorAll('.service-checkbox input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', recalculate);
        });

        recalculate();
    }

    function changeCounter(type, delta) {
        const counter = document.querySelector(`[data-counter="${type}"]`);
        let value = parseInt(counter.textContent) || 0;
        if (type === 'adults') {
            value = Math.max(1, value + delta);
        } else {
            value = Math.max(0, value + delta);
        }
        counter.textContent = value;
        recalculate();
    }

    function recalculate() {
        const adults = parseInt(document.querySelector('[data-counter="adults"]')?.textContent) || 0;
        const children = parseInt(document.querySelector('[data-counter="children"]')?.textContent) || 0;

        document.getElementById('total-tourists').textContent = 
            `${adults} (взрослые), ${children} (дети)`;

        const baseCost = adults * BASE_PRICE_PER_PERSON + children * BASE_PRICE_PER_PERSON * 0.5;

        selectedExtrasPrice = 0;
        document.querySelectorAll('.service-checkbox input[type="checkbox"]:checked').forEach(cb => {
            selectedExtrasPrice += parseInt(cb.dataset.price) || 0;
        });

        const total = baseCost + selectedRoomPrice + selectedExtrasPrice;

        const formatter = new Intl.NumberFormat('ru-RU', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });

        document.getElementById('base-cost').textContent = 
            `${formatter.format(baseCost)} ₽`;
        document.getElementById('extras-cost').textContent = 
            `+${formatter.format(selectedRoomPrice + selectedExtrasPrice)} ₽`;
        document.getElementById('total-cost').textContent = 
            `${formatter.format(total)} ₽`;
    }

    function getSelectedServices() {
        const selectedServices = [];
        
        // Получаем выбранные чекбоксы услуг
        document.querySelectorAll('.service-checkbox input[type="checkbox"]:checked').forEach(cb => {
            const label = cb.closest('label');
            if (label) {
                const serviceText = label.textContent.trim();
                const price = parseInt(cb.dataset.price) || 0;
                // Извлекаем название услуги (убираем цену)
                const serviceName = serviceText.replace(/\s*\(\+\d+[\s₽]*\)\s*$/, '').trim();
                selectedServices.push({
                    service: serviceName,
                    price: price
                });
            }
        });
        
        return selectedServices;
    }

    const proceedBtn = document.getElementById('proceedToBookingBtn');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', function() {
            const adults = parseInt(document.querySelector('[data-counter="adults"]')?.textContent) || 2;
            const children = parseInt(document.querySelector('[data-counter="children"]')?.textContent) || 0;
            const tourId = <?= (int) $tour['id'] ?>;
            const roomType = document.getElementById('room-type');
            const roomPrice = parseInt(roomType?.value) || 0;
            const selectedServices = getSelectedServices();
            
            // Сохраняем данные в сессию через AJAX
            const formData = new FormData();
            formData.append('action', 'save-booking-data');
            formData.append('tour_id', tourId);
            formData.append('adults', adults);
            formData.append('children', children);
            formData.append('room_price', roomPrice);
            formData.append('services', JSON.stringify(selectedServices));
            
            fetch('?action=save-booking-data', {
                method: 'POST',
                body: formData
            })
            .then(() => {
                window.location.href = `?page=booking&id=${tourId}&adults=${adults}&children=${children}`;
            })
            .catch(err => {
                console.error('Ошибка сохранения данных:', err);
                // Переходим даже при ошибке
                window.location.href = `?page=booking&id=${tourId}&adults=${adults}&children=${children}`;
            });
        });
    }
})();
</script>