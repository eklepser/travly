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

if (isset($_GET['action']) && $_GET['action'] === 'get-tour') {
    handleGetTour();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'update-tour') {
    handleUpdateTour();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'delete-tour') {
    handleDeleteTour();
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

$title = 'Travly - admin';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="style/styles.css">
  <style>
    body {
      padding-top: 108px;
      margin: 0;
    }
  </style>
</head>
<body>

<div class="admin-control-bar">
  <div class="logo">
    <span class="logo-text">Trav<span class="logo-text-highlight">ly</span> - admin</span>
    <div class="logo-icon"></div>
  </div>

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
          // Проверяем, является ли путь URL из интернета
          $isExternalUrl = !empty($imageUrl) && (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://'));
          
          if (empty($imageUrl) || (!$isExternalUrl && !file_exists(__DIR__ . '/' . $imageUrl))) {
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
                      onclick="editTour(<?= (int)$tour['tour_id'] ?>)">✏️</button>
              <button class="admin-btn tiny danger" 
                      onclick="deleteTour(<?= (int)$tour['tour_id'] ?>, {
                        id: <?= (int)$tour['tour_id'] ?>,
                        hotel: '<?= addslashes(htmlspecialchars($tour['hotel_name'])) ?>',
                        country: '<?= addslashes(htmlspecialchars($tour['country'])) ?>',
                        city: '<?= addslashes(htmlspecialchars($tour['city'])) ?>',
                        arrival_date: '<?= htmlspecialchars($tour['arrival_date']) ?>',
                        return_date: '<?= htmlspecialchars($tour['return_date']) ?>',
                        price: <?= (int)$tour['base_price'] ?>
                      }, this)"
                      data-tour-id="<?= (int)$tour['tour_id'] ?>">🗑️</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</main>

<script src="script/filters.js"></script>
<script>
function showNotification(message, type = 'info') {
  const existing = document.querySelector('.notification');
  if (existing) {
    existing.remove();
  }

  const notification = document.createElement('div');
  notification.className = `notification ${type}`;
  
  const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
  notification.innerHTML = `
    <span>${icon}</span>
    <span>${message}</span>
    <span class="notification-close" onclick="this.parentElement.remove()">&times;</span>
  `;

  document.body.appendChild(notification);

  setTimeout(() => {
    if (notification.parentElement) {
      notification.style.animation = 'slideIn 0.3s ease-out reverse';
      setTimeout(() => notification.remove(), 300);
    }
  }, 5000);
}

function deleteTour(tourId, tourData, buttonElement) {
  const arrivalDate = new Date(tourData.arrival_date).toLocaleDateString('ru-RU');
  const returnDate = new Date(tourData.return_date).toLocaleDateString('ru-RU');
  const price = tourData.price.toLocaleString('ru-RU');
  
  const message = `Вы уверены, что хотите удалить тур?\n\n` +
    `ID: ${tourData.id}\n` +
    `Отель: ${tourData.hotel}\n` +
    `Локация: ${tourData.country}, ${tourData.city}\n` +
    `Даты: ${arrivalDate} - ${returnDate}\n` +
    `Цена: ${price} ₽\n\n` +
    `Это действие нельзя отменить.`;
  
  if (!confirm(message)) {
    return;
  }
  
  // Блокируем кнопку
  const originalText = buttonElement.innerHTML;
  buttonElement.disabled = true;
  buttonElement.innerHTML = '⏳';
  
  const formData = new FormData();
  formData.append('tour_id', tourId);
  
  fetch('?action=delete-tour', {
    method: 'POST',
    body: formData
  })
  .then(async r => {
    let responseText = '';
    try {
      responseText = await r.text();
      const res = JSON.parse(responseText);
      
      if (!r.ok) {
        throw new Error('Ошибка сервера: ' + r.status + ' - ' + (res.message || responseText));
      }
      
      return res;
    } catch (parseError) {
      console.error('Ошибка парсинга ответа:', parseError);
      console.error('Ответ сервера:', responseText);
      throw new Error('Ошибка сервера: ' + r.status + '. Ответ: ' + responseText.substring(0, 200));
    }
  })
  .then(res => {
      if (res.success) {
        showNotification(`Тур ID=${tourId} успешно удален`, 'success');
      
      // Находим карточку тура и удаляем её с анимацией
      const card = buttonElement.closest('.admin-card');
      if (card) {
        card.style.transition = 'opacity 0.3s, transform 0.3s';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.8)';
        setTimeout(() => {
          card.remove();
          
          // Обновляем счетчик туров
          const countElement = document.querySelector('.count-value');
          if (countElement) {
            const currentCount = parseInt(countElement.textContent) || 0;
            countElement.textContent = Math.max(0, currentCount - 1);
          }
        }, 300);
      }
    } else {
      showNotification('Ошибка при удалении тура: ' + (res.message || 'Неизвестная ошибка'), 'error');
      buttonElement.disabled = false;
      buttonElement.innerHTML = originalText;
    }
  })
  .catch(err => {
    console.error('Ошибка при удалении тура:', err);
    showNotification('Ошибка сети: ' + err.message, 'error');
    buttonElement.disabled = false;
    buttonElement.innerHTML = originalText;
  });
}

</script>
</body>
</html>