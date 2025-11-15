// validation.js
$(document).ready(function() {
    setupValidation();
    interceptPageLoad();
});

function interceptPageLoad() {
    const originalLoadPage = window.loadPage;

    if (originalLoadPage) {
        window.loadPage = function(pagePath) {
            return originalLoadPage.call(this, pagePath).then(() => {
                setTimeout(() => {
                    setupValidation();
                }, 100);
            });
        };
    }
}

function setupGlobalEventHandlers() {
    $(document)
        .on('input', 'input', function(e) {
            const $input = $(e.target);
            if ($input.val().trim() !== '') {
                $input.css('color', '#1E1E1E');
            }
        })
        .on('blur', '#auth-email', function(e) {
            validateEmailOrPhone($(e.target));
        })
        .on('blur', '#auth-password', function(e) {
            validatePassword($(e.target));
        })
        .on('blur', '#reg-lastname, #reg-firstname', function(e) {
            validateName($(e.target));
        })
        .on('blur', '#reg-email', function(e) {
            validateEmailOrPhone($(e.target));
        })
        .on('blur', '#reg-password', function(e) {
            validatePassword($(e.target));
        })
        .on('blur', '#reg-confirm-password', function(e) {
            validateConfirmPassword($(e.target));
        })
        .on('blur', '.booking-form input', function(e) {
            validateBookingField($(e.target));
        })
        .on('submit', '.auth-form', function(e) {
            e.preventDefault();
            const $form = $(e.target);
            if ($form.find('#reg-lastname').length) {
                if (validateRegistrationForm()) {
                    $form[0].submit();
                }
            } else {
                if (validateAuthForm()) {
                    $form[0].submit();
                }
            }
        })
        .on('click', '.booking-form .clear-btn', function(e) {
            e.preventDefault();
            const $form = $(e.target).closest('.booking-form');
            clearForm($form);
        })
        .on('click', '.book-btn', function(e) {
            e.preventDefault();
            if (validateAllBookingForms()) {
                alert('Бронирование прошло успешно!');
            }
        });
}

function validateEmailOrPhone($input) {
    const value = $input.val().trim();
    let isValid = false;

    if (value.includes('@')) {
        isValid = isValidEmail(value);
    } else {
        isValid = isValidPhone(value);
    }

    setValidationState($input, isValid,
        isValid ? '' : 'Введите корректный email или номер телефона'
    );
    return isValid;
}

function validateName($input) {
    const value = $input.val().trim();
    const isValid = /^[A-Za-zА-Яа-яЁё\s\-]+$/.test(value) && value.length >= 2;

    setValidationState($input, isValid,
        isValid ? '' : 'Имя должно содержать только буквы и быть не короче 2 символов'
    );
    return isValid;
}

function validatePassword($input) {
    const value = $input.val();
    const isValid = value.length >= 6;

    setValidationState($input, isValid,
        isValid ? '' : 'Пароль должен быть не короче 6 символов'
    );
    return isValid;
}

function validateConfirmPassword($input) {
    const $passwordInput = $('#reg-password');
    const isValid = $input.val() === $passwordInput.val();

    setValidationState($input, isValid,
        isValid ? '' : 'Пароли не совпадают'
    );
    return isValid;
}

function validateBookingField($input) {
    const id = $input.attr('id');
    const value = $input.val().trim();
    let isValid = false;
    let message = '';

    switch(id) {
        case 'lastname':
        case 'firstname':
        case 'middlename':
            isValid = /^[A-Za-zА-Яа-яЁё\s\-]+$/.test(value) && value.length >= 2;
            message = isValid ? '' : 'Поле должно содержать только буквы (мин. 2 символа)';
            break;

        case 'birthdate':
        case 'doc-issue-date':
        case 'child-doc-date':
            isValid = isValidDate(value);
            message = isValid ? '' : 'Введите дату в формате дд.мм.гггг';
            break;

        case 'doc-series':
            isValid = /^\d{4}$/.test(value);
            message = isValid ? '' : 'Серия должна содержать 4 цифры';
            break;

        case 'doc-number':
            isValid = /^\d{6}$/.test(value);
            message = isValid ? '' : 'Номер должен содержать 6 цифр';
            break;

        case 'doc-department-code':
            isValid = /^\d{3}-\d{3}$/.test(value);
            message = isValid ? '' : 'Код должен быть в формате 000-000';
            break;

        case 'child-doc-number':
            isValid = /^[A-Za-zА-Яа-яЁё0-9\-\s]+$/.test(value) && value.length >= 5;
            message = isValid ? '' : 'Введите корректный номер свидетельства';
            break;

        case 'phone':
            isValid = isValidPhone(value);
            message = isValid ? '' : 'Введите корректный номер телефона';
            break;

        case 'email':
            isValid = isValidEmail(value);
            message = isValid ? '' : 'Введите корректный email';
            break;

        case 'address':
        case 'doc-issuing-authority':
            isValid = value.length >= 5;
            message = isValid ? '' : 'Поле должно содержать минимум 5 символов';
            break;

        default:
            isValid = value.length > 0;
            message = isValid ? '' : 'Это поле обязательно для заполнения';
    }

    setValidationState($input, isValid, message);
    return isValid;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidPhone(phone) {
    const cleaned = phone.replace(/\D/g, '');
    return cleaned.length >= 10 && cleaned.length <= 15;
}

function isValidDate(date) {
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

function setValidationState($input, isValid, message) {
    const $formGroup = $input.closest('.form-group');

    $formGroup.find('.error-message').remove();
    $input.removeClass('valid invalid');

    if (isValid) {
        $input.addClass('valid');
    } else {
        $input.addClass('invalid');

        if (message && $input.val().trim() !== '') {
            const $errorElement = $('<div>')
                .addClass('error-message')
                .text(message);

            $formGroup.append($errorElement);
        }
    }
}

function validateAuthForm() {
    const $authForm = $('.auth-form').not(':has(#reg-lastname)');
    if (!$authForm.length) return true;

    let isValid = true;

    const $emailInput = $('#auth-email');
    const $passwordInput = $('#auth-password');

    if ($emailInput.length) {
        isValid = validateEmailOrPhone($emailInput) && isValid;
    }

    if ($passwordInput.length) {
        isValid = validatePassword($passwordInput) && isValid;
    }

    return isValid;
}

function validateRegistrationForm() {
    const $regForm = $('.auth-form').has('#reg-lastname');
    if (!$regForm.length) return true;

    let isValid = true;

    isValid = validateName($('#reg-lastname')) && isValid;
    isValid = validateName($('#reg-firstname')) && isValid;
    isValid = validateEmailOrPhone($('#reg-email')) && isValid;
    isValid = validatePassword($('#reg-password')) && isValid;
    isValid = validateConfirmPassword($('#reg-confirm-password')) && isValid;

    return isValid;
}

function validateAllBookingForms() {
    const $bookingForms = $('.booking-form');
    let allValid = true;

    $bookingForms.each(function() {
        const $form = $(this);
        $form.find('input').each(function() {
            if (!validateBookingField($(this))) {
                allValid = false;
            }
        });
    });

    return allValid;
}

function clearForm($form) {
    $form.find('input').each(function() {
        const $input = $(this);
        $input.val('');
        $input.removeClass('valid invalid');
        $input.closest('.form-group').find('.error-message').remove();
    });
}

setupGlobalEventHandlers();