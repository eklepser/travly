<?php
$filterOptions = $filterOptions ?? $GLOBALS['filterOptions'] ?? ['countries' => [], 'hotels' => [], 'maxCapacity' => 4, 'tourTypes' => []];
?>
<div class="filters" data-filter-options='<?= json_encode($filterOptions, JSON_UNESCAPED_UNICODE) ?>'>
    <h2 class="filters-title">Подобрать тур</h2>
    <div class="filter-group">
        <div class="filter-item" data-filter="vacation-type">
            <span class="filter-label">Тип отдыха</span>
            <div class="filter-chevron"></div>
            <div class="dropdown-content" style="display: none;">
                <div class="dropdown-item" data-value="">Все</div>
            </div>
        </div>
        <div class="filter-item" data-filter="country">
            <span class="filter-label">Направление</span>
            <div class="filter-chevron"></div>
            <div class="dropdown-content" style="display: none;">
                <div class="dropdown-item" data-value="">Все</div>
            </div>
        </div>
        <div class="filter-item" data-filter="guests">
            <span class="filter-label">Количество туристов</span>
            <div class="filter-chevron"></div>
            <div class="dropdown-content" style="display: none;">
                <div class="dropdown-item" data-value="">Все</div>
            </div>
        </div>
        <div class="filter-item" data-filter="hotel">
            <span class="filter-label">Отель</span>
            <div class="filter-chevron"></div>
            <div class="dropdown-content" style="display: none;">
                <div class="dropdown-item" data-value="">Все</div>
            </div>
        </div>
    </div>
    <div class="budget-section">
        <span class="section-title">Бюджет</span>
        <div class="budget-inputs">
            <div class="input-group"><span class="input-label">от</span><input type="number" class="input-field budget-min" placeholder="0" min="0" max="1000000" value=""></div>
            <div class="input-group"><span class="input-label">до</span><input type="number" class="input-field budget-max" placeholder="100000" min="0" max="1000000" value=""></div>
        </div>
    </div>
    <div class="range-section">
        <span class="section-title">Стоимость тура (руб)</span>
        <div class="double-range-slider">
            <div class="slider-track"></div>
            <input type="range" class="slider price-min-slider" min="0" max="100000" value="0" step="1000">
            <input type="range" class="slider price-max-slider" min="0" max="100000" value="100000" step="1000">
        </div>
        <div class="range-values">
            <span class="range-value-min">0</span>
            <span class="range-value-max">100 000</span>
        </div>
    </div>
    <div class="duration-section">
        <span class="section-title">Длительность тура</span>
        <div class="duration-inputs">
            <div class="input-group"><span class="input-label">от</span><input type="number" class="input-field duration-min" placeholder="3" min="1" max="30" value="" oninput="if(this.value > 30) this.value = 30;"></div>
            <div class="input-group"><span class="input-label">до</span><input type="number" class="input-field duration-max" placeholder="30" min="1" max="30" value="" oninput="if(this.value > 30) this.value = 30;"></div>
        </div>
    </div>
    <div class="range-section">
        <span class="section-title">Длительность тура (ночей)</span>
        <div class="range-slider"><input type="range" class="slider nights-slider" min="1" max="30" value="30" step="1"></div>
    </div>
    <div class="filter-item" data-filter="rating">
        <span class="filter-label">Рейтинг</span>
        <div class="filter-chevron"></div>
        <div class="dropdown-content" style="display: none;">
            <div class="dropdown-item" data-value="">Все</div>
            <div class="dropdown-item" data-value="3">3+</div>
            <div class="dropdown-item" data-value="4">4+</div>
            <div class="dropdown-item" data-value="4.5">4.5+</div>
            <div class="dropdown-item" data-value="4.8">4.8+</div>
        </div>
    </div>
    <div class="filter-buttons">
        <button class="apply-btn" id="applyFilters">Подобрать!</button>
        <button class="reset-btn" id="resetFilters">Сбросить фильтры</button>
    </div>
</div>

