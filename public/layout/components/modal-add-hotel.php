<?php
?>
<div id="addHotelModal" class="modal" style="display: none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3>🏨 Добавить отель</h3>
      <span class="close" onclick="closeModal('addHotelModal')">&times;</span>
    </div>
    <div class="modal-body">
      <form id="addHotelForm">
        <div class="form-group">
          <label>Название отеля *</label>
          <input type="text" name="name" placeholder="Radisson Resort" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Рейтинг (1–5) *</label>
            <input type="number" name="rating" min="1" max="5" step="0.5" value="4.5" required>
          </div>
          <div class="form-group">
            <label>Макс. гостей в номере *</label>
            <input type="number" name="max_capacity_per_room" min="1" max="10" value="4" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="closeModal('addHotelModal')">Отмена</button>
          <button type="submit" class="btn-primary" id="submitHotelBtn">Добавить отель</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddHotelModal() {
  const modal = document.getElementById('addHotelModal');
  if (!modal) return;
  
  modal.style.display = 'flex';
  
  const form = document.getElementById('addHotelForm');
  if (form) {
    form.reset();
    const ratingInput = form.querySelector('input[name="rating"]');
    const capacityInput = form.querySelector('input[name="max_capacity_per_room"]');
    if (ratingInput) ratingInput.value = '4.5';
    if (capacityInput) capacityInput.value = '4';
  }
  
  setTimeout(() => {
    const modalBody = modal.querySelector('.modal-body');
    if (modalBody) {
      modalBody.scrollTop = 0;
    }
  }, 10);
}

document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('addHotelModal');
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === this) {
        closeModal('addHotelModal');
      }
    });
  }
});

document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('addHotelForm');
  if (!form) return;
  
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    if (!data.name || !data.rating || !data.max_capacity_per_room) {
      showNotification('Заполните все обязательные поля', 'error');
      return;
    }

    const rating = parseFloat(data.rating);
    if (rating < 1 || rating > 5) {
      showNotification('Рейтинг должен быть от 1 до 5', 'error');
      return;
    }

    const capacity = parseInt(data.max_capacity_per_room);
    if (capacity < 1 || capacity > 10) {
      showNotification('Максимальная вместимость должна быть от 1 до 10', 'error');
      return;
    }

    const submitButton = this.querySelector('button[type="submit"]');
    if (!submitButton) return;
    
    const originalText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = 'Добавление...';

    fetch('?action=add-hotel', {
      method: 'POST',
      body: formData
    })
    .then(r => {
      if (!r.ok) {
        throw new Error('Ошибка сервера: ' + r.status);
      }
      return r.json();
    })
    .then(res => {
      if (res.success) {
        showNotification('Отель успешно создан! ID отеля: ' + res.hotel_id, 'success');
        closeModal('addHotelModal');
        setTimeout(() => {
          location.reload();
        }, 1500);
      } else {
        showNotification('Ошибка при создании отеля: ' + (res.message || 'Неизвестная ошибка'), 'error');
        submitButton.disabled = false;
        submitButton.textContent = originalText;
      }
    })
    .catch(err => {
      showNotification('Ошибка сети: ' + err.message, 'error');
      submitButton.disabled = false;
      submitButton.textContent = originalText;
    });
  });
});
</script>

