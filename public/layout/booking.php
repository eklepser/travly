<main class="booking-main">
    <section class="booking-hero">
        <div class="booking-header">
            <h1>Бронирование тура</h1>
            <h2>Турция, Стамбул. 10-15 октября 2025</h2>
        </div>

        <div class="booking-content">
            <div class="booking-left-section">
                <div class="booking-card"></div>
                <button class="change-services-btn" onclick="location.href='hotel-selection.php'">Изменить услуги</button>
            </div>

            <div class="tour-info">
                <h3>Информация о туре</h3>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Место отправления:</span>
                        <span class="info-value">Россия, г. Москва, а/п Шереметьево</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Дата отправления:</span>
                        <span class="info-value">2025 год, 10 октября, 10.00 (МСК)</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Место прибытия:</span>
                        <span class="info-value">Турция, Стамбул, а/п Ататюрка</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Дата прибытия:</span>
                        <span class="info-value">2025 год, 10 октября, 19.00 (МСК)</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Место отъезда:</span>
                        <span class="info-value">Турция, Стамбул, а/п Ататюрка</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Дата отъезда:</span>
                        <span class="info-value">2025 год, 15 октября, 19.00 (МСК)</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Отель:</span>
                        <span class="info-value">Weingart Suites Hotel (4 звезды)</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Питание:</span>
                        <span class="info-value">завтрак, обед, ужин</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Доп. услуги:</span>
                        <span class="info-value">трансфер, медицинская страховка</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Длительность тура:</span>
                        <span class="info-value">5 ночей</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Туристы:</span>
                        <span class="info-value">2 взрослых, 1 ребенок</span>
                    </div>

                    <div class="info-item total-cost">
                        <span class="info-label">Стоимость тура</span>
                        <span class="info-value">120000 рублей</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="booking-form">
        <div class="booking-form-header">
            <button class="nav-arrow nav-arrow-left" id="prevFormBtn" style="display: none;">
                <span>←</span>
            </button>
            <h3 id="formTitle">Данные туриста 1 (заказчик)</h3>
            <button class="nav-arrow nav-arrow-right" id="nextFormBtn" style="display: none;">
                <span>→</span>
            </button>
        </div>

        <div class="booking-forms-container" id="bookingFormsContainer">
        </div>

        <div class="form-buttons">
            <button class="clear-btn" id="clearCurrentFormBtn">Очистить данные</button>
        </div>
    </section>

    <div class="final-button">
        <button class="book-btn" id="bookTourBtn">Забронировать тур</button>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="script/validation.js"></script>
<script src="script/booking-forms.js"></script>