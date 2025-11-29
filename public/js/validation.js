function setupGlobalEventHandlers() {
    $(document)
        .on('blur', '.booking-form input', e => validateBookingField($(e.target)))
        .on('blur', '.auth-form input', e => validateAuthField($(e.target)))
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
            message = 'Введите корректный номер телефона (например, +7 (999) 123-45-67)';
            break;

        case 'email':
            valid = isValidEmail(v);
            message = 'Введите корректный email (например, example@domain.com)';
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

    setValidationState($input, valid, message);
    return valid;
}

function validateAuthField($input) {
    const id = $input.attr('id');
    const v = $input.val();

    let valid = false, message = '';

    switch (id) {
        case 'auth-email':
        case 'reg-email':
            const trimmed = v.trim();
            if (trimmed === '') {
                valid = false;
                message = 'Введите email или номер телефона';
            } else if (trimmed.includes('@')) {
                valid = isValidEmail(trimmed);
                message = valid ? '' : 'Введите корректный email (например, example@domain.com)';
            } else {
                valid = isValidPhone(trimmed);
                message = valid ? '' : 'Введите корректный номер телефона (например, +7 (999) 123-45-67)';
            }
            break;

        case 'auth-password':
        case 'reg-password':
            valid = v.length >= 6;
            message = 'Пароль должен быть не короче 6 символов';
            break;

        case 'reg-confirm-password':
            const pass = $('#reg-password').val();
            if (pass.length === 0) {
                valid = false;
                message = 'Сначала введите пароль';
            } else {
                valid = v === pass;
                message = 'Пароли не совпадают';
            }
            break;

        case 'reg-lastname':
        case 'reg-firstname':
            const nameVal = v.trim();
            valid = /^[A-Za-zА-Яа-яЁё\s\-]+$/.test(nameVal) && nameVal.length >= 2;
            message = 'Поле должно содержать только буквы (мин. 2 символа)';
            break;

        default:
            valid = v.trim().length > 0;
            message = 'Это поле обязательно для заполнения';
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

setupGlobalEventHandlers();