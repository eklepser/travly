<?php
$pageTitle = 'Travly — Поиск';
require_once 'layout/header.php';
?>

<main class="main-page">

    <div class="filters">
        <h2 class="filters-title">Подобрать тур</h2>

        <div class="filter-group">
            <div class="filter-item">
                <span class="filter-label">Тип отдыха</span>
                <div class="filter-chevron"></div>
            </div>

            <div class="filter-item">
                <span class="filter-label">Направление</span>
                <div class="filter-chevron"></div>
            </div>

            <div class="filter-item">
                <span class="filter-label">Количество туристов</span>
                <div class="filter-chevron"></div>
            </div>

            <div class="filter-item">
                <span class="filter-label">Отель</span>
                <div class="filter-chevron"></div>
            </div>
        </div>

        <div class="budget-section">
            <span class="section-title">Бюджет</span>
            <div class="budget-inputs">
                <div class="input-group">
                    <span class="input-label">от</span>
                    <input type="text" class="input-field" placeholder="0">
                </div>
                <div class="input-group">
                    <span class="input-label">до</span>
                    <input type="text" class="input-field" placeholder="100000">
                </div>
            </div>
        </div>

        <div class="range-section">
            <span class="section-title">Стоимость тура (руб)</span>
            <div class="range-slider">
                <input type="range" class="slider" min="0" max="100000" value="50000">
            </div>
        </div>

        <div class="duration-section">
            <span class="section-title">Длительность тура</span>
            <div class="duration-inputs">
                <div class="input-group">
                    <span class="input-label">от</span>
                    <input type="text" class="input-field" placeholder="3">
                </div>
                <div class="input-group">
                    <span class="input-label">до</span>
                    <input type="text" class="input-field" placeholder="30">
                </div>
            </div>
        </div>

        <div class="range-section">
            <span class="section-title">Длительность тура (ночей)</span>
            <div class="range-slider">
                <input type="range" class="slider" min="3" max="30" value="14">
            </div>
        </div>

        <div class="filter-item">
            <span class="filter-label">Рейтинг</span>
            <div class="filter-chevron"></div>
        </div>

        <button class="apply-btn">Подобрать!</button>
    </div>

    <div class="tours-section">

        <div class="tours-title" style="margin-top: 0;">
            <div class="tours-icon map-icon"></div>
            <h2><b>Поиск</b> тура</h2>
        </div>

        <div class="sorting-options">
            <span class="filter-icon">☰</span>
            <div class="input-field">
                <select class="form-select">
                    <option>По популярности</option>
                    <option>По цене</option>
                    <option>По рейтингу</option>
                </select>
            </div>
        </div>

        <div class="cards-panel">

            <div class="card" data-page="layout/hotel-selection.html">
                <div class="card-image"></div>
                <div class="card-overlay"></div>

                <div class="card-top">
                    <div class="card-location">
                        <div class="card-country">Египет</div>
                        <div class="card-city">Хургада</div>
                    </div>
                    <div class="card-rating">9.1</div>
                </div>

                <div class="card-bottom">
                    <div class="card-hotel-info">
                        <div class="hotel-stars">★★★★☆</div>
                        <div class="hotel-name">Beach Resort Hotel</div>
                    </div>
                    <div class="card-details">
                        <div class="detail-item">
                            <span class="icon">🌙</span>
                            <span class="value">7</span>
                            <span class="icon">👥</span>
                            <span class="value">1-4</span>
                        </div>
                        <div class="card-price">от 35000 руб/чел</div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</main>

<?php require_once 'layout/footer.php'; ?>