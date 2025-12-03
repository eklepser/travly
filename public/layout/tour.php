<?php
require_once '../src/config/database.php';

$pdo = createPDO();
$tour = null;

if ($pdo) {
    $tourId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
    $error = null;
    if ($tourId === false) {
        $error = "Неверный идентификатор тура";
    } elseif (!$tour) {
        $error = "Тур не найден";
    }
    if ($tourId) {
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    t.id,
                    t.country,
                    t.location,
                    t.departure_point,
                    t.departure_date,
                    t.arrival_point,
                    t.arrival_date,
                    t.return_point,
                    t.return_date,
                    t.base_price,
                    t.additional_services,
                    t.image_url,
                    h.name AS hotel_name,
                    h.rating AS hotel_rating,
                    h.max_capacity_per_room,
                    h.beach_description,
                    h.amenities,
                    h.meal_plan
                FROM tours t
                INNER JOIN hotels h ON t.hotel_id = h.id
                WHERE t.id = :id
            ");
            $stmt->execute(['id' => $tourId]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[DB] Tour fetch failed: " . $e->getMessage());
        }
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
                    if (empty($imageUrl)) {
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
                    <button type="button" class="counter-btn" data-action="decrease" data-type="adults">−</button>
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
            <button class="proceed-btn" 
                    onclick="location.href='?page=booking&id=<?= (int) $tour['id'] ?>'">
                Перейти к оформлению
            </button>
        </div>
    </div>
</main>

<script src="script/hotelSelection.js"></script>
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

        document.querySelectorAll('[data-action="increase"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.dataset.type;
                changeCounter(type, +1);
            });
        });

        document.querySelectorAll('[data-action="decrease"]').forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.dataset.type;
                changeCounter(type, -1);
            });
        });

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
        value = Math.max(0, value + delta);
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
})();
</script>