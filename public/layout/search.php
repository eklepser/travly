<?php
require_once __DIR__ . '/../../src/handlers/filter-tours.php';
require_once __DIR__ . '/../../src/handlers/filter-options.php';
require_once __DIR__ . '/../../src/handlers/hotels-by-country.php';

$filters = [
    'vacation_type' => $_GET['vacation_type'] ?? null,
    'country' => $_GET['country'] ?? null,
    'min_price' => isset($_GET['min_price']) ? (int)$_GET['min_price'] : null,
    'max_price' => isset($_GET['max_price']) ? (int)$_GET['max_price'] : null,
    'min_nights' => isset($_GET['min_nights']) ? (int)$_GET['min_nights'] : null,
    'max_nights' => isset($_GET['max_nights']) ? (int)$_GET['max_nights'] : null,
    'min_guests' => isset($_GET['min_guests']) ? (int)$_GET['min_guests'] : null,
    'min_rating' => isset($_GET['min_rating']) ? (float)$_GET['min_rating'] : null,
    'hotel' => $_GET['hotel'] ?? null,
    'sort' => $_GET['sort'] ?? 'popularity'
];

$tours = getFilteredTours($filters);

$filterOptions = getFilterOptions();
$filterOptions['allHotels'] = $filterOptions['hotels'];

if ($filters['country']) {
    $filterOptions['hotels'] = getHotelsByCountry($filters['country']);
}

$pageTitle = 'Travly — Поиск туров';
$scripts = ['script/filters.js'];
?>
<main class="main-page">
    <?php require_once 'components/filter-panel.php'; ?>

    <div class="tours-section">

        <div class="tours-title" style="margin-top: 0;">
            <div class="tours-icon map-icon"></div>
            <h2><b>Поиск</b> тура</h2>
        </div>

        <div class="search-header">
            <div class="tours-count">
                Найдено туров: <span class="count-value"><?= count($tours) ?></span>
            </div>
            <div class="sorting-options">
                <div class="sort-filter-item" data-filter="sort">
                    <span class="sort-label"><?php
                        $sortLabels = [
                            'popularity' => 'По популярности',
                            'price_asc' => 'Сначала дешевые',
                            'price_desc' => 'Сначала дорогие',
                            'rating_desc' => 'Сначала с высоким рейтингом',
                            'rating_asc' => 'Сначала с низким рейтингом'
                        ];
                        echo $sortLabels[$filters['sort']] ?? 'Сортировка';
                    ?></span>
                    <div class="sort-chevron"></div>
                    <div class="dropdown-content" style="display: none;">
                        <div class="dropdown-item" data-value="popularity" <?= ($filters['sort'] === 'popularity') ? 'data-selected="true"' : '' ?>>По популярности</div>
                        <div class="dropdown-item" data-value="price_asc" <?= ($filters['sort'] === 'price_asc') ? 'data-selected="true"' : '' ?>>Сначала дешевые</div>
                        <div class="dropdown-item" data-value="price_desc" <?= ($filters['sort'] === 'price_desc') ? 'data-selected="true"' : '' ?>>Сначала дорогие</div>
                        <div class="dropdown-item" data-value="rating_desc" <?= ($filters['sort'] === 'rating_desc') ? 'data-selected="true"' : '' ?>>Сначала с высоким рейтингом</div>
                        <div class="dropdown-item" data-value="rating_asc" <?= ($filters['sort'] === 'rating_asc') ? 'data-selected="true"' : '' ?>>Сначала с низким рейтингом</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cards-panel" id="toursContainer">
            <?php if (empty($tours)): ?>
                <div style="text-align: center; padding: 40px; color: #666;">Туры не найдены</div>
            <?php else: ?>
                <?php foreach ($tours as $tour): ?>
                    <?php
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
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</main>