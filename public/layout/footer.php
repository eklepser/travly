<!-- FOOTER -->
<footer>
    <div class="contacts">
        <h3>Контактные данные:</h3>
        <div class="contact-item">
            <div class="contact-icon location"></div>
            <span>г. Рязань, ул. Пушкина, д.5</span>
        </div>
        <div class="contact-item">
            <div class="contact-icon phone"></div>
            <span>+7 (595) 555-95-95</span>
        </div>
        <div class="contact-item">
            <div class="contact-icon email"></div>
            <span>travly@gmail.com</span>
        </div>
    </div>
    <div class="social-media">
        <h3>Свяжитесь с нами!</h3>
        <div class="social-icons">
            <div class="social-icon telegram"></div>
            <div class="social-icon vk"></div>
            <div class="social-icon whatsapp"></div>
        </div>
    </div>
    <div class="disclaimer">
        <p>
            Вся информация, размещённая на сайте, носит информационный характер и не является рекламой и публичной офертой.
            Правила и условия предоставления услуг в отелях, в том числе концепция питания, описанные на сайте, могут изменяться по решению администрации отелей.
            Копирование материалов без письменного согласия запрещено.
            Сумма, отображаемая на сайте, включает в себя стоимость туристического продукта и размер лицензионного вознаграждения.
        </p>
    </div>
</footer>

<?php if (!empty($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
        <script src="<?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>