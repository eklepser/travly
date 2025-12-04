<?php

function renderTourCard($tour, $baseUrl = '') {

    $arrival = new DateTime($tour['arrival_date']);
    $return = new DateTime($tour['return_date']);
    $nights = max(1, $arrival->diff($return)->days);
    $rating = (float) $tour['hotel_rating'];
    $fullStars = min(5, max(0, (int) floor($rating)));
    $emptyStars = 5 - $fullStars;
    $price = number_format((int) $tour['base_price'], 0, '', ' ');
    $maxGuests = (int) ($tour['max_capacity_per_room'] ?? 4);
    
    $imageUrl = $tour['image_url'] ?? '';
    $isExternalUrl = !empty($imageUrl) && (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://'));
    
    if (empty($imageUrl)) {
        $imageUrl = $baseUrl . 'resources/images/tours/default_tour.png';
    } elseif ($isExternalUrl) {
    } else {
        if (!file_exists(__DIR__ . '/../../public/' . $imageUrl)) {
            $imageUrl = $baseUrl . 'resources/images/tours/default_tour.png';
        } else {
            $imageUrl = $baseUrl . $imageUrl;
        }
    }
    ?>
    <a href="<?= $baseUrl ?>?page=tour&id=<?= (int) $tour['tour_id'] ?>" class="card">
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