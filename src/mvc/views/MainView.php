<?php
require_once __DIR__ . '/../View.php';
require_once __DIR__ . '/../../ui/TourCardRenderer.php';

class MainView extends View {
    public function render() {
        $beachTours = $this->data['beachTours'] ?? [];
        $mountainTours = $this->data['mountainTours'] ?? [];
        $excursionTours = $this->data['excursionTours'] ?? [];
        $filterOptions = $this->data['filterOptions'] ?? [];
        $isAdmin = $this->data['isAdmin'] ?? false;
        
        // Делаем filterOptions доступной для filter-panel.php
        $GLOBALS['filterOptions'] = $filterOptions;
        
        ?>
<main class="main-page">
    <?php require_once __DIR__ . '/../../../public/layout/components/filter-panel.php'; ?>

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
            <div class="tours-icon beach-icon"></div>
            <h2><b>Пляжные</b> туры</h2>
        </div>
        <div class="cards-panel">
            <?php if (empty($beachTours)): ?>
                <div style="text-align: center; padding: 40px; color: #666;">Туры не найдены</div>
            <?php else: ?>
                <?php foreach ($beachTours as $tour): ?>
                    <?php renderTourCard($tour, '', !empty($isAdmin)); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="more-tours-section">
            <a href="?page=search&vacation_type=beach" class="more-tours-btn apply-btn">Найти больше туров</a>
        </div>

        <div class="tours-title">
            <div class="tours-icon map-icon"></div>
            <h2><b>Горные</b> туры</h2>
        </div>
        <div class="cards-panel">
            <?php if (empty($mountainTours)): ?>
                <div style="text-align: center; padding: 40px; color: #666;">Туры не найдены</div>
            <?php else: ?>
                <?php foreach ($mountainTours as $tour): ?>
                    <?php renderTourCard($tour, '', !empty($isAdmin)); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="more-tours-section">
            <a href="?page=search&vacation_type=mountain" class="more-tours-btn apply-btn">Найти больше туров</a>
        </div>

        <div class="tours-title">
            <div class="tours-icon discount-icon"></div>
            <h2><b>Экскурсионные</b> туры</h2>
        </div>
        <div class="cards-panel">
            <?php if (empty($excursionTours)): ?>
                <div style="text-align: center; padding: 40px; color: #666;">Туры не найдены</div>
            <?php else: ?>
                <?php foreach ($excursionTours as $tour): ?>
                    <?php renderTourCard($tour, '', !empty($isAdmin)); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="more-tours-section">
            <a href="?page=search&vacation_type=excursion" class="more-tours-btn apply-btn">Найти больше туров</a>
        </div>
    </div>
</main>
<script src="script/filters.js"></script>
        <?php
    }
}

