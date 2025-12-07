<?php
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Загружаем данные пользователя
$user = null;
$lastName = '';
$firstName = '';
$phone = '—';
$email = '—';
$registrationDate = '—';
$activeBookings = [];
$pastBookings = [];

if (isset($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/../../src/repositories/UserRepository.php';
        require_once __DIR__ . '/../../src/repositories/BookingRepository.php';
        
        $userRepo = new UserRepository();
        $user = $userRepo->findById($_SESSION['user_id']);
        
        if ($user) {
            // Разбираем full_name на фамилию и имя
            $nameParts = explode(' ', trim($user['full_name']), 2);
            $lastName = $nameParts[0] ?? '';
            $firstName = $nameParts[1] ?? '';
            
            $phone = $user['phone'] ?? '—';
            $email = $user['email'] ?? '—';
            
            // Форматируем дату регистрации
            if (!empty($user['created_at'])) {
                $date = new DateTime($user['created_at']);
                $registrationDate = $date->format('d.m.Y');
            }
        }
        
        // Загружаем туры пользователя
        $bookingRepo = new BookingRepository();
        $bookings = $bookingRepo->getUserBookings($_SESSION['user_id']);
        $activeBookings = $bookings['active'] ?? [];
        $pastBookings = $bookings['past'] ?? [];
    } catch (Exception $e) {
        error_log("[me.php] Error loading user data: " . $e->getMessage());
        // Продолжаем выполнение с пустыми данными
    }
}

// Функция для форматирования даты
function formatDate($dateStr, $includeTime = false) {
    if (empty($dateStr)) return '—';
    try {
        $date = new DateTime($dateStr);
        $months = [
            1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
            5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
            9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
        ];
        
        if ($includeTime) {
            $month = (int)$date->format('n');
            $monthName = $months[$month] ?? '';
            return $date->format('Y год, d') . ' ' . $monthName . ', ' . $date->format('H:i') . ' (МСК)';
        }
        return $date->format('d.m.Y');
    } catch (Exception $e) {
        return $dateStr;
    }
}

// Функция для расчета длительности тура
function calculateNights($arrivalDate, $returnDate) {
    if (empty($arrivalDate) || empty($returnDate)) return '—';
    try {
        $arrival = new DateTime($arrivalDate);
        $return = new DateTime($returnDate);
        $diff = $arrival->diff($return);
        $nights = $diff->days;
        return $nights . ' ' . getNightWord($nights);
    } catch (Exception $e) {
        return '—';
    }
}

function getNightWord($nights) {
    $lastDigit = $nights % 10;
    $lastTwoDigits = $nights % 100;
    
    if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
        return 'ночей';
    }
    
    if ($lastDigit == 1) {
        return 'ночь';
    } elseif ($lastDigit >= 2 && $lastDigit <= 4) {
        return 'ночи';
    } else {
        return 'ночей';
    }
}

// Функция для форматирования цены
function formatPrice($price) {
    return number_format((float)$price, 0, ',', ' ') . ' рублей';
}

// Функция для получения информации о туристах
function getTouristsInfo($booking) {
    $count = (int)($booking['tourists_count'] ?? 0);
    if ($count == 0) return '—';
    return $count . ' ' . getTouristWord($count);
}

function getTouristWord($count) {
    $lastDigit = $count % 10;
    $lastTwoDigits = $count % 100;
    
    if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
        return 'туристов';
    }
    
    if ($lastDigit == 1) {
        return 'турист';
    } elseif ($lastDigit >= 2 && $lastDigit <= 4) {
        return 'туриста';
    } else {
        return 'туристов';
    }
}
?>
<main class="account-main">
    <section class="profile-section">
        <h1 class="profile-title">Личный кабинет</h1>

        <div class="profile-content">
            <div class="profile-left">
                <div class="info-row">
                    <div class="info-group">
                        <label>Фамилия</label>
                        <div class="info-field">
                            <span class="text-value"><?= htmlspecialchars($lastName) ?></span>
                            <input type="text" class="edit-input" value="<?= htmlspecialchars($lastName) ?>" style="display: none;">
                        </div>
                    </div>
                    <div class="info-group">
                        <label>Имя</label>
                        <div class="info-field">
                            <span class="text-value"><?= htmlspecialchars($firstName) ?></span>
                            <input type="text" class="edit-input" value="<?= htmlspecialchars($firstName) ?>" style="display: none;">
                        </div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-group">
                        <label>Номер телефона</label>
                        <div class="info-field"><span><?= htmlspecialchars($phone) ?></span></div>
                    </div>
                    <div class="info-group">
                        <label>E-mail адрес</label>
                        <div class="info-field"><span><?= htmlspecialchars($email) ?></span></div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-group">
                        <label>Статус</label>
                        <div class="info-field"><span>Турист</span></div>
                    </div>
                    <div class="info-group">
                        <label>Дата регистрации</label>
                        <div class="info-field"><span><?= htmlspecialchars($registrationDate) ?></span></div>
                    </div>
                </div>

                <div class="account-actions">
                    <button class="edit-btn" id="editToggle">Изменить</button>
                    <button class="save-btn" style="display: none;">Сохранить</button>
                    <button class="cancel-btn" style="display: none;">Отмена</button>
                    <button class="extra-button logout-btn" onclick="handleLogout()">Выйти</button>
                </div>
            </div>

            <div class="profile-right">
                <div class="logo">
                    <span class="logo-text">Trav<span class="logo-text-highlight">ly</span></span>
                    <div class="logo-icon"></div>
                </div>

                <div class="promo-section">
                    <label class="promo-label">Активировать промокод</label>
                    <input type="text" value="TRAVLYPROMO">
                    <button class="activate-btn" onclick="testPromo()">Активировать</button>
                </div>
            </div>
        </div>
    </section>

    <section class="tours-section">
        <h1 class="tours-title">Забронированные туры</h1>
        
        <?php if (empty($activeBookings)): ?>
            <p style="text-align: center; padding: 40px; color: #666;">У вас нет активных бронирований</p>
        <?php else: ?>
            <?php foreach ($activeBookings as $booking): ?>
        <section class="booking-hero" data-booking-id="<?= $booking['booking_id'] ?>">
            <div class="booking-content">
                <div class="booking-card-wrapper">
                    <h2><?= htmlspecialchars($booking['country'] ?? '') ?>, <?= htmlspecialchars($booking['city'] ?? '') ?>. <?= formatDate($booking['arrival_date'] ?? '') ?>–<?= formatDate($booking['return_date'] ?? '') ?></h2>
                    <div class="booking-card">
                        <?php if (!empty($booking['image_url'])): ?>
                            <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="Тур" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-image"></div>
                        <?php endif; ?>
                    </div>
                    <button class="extra-button" data-action="cancel-booking">Отменить бронирование</button>
                </div>

                <div class="tour-info">
                    <h3>Информация о туре</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Место отправления:</span>
                            <span class="info-value"><?= htmlspecialchars($booking['departure_point'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Дата отправления:</span>
                            <span class="info-value"><?= formatDate($booking['departure_date'] ?? '', true) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Место прибытия:</span>
                            <span class="info-value"><?= htmlspecialchars($booking['arrival_point'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Дата прибытия:</span>
                            <span class="info-value"><?= formatDate($booking['arrival_date'] ?? '', true) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Место отъезда:</span>
                            <span class="info-value"><?= htmlspecialchars($booking['return_point'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Дата отъезда:</span>
                            <span class="info-value"><?= formatDate($booking['return_date'] ?? '', true) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Отель:</span>
                            <span class="info-value"><?= htmlspecialchars($booking['hotel_name'] ?? '—') ?> <?= !empty($booking['hotel_rating']) ? '(' . $booking['hotel_rating'] . ' звезды)' : '' ?></span>
                        </div>
                        <?php if (!empty($booking['services'])): 
                            $services = is_string($booking['services']) ? json_decode($booking['services'], true) : $booking['services'];
                            if (is_array($services) && !empty($services)): ?>
                        <div class="info-item">
                            <span class="info-label">Доп. услуги:</span>
                            <span class="info-value"><?= htmlspecialchars(implode(', ', $services)) ?></span>
                        </div>
                        <?php endif; endif; ?>
                        <div class="info-item">
                            <span class="info-label">Длительность тура:</span>
                            <span class="info-value"><?= calculateNights($booking['arrival_date'] ?? '', $booking['return_date'] ?? '') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Туристы:</span>
                            <span class="info-value"><?= getTouristsInfo($booking) ?></span>
                        </div>
                        <div class="info-item total-cost">
                            <span class="info-label">Стоимость тура</span>
                            <span class="info-value"><?= formatPrice($booking['total_price'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="tours-section">
        <h1 class="tours-title">Прошедшие туры</h1>
        
        <?php if (empty($pastBookings)): ?>
            <p style="text-align: center; padding: 40px; color: #666;">У вас нет прошедших туров</p>
        <?php else: ?>
            <?php foreach ($pastBookings as $booking): ?>
        <section class="booking-hero" data-booking-id="<?= $booking['booking_id'] ?>" style="opacity: 0.8;">
            <div class="booking-content">
                <div class="booking-card-wrapper">
                    <h2><?= htmlspecialchars($booking['country'] ?? '') ?>, <?= htmlspecialchars($booking['city'] ?? '') ?>. <?= formatDate($booking['arrival_date'] ?? '') ?>–<?= formatDate($booking['return_date'] ?? '') ?></h2>
                    <div class="booking-card">
                        <?php if (!empty($booking['image_url'])): ?>
                            <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="Тур" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-image"></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tour-info">
                    <h3>Информация о туре</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Место отправления:</span>
                            <span class="info-value"><?= htmlspecialchars($booking['departure_point'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Дата отправления:</span>
                            <span class="info-value"><?= formatDate($booking['departure_date'] ?? '', true) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Место прибытия:</span>
                            <span class="info-value"><?= htmlspecialchars($booking['arrival_point'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Дата прибытия:</span>
                            <span class="info-value"><?= formatDate($booking['arrival_date'] ?? '', true) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Место отъезда:</span>
                            <span class="info-value"><?= htmlspecialchars($booking['return_point'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Дата отъезда:</span>
                            <span class="info-value"><?= formatDate($booking['return_date'] ?? '', true) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Отель:</span>
                            <span class="info-value"><?= htmlspecialchars($booking['hotel_name'] ?? '—') ?> <?= !empty($booking['hotel_rating']) ? '(' . $booking['hotel_rating'] . ' звезды)' : '' ?></span>
                        </div>
                        <?php if (!empty($booking['services'])): 
                            $services = is_string($booking['services']) ? json_decode($booking['services'], true) : $booking['services'];
                            if (is_array($services) && !empty($services)): ?>
                        <div class="info-item">
                            <span class="info-label">Доп. услуги:</span>
                            <span class="info-value"><?= htmlspecialchars(implode(', ', $services)) ?></span>
                        </div>
                        <?php endif; endif; ?>
                        <div class="info-item">
                            <span class="info-label">Длительность тура:</span>
                            <span class="info-value"><?= calculateNights($booking['arrival_date'] ?? '', $booking['return_date'] ?? '') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Туристы:</span>
                            <span class="info-value"><?= getTouristsInfo($booking) ?></span>
                        </div>
                        <div class="info-item total-cost">
                            <span class="info-label">Стоимость тура</span>
                            <span class="info-value"><?= formatPrice($booking['total_price'] ?? 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <div class="modal-overlay" id="cancelModal" style="display: none;">
        <div class="modal">
            <h3>Подтверждение отмены</h3>
            <p>Вы уверены, что хотите отменить бронирование этого тура?</p>
            <div class="modal-buttons">
                <button class="modal-btn secondary" id="cancelNo">Нет</button>
                <button class="modal-btn primary" id="cancelYes">Да, отменить</button>
            </div>
        </div>
    </div>
</main>

<script src="script/account.js"></script>
<script>
function handleLogout() {
    if (confirm('Вы уверены, что хотите выйти из аккаунта?')) {
        window.location.href = '?action=logout';
    }
}
</script>
