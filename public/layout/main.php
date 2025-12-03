<?php
$pdo = createPDO();
$dbConnected = $pdo !== null;

$tours = [];
if ($dbConnected) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                t.id AS tour_id,
                t.country,
                t.location AS city,
                t.base_price,
                t.arrival_date,
                t.return_date,
                t.image_url,  
                h.name AS hotel_name,
                h.rating AS hotel_rating,
                h.max_capacity_per_room
            FROM tours t
            INNER JOIN hotels h ON t.hotel_id = h.id
            ORDER BY t.id
            LIMIT 10
        ");
        $stmt->execute();
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("[DB] Failed to fetch tours: " . $e->getMessage());
    }
}

$pageTitle = 'Travly — Лучшие туры для вас';
?>

<main class="main-page">
    <div class="filters">
        <h2 class="filters-title">Подобрать тур</h2>
        <div class="filter-group">
            <div class="filter-item"><span class="filter-label">Тип отдыха</span><div class="filter-chevron"></div></div>
            <div class="filter-item"><span class="filter-label">Направление</span><div class="filter-chevron"></div></div>
            <div class="filter-item"><span class="filter-label">Количество туристов</span><div class="filter-chevron"></div></div>
            <div class="filter-item"><span class="filter-label">Отель</span><div class="filter-chevron"></div></div>
        </div>
        <div class="budget-section">
            <span class="section-title">Бюджет</span>
            <div class="budget-inputs">
                <div class="input-group"><span class="input-label">от</span><input type="text" class="input-field" placeholder="0"></div>
                <div class="input-group"><span class="input-label">до</span><input type="text" class="input-field" placeholder="100000"></div>
            </div>
        </div>
        <div class="range-section">
            <span class="section-title">Стоимость тура (руб)</span>
            <div class="range-slider"><input type="range" class="slider" min="0" max="100000" value="50000"></div>
        </div>
        <div class="duration-section">
            <span class="section-title">Длительность тура</span>
            <div class="duration-inputs">
                <div class="input-group"><span class="input-label">от</span><input type="text" class="input-field" placeholder="3"></div>
                <div class="input-group"><span class="input-label">до</span><input type="text" class="input-field" placeholder="30"></div>
            </div>
        </div>
        <div class="range-section">
            <span class="section-title">Длительность тура (ночей)</span>
            <div class="range-slider"><input type="range" class="slider" min="3" max="30" value="14"></div>
        </div>
        <div class="filter-item"><span class="filter-label">Рейтинг</span><div class="filter-chevron"></div></div>
        <button class="apply-btn">Подобрать!</button>
    </div>

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

        <div class="tours-title">
            <div class="tours-icon discount-icon"></div>
            <h2><b>Супер</b> акции</h2>
        </div>

        <div class="cards-panel">
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
                ?>
                <a href="?page=tour&id=<?= (int) $tour['tour_id'] ?>" class="card">
                    <?php
                    $imageUrl = $tour['image_url'] ?? '';

                    if (empty($imageUrl)) {
                        $imageUrl = 'resources/images/tours/default_tour.png';
                    }

                    if (!file_exists($imageUrl)) {
                        $imageUrl = 'resources/images/tours/default_tour.png';
                    }
                    ?>
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
        </div>

        <div class="tours-title">
            <div class="tours-icon beach-icon"></div>
            <h2><b>Горячие</b> туры</h2>
        </div>
        <div class="cards-panel"></div>

        <div class="tours-title">
            <div class="tours-icon map-icon"></div>
            <h2>Туры для <b>Вас</b></h2>
        </div>
        <div class="cards-panel"></div>
    </div>
</main>
