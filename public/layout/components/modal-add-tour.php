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
            <option value="beach">Пляжный</option>
            <option value="mountain">Горный</option>
            <option value="excursion">Экскурсионный</option>
          </select>
        </div>

        <!-- Страна и город -->
        <div class="form-row">
          <div class="form-group">
            <label>Страна *</label>
            <input type="text" name="country" placeholder="Россия" required>
          </div>
          <div class="form-group">
            <label>Город *</label>
            <input type="text" name="city" placeholder="Сочи" required>
          </div>
        </div>

        <!-- Выбор отеля: существующий или новый -->
        <div class="form-group">
          <label>Отель *</label>
          <div class="radio-group">
            <label>
              <input type="radio" name="hotel_mode" value="existing" checked onchange="toggleHotelMode()">
              Существующий
            </label>
            <label>
              <input type="radio" name="hotel_mode" value="new" onchange="toggleHotelMode()">
              Новый
            </label>
          </div>
        </div>

        <!-- Существующий отель -->
        <div id="existingHotelSection">
          <div class="form-group">
            <select name="existing_hotel_id" id="existingHotelSelect" required>
              <option value="">— Загрузка отелей…</option>
            </select>
          </div>
        </div>

        <!-- Новый отель -->
        <div id="newHotelSection" style="display: none; margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 6px;">
          <h4 style="margin-top: 0;">Данные нового отеля</h4>
          <div class="form-group">
            <label>Название отеля *</label>
            <input type="text" name="new_hotel_name" placeholder="Radisson Resort" required>
          </div>
          <div class="form-group">
            <label>Рейтинг (1–5) *</label>
            <input type="number" name="new_hotel_rating" min="1" max="5" step="0.5" value="4" required>
          </div>
          <div class="form-group">
            <label>Макс. гостей в номере *</label>
            <input type="number" name="new_hotel_max_guests" min="1" max="10" value="4" required>
          </div>
        </div>

        <!-- Даты -->
        <div class="form-row">
          <div class="form-group">
            <label>Дата заезда *</label>
            <input type="date" name="arrival_date" required>
          </div>
          <div class="form-group">
            <label>Дата выезда *</label>
            <input type="date" name="return_date" required>
          </div>
        </div>

        <!-- Цена и фото -->
        <div class="form-row">
          <div class="form-group">
            <label>Базовая цена (₽) *</label>
            <input type="number" name="base_price" min="1000" step="100" placeholder="50000" required>
          </div>
          <div class="form-group">
            <label>URL изображения</label>
            <input type="url" name="image_url" placeholder="resources/images/tours/...">
          </div>
        </div>

        <!-- Кнопки -->
        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="closeModal('addTourModal')">Отмена</button>
          <button type="submit" class="btn-primary">Добавить тур</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleHotelMode() {
  const mode = document.querySelector('input[name="hotel_mode"]:checked').value;
  document.getElementById('existingHotelSection').style.display = mode === 'existing' ? 'block' : 'none';
  document.getElementById('newHotelSection').style.display = mode === 'new' ? 'block' : 'none';
}

function closeModal(id) {
  document.getElementById(id).style.display = 'none';
  document.body.style.overflow = 'auto';
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
  modal.style.display = 'block';
  loadHotels();
  
  // Прокрутка в начало окна при открытии
  setTimeout(() => {
    const modalBody = modal.querySelector('.modal-body');
    if (modalBody) {
      modalBody.scrollTop = 0;
    }
  }, 10);
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

// Обработка отправки формы
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('addTourForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const data = Object.fromEntries(formData);

      // Валидация вручную (или через HTML5 required)
      if (!data.vacation_type || !data.country || !data.city || !data.arrival_date || !data.return_date || !data.base_price) {
        alert('Заполните все обязательные поля');
        return;
      }

      if (data.hotel_mode === 'existing' && !data.existing_hotel_id) {
        alert('Выберите отель');
        return;
      }

      if (data.hotel_mode === 'new') {
        if (!data.new_hotel_name || !data.new_hotel_rating || !data.new_hotel_max_guests) {
          alert('Заполните данные нового отеля');
          return;
        }
      }

      // → Отправка на сервер (см. Step 2)
      fetch('?action=add-tour', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          alert('✅ Тур добавлен!\nID: ' + res.tour_id);
          closeModal('addTourModal');
          // Опционально: обновить список туров
          location.reload(); // или частичная перезагрузка
        } else {
          alert('❌ Ошибка: ' + (res.message || 'неизвестная'));
        }
      })
      .catch(err => {
        alert('Ошибка сети: ' + err.message);
      });
    });
  }
});
</script>