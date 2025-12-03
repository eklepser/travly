// Инициализация фильтров
document.addEventListener('DOMContentLoaded', function() {
    const filterManager = new FilterManager();
    filterManager.init();
});

class FilterManager {
    constructor() {
        this.filters = {
            vacationType: '',
            country: '',
            guests: '',
            hotel: '',
            minPrice: null,
            maxPrice: null,
            minNights: null,
            maxNights: null,
            minRating: null
        };
        this.filterOptions = {
            countries: [],
            hotels: [],
            maxCapacity: 4
        };
    }

    async init() {
        this.loadFilterOptions();
        this.restoreFiltersFromURL();
        this.setupDropdowns();
        this.setupSliders();
        this.setupApplyButton();
        this.setupInputs();
        this.setupSorting();
    }

    loadFilterOptions() {
        // Загружаем опции из data-атрибута компонента фильтров
        const filterPanel = document.querySelector('.filters[data-filter-options]');
        if (filterPanel) {
            try {
                const options = JSON.parse(filterPanel.getAttribute('data-filter-options'));
                if (options.countries) {
                    this.filterOptions.countries = options.countries;
                    this.populateCountryDropdown();
                }
                if (options.hotels) {
                    this.filterOptions.hotels = options.hotels;
                    this.populateHotelDropdown();
                }
                if (options.maxCapacity) {
                    this.filterOptions.maxCapacity = options.maxCapacity;
                    this.populateGuestsDropdown();
                }
            } catch (error) {
                console.error('Failed to parse filter options:', error);
            }
        }
    }

    restoreFiltersFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Восстанавливаем фильтры из URL
        if (urlParams.has('country')) {
            this.filters.country = urlParams.get('country');
            const countryItem = document.querySelector('[data-filter="country"]');
            if (countryItem) {
                const label = countryItem.querySelector('.filter-label');
                if (label) label.textContent = this.filters.country;
            }
        }
        
        if (urlParams.has('min_price')) {
            this.filters.minPrice = parseInt(urlParams.get('min_price'));
            const priceMinInput = document.querySelector('.budget-min');
            const priceMinSlider = document.querySelector('.price-min-slider');
            if (priceMinInput) priceMinInput.value = this.filters.minPrice;
            if (priceMinSlider) priceMinSlider.value = this.filters.minPrice;
        }
        
        if (urlParams.has('max_price')) {
            this.filters.maxPrice = parseInt(urlParams.get('max_price'));
            const priceMaxInput = document.querySelector('.budget-max');
            const priceMaxSlider = document.querySelector('.price-max-slider');
            if (priceMaxInput) priceMaxInput.value = this.filters.maxPrice;
            if (priceMaxSlider) priceMaxSlider.value = this.filters.maxPrice;
        }
        
        if (urlParams.has('min_nights')) {
            this.filters.minNights = parseInt(urlParams.get('min_nights'));
            const nightsMinInput = document.querySelector('.duration-min');
            if (nightsMinInput) nightsMinInput.value = this.filters.minNights;
        }
        
        if (urlParams.has('max_nights')) {
            this.filters.maxNights = parseInt(urlParams.get('max_nights'));
            const nightsMaxInput = document.querySelector('.duration-max');
            const nightsSlider = document.querySelector('.nights-slider');
            if (nightsMaxInput) nightsMaxInput.value = this.filters.maxNights;
            if (nightsSlider) nightsSlider.value = this.filters.maxNights;
        }
        
        if (urlParams.has('min_guests')) {
            this.filters.guests = parseInt(urlParams.get('min_guests'));
            const guestsItem = document.querySelector('[data-filter="guests"]');
            if (guestsItem) {
                const label = guestsItem.querySelector('.filter-label');
                if (label) label.textContent = this.filters.guests + (this.filters.guests === 1 ? ' турист' : this.filters.guests < 5 ? ' туриста' : ' туристов');
            }
        }
        
        if (urlParams.has('min_rating')) {
            this.filters.minRating = parseFloat(urlParams.get('min_rating'));
            const ratingItem = document.querySelector('[data-filter="rating"]');
            if (ratingItem) {
                const label = ratingItem.querySelector('.filter-label');
                if (label) label.textContent = this.filters.minRating + '+';
            }
        }
        
        if (urlParams.has('hotel')) {
            this.filters.hotel = urlParams.get('hotel');
            const hotelItem = document.querySelector('[data-filter="hotel"]');
            if (hotelItem) {
                const label = hotelItem.querySelector('.filter-label');
                if (label) label.textContent = this.filters.hotel;
            }
        }
        
        // Если выбрана страна, загружаем отели для этой страны
        if (this.filters.country) {
            this.loadHotelsByCountry(this.filters.country);
        }
    }


    populateCountryDropdown() {
        const dropdown = document.querySelector('[data-filter="country"] .dropdown-content');
        if (!dropdown) return;
        
        // Оставляем "Все"
        const allItem = dropdown.querySelector('[data-value=""]');
        dropdown.innerHTML = '';
        if (allItem) dropdown.appendChild(allItem);
        
        this.filterOptions.countries.forEach(country => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';
            item.setAttribute('data-value', country);
            item.textContent = country;
            dropdown.appendChild(item);
        });
    }

    populateHotelDropdown(hotels = null) {
        const dropdown = document.querySelector('[data-filter="hotel"] .dropdown-content');
        if (!dropdown) return;
        
        const allItem = dropdown.querySelector('[data-value=""]');
        const hotelsList = hotels || this.filterOptions.hotels;
        
        // Сохраняем текущее значение, если оно есть
        const currentValue = this.filters.hotel;
        
        dropdown.innerHTML = '';
        if (allItem) dropdown.appendChild(allItem);
        
        hotelsList.forEach(hotel => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';
            item.setAttribute('data-value', hotel);
            item.textContent = hotel;
            dropdown.appendChild(item);
        });
        
        // Переустанавливаем обработчики событий для новых элементов
        const filterItem = document.querySelector('[data-filter="hotel"]');
        const label = filterItem ? filterItem.querySelector('.filter-label') : null;
        const chevron = filterItem ? filterItem.querySelector('.filter-chevron') : null;
        
        dropdown.querySelectorAll('.dropdown-item').forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                const value = option.getAttribute('data-value');
                
                // Обновляем текст в фильтре
                if (value === '') {
                    if (label) label.textContent = 'Отель';
                } else {
                    if (label) label.textContent = option.textContent;
                }
                
                // Сохраняем значение
                this.updateFilter('hotel', value);
                
                // Закрываем выпадающий список
                dropdown.style.display = 'none';
                if (chevron) {
                    chevron.style.transform = 'rotate(45deg)';
                    chevron.style.marginTop = '0';
                }
            });
        });
        
        // Если текущий выбранный отель не в новом списке, сбрасываем выбор
        if (currentValue && !hotelsList.includes(currentValue)) {
            if (label) label.textContent = 'Отель';
            this.filters.hotel = '';
        }
        
        // Если список отелей пуст (кроме "Все"), сбрасываем выбор отеля
        if (hotelsList.length === 0) {
            if (label) label.textContent = 'Отель';
            this.filters.hotel = '';
        }
    }

    loadHotelsByCountry(country) {
        if (!country) {
            // Если страна не выбрана, показываем все отели из начальных опций
            const filterPanel = document.querySelector('.filters[data-filter-options]');
            if (filterPanel) {
                try {
                    const options = JSON.parse(filterPanel.getAttribute('data-filter-options'));
                    const allHotels = options.allHotels || options.hotels || [];
                    this.populateHotelDropdown(allHotels);
                } catch (error) {
                    this.populateHotelDropdown();
                }
            } else {
                this.populateHotelDropdown();
            }
            return;
        }
        
        // Фильтруем отели на клиенте из всех загруженных отелей
        // Для этого нужно получить список отелей для выбранной страны
        // Так как у нас нет API, перезагружаем страницу с параметром country
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = urlParams.get('page') || 'search';
        urlParams.set('country', country);
        // Убираем hotel из параметров, так как список отелей изменится
        urlParams.delete('hotel');
        window.location.href = `?page=${currentPage}&${urlParams.toString()}`;
    }

    populateGuestsDropdown() {
        const dropdown = document.querySelector('[data-filter="guests"] .dropdown-content');
        if (!dropdown) return;
        
        const allItem = dropdown.querySelector('[data-value=""]');
        dropdown.innerHTML = '';
        if (allItem) dropdown.appendChild(allItem);
        
        for (let i = 1; i <= this.filterOptions.maxCapacity; i++) {
            const item = document.createElement('div');
            item.className = 'dropdown-item';
            item.setAttribute('data-value', i);
            item.textContent = i + (i === 1 ? ' турист' : i < 5 ? ' туриста' : ' туристов');
            dropdown.appendChild(item);
        }
    }

    setupDropdowns() {
        const filterItems = document.querySelectorAll('.filter-item[data-filter]');
        
        filterItems.forEach(item => {
            const label = item.querySelector('.filter-label');
            const chevron = item.querySelector('.filter-chevron');
            const dropdown = item.querySelector('.dropdown-content');
            
            if (!dropdown) return;
            
            // Закрытие при клике вне элемента
            document.addEventListener('click', (e) => {
                if (!item.contains(e.target)) {
                    dropdown.style.display = 'none';
                    chevron.style.transform = 'rotate(45deg)';
                    chevron.style.marginTop = '0';
                    item.classList.remove('dropdown-open');
                }
            });
            
            // Открытие/закрытие при клике на элемент
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = dropdown.style.display === 'block';
                
                // Закрываем все другие выпадающие списки
                document.querySelectorAll('.dropdown-content').forEach(dd => {
                    if (dd !== dropdown) {
                        dd.style.display = 'none';
                    }
                });
                document.querySelectorAll('.filter-chevron').forEach(ch => {
                    if (ch !== chevron) {
                        ch.style.transform = 'rotate(45deg)';
                        ch.style.marginTop = '0';
                    }
                });
                // Убираем класс dropdown-open со всех элементов
                document.querySelectorAll('.filter-item').forEach(fi => {
                    fi.classList.remove('dropdown-open');
                });
                
                dropdown.style.display = isOpen ? 'none' : 'block';
                chevron.style.transform = isOpen ? 'rotate(45deg)' : 'rotate(225deg)';
                chevron.style.marginTop = isOpen ? '0' : '4px';
                
                // Добавляем/убираем класс для z-index
                if (!isOpen) {
                    item.classList.add('dropdown-open');
                } else {
                    item.classList.remove('dropdown-open');
                }
            });
            
            // Обработка выбора значения
            dropdown.querySelectorAll('.dropdown-item').forEach(option => {
                option.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const value = option.getAttribute('data-value');
                    const filterType = item.getAttribute('data-filter');
                    
                    // Обновляем текст в фильтре
                    if (value === '') {
                        label.textContent = this.getDefaultLabel(filterType);
                    } else {
                        label.textContent = option.textContent;
                    }
                    
                    // Сохраняем значение
                    this.updateFilter(filterType, value);
                    
                    // Если выбрана страна, обновляем список отелей
                    if (filterType === 'country') {
                        this.loadHotelsByCountry(value);
                    }
                    
                    // Закрываем выпадающий список
                    dropdown.style.display = 'none';
                    chevron.style.transform = 'rotate(45deg)';
                    chevron.style.marginTop = '0';
                    item.classList.remove('dropdown-open');
                });
            });
        });
    }

    getDefaultLabel(filterType) {
        const labels = {
            'vacation-type': 'Тип отдыха',
            'country': 'Направление',
            'guests': 'Количество туристов',
            'hotel': 'Отель',
            'rating': 'Рейтинг'
        };
        return labels[filterType] || '';
    }

    updateFilter(type, value) {
        switch(type) {
            case 'vacation-type':
                this.filters.vacationType = value;
                break;
            case 'country':
                this.filters.country = value;
                break;
            case 'guests':
                this.filters.guests = value === '' ? null : parseInt(value);
                break;
            case 'hotel':
                this.filters.hotel = value;
                break;
            case 'rating':
                this.filters.minRating = value === '' ? null : parseFloat(value);
                break;
        }
    }

    setupSliders() {
        this.setupDoublePriceSlider();
        this.setupNightsSlider();
    }

    setupDoublePriceSlider() {
        const priceMinSlider = document.querySelector('.price-min-slider');
        const priceMaxSlider = document.querySelector('.price-max-slider');
        const priceMinInput = document.querySelector('.budget-min');
        const priceMaxInput = document.querySelector('.budget-max');
        const sliderTrack = document.querySelector('.double-range-slider .slider-track');
        const rangeValueMin = document.querySelector('.range-value-min');
        const rangeValueMax = document.querySelector('.range-value-max');
        
        if (!priceMinSlider || !priceMaxSlider) return;
        
        const maxPrice = 100000;
        
        const updatePriceSlider = () => {
            const minVal = parseInt(priceMinSlider.value);
            const maxVal = parseInt(priceMaxSlider.value);
            
            // Убеждаемся, что min не больше max
            if (minVal > maxVal) {
                priceMinSlider.value = maxVal;
                priceMaxSlider.value = minVal;
            }
            
            const finalMin = Math.min(parseInt(priceMinSlider.value), parseInt(priceMaxSlider.value));
            const finalMax = Math.max(parseInt(priceMinSlider.value), parseInt(priceMaxSlider.value));
            
            // Обновляем визуальный трек
            if (sliderTrack) {
                const minPercent = (finalMin / maxPrice) * 100;
                const maxPercent = (finalMax / maxPrice) * 100;
                sliderTrack.style.left = minPercent + '%';
                sliderTrack.style.width = (maxPercent - minPercent) + '%';
            }
            
            // Обновляем значения в полях ввода
            if (priceMinInput) {
                priceMinInput.value = finalMin || '';
                this.filters.minPrice = finalMin || null;
            }
            if (priceMaxInput) {
                priceMaxInput.value = finalMax || '';
                this.filters.maxPrice = finalMax || null;
            }
            
            // Обновляем отображаемые значения
            if (rangeValueMin) {
                rangeValueMin.textContent = finalMin.toLocaleString('ru-RU').replace(/,/g, ' ');
            }
            if (rangeValueMax) {
                rangeValueMax.textContent = finalMax.toLocaleString('ru-RU').replace(/,/g, ' ');
            }
        };
        
        priceMinSlider.addEventListener('input', updatePriceSlider);
        priceMaxSlider.addEventListener('input', updatePriceSlider);
        
        // Синхронизация с полями ввода
        if (priceMinInput) {
            // Удаляем все предыдущие обработчики
            const newMinInput = priceMinInput.cloneNode(true);
            priceMinInput.parentNode.replaceChild(newMinInput, priceMinInput);
            
            newMinInput.addEventListener('keydown', (e) => {
                // Разрешаем все клавиши для редактирования
            });
            
            newMinInput.addEventListener('input', (e) => {
                let value = e.target.value.trim();
                // Разрешаем пустое значение во время ввода
                if (value === '') {
                    return;
                }
                // Удаляем все нецифровые символы
                value = value.replace(/\D/g, '');
                if (value === '') {
                    e.target.value = '';
                    return;
                }
                let numValue = parseInt(value);
                if (isNaN(numValue)) {
                    e.target.value = '';
                    return;
                }
                numValue = Math.max(0, Math.min(numValue, maxPrice));
                e.target.value = numValue;
                priceMinSlider.value = numValue;
                updatePriceSlider();
            });
            
            // Обработка потери фокуса для валидации
            newMinInput.addEventListener('blur', (e) => {
                let value = e.target.value.trim();
                if (value === '') {
                    e.target.value = '';
                    priceMinSlider.value = 0;
                    updatePriceSlider();
                    return;
                }
                let numValue = parseInt(value);
                if (isNaN(numValue) || numValue < 0) {
                    e.target.value = '';
                    priceMinSlider.value = 0;
                    updatePriceSlider();
                } else {
                    numValue = Math.max(0, Math.min(numValue, maxPrice));
                    e.target.value = numValue;
                    priceMinSlider.value = numValue;
                    updatePriceSlider();
                }
            });
        }
        
        if (priceMaxInput) {
            // Удаляем все предыдущие обработчики
            const newMaxInput = priceMaxInput.cloneNode(true);
            priceMaxInput.parentNode.replaceChild(newMaxInput, priceMaxInput);
            
            newMaxInput.addEventListener('keydown', (e) => {
                // Разрешаем все клавиши для редактирования
            });
            
            newMaxInput.addEventListener('input', (e) => {
                let value = e.target.value.trim();
                // Разрешаем пустое значение во время ввода
                if (value === '') {
                    return;
                }
                // Удаляем все нецифровые символы
                value = value.replace(/\D/g, '');
                if (value === '') {
                    e.target.value = '';
                    return;
                }
                let numValue = parseInt(value);
                if (isNaN(numValue)) {
                    e.target.value = '';
                    return;
                }
                numValue = Math.max(0, Math.min(numValue, maxPrice));
                e.target.value = numValue;
                priceMaxSlider.value = numValue;
                updatePriceSlider();
            });
            
            // Обработка потери фокуса для валидации
            newMaxInput.addEventListener('blur', (e) => {
                let value = e.target.value.trim();
                if (value === '') {
                    e.target.value = '';
                    priceMaxSlider.value = maxPrice;
                    updatePriceSlider();
                    return;
                }
                let numValue = parseInt(value);
                if (isNaN(numValue) || numValue < 0) {
                    e.target.value = '';
                    priceMaxSlider.value = maxPrice;
                    updatePriceSlider();
                } else {
                    numValue = Math.max(0, Math.min(numValue, maxPrice));
                    e.target.value = numValue;
                    priceMaxSlider.value = numValue;
                    updatePriceSlider();
                }
            });
        }
        
        // Инициализация
        updatePriceSlider();
        
        // Если значения уже были восстановлены из URL, обновляем слайдеры
        if (priceMinInput && priceMinInput.value) {
            priceMinSlider.value = parseInt(priceMinInput.value) || 0;
        }
        if (priceMaxInput && priceMaxInput.value) {
            priceMaxSlider.value = parseInt(priceMaxInput.value) || 100000;
        }
        updatePriceSlider();
    }

    setupNightsSlider() {
        const nightsSlider = document.querySelector('.nights-slider');
        const nightsMinInput = document.querySelector('.duration-min');
        const nightsMaxInput = document.querySelector('.duration-max');
        const maxNights = 30;
        
        // Синхронизация слайдера ночей с полями ввода
        if (nightsSlider && nightsMaxInput) {
            nightsSlider.addEventListener('input', (e) => {
                nightsMaxInput.value = e.target.value;
                this.filters.maxNights = parseInt(e.target.value);
            });
            
            nightsMaxInput.addEventListener('input', (e) => {
                let value = parseInt(e.target.value);
                if (isNaN(value)) {
                    return; // Разрешаем ввод, но не обновляем фильтр
                }
                if (value > maxNights) {
                    e.target.value = maxNights;
                    value = maxNights;
                }
                if (value >= 1 && value <= maxNights) {
                    nightsSlider.value = Math.min(value, maxNights);
                    this.filters.maxNights = value;
                } else if (value < 1) {
                    e.target.value = '';
                    this.filters.maxNights = null;
                }
            });
            
            // Валидация при потере фокуса
            nightsMaxInput.addEventListener('blur', (e) => {
                let value = parseInt(e.target.value);
                if (isNaN(value) || value < 1) {
                    e.target.value = '';
                    this.filters.maxNights = null;
                } else if (value > maxNights) {
                    e.target.value = maxNights;
                    nightsSlider.value = maxNights;
                    this.filters.maxNights = maxNights;
                }
            });
        }
        
        if (nightsMinInput) {
            nightsMinInput.addEventListener('input', (e) => {
                let value = parseInt(e.target.value);
                if (isNaN(value)) {
                    return; // Разрешаем ввод, но не обновляем фильтр
                }
                if (value > maxNights) {
                    e.target.value = maxNights;
                    value = maxNights;
                }
                if (value >= 1 && value <= maxNights) {
                    this.filters.minNights = value;
                } else if (value < 1) {
                    e.target.value = '';
                    this.filters.minNights = null;
                }
            });
            
            // Валидация при потере фокуса
            nightsMinInput.addEventListener('blur', (e) => {
                let value = parseInt(e.target.value);
                if (isNaN(value) || value < 1) {
                    e.target.value = '';
                    this.filters.minNights = null;
                } else if (value > maxNights) {
                    e.target.value = maxNights;
                    this.filters.minNights = maxNights;
                }
            });
        }
    }

    setupInputs() {
        // Инициализация значений из полей ввода и слайдеров
        const nightsMinInput = document.querySelector('.duration-min');
        const nightsMaxInput = document.querySelector('.duration-max');
        const nightsSlider = document.querySelector('.nights-slider');
        
        // Инициализация слайдера ночей
        const maxNights = 30;
        if (nightsSlider && nightsMaxInput) {
            if (!nightsMaxInput.value) {
                nightsMaxInput.value = nightsSlider.value;
            } else {
                nightsSlider.value = Math.min(parseInt(nightsMaxInput.value) || maxNights, maxNights);
            }
            this.filters.maxNights = parseInt(nightsMaxInput.value) || null;
        }
        
        if (nightsMinInput && nightsMinInput.value) {
            this.filters.minNights = parseInt(nightsMinInput.value);
        }
    }

    setupApplyButton() {
        const applyBtn = document.getElementById('applyFilters');
        if (applyBtn) {
            applyBtn.addEventListener('click', () => {
                this.applyFilters();
            });
        }
        
        const resetBtn = document.getElementById('resetFilters');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                this.resetFilters();
            });
        }
    }

    applyFilters() {
        // Формируем параметры для URL
        const params = new URLSearchParams();
        
        if (this.filters.country) params.append('country', this.filters.country);
        if (this.filters.minPrice !== null) params.append('min_price', this.filters.minPrice);
        if (this.filters.maxPrice !== null) params.append('max_price', this.filters.maxPrice);
        if (this.filters.minNights !== null) params.append('min_nights', this.filters.minNights);
        if (this.filters.maxNights !== null) params.append('max_nights', this.filters.maxNights);
        if (this.filters.guests !== null) params.append('min_guests', this.filters.guests);
        if (this.filters.minRating !== null) params.append('min_rating', this.filters.minRating);
        if (this.filters.hotel) params.append('hotel', this.filters.hotel);
        
        // Переходим на страницу поиска с параметрами
        window.location.href = `?page=search&${params.toString()}`;
    }

    resetFilters() {
        // Сброс всех фильтров
        this.filters = {
            vacationType: '',
            country: '',
            guests: '',
            hotel: '',
            minPrice: null,
            maxPrice: null,
            minNights: null,
            maxNights: null,
            minRating: null
        };
        
        // Сброс выпадающих списков
        document.querySelectorAll('.filter-item[data-filter]').forEach(item => {
            const label = item.querySelector('.filter-label');
            const filterType = item.getAttribute('data-filter');
            if (label) {
                label.textContent = this.getDefaultLabel(filterType);
            }
        });
        
        // Сброс полей ввода цены
        const priceMinInput = document.querySelector('.budget-min');
        const priceMaxInput = document.querySelector('.budget-max');
        if (priceMinInput) priceMinInput.value = '';
        if (priceMaxInput) priceMaxInput.value = '';
        
        // Сброс слайдеров цены
        const priceMinSlider = document.querySelector('.price-min-slider');
        const priceMaxSlider = document.querySelector('.price-max-slider');
        if (priceMinSlider) priceMinSlider.value = 0;
        if (priceMaxSlider) priceMaxSlider.value = 100000;
        
        // Обновление визуального трека
        if (priceMinSlider && priceMaxSlider) {
            const sliderTrack = document.querySelector('.double-range-slider .slider-track');
            if (sliderTrack) {
                sliderTrack.style.left = '0%';
                sliderTrack.style.width = '100%';
            }
            
            const rangeValueMin = document.querySelector('.range-value-min');
            const rangeValueMax = document.querySelector('.range-value-max');
            if (rangeValueMin) rangeValueMin.textContent = '0';
            if (rangeValueMax) rangeValueMax.textContent = '100 000';
        }
        
        // Сброс полей ввода длительности
        const nightsMinInput = document.querySelector('.duration-min');
        const nightsMaxInput = document.querySelector('.duration-max');
        if (nightsMinInput) nightsMinInput.value = '';
        if (nightsMaxInput) nightsMaxInput.value = '';
        
        // Сброс слайдера ночей
        const nightsSlider = document.querySelector('.nights-slider');
        if (nightsSlider) nightsSlider.value = 30;
        
        // Восстановление полного списка отелей
        this.populateHotelDropdown();
        
        // Закрытие всех выпадающих списков
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.style.display = 'none';
        });
        document.querySelectorAll('.filter-chevron').forEach(chevron => {
            chevron.style.transform = 'rotate(45deg)';
            chevron.style.marginTop = '0';
        });
        
        // Применение сброшенных фильтров (показать все туры)
        // Переходим на страницу поиска без параметров
        window.location.href = '?page=search';
    }

    setupSorting() {
        const sortFilterItem = document.querySelector('[data-filter="sort"]');
        if (!sortFilterItem) return;
        
        const sortLabel = sortFilterItem.querySelector('.sort-label');
        const dropdown = sortFilterItem.querySelector('.dropdown-content');
        const chevron = sortFilterItem.querySelector('.sort-chevron');
        const dropdownItems = dropdown ? dropdown.querySelectorAll('.dropdown-item') : [];
        
        // Обработчик клика на элемент сортировки
        sortFilterItem.addEventListener('click', (e) => {
            // Не открываем dropdown, если клик был на элемент внутри dropdown
            if (e.target.closest('.dropdown-content')) {
                return;
            }
            
            const isOpen = dropdown && dropdown.style.display !== 'none';
            
            // Закрываем все другие dropdowns
            document.querySelectorAll('.dropdown-content').forEach(dd => {
                if (dd !== dropdown) {
                    dd.style.display = 'none';
                }
            });
            document.querySelectorAll('.filter-item, .sort-filter-item').forEach(item => {
                if (item !== sortFilterItem) {
                    item.classList.remove('dropdown-open');
                }
            });
            
            // Переключаем текущий dropdown
            if (dropdown) {
                dropdown.style.display = isOpen ? 'none' : 'block';
                sortFilterItem.classList.toggle('dropdown-open', !isOpen);
                
                if (chevron) {
                    if (isOpen) {
                        chevron.style.transform = 'rotate(45deg)';
                        chevron.style.marginTop = '0';
                    } else {
                        chevron.style.transform = 'rotate(225deg)';
                        chevron.style.marginTop = '4px';
                    }
                }
            }
        });
        
        // Обработчики для элементов dropdown
        dropdownItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                const sortValue = item.getAttribute('data-value');
                
                if (!sortValue) return;
                
                // Обновляем label
                if (sortLabel) {
                    sortLabel.textContent = item.textContent;
                }
                
                // Убираем выделение с предыдущего элемента
                dropdownItems.forEach(i => {
                    i.removeAttribute('data-selected');
                });
                // Выделяем выбранный элемент
                item.setAttribute('data-selected', 'true');
                
                // Закрываем dropdown
                if (dropdown) {
                    dropdown.style.display = 'none';
                }
                sortFilterItem.classList.remove('dropdown-open');
                if (chevron) {
                    chevron.style.transform = 'rotate(45deg)';
                    chevron.style.marginTop = '0';
                }
                
                // Обновляем URL и перезагружаем страницу
                const urlParams = new URLSearchParams(window.location.search);
                
                if (sortValue && sortValue !== 'popularity') {
                    urlParams.set('sort', sortValue);
                } else {
                    urlParams.delete('sort');
                }
                
                const currentPage = urlParams.get('page') || 'search';
                urlParams.set('page', currentPage);
                
                window.location.href = `?${urlParams.toString()}`;
            });
        });
        
        // Закрываем dropdown при клике вне его
        document.addEventListener('click', (e) => {
            if (!sortFilterItem.contains(e.target)) {
                if (dropdown) {
                    dropdown.style.display = 'none';
                }
                sortFilterItem.classList.remove('dropdown-open');
                if (chevron) {
                    chevron.style.transform = 'rotate(45deg)';
                    chevron.style.marginTop = '0';
                }
            }
        });
        
        // Выделяем выбранный элемент при загрузке
        dropdownItems.forEach(item => {
            if (item.getAttribute('data-selected') === 'true') {
                item.style.fontWeight = '500';
                item.style.color = '#459292';
            }
        });
    }
}

