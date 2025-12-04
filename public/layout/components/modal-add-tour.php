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

<style>
/* === Модальное окно === */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 20px;
  box-sizing: border-box;
  overflow-y: auto;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 50%;
  max-width: 800px;
  min-width: 600px;
  box-shadow: 0 4px 30px rgba(0,0,0,0.3);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  margin: auto;
}

.modal-header {
  padding: 1.2rem 1.8rem;
  background: #2c3e50;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.modal-header h3 { 
  margin: 0; 
  font-size: 1.4rem;
}

.close {
  font-size: 2rem;
  cursor: pointer;
  line-height: 1;
  opacity: 0.8;
  transition: opacity 0.2s;
}

.close:hover {
  opacity: 1;
}

.modal-body {
  padding: 1.8rem;
  overflow-y: auto;
  flex: 1;
}

/* Стилизация скроллбара для модального контента */
.modal-body::-webkit-scrollbar {
  width: 6px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.modal-body::-webkit-scrollbar-thumb {
  background: #bdc3c7;
  border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: #95a5a6;
}

.form-group {
  margin-bottom: 1.2rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #2c3e50;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  box-sizing: border-box;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-row {
  display: flex;
  gap: 1.2rem;
}

.form-row .form-group { 
  flex: 1; 
}

.radio-group {
  display: flex;
  gap: 1.5rem;
  margin-top: 0.5rem;
}

.radio-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-weight: normal;
  color: #34495e;
}

.radio-group input[type="radio"] {
  width: auto;
  margin: 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.2rem 1.8rem;
  background: #f8f9fa;
  border-top: 1px solid #eee;
  flex-shrink: 0;
}

.btn-primary {
  background: #3498db;
  color: white;
  border: none;
  padding: 0.75rem 1.8rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  transition: background 0.2s;
}

.btn-primary:hover { 
  background: #2980b9; 
}

.btn-secondary {
  background: #ecf0f1;
  border: 1px solid #bdc3c7;
  padding: 0.75rem 1.8rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  transition: background 0.2s;
}

.btn-secondary:hover {
  background: #d5dbdb;
}
</style>

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
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
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