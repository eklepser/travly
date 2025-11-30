<?php
$pageTitle = 'Travly — Помощь';
require_once 'layout/header.php';
?>

<main class="help-page">
    <div class="help-content">

        <h1 class="faq-title">Популярные вопросы (FAQ)</h1>

        <div class="faq-section">

            <div class="faq-item">
                <h2>1. Как работает подбор тура?</h2>
                <span>Trav<span class="logo-text-highlight">ly</span> автоматически находит самые выгодные и подходящие предложения из сотен туроператоров. Просто укажите свои пожелания в фильтрах слева и нажмите «Подобрать!».</span>
            </div>

            <div class="faq-item">

                <h2>2. Почему важно указывать рейтинг отеля?</h2>
                <span>Рейтинг (звездность) помогает нам понять ваш уровень комфорта:</span>

                <div class="rating-examples">

                    <div class="rating-item">
                        <span class="star">★★★</span>
                        <span class="rating-text">— экономный вариант с базовыми услугами;</span>
                    </div>

                    <div class="rating-item">
                        <span class="star">★★★★</span>
                        <span class="rating-text">— комфортное размещение с хорошим сервисом;</span>
                    </div>

                    <div class="rating-item">
                        <span class="star">★★★★★</span>
                        <span class="rating-text">— премиум-отдых с высоким уровнем обслуживания.</span>
                    </div>

                </div>
            </div>

            <div class="faq-item">
                <h2>3. Что делать, если не уверены в направлении?</h2>
                <span>Оставьте поле «Направление» пустым! Система покажет вам лучшие предложения по выбранному типу отдыха и бюджету — возможно, вы откроете для себя новые интересные места.</span>
            </div>

        </div>

        <div class="support-section">

            <h2 class="support-title">Связь с поддержкой</h2>
            <p class="support-description">При возникновении любых вопросов вы всегда можете обратиться на линию поддержки:</p>

            <div class="contact-methods">
                <div class="contact-method">
                    <div class="contact-icon email-white"></div>
                    <span class="contact-info">trav<span class="logo-text-highlight">ly</span>_support@gmail.com</span>
                </div>
                <div class="contact-method">
                    <div class="contact-icon phone-white"></div>
                    <span class="contact-info">+7 (595) 555-90-90</span>
                </div>
            </div>

        </div>

        <button class="back-to-main-btn" onclick="window.location.href='index.html'">
            Вернуться на главную
        </button>

    </div>
</main>

<?php require_once 'layout/footer.php'; ?>