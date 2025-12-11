<?php

function renderTourCard($tour, $baseUrl = '', $isAdmin = false) {

    $adminMode = !empty($isAdmin);

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

    $cardTag = $adminMode ? 'div' : 'a';
    $cardClasses = 'card' . ($adminMode ? ' admin-card' : '');
    $cardHref = $baseUrl . '?page=tour&id=' . (int) $tour['tour_id'];

    $hotelJs = json_encode((string) ($tour['hotel_name'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $countryJs = json_encode((string) ($tour['country'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $cityJs = json_encode((string) ($tour['city'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $arrivalJs = json_encode((string) ($tour['arrival_date'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    $returnJs = json_encode((string) ($tour['return_date'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    ?>
    <<?= $cardTag ?>
        <?= $adminMode ? "onclick=\"if(!event.target.closest('.admin-card-controls')) window.location.href='{$cardHref}'\"" : "href=\"{$cardHref}\"" ?>
        class="<?= $cardClasses ?>" 
        data-tour-id="<?= (int) $tour['tour_id'] ?>">
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
        <?php if ($adminMode): ?>
            <div class="admin-card-controls">
                <button
                    type="button"
                    class="admin-btn tiny success"
                    onclick="event.preventDefault(); event.stopPropagation(); editTour(<?= (int) $tour['tour_id'] ?>);">✏️</button>
                <button
                    type="button"
                    class="admin-btn tiny danger"
                    data-tour-id="<?= (int) $tour['tour_id'] ?>"
                    data-tour-data='<?= json_encode(['id' => (int) $tour['tour_id'], 'hotel' => (string) ($tour['hotel_name'] ?? ''), 'country' => (string) ($tour['country'] ?? ''), 'city' => (string) ($tour['city'] ?? ''), 'arrival_date' => (string) ($tour['arrival_date'] ?? ''), 'return_date' => (string) ($tour['return_date'] ?? ''), 'price' => (int) $tour['base_price']], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'
                    onclick="(function(evt){evt=evt||window.event;if(evt){evt.stopPropagation();evt.preventDefault();evt.stopImmediatePropagation();}var tid=<?= (int) $tour['tour_id'] ?>;var tdata={id:tid,hotel:<?= $hotelJs ?>,country:<?= $countryJs ?>,city:<?= $cityJs ?>,arrival_date:<?= $arrivalJs ?>,return_date:<?= $returnJs ?>,price:<?= (int) $tour['base_price'] ?>};if(typeof window.deleteTourHandler==='function'){window.deleteTourHandler(evt,tid,tdata,this);}else{console.error('deleteTourHandler не найдена');alert('Ошибка: функция удаления не загружена');}return false;})(event);">🗑️</button>
            </div>
        <?php endif; ?>
    </<?= $cardTag ?>>
    <?php
}