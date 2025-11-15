$(document).ready(function () {
    interceptPageLoad();
});

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

function setValidationState($input, isValid, message = '') {
    const $group = $input.closest('.form-group');
    $group.find('.error-message').remove();
    $input.removeClass('valid invalid').addClass(isValid ? 'valid' : 'invalid');

    if (!isValid && message && $input.val().trim()) {
        $group.append(`<div class="error-message">${message}</div>`);
    }
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

function validateForm($form) {
    let valid = true;

    if ($form.hasClass('auth-form')) {
        if ($form.find('#auth-email').length) {
            valid = validateEmailOrPhone($('#auth-email')) && validatePassword($('#auth-password')) && valid;
        }
        else if ($form.find('#reg-lastname').length) {
            valid = validateName($('#reg-lastname'))
                && validateName($('#reg-firstname'))
                && validateEmailOrPhone($('#reg-email'))
                && validatePassword($('#reg-password'))
                && validateConfirmPassword($('#reg-confirm-password'))
                && valid;
        }
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

function setupGlobalEventHandlers() {
    $(document)
        .on('input', 'input', e => {
            const $in = $(e.target);
            if ($in.val().trim()) $in.css('color', '#1E1E1E');
        })

        .on('blur', '#auth-email, #reg-email, #auth-password, #reg-password, #reg-confirm-password, #reg-lastname, #reg-firstname', e => {
            const $in = $(e.target);
            const id = $in.attr('id');
            if (id.includes('email')) validateEmailOrPhone($in);
            else if (id.includes('password')) {
                if (id === 'reg-confirm-password') validateConfirmPassword($in);
                else validatePassword($in);
            }
            else validateName($in);
        })

        .on('blur', '.booking-form input', e => validateBookingField($(e.target)))

        .on('submit', '.auth-form', e => {
            e.preventDefault();
            const $form = $(e.target);
            if (validateForm($form)) $form[0].submit();
        })

        .on('click', '.booking-form .clear-btn', e => {
            e.preventDefault();
            clearForm($(e.target).closest('.booking-form'));
        })

        .on('click', '.book-btn', e => {
            e.preventDefault();
            if (validateForm($('.booking-form'))) alert('Бронирование прошло успешно!');
        });
}

function interceptPageLoad() {
    const orig = window.loadPage;
    if (orig) {
        window.loadPage = function (page) {
            return orig.call(this, page).then(() => setTimeout(setupValidation, 100));
        };
    }
}

setupGlobalEventHandlers();