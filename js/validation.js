// validation.js
class FormValidator {
    constructor() {
        this.init();
    }

    init() {
        // Инициализация при загрузке DOM
        $(document).ready(() => {
            this.setupGlobalEventHandlers();
            this.setupValidation();
        });

        // Перехват события загрузки страницы из menu.js
        this.interceptPageLoad();
    }

    // Перехватываем функцию loadPage из menu.js
    interceptPageLoad() {
        // Сохраняем оригинальную функцию
        const originalLoadPage = window.loadPage;

        if (originalLoadPage) {
            window.loadPage = function(pagePath) {
                return originalLoadPage.call(this, pagePath).then(() => {
                    // После загрузки страницы инициализируем валидацию
                    setTimeout(() => {
                        window.formValidator.setupValidation();
                    }, 100);
                });
            };
        }
    }

    setupGlobalEventHandlers() {
        // Глобальные обработчики через делегирование
        $(document)
            .on('input', 'input', (e) => {
                // При вводе текста обновляем цвет
                const $input = $(e.target);
                if ($input.val().trim() !== '') {
                    $input.css('color', '#1E1E1E');
                }
            })
            .on('blur', '.auth-form input[type="text"]', (e) => {
                const $input = $(e.target);
                const placeholder = $input.attr('placeholder');
                if (placeholder === 'E-mail или номер телефона') {
                    this.validateEmailOrPhone($input);
                }
            })
            .on('blur', '.auth-form input[type="password"]', (e) => {
                const $input = $(e.target);
                if ($input.attr('placeholder') === 'Пароль') {
                    this.validatePassword($input);
                } else if ($input.attr('placeholder') === 'Подтвердите пароль') {
                    this.validateConfirmPassword($input);
                }
            })
            .on('blur', '.auth-form input[placeholder="Фамилия"], .auth-form input[placeholder="Имя"]', (e) => {
                this.validateName($(e.target));
            })
            .on('blur', '.booking-form input', (e) => {
                this.validateBookingField($(e.target));
            })
            .on('submit', '.auth-form', (e) => {
                e.preventDefault();
                const $form = $(e.target);
                if ($form.find('input[placeholder="Фамилия"]').length) {
                    // Форма регистрации
                    if (this.validateRegistrationForm()) {
                        $form[0].submit();
                    }
                } else {
                    // Форма входа
                    if (this.validateAuthForm()) {
                        $form[0].submit();
                    }
                }
            })
            .on('click', '.booking-form .clear-btn', (e) => {
                e.preventDefault();
                const $form = $(e.target).closest('.booking-form');
                this.clearForm($form);
            })
            .on('click', '.book-btn', (e) => {
                e.preventDefault();
                if (this.validateAllBookingForms()) {
                    alert('Бронирование прошло успешно!');
                }
            });
    }

    setupValidation() {
        console.log('Initializing validation for dynamically loaded content...');

        // Дополнительная инициализация для специфических случаев
        this.setupAuthValidation();
        this.setupRegistrationValidation();
        this.setupBookingValidation();
    }

    // Дополнительная инициализация форм авторизации
    setupAuthValidation() {
        const $authForm = $('.auth-form').not(':has(input[placeholder="Фамилия"])');
        if (!$authForm.length) return;

        console.log('Auth form found, setting up validation...');
    }

    // Дополнительная инициализация форм регистрации
    setupRegistrationValidation() {
        const $regForm = $('.auth-form').has('input[placeholder="Фамилия"]');
        if (!$regForm.length) return;

        console.log('Registration form found, setting up validation...');
    }

    // Дополнительная инициализация форм бронирования
    setupBookingValidation() {
        const $bookingForms = $('.booking-form');
        if (!$bookingForms.length) return;

        console.log('Booking forms found:', $bookingForms.length);
    }

    // Валидация email или телефона
    validateEmailOrPhone($input) {
        const value = $input.val().trim();
        let isValid = false;

        if (value.includes('@')) {
            isValid = this.isValidEmail(value);
        } else {
            isValid = this.isValidPhone(value);
        }

        this.setValidationState($input, isValid,
            isValid ? '' : 'Введите корректный email или номер телефона'
        );
        return isValid;
    }

    // Валидация имени/фамилии
    validateName($input) {
        const value = $input.val().trim();
        const isValid = /^[A-Za-zА-Яа-яЁё\s\-]+$/.test(value) && value.length >= 2;

        this.setValidationState($input, isValid,
            isValid ? '' : 'Имя должно содержать только буквы и быть не короче 2 символов'
        );
        return isValid;
    }

    // Валидация пароля
    validatePassword($input) {
        const value = $input.val();
        const isValid = value.length >= 6;

        this.setValidationState($input, isValid,
            isValid ? '' : 'Пароль должен быть не короче 6 символов'
        );
        return isValid;
    }

    // Валидация подтверждения пароля
    validateConfirmPassword($input) {
        const $passwordInput = $input.closest('.auth-form').find('input[type="password"]').first();
        const isValid = $input.val() === $passwordInput.val();

        this.setValidationState($input, isValid,
            isValid ? '' : 'Пароли не совпадают'
        );
        return isValid;
    }

    // Валидация полей бронирования
    validateBookingField($input) {
        const placeholder = $input.attr('placeholder') || '';
        const value = $input.val().trim();
        let isValid = false;
        let message = '';

        switch(placeholder) {
            case 'Фамилия':
            case 'Имя':
            case 'Отчество':
                isValid = /^[A-Za-zА-Яа-яЁё\s\-]+$/.test(value) && value.length >= 2;
                message = isValid ? '' : 'Поле должно содержать только буквы (мин. 2 символа)';
                break;

            case 'дд.мм.гггг':
                isValid = this.isValidDate(value);
                message = isValid ? '' : 'Введите дату в формате дд.мм.гггг';
                break;

            case 'Серия':
                isValid = /^\d{4}$/.test(value);
                message = isValid ? '' : 'Серия должна содержать 4 цифры';
                break;

            case 'Номер документа':
                isValid = /^\d{6}$/.test(value);
                message = isValid ? '' : 'Номер должен содержать 6 цифр';
                break;

            case 'Код подразделения':
                isValid = /^\d{3}-\d{3}$/.test(value);
                message = isValid ? '' : 'Код должен быть в формате 000-000';
                break;

            case 'Номер свидетельства о рождении':
                isValid = /^[A-Za-zА-Яа-яЁё0-9\-\s]+$/.test(value) && value.length >= 5;
                message = isValid ? '' : 'Введите корректный номер свидетельства';
                break;

            case 'Номер телефона':
                isValid = this.isValidPhone(value);
                message = isValid ? '' : 'Введите корректный номер телефона';
                break;

            case 'E-mail адрес':
                isValid = this.isValidEmail(value);
                message = isValid ? '' : 'Введите корректный email';
                break;

            case 'Адрес регистрации':
            case 'Орган, выдавший документ':
                isValid = value.length >= 5;
                message = isValid ? '' : 'Поле должно содержать минимум 5 символов';
                break;

            default:
                isValid = value.length > 0;
                message = isValid ? '' : 'Это поле обязательно для заполнения';
        }

        this.setValidationState($input, isValid, message);
        return isValid;
    }

    // Вспомогательные методы валидации
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    isValidPhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 10 && cleaned.length <= 15;
    }

    isValidDate(date) {
        if (!/^\d{2}\.\d{2}\.\d{4}$/.test(date)) return false;

        const parts = date.split('.');
        const day = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10);
        const year = parseInt(parts[2], 10);

        if (year < 1900 || year > new Date().getFullYear()) return false;
        if (month < 1 || month > 12) return false;

        const daysInMonth = new Date(year, month, 0).getDate();
        return day >= 1 && day <= daysInMonth;
    }

    setValidationState($input, isValid, message) {
        // Находим родительский .input-field или .form-group
        const $container = $input.closest('.input-field');

        // Удаляем предыдущие сообщения об ошибках
        $container.find('.error-message').remove();

        // Удаляем предыдущие классы
        $input.removeClass('valid invalid');

        if (isValid) {
            $input.addClass('valid');
        } else {
            $input.addClass('invalid');

            // Добавляем сообщение об ошибке
            if (message) {
                const $errorElement = $('<div>')
                    .addClass('error-message')
                    .text(message)
                    .css({
                        color: '#d32f2f',
                        fontSize: '12px',
                        marginTop: '4px',
                        fontFamily: 'Inter, sans-serif',
                        display: 'block',
                        minHeight: '16px'
                    });

                // Добавляем после input внутри .input-field
                $input.after($errorElement);
            }
        }
    }

// Добавим метод для исправления цвета текста
    fixInputTextColor() {
        $('input').each(function() {
            const $input = $(this);
            // Если в поле есть текст, убедимся что он темный
            if ($input.val().trim() !== '') {
                $input.css('color', '#1E1E1E');
            }
        });
    }

// Обновим setupValidation
    setupValidation() {
        console.log('Initializing validation for dynamically loaded content...');
        this.fixInputTextColor();

        // Дополнительная инициализация для специфических случаев
        this.setupAuthValidation();
        this.setupRegistrationValidation();
        this.setupBookingValidation();
    }

    // Проверка всей формы авторизации
    validateAuthForm() {
        const $authForm = $('.auth-form').not(':has(input[placeholder="Фамилия"])');
        if (!$authForm.length) return true;

        let isValid = true;

        const $emailInput = $authForm.find('input[type="text"]');
        const $passwordInput = $authForm.find('input[type="password"]');

        if ($emailInput.length) {
            isValid = this.validateEmailOrPhone($emailInput) && isValid;
        }

        if ($passwordInput.length) {
            isValid = this.validatePassword($passwordInput) && isValid;
        }

        if (!isValid) {
            alert('Пожалуйста, исправьте ошибки в форме');
        }

        return isValid;
    }

    // Проверка всей формы регистрации
    validateRegistrationForm() {
        const $regForm = $('.auth-form').has('input[placeholder="Фамилия"]');
        if (!$regForm.length) return true;

        let isValid = true;

        $regForm.find('input').each((index, input) => {
            const $input = $(input);
            switch($input.attr('placeholder')) {
                case 'Фамилия':
                case 'Имя':
                    isValid = this.validateName($input) && isValid;
                    break;
                case 'E-mail или номер телефона':
                    isValid = this.validateEmailOrPhone($input) && isValid;
                    break;
                case 'Пароль':
                    isValid = this.validatePassword($input) && isValid;
                    break;
                case 'Подтвердите пароль':
                    isValid = this.validateConfirmPassword($input) && isValid;
                    break;
            }
        });

        if (!isValid) {
            alert('Пожалуйста, исправьте ошибки в форме регистрации');
        }

        return isValid;
    }

    // Проверка всех форм бронирования
    validateAllBookingForms() {
        const $bookingForms = $('.booking-form');
        let allValid = true;

        $bookingForms.each((index, form) => {
            const $form = $(form);
            $form.find('input').each((i, input) => {
                if (!this.validateBookingField($(input))) {
                    allValid = false;
                }
            });
        });

        if (!allValid) {
            alert('Пожалуйста, исправьте ошибки в форме бронирования');
        }

        return allValid;
    }

    // Очистка формы
    clearForm($form) {
        $form.find('input').each((index, input) => {
            const $input = $(input);
            $input.val('');
            $input.removeClass('valid invalid');
            $input.siblings('.error-message').remove();
        });
    }
}

// Инициализация при загрузке страницы
$(document).ready(() => {
    window.formValidator = new FormValidator();
});