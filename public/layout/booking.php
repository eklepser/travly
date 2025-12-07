<?php
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../../src/repositories/TourRepository.php';

// Получаем ID тура из GET параметра
$tourId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
$tour = null;
$error = null;

// Получаем количество туристов из GET или сессии
$adults = isset($_GET['adults']) ? (int)$_GET['adults'] : (isset($_SESSION['booking_data']['adults']) ? (int)$_SESSION['booking_data']['adults'] : 1);
$children = isset($_GET['children']) ? (int)$_GET['children'] : (isset($_SESSION['booking_data']['children']) ? (int)$_SESSION['booking_data']['children'] : 0);

// Получаем выбранные услуги из сессии
$bookingData = $_SESSION['booking_data'] ?? [];
$roomPrice = $bookingData['room_price'] ?? 0;
$selectedServices = $bookingData['services'] ?? [];

// Если services - это JSON строка, декодируем её
if (is_string($selectedServices)) {
    $decoded = json_decode($selectedServices, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $selectedServices = $decoded;
    } else {
        $selectedServices = [];
    }
}

if ($tourId === false) {
    $error = "Неверный идентификатор тура";
} else {
    $tourRepository = new TourRepository();
    $tour = $tourRepository->findById($tourId);
    if (!$tour) {
        $error = "Тур не найден";
    }
}

// Функции для форматирования
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

function formatPrice($price) {
    return number_format((float)$price, 0, ',', ' ') . ' рублей';
}

function getTouristText($adults, $children) {
    $parts = [];
    if ($adults > 0) {
        $parts[] = $adults . ' ' . getTouristWord($adults, 'adult');
    }
    if ($children > 0) {
        $parts[] = $children . ' ' . getTouristWord($children, 'child');
    }
    return !empty($parts) ? implode(', ', $parts) : '—';
}

function getTouristWord($count, $type = 'adult') {
    $lastDigit = $count % 10;
    $lastTwoDigits = $count % 100;
    
    if ($type === 'adult') {
        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
            return 'взрослых';
        }
        if ($lastDigit == 1) {
            return 'взрослый';
        } elseif ($lastDigit >= 2 && $lastDigit <= 4) {
            return 'взрослых';
        } else {
            return 'взрослых';
        }
    } else {
        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
            return 'детей';
        }
        if ($lastDigit == 1) {
            return 'ребенок';
        } elseif ($lastDigit >= 2 && $lastDigit <= 4) {
            return 'ребенка';
        } else {
            return 'детей';
        }
    }
}

// Рассчитываем цену
$basePrice = 0;
$totalPrice = 0;
$servicesText = '—';

if ($tour) {
    $basePricePerPerson = (int)($tour['base_price'] ?? 0);
    // Базовая цена: взрослые по полной цене, дети по 50%
    $basePrice = $adults * $basePricePerPerson + $children * $basePricePerPerson * 0.5;
    
    // Добавляем цену номера
    $servicesPrice = $roomPrice;
    
    // Добавляем цены выбранных услуг
    $servicesList = [];
    foreach ($selectedServices as $service) {
        if (isset($service['price'])) {
            $servicesPrice += (int)$service['price'];
        }
        if (isset($service['service'])) {
            $servicesList[] = $service['service'];
        }
    }
    
    if (!empty($servicesList)) {
        $servicesText = implode(', ', $servicesList);
    }
    
    $totalPrice = $basePrice + $servicesPrice;
}

// Получаем изображение тура (используем тот же подход, что в карточках туров)
$imageUrl = '';
if ($tour && !empty($tour['image_url'])) {
    $imageUrl = $tour['image_url'];
    $isExternalUrl = str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://');
    
    if ($isExternalUrl) {
        // Внешний URL - используем как есть
    } else {
        // Для локальных путей проверяем существование файла относительно public/
        $fullPath = __DIR__ . '/../../public/' . $imageUrl;
        if (!file_exists($fullPath)) {
            $imageUrl = 'resources/images/tours/default_tour.png';
        }
    }
} else {
    $imageUrl = 'resources/images/tours/default_tour.png';
}

if ($error || !$tour):
?>
<main class="booking-main">
    <section class="booking-hero">
        <div class="booking-header">
            <h1>Ошибка</h1>
            <p><?= htmlspecialchars($error ?? 'Тур не найден') ?></p>
        </div>
    </section>
</main>
<?php else: ?>
<main class="booking-main">
    <section class="booking-hero">
        <div class="booking-header">
            <h1>Бронирование тура</h1>
            <h2>
                <?= htmlspecialchars($tour['country'] ?? '') ?>, 
                <?= htmlspecialchars($tour['location'] ?? '') ?>. 
                <?= formatDate($tour['arrival_date'] ?? '') ?>–<?= formatDate($tour['return_date'] ?? '') ?>
            </h2>
        </div>

        <div class="booking-content">
            <div class="booking-left-section">
                <div class="booking-card">
                    <div class="card-image" 
                        style="background-image: url('<?= htmlspecialchars($imageUrl) ?>'); 
                                width: 100%; height: 100%; border-radius: 8px;">
                    </div>
                </div>
                <button class="change-services-btn" onclick="location.href='?page=tour&id=<?= (int)$tour['id'] ?>'">Изменить услуги</button>
            </div>

            <div class="tour-info">
                <h3>Информация о туре</h3>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Место отправления:</span>
                        <span class="info-value"><?= htmlspecialchars($tour['departure_point'] ?? '—') ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Дата отправления:</span>
                        <span class="info-value"><?= formatDate($tour['departure_date'] ?? '', true) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Место прибытия:</span>
                        <span class="info-value"><?= htmlspecialchars($tour['arrival_point'] ?? '—') ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Дата прибытия:</span>
                        <span class="info-value"><?= formatDate($tour['arrival_date'] ?? '', true) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Место отъезда:</span>
                        <span class="info-value"><?= htmlspecialchars($tour['return_point'] ?? '—') ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Дата отъезда:</span>
                        <span class="info-value"><?= formatDate($tour['return_date'] ?? '', true) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Отель:</span>
                        <span class="info-value">
                            <?= htmlspecialchars($tour['hotel_name'] ?? '—') ?>
                            <?php if (!empty($tour['hotel_rating'])): ?>
                                (<?= number_format((float)$tour['hotel_rating'], 1) ?> звезды)
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Питание:</span>
                        <span class="info-value"><?= htmlspecialchars($tour['meal_plan'] ?? '—') ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Доп. услуги:</span>
                        <span class="info-value"><?= htmlspecialchars($servicesText) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Длительность тура:</span>
                        <span class="info-value"><?= calculateNights($tour['arrival_date'] ?? '', $tour['return_date'] ?? '') ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Туристы:</span>
                        <span class="info-value"><?= htmlspecialchars(getTouristText($adults, $children)) ?></span>
                    </div>

                    <div class="info-item total-cost">
                        <span class="info-label">Стоимость тура</span>
                        <span class="info-value" id="totalPriceValue"><?= formatPrice($totalPrice) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="booking-form">
        <div class="booking-form-header">
            <button class="nav-arrow nav-arrow-left" id="prevFormBtn" style="display: none;">
                <span>◄</span>
            </button>
            <h3 id="formTitle">Данные туриста 1 (заказчик)</h3>
            <button class="nav-arrow nav-arrow-right" id="nextFormBtn" style="display: none;">
                <span>►</span>
            </button>
        </div>

        <div class="booking-forms-container" id="bookingFormsContainer">
        </div>

        <div class="form-buttons">
            <button class="clear-btn" id="clearCurrentFormBtn">Очистить данные</button>
        </div>
    </section>

    <div class="final-button">
        <button class="book-btn" id="bookTourBtn">Забронировать тур</button>
    </div>
</main>

<script>
// Сохраняем данные для JavaScript
window.bookingData = {
    tourId: <?= (int)$tour['id'] ?>,
    totalPrice: <?= $totalPrice ?>,
    adults: <?= $adults ?>,
    children: <?= $children ?>,
    services: <?= json_encode($selectedServices, JSON_UNESCAPED_UNICODE) ?>
};
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="script/validation.js"></script>
<script src="script/booking-forms.js"></script>
<?php endif; ?>
