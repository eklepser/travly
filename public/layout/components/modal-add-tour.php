<?php
// Загрузка списка отелей — только если нужно (например, при рендеринге формы)
// Но лучше получать через AJAX — поэтому здесь только структура.
?>
<div id="addTourModal" class="modal" style="display: none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3>➕ Добавить тур</h3>
      <span class="close" onclick="closeModal('addTourModal')">&times;</span>
    </div>
    <div class="modal-body">
      <form id="addTourForm">
        <!-- Тип отдыха -->
        <div class="form-group">
          <label>Тип отдыха *</label>
          <select name="vacation_type" required>
            <option value="">— Выберите —</option>
            <option value="beach" selected>Пляжный</option>
            <option value="mountain">Горный</option>
            <option value="excursion">Экскурсионный</option>
          </select>
        </div>

        <!-- Страна и город -->
        <div class="form-row">
          <div class="form-group">
            <label>Страна *</label>
            <input type="text" name="country" placeholder="Россия" value="Турция" required>
          </div>
          <div class="form-group">
            <label>Город *</label>
            <input type="text" name="city" placeholder="Сочи" value="Анталия" required>
          </div>
        </div>

        <!-- Выбор отеля: существующий или новый -->
          <div class="form-group">
            <label>Отель *</label>
            <div class="radio-group">
              <label>
                <input type="radio" name="hotel_mode" value="existing" onchange="toggleHotelMode()">
                Существующий
              </label>
              <label>
                <input type="radio" name="hotel_mode" value="new" checked onchange="toggleHotelMode()">
                Новый
              </label>
            </div>
          </div>

        <!-- Существующий отель -->
        <div id="existingHotelSection" style="display: none;">
          <div class="form-group">
            <select name="existing_hotel_id" id="existingHotelSelect">
              <option value="">— Выберите отель —</option>
            </select>
          </div>
        </div>

        <!-- Новый отель -->
        <div id="newHotelSection" style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 6px;">
          <h4 style="margin-top: 0;">Данные нового отеля</h4>
          <div class="form-group">
            <label>Название отеля *</label>
            <input type="text" name="new_hotel_name" placeholder="Radisson Resort" value="Sunny Beach Palace" required>
          </div>
          <div class="form-group">
            <label>Рейтинг (1–5) *</label>
            <input type="number" name="new_hotel_rating" min="1" max="5" step="0.5" value="4.5" required>
          </div>
          <div class="form-group">
            <label>Макс. гостей в номере *</label>
            <input type="number" name="new_hotel_max_guests" min="1" max="10" value="4" required>
          </div>
        </div>

        <!-- Точка отправления -->
        <div class="form-group">
          <label>Точка отправления *</label>
          <input type="text" name="departure_point" placeholder="Москва" value="Москва" required>
        </div>

        <!-- Даты -->
        <div class="form-row">
          <div class="form-group">
            <label>Дата отправления *</label>
            <input type="date" name="departure_date" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
          </div>
          <div class="form-group">
            <label>Дата заезда *</label>
            <input type="date" name="arrival_date" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
          </div>
          <div class="form-group">
            <label>Дата выезда *</label>
            <input type="date" name="return_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
          </div>
        </div>

        <!-- Цена и фото -->
        <div class="form-row">
          <div class="form-group">
            <label>Базовая цена (₽) *</label>
            <input type="number" name="base_price" min="1000" step="100" placeholder="50000" value="75000" required>
          </div>
          <div class="form-group">
            <label>URL изображения</label>
            <input type="url" name="image_url" placeholder="resources/images/tours/...">
          </div>
        </div>

        <!-- Кнопки -->
        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="closeModal('addTourModal')">Отмена</button>
          <button type="submit" class="btn-primary" id="submitTourBtn">Добавить тур</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleHotelMode() {
  const mode = document.querySelector('input[name="hotel_mode"]:checked').value;
  const existingSection = document.getElementById('existingHotelSection');
  const newSection = document.getElementById('newHotelSection');
  const existingSelect = document.getElementById('existingHotelSelect');
  
  if (mode === 'existing') {
    existingSection.style.display = 'block';
    newSection.style.display = 'none';
    if (existingSelect) {
      existingSelect.setAttribute('required', 'required');
    }
    // Убираем required с полей нового отеля
    const newHotelFields = newSection.querySelectorAll('[required]');
    newHotelFields.forEach(field => field.removeAttribute('required'));
  } else {
    existingSection.style.display = 'none';
    newSection.style.display = 'block';
    if (existingSelect) {
      existingSelect.removeAttribute('required');
    }
    // Добавляем required к полям нового отеля
    const newHotelName = newSection.querySelector('[name="new_hotel_name"]');
    const newHotelRating = newSection.querySelector('[name="new_hotel_rating"]');
    const newHotelGuests = newSection.querySelector('[name="new_hotel_max_guests"]');
    if (newHotelName) newHotelName.setAttribute('required', 'required');
    if (newHotelRating) newHotelRating.setAttribute('required', 'required');
    if (newHotelGuests) newHotelGuests.setAttribute('required', 'required');
  }
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.style.display = 'none';
  }
}

// Загрузка списка отелей при открытии модального окна
function loadHotels() {
  const select = document.getElementById('existingHotelSelect');
  if (select.innerHTML.includes('Загрузка')) {
    fetch('?action=get-hotels')
      .then(r => r.json())
      .then(hotels => {
        select.innerHTML = '<option value="">— Выберите отель —</option>';
        hotels.forEach(h => {
          const opt = document.createElement('option');
          opt.value = h.hotel_id;
          opt.textContent = `${h.hotel_name} (${h.country}, ${h.city}) ★${h.hotel_rating}`;
          select.appendChild(opt);
        });
      })
      .catch(() => {
        select.innerHTML = '<option value="">Ошибка загрузки</option>';
      });
  }
}

// Открытие модального окна + подгрузка отелей
function openAddTourModal() {
  const modal = document.getElementById('addTourModal');
  if (!modal) return;
  
  modal.style.display = 'flex';
  loadHotels();
  toggleHotelMode();
  
  setTimeout(() => {
    const modalBody = modal.querySelector('.modal-body');
    if (modalBody) {
      modalBody.scrollTop = 0;
    }
  }, 10);
}

// Функция для показа уведомлений
function showNotification(message, type = 'info') {
  // Удаляем предыдущее уведомление, если есть
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

  // Автоматически удаляем через 5 секунд
  setTimeout(() => {
    if (notification.parentElement) {
      notification.style.animation = 'slideIn 0.3s ease-out reverse';
      setTimeout(() => notification.remove(), 300);
    }
  }, 5000);
}

// Закрытие по клику вне окна
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('addTourModal');
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === this) {
        closeModal('addTourModal');
      }
    });
  }
});

// Обработка отправки формы - один обработчик на форме
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('addTourForm');
  if (!form) return;
  
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    // Валидация
    if (!data.vacation_type || !data.country || !data.city || 
        !data.departure_point || !data.departure_date || 
        !data.arrival_date || !data.return_date || !data.base_price) {
      showNotification('Заполните все обязательные поля', 'error');
      return;
    }

    if (data.hotel_mode === 'existing' && !data.existing_hotel_id) {
      showNotification('Выберите отель', 'error');
      return;
    }

    if (data.hotel_mode === 'new') {
      if (!data.new_hotel_name || !data.new_hotel_rating || !data.new_hotel_max_guests) {
        showNotification('Заполните данные нового отеля', 'error');
        return;
      }
    }

    // Индикатор загрузки
    const submitButton = this.querySelector('button[type="submit"]');
    if (!submitButton) return;
    
    const originalText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = 'Добавление...';

    // Отправка на сервер
    fetch('?action=add-tour', {
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
        showNotification('Тур успешно создан! ID тура: ' + res.tour_id, 'success');
        closeModal('addTourModal');
        setTimeout(() => {
          location.reload();
        }, 1500);
      } else {
        showNotification('Ошибка при создании тура: ' + (res.message || 'Неизвестная ошибка'), 'error');
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