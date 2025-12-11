<?php
// Единый вывод админ-панели и модальных окон на всех страницах
if (empty($isAdmin)) {
    return;
}

if (defined('ADMIN_PANEL_RENDERED')) {
    return;
}
define('ADMIN_PANEL_RENDERED', true);
?>

<style>
body.admin-mode {
  padding-top: 0;
  margin: 0;
}
</style>

<div class="admin-control-bar">
  <div class="logo">
    <span class="logo-text">Trav<span class="logo-text-highlight">ly</span> — admin</span>
    <div class="logo-icon"></div>
  </div>

  <button class="admin-btn secondary" onclick="openAddTourModal()">Добавить тур</button>
  <button class="admin-btn secondary" onclick="openAddHotelModal()">Добавить отель</button>

  <span class="admin-return-link">Админ-режим активен</span>
</div>

<?php include __DIR__ . '/modal-add-tour.php'; ?>
<?php include __DIR__ . '/modal-add-hotel.php'; ?>

<script>
// Удаление тура из карточки (кнопка доступна только в админ-режиме)
if (typeof deleteTour === 'undefined') {
  function deleteTour(tourId, tourData, buttonElement, event) {
    if (event) {
      event.stopPropagation();
      event.preventDefault();
    }

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
            
            // Обновляем счетчик туров, если он есть
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
}
</script>

