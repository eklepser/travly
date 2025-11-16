function setupGlobalEventHandlers() {
    $(document)
        // обработчики полей ввода
        .on('blur', '.booking-form input', e => validateBookingField($(e.target)))

        .on('blur', '.auth-form input', e => validateBookingField($(e.target)))

        // обработчики кнопок подвтерждения
        .on('click', '.book-btn', e => {
            e.preventDefault();
            if (validateForm($('.booking-form'))) alert('Бронирование прошло успешно!');
        })

        .on('click', '.submit-btn', e => {
            e.preventDefault();
            if (validateForm($('.auth-form'))) alert('Регистрация прошла успешно!');
        })

        .on('click', '.booking-form .clear-btn', e => {
            e.preventDefault();
            clearForm($(e.target).closest('.booking-form'));
    });
}

function setValidationState($input, isValid, message = '') {
    const $group = $input.closest('.form-group');
    $group.find('.error-message').remove();
    $input.removeClass('valid invalid').addClass(isValid ? 'valid' : 'invalid');

    if (!isValid && message) {
        $group.append(`<div class="error-message">${message}</div>`);
    }
}

function validateBookingField($input) {
    const id = $input.attr('id');
    const v = $input.val().trim();

    let valid = false, message = '';

    switch (id) {
        case 'lastname':
        case 'firstname':
        case 'middlename':
            valid = /^[A-Za-zА-Яа-яЁё\s\-]+$/.test(v) && v.length >= 2;
            message = 'Поле должно содержать только буквы (мин. 2 символа)';
            break;

        case 'birthdate':
        case 'doc-issue-date':
        case 'child-doc-date':
            valid = isValidDate(v);
            message = 'Введите дату в формате дд.мм.гггг';
            break;

        case 'doc-series':
            valid = /^\d{4}$/.test(v);
            message = 'Серия должна содержать 4 цифры';
            break;

        case 'doc-number':
            valid = /^\d{6}$/.test(v);
            message = 'Номер должен содержать 6 цифр';
            break;

        case 'doc-department-code':
            valid = /^\d{3}-\d{3}$/.test(v);
            message = 'Код должен быть в формате 000-000';
            break;

        case 'child-doc-number':
            valid = /^[A-Za-zА-Яа-яЁё0-9\-\s]+$/.test(v) && v.length >= 5;
            message = 'Введите корректный номер свидетельства';
            break;

        case 'phone':
            valid = isValidPhone(v);
            message = 'Введите корректный номер телефона';
            break;

        case 'email':
            valid = isValidEmail(v);
            message = 'Введите корректный email';
            break;

        case 'address':
        case 'doc-issuing-authority':
            valid = v.length >= 5;
            message = 'Поле должно содержать минимум 5 символов';
            break;

        default:
            valid = v.length > 0;
            message = 'Это поле обязательно для заполнения';
    }

    setValidationState($input, valid, valid ? '' : message);
    return valid;
}

function validateAuthField($input) {
    const id = $input.attr('id');
    const v = $input.val().trim();

    let valid = false, message = '';

    switch (id) {
        case 'auth-email':
        case 'reg-email':
            if (v.includes('@')) {
                valid = isValidEmail(v);
                message = valid ? '' : 'Введите корректный email';
            } else {
                valid = isValidPhone(v);
                message = valid ? '' : 'Введите корректный номер телефона';
            }
            break;

        case 'auth-password':
        case 'reg-password':
            valid = v.length >= 6;
            message = 'Пароль должен быть не короче 6 символов';
            break;

        case 'reg-confirm-password':
            valid = v === $('#reg-password').val();
            message = 'Пароли не совпадают';
            break;

        case 'reg-lastname':
        case 'reg-firstname':
            valid = /^[A-Za-zА-Яа-яЁё\s\-]+$/.test(v) && v.length >= 2;
            message = 'Поле должно содержать только буквы (мин. 2 символа)';
            break;

        default:
            valid = true;
    }

    setValidationState($input, valid, message);
    return valid;
}

function validateForm($form) {
    let valid = true;

    if ($form.hasClass('auth-form')) {
        $form.find('input').each((_, el) => {
            if (!validateAuthField($(el))) valid = false;
        });
    }
    else if ($form.hasClass('booking-form')) {
        $form.find('input').each((_, el) => {
            if (!validateBookingField($(el))) valid = false;
        });
    }

    return valid;
}

function clearForm($form) {
    $form.find('input')
        .val('')
        .removeClass('valid invalid');
    $form.find('.error-message').remove();
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
    const [d, m, y] = date.split('.').map(Number);
    if (y < 1900 || y > new Date().getFullYear() || m < 1 || m > 12) return false;
    return d >= 1 && d <= new Date(y, m, 0).getDate();
}

function validateEmailOrPhone($input) {
    const v = $input.val().trim();
    const valid = v.includes('@') ? isValidEmail(v) : isValidPhone(v);
    setValidationState($input, valid, valid ? '' : 'Введите корректный email или номер телефона');
    return valid;
}

function validateName($input) {
    const v = $input.val().trim();
    const valid = /^[A-Za-zА-Яа-яЁё\s\-]+$/.test(v) && v.length >= 2;
    setValidationState($input, valid, valid ? '' : 'Имя должно содержать только буквы и быть не короче 2 символов');
    return valid;
}

function validatePassword($input) {
    const v = $input.val();
    const valid = v.length >= 6;
    setValidationState($input, valid, valid ? '' : 'Пароль должен быть не короче 6 символов');
    return valid;
}

function validateConfirmPassword($input) {
    const valid = $input.val() === $('#reg-password').val();
    setValidationState($input, valid, valid ? '' : 'Пароли не совпадают');
    return valid;
}

setupGlobalEventHandlers();