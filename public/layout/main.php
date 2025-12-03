<?php
require_once __DIR__ . '/../../src/repositories/TourRepository.php';
require_once __DIR__ . '/../../src/handlers/filter-options.php';

$tourRepository = new TourRepository();

// Получаем туры по типам
$beachTours = $tourRepository->findByFilters(['vacation_type' => 'beach']);
$beachTours = array_slice($beachTours, 0, 6);

$mountainTours = $tourRepository->findByFilters(['vacation_type' => 'mountain']);
$mountainTours = array_slice($mountainTours, 0, 6);

$excursionTours = $tourRepository->findByFilters(['vacation_type' => 'excursion']);
$excursionTours = array_slice($excursionTours, 0, 6);

// Загружаем опции фильтров
$filterOptions = getFilterOptions();
// Сохраняем все отели для восстановления при сбросе страны
$filterOptions['allHotels'] = $filterOptions['hotels'];

$pageTitle = 'Travly — Лучшие туры для вас';
$scripts = ['script/filters.js'];

// Функция для отображения карточки тура
function renderTourCard($tour) {
    $arrival = new DateTime($tour['arrival_date']);
    $return = new DateTime($tour['return_date']);
    $nights = max(1, $arrival->diff($return)->days);
    $rating = (float) $tour['hotel_rating'];
    $fullStars = min(5, max(0, (int) floor($rating)));
    $emptyStars = 5 - $fullStars;
    $price = number_format((int) $tour['base_price'], 0, '', ' ');
    $maxGuests = (int) ($tour['max_capacity_per_room'] ?? 4);
    
    $imageUrl = $tour['image_url'] ?? '';
    if (empty($imageUrl) || !file_exists($imageUrl)) {
        $imageUrl = 'resources/images/tours/default_tour.png';
    }
    ?>
    <a href="?page=tour&id=<?= (int) $tour['tour_id'] ?>" class="card">
        <div class="card-image" style="background-image: url('<?= htmlspecialchars($imageUrl) ?>');"></div>
        <div class="card-overlay"></div>
        <div class="card-top">
            <div class="card-location">
                <div class="card-country"><?= htmlspecialchars($tour['country']) ?></div>
                <div class="card-city"><?= htmlspecialchars($tour['city']) ?></div>
            </div>
            <div class="card-rating"><?= number_format($rating, 1, '.', '') ?></div>
        </div>
        <div class="card-bottom">
            <div class="card-hotel-info">
                <div class="hotel-stars">
                    <?= str_repeat('★', $fullStars) . str_repeat('☆', $emptyStars) ?>
                </div>
                <div class="hotel-name"><?= htmlspecialchars($tour['hotel_name']) ?></div>
            </div>
            <div class="card-details">
                <div class="detail-item">
                    <span class="icon">🌙</span>
                    <span class="value"><?= $nights ?></span>
                    <span class="icon">👥</span>
                    <span class="value">1-<?= $maxGuests ?></span>
                </div>
                <div class="card-price">от <?= $price ?> руб/чел</div>
            </div>
        </div>
    </a>
    <?php
}
?>

<main class="main-page">
    <?php require_once 'components/filter-panel.php'; ?>

    <div class="tours-section">
        <div class="tours-banner-slider">
            <div class="slide slide1">
                <div class="tours-banner-overlay">
                    <div class="tours-banner-text">
                        <span class="line1">Отправляйтесь в</span>
                        <span class="line2">Ваше лучшее путешествие</span>
                        <span class="line3">уже сейчас!</span>
                    </div>
                </div>
            </div>
            <div class="slide slide2">
                <div class="tours-banner-overlay">
                    <div class="tours-banner-text">
                        <span class="line1">Откройте для себя</span>
                        <span class="line2">Новые горизонты</span>
                        <span class="line3">с нами!</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Пляжные туры -->
        <div class="tours-title">
            <div class="tours-icon beach-icon"></div>
            <h2><b>Пляжные</b> туры</h2>
        </div>
        <div class="cards-panel">
            <?php if (empty($beachTours)): ?>
                <div style="text-align: center; padding: 40px; color: #666;">Туры не найдены</div>
            <?php else: ?>
                <?php foreach ($beachTours as $tour): ?>
                    <?php renderTourCard($tour); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="more-tours-section">
            <a href="?page=search&vacation_type=beach" class="more-tours-btn apply-btn">Найти больше туров</a>
        </div>

        <!-- Горные туры -->
        <div class="tours-title">
            <div class="tours-icon map-icon"></div>
            <h2><b>Горные</b> туры</h2>
        </div>
        <div class="cards-panel">
            <?php if (empty($mountainTours)): ?>
                <div style="text-align: center; padding: 40px; color: #666;">Туры не найдены</div>
            <?php else: ?>
                <?php foreach ($mountainTours as $tour): ?>
                    <?php renderTourCard($tour); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="more-tours-section">
            <a href="?page=search&vacation_type=mountain" class="more-tours-btn apply-btn">Найти больше туров</a>
        </div>

        <!-- Экскурсионные туры -->
        <div class="tours-title">
            <div class="tours-icon discount-icon"></div>
            <h2><b>Экскурсионные</b> туры</h2>
        </div>
        <div class="cards-panel">
            <?php if (empty($excursionTours)): ?>
                <div style="text-align: center; padding: 40px; color: #666;">Туры не найдены</div>
            <?php else: ?>
                <?php foreach ($excursionTours as $tour): ?>
                    <?php renderTourCard($tour); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="more-tours-section">
            <a href="?page=search&vacation_type=excursion" class="more-tours-btn apply-btn">Найти больше туров</a>
        </div>
    </div>
</main>
