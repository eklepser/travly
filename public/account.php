<?php
$pageTitle = 'Travly — Мой профиль';
require_once 'layout/header.php';
?>

<main class="account-main">
    <section class="profile-section">
        <h1 class="profile-title">Личный кабинет</h1>

        <div class="profile-content">
            <div class="profile-left">
                <div class="info-row">
                    <div class="info-group">
                        <label>Фамилия</label>
                        <div class="info-field">
                            <span class="text-value">Tishkov</span>
                            <input type="text" class="edit-input" value="Tishkov" style="display: none;">
                        </div>
                    </div>
                    <div class="info-group">
                        <label>Имя</label>
                        <div class="info-field">
                            <span class="text-value">Nikita</span>
                            <input type="text" class="edit-input" value="Nikita" style="display: none;">
                        </div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-group">
                        <label>Номер телефона</label>
                        <div class="info-field"><span>+7 (929) 066-66-56</span></div>
                    </div>
                    <div class="info-group">
                        <label>E-mail адрес</label>
                        <div class="info-field"><span>nikitos@gmail.com</span></div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-group">
                        <label>Статус</label>
                        <div class="info-field"><span>Турист</span></div>
                    </div>
                    <div class="info-group">
                        <label>Дата регистрации</label>
                        <div class="info-field"><span>02.05.2025</span></div>
                    </div>
                </div>

                <div class="account-actions">
                    <button class="edit-btn" id="editToggle">Изменить</button>
                    <button class="save-btn" style="display: none;">Сохранить</button>
                    <button class="cancel-btn" style="display: none;">Отмена</button>
                    <button class="extra-button logout-btn">Выйти</button>
                </div>
            </div>

            <div class="profile-right">
                <div class="logo">
                    <span class="logo-text">Trav<span class="logo-text-highlight">ly</span></span>
                    <div class="logo-icon"></div>
                </div>

                <div class="promo-section">
                    <label class="promo-label">Активировать промокод</label>
                    <input type="text" value="TRAVLYPROMO">
                    <button class="activate-btn" onclick="testPromo()">Активировать</button>
                </div>
            </div>
        </div>
    </section>

    <section class="tours-section">
        <h1 class="tours-title">Забронированные туры</h1>

        <section class="booking-hero" data-booking-id="BK-2025-001">
            <div class="booking-content">
                <div class="booking-card-wrapper">
                    <h2>Турция, Стамбул. 10–15 октября 2025</h2>
                    <div class="booking-card">
                        <div class="card-image"></div>
                    </div>
                    <button class="extra-button" data-action="cancel-booking">Отменить бронирование</button>
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
                            <span class="info-label">Доп. услуги:</span>
                            <span class="info-value">трансфер, медицинская страховка</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Питание:</span>
                            <span class="info-value">завтрак, обед, ужин</span>
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
                            <span class="info-value">120 000 рублей</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="booking-hero" data-booking-id="BK-2025-002">
            <div class="booking-content">
                <div class="booking-card-wrapper">
                    <h2>Испания, Барселона. 20–27 октября 2025</h2>
                    <div class="booking-card">
                        <div class="card-image"></div>
                    </div>
                    <button class="extra-button" data-action="cancel-booking">Отменить бронирование</button>
                </div>

                <div class="tour-info">
                    <h3>Информация о туре</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Место отправления:</span>
                            <span class="info-value">Россия, г. Москва, а/п Домодедово</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Дата отправления:</span>
                            <span class="info-value">2025 год, 20 октября, 08.30 (МСК)</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Место прибытия:</span>
                            <span class="info-value">Испания, Барселона, а/п Эль-Прат</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Дата прибытия:</span>
                            <span class="info-value">2025 год, 20 октября, 15.00 (МСК)</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Место отъезда:</span>
                            <span class="info-value">Испания, Барселона, а/п Эль-Прат</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Дата отъезда:</span>
                            <span class="info-value">2025 год, 27 октября, 17.20 (МСК)</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Отель:</span>
                            <span class="info-value">Barcelona Bay Hotel (4 звезды)</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Длительность тура:</span>
                            <span class="info-value">7 ночей</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Туристы:</span>
                            <span class="info-value">2 взрослых</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Питание:</span>
                            <span class="info-value">завтраки</span>
                        </div>
                        <div class="info-item total-cost">
                            <span class="info-label">Стоимость тура</span>
                            <span class="info-value">185 000 рублей</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>

    <div class="modal-overlay" id="cancelModal" style="display: none;">
        <div class="modal">
            <h3>Подтверждение отмены</h3>
            <p>Вы уверены, что хотите отменить бронирование этого тура?</p>
            <div class="modal-buttons">
                <button class="modal-btn secondary" id="cancelNo">Нет</button>
                <button class="modal-btn primary" id="cancelYes">Да, отменить</button>
            </div>
        </div>
    </div>
</main>

<script src="script/account.js"></script>

<?php require_once 'layout/footer.php'; ?>