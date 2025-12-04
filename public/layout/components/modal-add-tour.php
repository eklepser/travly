<?php
?>
<div id="addTourModal" class="modal" style="display: none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="tourModalTitle">➕ Добавить тур</h3>
      <span class="close" onclick="closeModal('addTourModal')">&times;</span>
    </div>
    <div class="modal-body">
      <form id="addTourForm">
        <input type="hidden" name="tour_id" id="tourIdInput" value="">
        <div class="form-group">
          <label>Тип отдыха *</label>
          <select name="vacation_type" required>
            <option value="">— Выберите —</option>
            <option value="beach" selected>Пляжный</option>
            <option value="mountain">Горный</option>
            <option value="excursion">Экскурсионный</option>
          </select>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Страна *</label>
            <input type="text" name="country" id="countryInput" placeholder="Россия" value="Турция" required>
          </div>
          <div class="form-group">
            <label>Город *</label>
            <input type="text" name="city" placeholder="Сочи" value="Анталия" required>
          </div>
        </div>

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

        <div id="existingHotelSection" style="display: none;">
          <div class="form-group">
            <select name="existing_hotel_id" id="existingHotelSelect">
              <option value="">— Выберите отель —</option>
            </select>
          </div>
        </div>

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

        <div class="form-group">
          <label>Точка отправления *</label>
          <input type="text" name="departure_point" placeholder="Москва" value="Москва" required>
        </div>

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

        <div class="form-row">
          <div class="form-group">
            <label>Базовая цена (₽) *</label>
            <input type="number" name="base_price" min="1000" step="100" placeholder="50000" value="75000" required>
          </div>
          <div class="form-group">
            <label>URL изображения</label>
            <input type="text" name="image_url" placeholder="resources/images/tours/turkey_alanya.jpg">
          </div>
        </div>

        <div class="form-group">
          <label>Дополнительные услуги</label>
          <textarea name="additional_services" id="additionalServicesInput" rows="14" style="font-size: 1.1rem;"></textarea>
        </div>

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
    const newHotelFields = newSection.querySelectorAll('[required]');
    newHotelFields.forEach(field => field.removeAttribute('required'));
  } else {
    existingSection.style.display = 'none';
    newSection.style.display = 'block';
    if (existingSelect) {
      existingSelect.removeAttribute('required');
    }
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

function loadHotels(country = null) {
  const select = document.getElementById('existingHotelSelect');
  if (!select) return;
  
  select.innerHTML = '<option value="">— Загрузка отелей…</option>';
  select.disabled = true;
  
  let url = '?action=get-hotels';
  if (country) {
    url += '&country=' + encodeURIComponent(country);
  }
  
  fetch(url)
    .then(r => r.json())
    .then(hotels => {
      select.innerHTML = '<option value="">— Выберите отель —</option>';
      if (hotels.length === 0) {
        select.innerHTML = '<option value="">— Отели не найдены —</option>';
      } else {
        hotels.forEach(h => {
          const opt = document.createElement('option');
          opt.value = h.hotel_id;
          const city = h.city || '—';
          opt.textContent = `${h.hotel_name} (${city}) ★${h.hotel_rating || '—'}`;
          select.appendChild(opt);
        });
      }
      select.disabled = false;
    })
    .catch(() => {
      select.innerHTML = '<option value="">Ошибка загрузки</option>';
      select.disabled = false;
    });
}

function openAddTourModal() {
  const modal = document.getElementById('addTourModal');
  if (!modal) return;
  
  // Сбрасываем форму и режим редактирования
  const form = document.getElementById('addTourForm');
  if (form) {
    form.reset();
    document.getElementById('tourIdInput').value = '';
    // Очищаем поле дополнительных услуг
    const additionalServicesInput = document.getElementById('additionalServicesInput');
    if (additionalServicesInput) {
      additionalServicesInput.value = '';
    }
  }
  
  // Устанавливаем режим добавления
  document.getElementById('tourModalTitle').textContent = '➕ Добавить тур';
  document.getElementById('submitTourBtn').textContent = 'Добавить тур';
  
  modal.style.display = 'flex';
  toggleHotelMode();
  
  const countryInput = document.getElementById('countryInput');
  const country = countryInput ? countryInput.value.trim() : null;
  loadHotels(country);
  
  setTimeout(() => {
    const modalBody = modal.querySelector('.modal-body');
    if (modalBody) {
      modalBody.scrollTop = 0;
    }
  }, 10);
}

function editTour(tourId) {
  const modal = document.getElementById('addTourModal');
  if (!modal) return;
  
  // Показываем загрузку
  document.getElementById('tourModalTitle').textContent = '⏳ Загрузка...';
  document.getElementById('submitTourBtn').textContent = 'Загрузка...';
  document.getElementById('submitTourBtn').disabled = true;
  modal.style.display = 'flex';
  
  // Загружаем данные тура
  fetch(`?action=get-tour&tour_id=${tourId}`)
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
      if (res.success && res.tour) {
        const tour = res.tour;
        
        // Заполняем форму данными тура
        document.getElementById('tourIdInput').value = tour.tour_id || '';
        document.querySelector('[name="vacation_type"]').value = tour.vacation_type || '';
        document.getElementById('countryInput').value = tour.country || '';
        document.querySelector('[name="city"]').value = tour.city || '';
        document.querySelector('[name="departure_point"]').value = tour.departure_point || '';
        document.querySelector('[name="departure_date"]').value = tour.departure_date || '';
        document.querySelector('[name="arrival_date"]').value = tour.arrival_date || '';
        document.querySelector('[name="return_date"]').value = tour.return_date || '';
        document.querySelector('[name="base_price"]').value = tour.base_price || '';
        document.querySelector('[name="image_url"]').value = tour.image_url || '';
        
        // Заполняем дополнительные услуги
        const additionalServicesInput = document.getElementById('additionalServicesInput');
        if (additionalServicesInput && tour.additional_services) {
          try {
            // Если это уже JSON строка, форматируем её красиво
            const services = typeof tour.additional_services === 'string' 
              ? JSON.parse(tour.additional_services) 
              : tour.additional_services;
            additionalServicesInput.value = JSON.stringify(services, null, 2);
          } catch (e) {
            // Если не JSON, просто вставляем как есть
            additionalServicesInput.value = tour.additional_services;
          }
        } else if (additionalServicesInput) {
          additionalServicesInput.value = '';
        }
        
        // Устанавливаем режим существующего отеля
        document.querySelector('[name="hotel_mode"][value="existing"]').checked = true;
        toggleHotelMode();
        
        // Загружаем отели для выбора
        loadHotels(tour.country);
        
        // После загрузки отелей устанавливаем выбранный отель
        setTimeout(() => {
          const hotelSelect = document.getElementById('existingHotelSelect');
          if (hotelSelect && tour.hotel_id) {
            hotelSelect.value = tour.hotel_id;
          }
        }, 500);
        
        // Обновляем заголовок и кнопку
        document.getElementById('tourModalTitle').textContent = '✏️ Редактировать тур';
        document.getElementById('submitTourBtn').textContent = 'Сохранить изменения';
        document.getElementById('submitTourBtn').disabled = false;
      } else {
        showNotification('Ошибка загрузки данных тура: ' + (res.message || 'Неизвестная ошибка'), 'error');
        closeModal('addTourModal');
      }
    })
    .catch(err => {
      console.error('Ошибка при загрузке тура:', err);
      showNotification('Ошибка сети: ' + err.message, 'error');
      closeModal('addTourModal');
    });
}

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

document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('addTourForm');
  if (!form) return;
  
  const countryInput = document.getElementById('countryInput');
  if (countryInput) {
    let countryTimeout;
    countryInput.addEventListener('input', function() {
      clearTimeout(countryTimeout);
      const country = this.value.trim();
      
      countryTimeout = setTimeout(() => {
        if (country) {
          loadHotels(country);
        } else {
          loadHotels();
        }
      }, 500);
    });
  }
  
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

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

    const submitButton = this.querySelector('button[type="submit"]');
    if (!submitButton) return;
    
    const originalText = submitButton.textContent;
    submitButton.disabled = true;
    
    // Определяем режим (добавление или редактирование)
    const tourId = formData.get('tour_id');
    const isEditMode = tourId && tourId !== '';
    const action = isEditMode ? 'update-tour' : 'add-tour';
    submitButton.textContent = isEditMode ? 'Сохранение...' : 'Добавление...';

    fetch(`?action=${action}`, {
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
        if (isEditMode) {
          const tourId = formData.get('tour_id');
          showNotification(`Тур ID=${tourId} успешно обновлен!`, 'success');
        } else {
          showNotification(`Тур ID=${res.tour_id} успешно создан!`, 'success');
        }
        closeModal('addTourModal');
        setTimeout(() => {
          location.reload();
        }, 1500);
      } else {
        const errorMsg = res.message || 'Неизвестная ошибка';
        console.error('Ошибка создания тура:', errorMsg);
        
        // Выводим детальную информацию об ошибке в консоль, если доступна
        if (res.debug) {
          console.error('Детали ошибки:', res.debug);
          if (res.debug.hotel_data) {
            console.error('Данные отеля:', res.debug.hotel_data);
          }
          if (res.debug.tour_data) {
            console.error('Данные тура:', res.debug.tour_data);
          }
          if (res.debug.exception) {
            console.error('Исключение:', res.debug.exception);
            console.error('Файл:', res.debug.file, 'Строка:', res.debug.line);
            if (res.debug.trace) {
              console.error('Трассировка:', res.debug.trace);
            }
          }
        }
        
        showNotification('Ошибка при создании тура: ' + errorMsg, 'error');
        submitButton.disabled = false;
        submitButton.textContent = originalText;
      }
    })
    .catch(err => {
      console.error('Ошибка при отправке запроса:', err);
      showNotification('Ошибка сети: ' + err.message, 'error');
      submitButton.disabled = false;
      submitButton.textContent = originalText;
    });
  });
});
</script>