<?php
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
  <span class="admin-label">Admin</span>
  <div class="admin-buttons-group">
    <button class="admin-btn secondary" onclick="openAddTourModal()">Добавить тур</button>
    <button class="admin-btn secondary" onclick="openAddHotelModal()">Добавить отель</button>
  </div>
</div>

<?php include __DIR__ . '/modal-add-tour.php'; ?>
<?php include __DIR__ . '/modal-add-hotel.php'; ?>

<script>
window.deleteTourHandler = function(event, tourId, tourData, buttonElement) {
  console.log('deleteTourHandler вызвана', {tourId, tourData, buttonElement});
  
  if (event) {
    event.stopPropagation();
    event.preventDefault();
    event.stopImmediatePropagation();
  }
  
  if (buttonElement && buttonElement.closest) {
    const card = buttonElement.closest('.admin-card');
    if (card) {
      const originalOnclick = card.getAttribute('onclick');
      if (originalOnclick) {
        card.removeAttribute('onclick');
        setTimeout(() => {
          if (originalOnclick) {
            card.setAttribute('onclick', originalOnclick);
          }
        }, 1000);
      }
    }
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
    return false;
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
        if (typeof showNotification === 'function') {
          showNotification(`Тур ID=${tourId} успешно удален`, 'success');
        } else {
          alert(`Тур ID=${tourId} успешно удален`);
        }
      
      const card = buttonElement.closest('.admin-card');
      if (card) {
        card.style.transition = 'opacity 0.3s, transform 0.3s';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.8)';
        setTimeout(() => {
          card.remove();
          
          const countElement = document.querySelector('.count-value');
          if (countElement) {
            const currentCount = parseInt(countElement.textContent) || 0;
            countElement.textContent = Math.max(0, currentCount - 1);
          }
        }, 300);
      }
    } else {
      const errorMsg = res.message || 'Неизвестная ошибка';
      if (typeof showNotification === 'function') {
        showNotification('Ошибка при удалении тура: ' + errorMsg, 'error');
      } else {
        alert('Ошибка при удалении тура: ' + errorMsg);
      }
      buttonElement.disabled = false;
      buttonElement.innerHTML = originalText;
    }
  })
  .catch(err => {
    console.error('Ошибка при удалении тура:', err);
    const errorMsg = err.message || 'Неизвестная ошибка';
    if (typeof showNotification === 'function') {
      showNotification('Ошибка сети: ' + errorMsg, 'error');
    } else {
      alert('Ошибка сети: ' + errorMsg);
    }
    buttonElement.disabled = false;
    buttonElement.innerHTML = originalText;
  });
  
  return false;
}

if (typeof deleteTour === 'undefined') {
  window.deleteTour = window.deleteTourHandler;
}

console.log('deleteTourHandler определена:', typeof window.deleteTourHandler);

document.addEventListener('DOMContentLoaded', function() {
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.admin-btn.tiny.danger[data-tour-id]');
    if (btn && btn.dataset.tourId) {
      e.stopPropagation();
      e.preventDefault();
      e.stopImmediatePropagation();
      
      const tourId = parseInt(btn.dataset.tourId);
      const tourData = btn.dataset.tourData ? JSON.parse(btn.dataset.tourData) : {};
      
      if (typeof window.deleteTourHandler === 'function') {
        window.deleteTourHandler(e, tourId, tourData, btn);
      } else {
        console.error('deleteTourHandler не найдена при делегировании');
        alert('Ошибка: функция удаления не загружена');
      }
    }
  }, true);
});
</script>

