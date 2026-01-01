<?php
require_once __DIR__ . '/../View.php';
require_once __DIR__ . '/../../ui/TourCardRenderer.php';

class SearchView extends View {
    public function render() {
        $tours = $this->data['tours'] ?? [];
        $filters = $this->data['filters'] ?? [];
        $filterOptions = $this->data['filterOptions'] ?? [];
        $isAdmin = $this->data['isAdmin'] ?? false;
        
        // Делаем filterOptions доступной для filter-panel.php
        $GLOBALS['filterOptions'] = $filterOptions;
        
        ?>
<main class="main-page">
    <?php require_once __DIR__ . '/../../../public/layout/components/filter-panel.php'; ?>

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
                            'price_asc' => 'Сначала дешевые',
                            'price_desc' => 'Сначала дорогие',
                            'rating_desc' => 'Сначала с высоким рейтингом',
                            'rating_asc' => 'Сначала с низким рейтингом',
                            'newest' => 'Сначала самые новые',
                            'oldest' => 'Сначала самые старые'
                        ];
                        echo $sortLabels[$filters['sort'] ?? 'newest'] ?? 'Сортировка';
                    ?></span>
                    <div class="sort-chevron"></div>
                    <div class="dropdown-content" style="display: none;">
                        <div class="dropdown-item" data-value="newest" <?= (($filters['sort'] ?? 'newest') === 'newest') ? 'data-selected="true"' : '' ?>>Сначала самые новые</div>
                        <div class="dropdown-item" data-value="oldest" <?= (($filters['sort'] ?? 'newest') === 'oldest') ? 'data-selected="true"' : '' ?>>Сначала самые старые</div>
                        <div class="dropdown-item" data-value="price_asc" <?= (($filters['sort'] ?? 'newest') === 'price_asc') ? 'data-selected="true"' : '' ?>>Сначала дешевые</div>
                        <div class="dropdown-item" data-value="price_desc" <?= (($filters['sort'] ?? 'newest') === 'price_desc') ? 'data-selected="true"' : '' ?>>Сначала дорогие</div>
                        <div class="dropdown-item" data-value="rating_desc" <?= (($filters['sort'] ?? 'newest') === 'rating_desc') ? 'data-selected="true"' : '' ?>>Сначала с высоким рейтингом</div>
                        <div class="dropdown-item" data-value="rating_asc" <?= (($filters['sort'] ?? 'newest') === 'rating_asc') ? 'data-selected="true"' : '' ?>>Сначала с низким рейтингом</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cards-panel" id="toursContainer">
            <?php if (empty($tours)): ?>
                <div style="text-align: center; padding: 40px; color: #666;">Туры не найдены</div>
            <?php else: ?>
                <?php foreach ($tours as $tour): ?>
                    <?php renderTourCard($tour, '', !empty($isAdmin)); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</main>
<script src="script/filters.js"></script>
        <?php
    }
}

