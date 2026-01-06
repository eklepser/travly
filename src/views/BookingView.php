<?php
require_once __DIR__ . '/../core/View.php';

class BookingView extends View {
    private function formatDate($dateStr, $includeTime = false) {
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
    
    private function calculateNights($arrivalDate, $returnDate) {
        if (empty($arrivalDate) || empty($returnDate)) return '—';
        try {
            $arrival = new DateTime($arrivalDate);
            $return = new DateTime($returnDate);
            $diff = $arrival->diff($return);
            $nights = $diff->days;
            return $nights . ' ' . $this->getNightWord($nights);
        } catch (Exception $e) {
            return '—';
        }
    }
    
    private function getNightWord($nights) {
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
    
    private function formatPrice($price) {
        return number_format((float)$price, 0, ',', ' ') . ' рублей';
    }
    
    private function getTouristText($adults, $children) {
        $parts = [];
        if ($adults > 0) {
            $parts[] = $adults . ' ' . $this->getTouristWord($adults, 'adult');
        }
        if ($children > 0) {
            $parts[] = $children . ' ' . $this->getTouristWord($children, 'child');
        }
        return !empty($parts) ? implode(', ', $parts) : '—';
    }
    
    private function getTouristWord($count, $type = 'adult') {
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
    
    public function render() {
        $tour = $this->data['tour'] ?? null;
        $error = $this->data['error'] ?? null;
        $adults = $this->data['adults'] ?? 1;
        $children = $this->data['children'] ?? 0;
        $totalPrice = $this->data['totalPrice'] ?? 0;
        $servicesText = $this->data['servicesText'] ?? '—';
        $selectedServices = $this->data['selectedServices'] ?? [];
        $imageUrl = $this->data['imageUrl'] ?? 'resources/images/tours/default_tour.png';
        
        if ($error || !$tour) {
            ?>
<main class="booking-main">
    <section class="booking-hero">
        <div class="booking-header">
            <h1>Ошибка</h1>
            <p><?= $this->escape($error ?? 'Тур не найден') ?></p>
        </div>
    </section>
</main>
            <?php
            return;
        }
        ?>
<main class="booking-main">
    <section class="booking-hero">
        <div class="booking-header">
            <h1>Бронирование тура</h1>
            <h2>
                <?= $this->escape($tour['country'] ?? '') ?>, 
                <?= $this->escape($tour['location'] ?? '') ?>. 
                <?= $this->formatDate($tour['arrival_date'] ?? '') ?>–<?= $this->formatDate($tour['return_date'] ?? '') ?>
            </h2>
        </div>

        <div class="booking-content">
            <div class="booking-left-section">
                <div class="booking-card">
                    <div class="card-image" 
                        style="background-image: url('<?= $this->escape($imageUrl) ?>'); 
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
                        <span class="info-value"><?= $this->escape($tour['departure_point'] ?? '—') ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Дата отправления:</span>
                        <span class="info-value"><?= $this->formatDate($tour['departure_date'] ?? '', true) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Место прибытия:</span>
                        <span class="info-value"><?= $this->escape($tour['arrival_point'] ?? '—') ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Дата прибытия:</span>
                        <span class="info-value"><?= $this->formatDate($tour['arrival_date'] ?? '', true) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Место отъезда:</span>
                        <span class="info-value"><?= $this->escape($tour['return_point'] ?? '—') ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Дата отъезда:</span>
                        <span class="info-value"><?= $this->formatDate($tour['return_date'] ?? '', true) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Отель:</span>
                        <span class="info-value">
                            <?= $this->escape($tour['hotel_name'] ?? '—') ?>
                            <?php if (!empty($tour['hotel_rating'])): ?>
                                (<?= number_format((float)$tour['hotel_rating'], 1) ?> звезды)
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Питание:</span>
                        <span class="info-value"><?= $this->escape($tour['meal_plan'] ?? '—') ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Доп. услуги:</span>
                        <span class="info-value"><?= $this->escape($servicesText) ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Длительность тура:</span>
                        <span class="info-value"><?= $this->calculateNights($tour['arrival_date'] ?? '', $tour['return_date'] ?? '') ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Туристы:</span>
                        <span class="info-value"><?= $this->escape($this->getTouristText($adults, $children)) ?></span>
                    </div>

                    <div class="info-item total-cost">
                        <span class="info-label">Стоимость тура</span>
                        <span class="info-value" id="totalPriceValue"><?= $this->formatPrice($totalPrice) ?></span>
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
        <?php
    }
}

