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
        await this.loadFilterOptions();
        this.setupDropdowns();
        this.setupSliders();
        this.setupApplyButton();
        this.setupInputs();
    }

    async loadFilterOptions() {
        try {
            const response = await fetch('api/filter-options.php');
            const data = await response.json();
            
            if (data.countries) {
                this.filterOptions.countries = data.countries;
                this.populateCountryDropdown();
            }
            
            if (data.hotels) {
                this.filterOptions.hotels = data.hotels;
                this.populateHotelDropdown();
            }
            
            if (data.maxCapacity) {
                this.filterOptions.maxCapacity = data.maxCapacity;
                this.populateGuestsDropdown();
            }
        } catch (error) {
            console.error('Failed to load filter options:', error);
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

    populateHotelDropdown() {
        const dropdown = document.querySelector('[data-filter="hotel"] .dropdown-content');
        if (!dropdown) return;
        
        const allItem = dropdown.querySelector('[data-value=""]');
        dropdown.innerHTML = '';
        if (allItem) dropdown.appendChild(allItem);
        
        this.filterOptions.hotels.forEach(hotel => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';
            item.setAttribute('data-value', hotel);
            item.textContent = hotel;
            dropdown.appendChild(item);
        });
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
                
                dropdown.style.display = isOpen ? 'none' : 'block';
                chevron.style.transform = isOpen ? 'rotate(45deg)' : 'rotate(225deg)';
                chevron.style.marginTop = isOpen ? '0' : '4px';
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
                    
                    // Закрываем выпадающий список
                    dropdown.style.display = 'none';
                    chevron.style.transform = 'rotate(45deg)';
                    chevron.style.marginTop = '0';
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
            priceMinInput.addEventListener('input', (e) => {
                const value = Math.max(0, Math.min(parseInt(e.target.value) || 0, maxPrice));
                priceMinSlider.value = value;
                updatePriceSlider();
            });
        }
        
        if (priceMaxInput) {
            priceMaxInput.addEventListener('input', (e) => {
                const value = Math.max(0, Math.min(parseInt(e.target.value) || maxPrice, maxPrice));
                priceMaxSlider.value = value;
                updatePriceSlider();
            });
        }
        
        // Инициализация
        updatePriceSlider();
    }

    setupNightsSlider() {
        const nightsSlider = document.querySelector('.nights-slider');
        const nightsMinInput = document.querySelector('.duration-min');
        const nightsMaxInput = document.querySelector('.duration-max');
        
        // Синхронизация слайдера ночей с полями ввода
        if (nightsSlider && nightsMaxInput) {
            nightsSlider.addEventListener('input', (e) => {
                nightsMaxInput.value = e.target.value;
                this.filters.maxNights = parseInt(e.target.value);
            });
            
            nightsMaxInput.addEventListener('input', (e) => {
                const value = parseInt(e.target.value) || 0;
                if (value >= 1 && value <= 365) {
                    nightsSlider.value = Math.min(value, 365);
                    this.filters.maxNights = value;
                }
            });
        }
        
        if (nightsMinInput) {
            nightsMinInput.addEventListener('input', (e) => {
                const value = parseInt(e.target.value) || 0;
                if (value >= 1 && value <= 365) {
                    this.filters.minNights = value;
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
        if (nightsSlider && nightsMaxInput) {
            if (!nightsMaxInput.value) {
                nightsMaxInput.value = nightsSlider.value;
            } else {
                nightsSlider.value = Math.min(parseInt(nightsMaxInput.value) || 365, 365);
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

    async applyFilters() {
        const container = document.getElementById('toursContainer');
        if (!container) return;
        
        // Показываем индикатор загрузки
        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #666;">Загрузка...</div>';
        
        // Формируем параметры запроса
        const params = new URLSearchParams();
        
        if (this.filters.country) params.append('country', this.filters.country);
        if (this.filters.minPrice !== null) params.append('min_price', this.filters.minPrice);
        if (this.filters.maxPrice !== null) params.append('max_price', this.filters.maxPrice);
        if (this.filters.minNights !== null) params.append('min_nights', this.filters.minNights);
        if (this.filters.maxNights !== null) params.append('max_nights', this.filters.maxNights);
        if (this.filters.guests !== null) params.append('min_guests', this.filters.guests);
        if (this.filters.minRating !== null) params.append('min_rating', this.filters.minRating);
        if (this.filters.hotel) params.append('hotel', this.filters.hotel);
        
        try {
            const response = await fetch(`api/filter-tours.php?${params.toString()}`);
            const data = await response.json();
            
            if (data.tours) {
                this.renderTours(data.tours, container);
            } else {
                container.innerHTML = '<div style="text-align: center; padding: 40px; color: #666;">Туры не найдены</div>';
            }
        } catch (error) {
            console.error('Failed to apply filters:', error);
            container.innerHTML = '<div style="text-align: center; padding: 40px; color: #f00;">Ошибка загрузки туров</div>';
        }
    }

    renderTours(tours, container) {
        if (tours.length === 0) {
            container.innerHTML = '<div style="text-align: center; padding: 40px; color: #666;">Туры не найдены</div>';
            return;
        }
        
        container.innerHTML = tours.map(tour => {
            const arrival = new Date(tour.arrival_date);
            const returnDate = new Date(tour.return_date);
            const nights = Math.max(1, Math.floor((returnDate - arrival) / (1000 * 60 * 60 * 24)));
            const rating = parseFloat(tour.hotel_rating);
            const fullStars = Math.min(5, Math.max(0, Math.floor(rating)));
            const emptyStars = 5 - fullStars;
            const price = parseInt(tour.base_price).toLocaleString('ru-RU').replace(/,/g, ' ');
            const maxGuests = parseInt(tour.max_capacity_per_room) || 4;
            const imageUrl = tour.image_url || 'resources/images/tours/default_tour.png';
            
            return `
                <a href="?page=tour&id=${tour.tour_id}" class="card">
                    <div class="card-image" style="background-image: url('${this.escapeHtml(imageUrl)}');"></div>
                    <div class="card-overlay"></div>
                    <div class="card-top">
                        <div class="card-location">
                            <div class="card-country">${this.escapeHtml(tour.country)}</div>
                            <div class="card-city">${this.escapeHtml(tour.city)}</div>
                        </div>
                        <div class="card-rating">${rating.toFixed(1)}</div>
                    </div>
                    <div class="card-bottom">
                        <div class="card-hotel-info">
                            <div class="hotel-stars">${'★'.repeat(fullStars)}${'☆'.repeat(emptyStars)}</div>
                            <div class="hotel-name">${this.escapeHtml(tour.hotel_name)}</div>
                        </div>
                        <div class="card-details">
                            <div class="detail-item">
                                <span class="icon">🌙</span>
                                <span class="value">${nights}</span>
                                <span class="icon">👥</span>
                                <span class="value">1-${maxGuests}</span>
                            </div>
                            <div class="card-price">от ${price} руб/чел</div>
                        </div>
                    </div>
                </a>
            `;
        }).join('');
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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
        if (nightsSlider) nightsSlider.value = 365;
        
        // Закрытие всех выпадающих списков
        document.querySelectorAll('.dropdown-content').forEach(dropdown => {
            dropdown.style.display = 'none';
        });
        document.querySelectorAll('.filter-chevron').forEach(chevron => {
            chevron.style.transform = 'rotate(45deg)';
            chevron.style.marginTop = '0';
        });
        
        // Применение сброшенных фильтров (показать все туры)
        this.applyFilters();
    }
}

