(function() {
    function getTouristsConfig() {
        const urlParams = new URLSearchParams(window.location.search);
        let adults = parseInt(urlParams.get('adults'));
        let children = parseInt(urlParams.get('children'));
        
        if (!adults || adults < 1) {
            const touristsItems = document.querySelectorAll('.info-item');
            touristsItems.forEach(item => {
                const label = item.querySelector('.info-label');
                const value = item.querySelector('.info-value');
                if (label && value && label.textContent.includes('Туристы')) {
                    const text = value.textContent;
                    const adultsMatch = text.match(/(\d+)\s*взросл/);
                    const childrenMatch = text.match(/(\d+)\s*ребен/);
                    if (adultsMatch) adults = parseInt(adultsMatch[1]);
                    if (childrenMatch) children = parseInt(childrenMatch[1]);
                }
            });
        }
        
        return {
            adults: adults || 1,
            children: children || 0
        };
    }

    let currentFormIndex = 0;
    let forms = [];
    let touristsConfig = getTouristsConfig();

    function initBookingForms() {
        touristsConfig = getTouristsConfig();
        
        if (touristsConfig.adults < 1) {
            console.warn('Количество взрослых туристов должно быть минимум 1');
            touristsConfig.adults = 1;
        }
        
        const totalTourists = touristsConfig.adults + touristsConfig.children;
        forms = [];

        for (let i = 0; i < totalTourists; i++) {
            const isCustomer = i === 0;
            const isChild = i >= touristsConfig.adults;
            forms.push({
                index: i,
                isCustomer: isCustomer,
                isChild: isChild,
                type: isCustomer ? 'customer' : (isChild ? 'child' : 'adult')
            });
        }

        renderForms();
        updateNavigation();
        
        window.updateNavigation = updateNavigation;
        
        if (typeof setupGlobalEventHandlers === 'function') {
            setTimeout(() => {
                setupGlobalEventHandlers();
            }, 200);
        }
    }

    function renderForms() {
        const container = document.getElementById('bookingFormsContainer');
        if (!container) return;

        container.innerHTML = '';

        forms.forEach((form, index) => {
            const formElement = document.createElement('div');
            formElement.className = `tourist-form ${form.type}-form`;
            formElement.dataset.formIndex = index;
            formElement.style.display = index === currentFormIndex ? 'block' : 'none';

            if (form.isChild) {
                formElement.innerHTML = renderChildForm(form, index);
            } else {
                formElement.innerHTML = renderAdultForm(form, index);
            }

            container.appendChild(formElement);
        });
        
        if (typeof setupGlobalEventHandlers === 'function') {
            setupGlobalEventHandlers();
        }
    }

    function renderAdultForm(form, index) {
        const customerLabel = form.isCustomer ? ' (заказчик)' : '';
        const formId = `tourist_${index}`;

        return `
            <div class="form-row">
                <div class="form-group">
                    <label>Фамилия</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_lastname" placeholder="Фамилия">
                    </div>
                </div>

                <div class="form-group">
                    <label>Имя</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_firstname" placeholder="Имя">
                    </div>
                </div>

                <div class="form-group">
                    <label>Отчество</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_middlename" placeholder="Отчество">
                    </div>
                </div>

                <div class="form-group">
                    <label>Дата рождения</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_birthdate" placeholder="дд.мм.гггг">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Серия</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_doc-series" placeholder="Серия">
                    </div>
                </div>

                <div class="form-group">
                    <label>Номер документа</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_doc-number" placeholder="Номер документа">
                    </div>
                </div>

                <div class="form-group">
                    <label>Код подразделения</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_doc-department-code" placeholder="Код подразделения">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group wide">
                    <label>Дата выдачи документа</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_doc-issue-date" placeholder="дд.мм.гггг">
                    </div>
                </div>

                <div class="form-group extra-wide">
                    <label>Орган, выдавший документ</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_doc-issuing-authority" placeholder="Орган, выдавший документ">
                    </div>
                </div>
            </div>

            ${form.isCustomer ? `
            <div class="form-row">
                <div class="form-group">
                    <label>Номер телефона</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_phone" placeholder="Номер телефона">
                    </div>
                </div>

                <div class="form-group">
                    <label>E-mail адрес</label>
                    <div class="input-field">
                        <input type="email" name="${formId}_email" placeholder="E-mail адрес">
                    </div>
                </div>

                <div class="form-group">
                    <label>Адрес регистрации</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_address" placeholder="Адрес регистрации">
                    </div>
                </div>
            </div>
            ` : ''}
        `;
    }

    function renderChildForm(form, index) {
        const formId = `tourist_${index}`;

        return `
            <div class="form-row">
                <div class="form-group">
                    <label>Фамилия</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_lastname" placeholder="Фамилия">
                    </div>
                </div>

                <div class="form-group">
                    <label>Имя</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_firstname" placeholder="Имя">
                    </div>
                </div>

                <div class="form-group">
                    <label>Отчество</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_middlename" placeholder="Отчество">
                    </div>
                </div>

                <div class="form-group">
                    <label>Дата рождения</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_birthdate" placeholder="дд.мм.гггг">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group extra-wide">
                    <label>Свидетельство о рождении</label>
                    <div class="input-field">
                        <input type="text" name="${formId}_birth-certificate" placeholder="Серия и номер свидетельства о рождении">
                    </div>
                </div>
            </div>
        `;
    }

    function isFormValid(formIndex) {
        if (formIndex < 0 || formIndex >= forms.length) {
            return true;
        }
        
        const formElement = document.querySelector(`[data-form-index="${formIndex}"]`);
        if (!formElement) {
            return true;
        }
        
        const invalidInputs = formElement.querySelectorAll('input.invalid');
        if (invalidInputs.length > 0) {
            return false;
        }
        
        const inputs = formElement.querySelectorAll('input');
        let hasEmptyRequired = false;
        
        inputs.forEach(input => {
            const name = input.getAttribute('name') || '';
            const value = input.value.trim();
            
            const isRequired = name.includes('_lastname') || 
                             name.includes('_firstname') || 
                             name.includes('_birthdate') ||
                             (name.includes('_doc-series') && !name.includes('birth-certificate')) ||
                             (name.includes('_doc-number') && !name.includes('birth-certificate')) ||
                             name.includes('_birth-certificate') ||
                             (forms[formIndex] && forms[formIndex].isCustomer && (
                                 name.includes('_middlename') ||
                                 name.includes('_doc-department-code') ||
                                 name.includes('_doc-issue-date') ||
                                 name.includes('_doc-issuing-authority') ||
                                 name.includes('_phone') ||
                                 name.includes('_email') ||
                                 name.includes('_address')
                             ));
            
            if (isRequired && value === '') {
                hasEmptyRequired = true;
            }
        });
        
        return !hasEmptyRequired;
    }

    function updateNavigation() {
        const prevBtn = document.getElementById('prevFormBtn');
        const nextBtn = document.getElementById('nextFormBtn');
        const formTitle = document.getElementById('formTitle');

        if (prevBtn) {
            const hasPrev = currentFormIndex > 0;
            prevBtn.style.display = hasPrev ? 'flex' : 'none';
            
            if (hasPrev) {
                const prevFormValid = isFormValid(currentFormIndex - 1);
                if (prevFormValid) {
                    prevBtn.classList.remove('error-state');
                } else {
                    prevBtn.classList.add('error-state');
                }
            }
        }

        if (nextBtn) {
            const hasNext = currentFormIndex < forms.length - 1;
            nextBtn.style.display = hasNext ? 'flex' : 'none';
            
            if (hasNext) {
                const nextFormValid = isFormValid(currentFormIndex + 1);
                if (nextFormValid) {
                    nextBtn.classList.remove('error-state');
                } else {
                    nextBtn.classList.add('error-state');
                }
            }
        }

        if (formTitle && forms[currentFormIndex]) {
            const form = forms[currentFormIndex];
            const touristNumber = currentFormIndex + 1;
            let title = `Данные туриста ${touristNumber}`;

            if (form.isCustomer) {
                title += ' (заказчик)';
            } else if (form.isChild) {
                title += ' (ребенок)';
            }

            formTitle.textContent = title;
        }

        forms.forEach((form, index) => {
            const formElement = document.querySelector(`[data-form-index="${index}"]`);
            if (formElement) {
                formElement.style.display = index === currentFormIndex ? 'block' : 'none';
            }
        });
    }

    function showNextForm() {
        if (currentFormIndex < forms.length - 1) {
            currentFormIndex++;
            updateNavigation();
        }
    }

    function showPrevForm() {
        if (currentFormIndex > 0) {
            currentFormIndex--;
            updateNavigation();
        }
    }

    function clearCurrentForm() {
        const currentForm = document.querySelector(`[data-form-index="${currentFormIndex}"]`);
        if (currentForm) {
            const inputs = currentForm.querySelectorAll('input');
            inputs.forEach(input => {
                input.value = '';
            });
        }
    }

    function collectAllTouristsData() {
        const touristsData = [];
        
        console.log('[Booking] Collecting tourists data. Forms count:', forms.length);
        
        forms.forEach((form, index) => {
            const formElement = document.querySelector(`[data-form-index="${index}"]`);
            if (!formElement) {
                console.warn('[Booking] Form element not found for index:', index);
                return;
            }
            
            const formId = `tourist_${index}`;
            const inputs = formElement.querySelectorAll('input');
            
            console.log(`[Booking] Processing form #${index}, inputs count:`, inputs.length);
            
            const touristData = {
                is_orderer: form.isCustomer,
                is_child: form.isChild
            };
            
            inputs.forEach(input => {
                const name = input.name || '';
                const value = input.value.trim();
                
                if (name.includes('_lastname')) {
                    touristData.last_name = value;
                } else if (name.includes('_firstname')) {
                    touristData.first_name = value;
                } else if (name.includes('_middlename')) {
                    touristData.middle_name = value;
                } else if (name.includes('_birthdate')) {
                    touristData.birthdate = value;
                } else if (name.includes('_doc-series')) {
                    touristData.doc_series = value;
                } else if (name.includes('_doc-number')) {
                    touristData.doc_number = value;
                } else if (name.includes('_doc-issue-date')) {
                    touristData.doc_issue_date = value;
                } else if (name.includes('_doc-issuing-authority')) {
                    touristData.doc_issuing_authority = value;
                } else if (name.includes('_birth-certificate')) {
                    touristData.birth_certificate = value;
                }
            });
            
            console.log(`[Booking] Tourist #${index} collected data:`, touristData);
            
            if (touristData.first_name || touristData.last_name) {
                touristsData.push(touristData);
                console.log(`[Booking] Tourist #${index} added to array`);
            } else {
                console.warn(`[Booking] Tourist #${index} skipped - no first_name or last_name`);
            }
        });
        
        console.log('[Booking] Total tourists collected:', touristsData.length);
        console.log('[Booking] Tourists data:', touristsData);
        
        return touristsData;
    }

    function getTourId() {
        if (window.bookingData && window.bookingData.tourId) {
            return window.bookingData.tourId;
        }
        const urlParams = new URLSearchParams(window.location.search);
        const tourId = parseInt(urlParams.get('tour_id')) || parseInt(urlParams.get('id')) || 0;
        return tourId;
    }

    function getTotalPrice() {
        if (window.bookingData && window.bookingData.totalPrice) {
            return window.bookingData.totalPrice;
        }
        const priceElement = document.querySelector('.total-cost .info-value');
        if (priceElement) {
            const text = priceElement.textContent;
            const match = text.match(/[\d\s]+/);
            if (match) {
                const priceStr = match[0].replace(/\s/g, '');
                return parseFloat(priceStr) || 0;
            }
        }
        return 0;
    }

    function validateAllTouristForms() {
        let allValid = true;
        const errors = [];
        
        forms.forEach((form, index) => {
            const formElement = document.querySelector(`[data-form-index="${index}"]`);
            if (!formElement) {
                errors.push(`Форма туриста ${index + 1} не найдена`);
                allValid = false;
                return;
            }
            
            const inputs = formElement.querySelectorAll('input');
            const touristData = {
                first_name: '',
                last_name: '',
                birthdate: '',
                doc_series: '',
                doc_number: '',
                birth_certificate: ''
            };
            
            inputs.forEach(input => {
                const name = input.name || '';
                const value = input.value.trim();
                
                if (name.includes('_firstname')) {
                    touristData.first_name = value;
                } else if (name.includes('_lastname')) {
                    touristData.last_name = value;
                } else if (name.includes('_birthdate')) {
                    touristData.birthdate = value;
                } else if (name.includes('_doc-series')) {
                    touristData.doc_series = value;
                } else if (name.includes('_doc-number')) {
                    touristData.doc_number = value;
                } else if (name.includes('_birth-certificate')) {
                    touristData.birth_certificate = value;
                }
            });
            
            if (!touristData.first_name) {
                errors.push(`Турист ${index + 1}: не указано имя`);
                allValid = false;
            }
            if (!touristData.last_name) {
                errors.push(`Турист ${index + 1}: не указана фамилия`);
                allValid = false;
            }
            if (!touristData.birthdate) {
                errors.push(`Турист ${index + 1}: не указана дата рождения`);
                allValid = false;
            }
            
            if (form.isChild) {
                if (!touristData.birth_certificate) {
                    errors.push(`Турист ${index + 1} (ребенок): не указано свидетельство о рождении`);
                    allValid = false;
                }
            } else {
                if (!touristData.doc_series) {
                    errors.push(`Турист ${index + 1}: не указана серия документа`);
                    allValid = false;
                }
                if (!touristData.doc_number) {
                    errors.push(`Турист ${index + 1}: не указан номер документа`);
                    allValid = false;
                }
            }
        });
        
        if (!allValid && errors.length > 0) {
            console.error('[Booking] Validation errors:', errors);
        }
        
        return allValid;
    }

    function submitBooking() {
        console.log('[Booking] submitBooking called');
        if (!validateAllTouristForms()) {
            console.log('[Booking] Validation failed');
            alert('Пожалуйста, заполните все обязательные поля для всех туристов.');
            return;
        }
        console.log('[Booking] Validation passed');
        
        if (typeof validateForm === 'function') {
            const $bookingForm = $('.booking-form');
            if (!$bookingForm.length) {
                console.error('[Booking] Form not found');
                return;
            }
            
            if (!validateForm($bookingForm)) {
                console.error('[Booking] Form validation failed');
                return;
            }
        }
        
        const tourId = getTourId();
        const totalPrice = getTotalPrice();
        const tourists = collectAllTouristsData();
        
        if (tourId <= 0) {
            console.error('[Booking] Invalid tour ID:', tourId);
            alert('Ошибка: неверный идентификатор тура. Обновите страницу и попробуйте снова.');
            return;
        }
        
        if (totalPrice <= 0) {
            console.error('[Booking] Invalid total price:', totalPrice);
            alert('Ошибка: неверная стоимость тура. Обновите страницу и попробуйте снова.');
            return;
        }
        
        if (tourists.length === 0) {
            console.error('[Booking] No tourists data');
            alert('Ошибка: данные туристов не найдены. Пожалуйста, заполните формы туристов.');
            return;
        }
        
        const requiredFields = ['first_name', 'last_name', 'birthdate'];
        for (let tourist of tourists) {
            for (let field of requiredFields) {
                if (!tourist[field] || tourist[field].trim() === '') {
                    console.error(`[Booking] Missing required field for tourist:`, field);
                    alert(`Ошибка: не заполнено обязательное поле "${field}" для одного из туристов.`);
                    return;
                }
            }
            
            if (!tourist.is_child) {
                if (!tourist.doc_series || !tourist.doc_number) {
                    console.error('[Booking] Missing document for adult tourist');
                    alert('Ошибка: не указаны данные документа для взрослого туриста.');
                    return;
                }
            } else {
                if (!tourist.birth_certificate) {
                    console.error('[Booking] Missing birth certificate for child');
                    alert('Ошибка: не указано свидетельство о рождении для ребенка.');
                    return;
                }
            }
        }
        
        const bookBtn = document.querySelector('.book-btn');
        if (bookBtn) {
            bookBtn.disabled = true;
            bookBtn.textContent = 'Оформление...';
        }
        
        const selectedServices = window.bookingData?.services || [];
        
        console.log('[Booking] Tour ID:', tourId);
        console.log('[Booking] Total Price:', totalPrice);
        console.log('[Booking] Tourists count:', tourists.length);
        console.log('[Booking] Tourists data:', tourists);
        console.log('[Booking] Selected services:', selectedServices);
        
        const formData = new FormData();
        formData.append('tour_id', tourId);
        formData.append('total_price', totalPrice);
        formData.append('tourists', JSON.stringify(tourists));
        formData.append('services', JSON.stringify(selectedServices));
        
        console.log('[Booking] FormData contents:');
        for (let pair of formData.entries()) {
            console.log('[Booking]', pair[0] + ':', pair[1]);
        }
        
        fetch('?action=create-booking', {
            method: 'POST',
            body: formData
        })
        .then(async r => {
            let responseText = '';
            try {
                responseText = await r.text();
                const res = JSON.parse(responseText);
                
                if (!r.ok) {
                    throw new Error('Ошибка сервера: ' + r.status + ' - ' + (res.message || responseText));
                }
                
                return res;
            } catch (parseError) {
                console.error('Ошибка парсинга ответа:', parseError);
                console.error('Ответ сервера:', responseText);
                throw new Error('Ошибка сервера: ' + r.status + '. Ответ: ' + responseText.substring(0, 200));
            }
        })
        .then(res => {
            if (res.success) {
                window.location.href = '?page=me';
            } else {
                console.error('[Booking] Booking failed:', res.message || 'Unknown error');
                alert('Ошибка при бронировании: ' + (res.message || 'Неизвестная ошибка'));
                if (bookBtn) {
                    bookBtn.disabled = false;
                    bookBtn.textContent = 'Забронировать тур';
                }
            }
        })
        .catch(err => {
            console.error('[Booking] Network error:', err);
            alert('Ошибка сети при отправке данных. Проверьте подключение к интернету и попробуйте снова.');
            if (bookBtn) {
                bookBtn.disabled = false;
                bookBtn.textContent = 'Забронировать тур';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ === 'undefined') {
            console.error('jQuery не загружен');
            return;
        }
        
        initBookingForms();

        const prevBtn = document.getElementById('prevFormBtn');
        const nextBtn = document.getElementById('nextFormBtn');
        const clearBtn = document.getElementById('clearCurrentFormBtn');
        const bookBtn = document.querySelector('.book-btn') || document.getElementById('bookTourBtn');
        
        console.log('[Booking] DOMContentLoaded - bookBtn:', bookBtn);

        if (prevBtn) {
            prevBtn.addEventListener('click', showPrevForm);
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', showNextForm);
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', clearCurrentForm);
        }

        if (bookBtn) {
            console.log('[Booking] Book button found, adding event listener');
            bookBtn.addEventListener('click', function(e) {
                console.log('[Booking] Book button clicked');
                e.preventDefault();
                e.stopPropagation();
                submitBooking();
            });
        } else {
            console.error('[Booking] Book button not found!');
        }
        
        setTimeout(() => {
            if (typeof setupGlobalEventHandlers === 'function') {
                setupGlobalEventHandlers();
            }
        }, 300);
    });

    window.submitBooking = submitBooking;
})();

