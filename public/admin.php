<?php
// 1. Загрузка зависимостей
require_once '../src/config/database.php';
require_once '../src/handlers/filter-tours.php';
require_once '../src/handlers/filter-options.php';
require_once '../src/handlers/hotels-by-country.php';
require_once '../src/ui/TourCardRenderer.php'; // если вынесли renderTourCard — используем его
// В самом верху, где подключаются зависимости:
require_once '../src/repositories/HotelRepository.php'; // если ещё не подключён

// 2. Получаем фильтры (безопасно)
$filters = [
    'vacation_type' => $_GET['vacation_type'] ?? null,
    'country'       => $_GET['country']       ?? null,
    'min_price'     => isset($_GET['min_price'])     ? (int)$_GET['min_price']     : null,
    'max_price'     => isset($_GET['max_price'])     ? (int)$_GET['max_price']     : null,
    'min_nights'    => isset($_GET['min_nights'])    ? (int)$_GET['min_nights']    : null,
    'max_nights'    => isset($_GET['max_nights'])    ? (int)$_GET['max_nights']    : null,
    'min_guests'    => isset($_GET['min_guests'])    ? (int)$_GET['min_guests']    : null,
    'min_rating'    => isset($_GET['min_rating'])    ? (float)$_GET['min_rating']  : null,
    'hotel'         => $_GET['hotel']                ?? null,
    'sort'          => $_GET['sort']                 ?? 'popularity'
];

// 3. Получаем данные
$tours = getFilteredTours($filters);
$filterOptions = getFilterOptions();
$filterOptions['allHotels'] = $filterOptions['hotels'];

if ($filters['country']) {
    $filterOptions['hotels'] = getHotelsByCountry($filters['country']);
}

$title = 'Админ-панель • Поиск и управление';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="style/styles.css">
  <style>
    /* === Фиксированная админ-панель === */
    .admin-control-bar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: linear-gradient(135deg, #1a3a5f, #2c3e50);
      color: white;
      padding: 0.8rem 1.5rem;
      box-shadow: 0 3px 10px rgba(0,0,0,0.25);
      z-index: 2000;
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .admin-control-bar h1 {
      margin: 0;
      font-size: 1.3rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .admin-btn {
      padding: 0.6rem 1.2rem;
      border: none;
      border-radius: 6px;
      background: #3498db;
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      white-space: nowrap;
      display: flex;
      align-items: center;
      gap: 0.4rem;
    }
    .admin-btn:hover {
      background: #2980b9;
      transform: translateY(-1px);
    }
    .admin-btn.danger {
      background: #e74c3c;
    }
    .admin-btn.danger:hover {
      background: #c0392b;
    }
    .admin-btn.success {
      background: #2ecc71;
    }
    .admin-btn.success:hover {
      background: #27ae60;
    }

    /* Сдвиг контента под панель */
    body {
      padding-top: 72px;
      margin: 0;
    }

    /* Стиль для кнопки выхода */
    .admin-return-link {
      margin-left: auto;
      text-decoration: none;
      color: #ecf0f1;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.6rem 1.2rem;
      border-radius: 6px;
      background: rgba(255,255,255,0.1);
    }
    .admin-return-link:hover {
      background: rgba(255,255,255,0.2);
      text-decoration: none;
    }

    /* Подсветка админ-режима */
    .admin-mode-badge {
      background: #e74c3c;
      color: white;
      padding: 0.2rem 0.6rem;
      border-radius: 12px;
      font-size: 0.85rem;
      font-weight: bold;
    }

    /* Расширенный заголовок */
    .admin-search-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    .admin-tours-count {
      font-size: 1.2rem;
      font-weight: 600;
    }
  </style>
</head>
<body>

<!-- 🔧 Фиксированная админ-панель -->
<div class="admin-control-bar">
  <h1>🛠️ <span class="admin-mode-badge">АДМИН</span> Панель управления</h1>
  
  <button class="admin-btn" onclick="openAddTourModal()">➕ Туры</button>
  <button class="admin-btn success" onclick="alert('Открыта форма добавления отеля')">
    🏨 Добавить отель
  </button>
  <button class="admin-btn" onclick="alert('Выберите запись → нажмите «Редактировать»')">
    ✏️ Редактировать
  </button>
  <button class="admin-btn danger" onclick="if(confirm('Удалить выбранную запись? Действие необратимо.')) alert('Запись удалена')">
    🗑️ Удалить
  </button>

  <a href="./" class="admin-return-link">🚪 Выйти в публичную часть</a>
</div>

<!-- 🔍 Основной контент: как в search.php -->
<main class="main-page">
  <?php require_once 'layout/components/filter-panel.php'; ?>

  <div class="tours-section">
    <div class="tours-title" style="margin-top: 0;">
      <div class="tours-icon map-icon"></div>
      <h2><b>Управление</b> турами</h2>
    </div>

    <!-- Админ-заголовок с количеством -->
    <div class="admin-search-header">
      <div class="admin-tours-count">
        Всего туров: <span class="count-value"><?= count($tours) ?></span>
        <?php if (!empty($filters)): ?>
          <small style="color:#95a5a6; margin-left:1rem;">(применены фильтры)</small>
        <?php endif; ?>
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

    <!-- Сетка карточек -->
    <div class="cards-panel" id="toursContainer">
      <?php if (empty($tours)): ?>
        <div style="text-align: center; padding: 60px 20px; color: #7f8c8d; font-size: 1.1rem;">
          🗂️ Ни одного тура не найдено.<br>
          <small>Попробуйте сбросить фильтры или добавьте новый тур</small>
        </div>
      <?php else: ?>
        <?php foreach ($tours as $tour): ?>
          <?php
          // Если вы уже вынесли renderTourCard() в TourCardRenderer.php — раскомментируйте:
          // renderTourCard($tour);

          // А пока — оставляем inline (как в search.php), чтобы работало:
          $arrival = new DateTime($tour['arrival_date']);
          $return = new DateTime($tour['return_date']);
          $nights = max(1, $arrival->diff($return)->days);
          $rating = (float) $tour['hotel_rating'];
          $fullStars = min(5, max(0, (int) floor($rating)));
          $emptyStars = 5 - $fullStars;
          $price = number_format((int) $tour['base_price'], 0, '', ' ');
          $maxGuests = (int) ($tour['max_capacity_per_room'] ?? 4);
          $imageUrl = $tour['image_url'] ?? '';
          if (empty($imageUrl) || !file_exists(__DIR__ . '/' . $imageUrl)) {
              $imageUrl = 'resources/images/tours/default_tour.png';
          }
          ?>
          <div class="card admin-card" data-tour-id="<?= (int)$tour['tour_id'] ?>">
            <!-- Обернули в div вместо <a>, чтобы не было перехода -->
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

            <!-- ✅ Кнопки управления ПРЯМО НА КАРТОЧКЕ -->
            <div class="admin-card-controls">
              <button class="admin-btn tiny success" 
                      onclick="alert('Редактирование тура ID <?= (int)$tour['tour_id'] ?>')">✏️</button>
              <button class="admin-btn tiny danger" 
                      onclick="if(confirm('Удалить тур «<?= addslashes(htmlspecialchars($tour['hotel_name'])) ?>»?')) 
                               alert('Тур ID <?= (int)$tour['tour_id'] ?> удалён')">🗑️</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
  <?php include 'layout/components/modal-add-tour.php'; ?>
</main>

<style>
  /* Доп. стили для админ-режима */
  .admin-card {
    position: relative;
    transition: transform 0.2s;
  }
  .admin-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
  }

  /* Панель управления на карточке */
  .admin-card-controls {
    position: absolute;
    top: 12px;
    right: 12px;
    display: flex;
    gap: 0.4rem;
    opacity: 0;
    transition: opacity 0.2s;
  }
  .admin-card:hover .admin-card-controls {
    opacity: 1;
  }

  .admin-btn.tiny {
    padding: 0.3rem 0.6rem;
    font-size: 0.85rem;
    border-radius: 4px;
  }
</style>

<!-- Подключаем скрипты фильтрации (работает как на сайте) -->
<script src="script/filters.js"></script>
</body>
</html>