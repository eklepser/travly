<?php
require_once '../src/config/database.php';
require_once '../src/handlers/filter-tours.php';
require_once '../src/handlers/filter-options.php';
require_once '../src/handlers/hotels-by-country.php';
require_once '../src/handlers/admin-actions.php';
require_once '../src/ui/TourCardRenderer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'add-tour') {
    handleAddTour();
}

if (isset($_GET['action']) && $_GET['action'] === 'get-hotels') {
    handleGetHotels();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'add-hotel') {
    handleAddHotel();
}

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
    .admin-control-bar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: linear-gradient(135deg, #275858, #459292);
      color: #ffffff;
      padding: 1.2rem 2.25rem;
      box-shadow: 0 3px 10px rgba(0,0,0,0.25);
      z-index: 2000;
      display: flex;
      align-items: center;
      gap: 1.5rem;
      flex-wrap: wrap;
    }
    .admin-title {
      margin: 0;
      font-size: 1.95rem;
      font-weight: 700;
    }
    .admin-btn {
      padding: 0.9rem 1.8rem;
      border: none;
      border-radius: 12px;
      background: #275858;
      color: #ffffff;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.2s;
      white-space: nowrap;
      display: inline-flex;
      align-items: center;
      gap: 0.6rem;
    }
    .admin-btn:hover {
      background: #1c4141;
      transform: translateY(-1px);
    }
    .admin-btn.secondary {
      background: #627878;
    }
    .admin-btn.secondary:hover {
      background: #4a5a5a;
    }

    body {
      padding-top: 108px;
      margin: 0;
    }

    .admin-return-link {
      margin-left: auto;
      text-decoration: none;
      color: #ffffff;
      font-weight: 600;
      font-size: 1rem;
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.9rem 1.8rem;
      border-radius: 12px;
      background: rgba(255,255,255,0.12);
    }
    .admin-return-link:hover {
      background: rgba(255,255,255,0.2);
      text-decoration: none;
    }
  </style>
</head>
<body>

<div class="admin-control-bar">
  <h1 class="admin-title">Панель управления турами</h1>

  <button class="admin-btn secondary" onclick="openAddTourModal()">Добавить тур</button>
  <button class="admin-btn secondary" onclick="openAddHotelModal()">
    Добавить отель
  </button>

  <a href="index.php" class="admin-return-link">Вид пользователя</a>
</div>

<main class="main-page">
  <?php require_once 'layout/components/filter-panel.php'; ?>

  <div class="tours-section">
    <div class="tours-title" style="margin-top: 0;">
      <div class="tours-icon map-icon"></div>
      <h2><b>Управление</b> турами</h2>
    </div>

    <div class="search-header">
      <div class="tours-count">
        Всего туров: <span class="count-value"><?= count($tours) ?></span>
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

    <?php include 'layout/components/modal-add-tour.php'; ?>
    <?php include 'layout/components/modal-add-hotel.php'; ?>

    <div class="cards-panel" id="toursContainer">
      <?php if (empty($tours)): ?>
        <div style="text-align: center; padding: 60px 20px; color: #7f8c8d; font-size: 1.1rem;">
          🗂️ Ни одного тура не найдено.<br>
          <small>Попробуйте сбросить фильтры или добавьте новый тур</small>
        </div>
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
          if (empty($imageUrl) || !file_exists(__DIR__ . '/' . $imageUrl)) {
              $imageUrl = 'resources/images/tours/default_tour.png';
          }
          ?>
          <div class="card admin-card" data-tour-id="<?= (int)$tour['tour_id'] ?>">
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
</main>

<style>
  .admin-card {
    position: relative;
    transition: transform 0.2s;
  }
  .admin-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
  }

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


    .notification {
      position: fixed;
      top: 120px;
      right: 20px;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 4000;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      min-width: 300px;
      max-width: 500px;
      animation: slideIn 0.3s ease-out;
    }

    .notification.success {
      background: #10b981;
      color: #ffffff;
    }

    .notification.error {
      background: #ef4444;
      color: #ffffff;
    }

    .notification.info {
      background: #3b82f6;
      color: #ffffff;
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    .notification-close {
      margin-left: auto;
      cursor: pointer;
      font-size: 1.2rem;
      opacity: 0.8;
      transition: opacity 0.2s;
    }

    .notification-close:hover {
      opacity: 1;
    }
</style>

<script src="script/filters.js"></script>
</body>
</html>