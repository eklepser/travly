<?php
require_once '../src/config/database.php';

$pdo = createPDO();
$tour = null;

if ($pdo) {
    $tourId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
    if ($tourId === false) {
        die("Неверный идентификатор тура");
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
                    h.max_capacity_per_room
                FROM tours t
                INNER JOIN hotels h ON t.hotel_id = h.id
                WHERE t.id = :id
            ");
            $stmt->execute(['id' => $tourId]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo("[DB] Tour fetch failed: " . $e->getMessage());
        }
    }
}
if (!$tour) {
    http_response_code(404);
    die('Тур не найден');
}

// Форматируем даты
$departDate = (new DateTime($tour['departure_date']))->format('d.m.Y');
$returnDate = (new DateTime($tour['return_date']))->format('d.m.Y');

// Заголовок страницы
$pageTitle = "Travly — {$tour['country']}, {$tour['location']}";
require_once 'layout/header.php';
?>

<main class="selection-main">
    <section class="selection-hero">
        <div class="selection-header">
            <h1>Выбор условий по туру</h1>
            <h2>
                <?= htmlspecialchars($tour['country']) ?>, 
                <?= htmlspecialchars($tour['location']) ?>. 
                <?= $departDate ?> – <?= $returnDate ?>
            </h2>
        </div>

        <div class="selection-content">
            <div class="hotel-left-section">
                <!-- Карточка бронирования — пока пустая, можно добавить позже -->
                <div class="booking-card">
                  
                </div>
                <button class="extra-button" onclick="location.href='search.php'">Сменить тур</button>
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
                        <?= str_repeat('★', (int)floor((float)$tour['hotel_rating'])) ?> 
                        (<?= number_format((float)$tour['hotel_rating'], 1) ?> звезды)
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
                    <span class="info-label">Макс. гостей в номере:</span>
                    <span class="info-value"><?= (int)$tour['max_capacity_per_room'] ?> человек</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Вылет:</span>
                    <span class="info-value">
                        <?= htmlspecialchars($tour['departure_point']) ?> → 
                        <?= htmlspecialchars($tour['arrival_point']) ?>, 
                        <?= $departDate ?>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Количество туристов -->
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

    <!-- Дополнительные услуги — из JSON -->
    <section class="extra-services-section">
        <h3>Дополнительные услуги</h3>

        <?php
        // Допуслуги из JSON (если есть)
        $services = [];
        if (!empty($tour['additional_services'])) {
            $services = json_decode($tour['additional_services'], true) ?: [];
        }
        ?>

        <div class="services-row">
            <div class="service-group">
                <label>Тип номера</label>
                <div class="input-field">
                    <select class="form-select" id="room-type">
                        <option value="0">Стандартный номер</option>
                        <option value="15000">Люкс (+15 000 ₽)</option>
                        <option value="20000">Семейный номер (+20 000 ₽)</option>
                    </select>
                </div>
            </div>
        </div>

        <?php if (!empty($services)): ?>
        <div class="services-row">
            <?php foreach ($services as $service): ?>
            <div class="service-group">
                <label class="service-checkbox">
                    <input type="checkbox" data-price="<?= (int)($service['price'] ?? 0) ?>">
                    <?= htmlspecialchars($service['service'] ?? 'Доп. услуга') ?> 
                    (+<?= number_format((int)($service['price'] ?? 0), 0, ',', ' ') ?> ₽)
                </label>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="no-services">Нет дополнительных услуг для этого тура.</p>
        <?php endif; ?>
    </section>

    <!-- Итог -->
    <div class="selection-summary">
        <div class="cost-breakdown">
            <div class="cost-item">
                <span>Базовая стоимость (на 1 чел.):</span>
                <span><?= number_format((int)$tour['base_price'], 0, '', ' ') ?> ₽</span>
            </div>
            <div class="cost-item">
                <span>Количество туристов:</span>
                <span id="total-tourists">2</span>
            </div>
            <div class="cost-item">
                <span>Базовая стоимость тура:</span>
                <span id="base-cost">120 000 ₽</span>
            </div>
            <div class="cost-item">
                <span>Доп. услуги и номер:</span>
                <span id="extras-cost">+0 ₽</span>
            </div>
            <div class="cost-total">
                <span>Итого к оплате:</span>
                <span id="total-cost">120 000 ₽</span>
            </div>
        </div>

        <div class="summary-buttons">
            <!-- Передаём tour_id дальше -->
            <button class="proceed-btn" 
                    onclick="location.href='booking.php?tour_id=<?= (int)$tour['id'] ?>'">
                Перейти к оформлению
            </button>
        </div>
    </div>
</main>

<script src="script/hotelSelection.js"></script>
<script>
    // Передаём базовую цену в JS для расчётов
    const BASE_PRICE_PER_PERSON = <?= (int)$tour['base_price'] ?>;
</script>

<?php require_once 'layout/footer.php'; ?>