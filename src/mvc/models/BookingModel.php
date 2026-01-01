<?php
require_once __DIR__ . '/../Model.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../repositories/TourRepository.php';

class BookingModel extends Model {
    private $tourRepository;
    
    public function __construct() {
        parent::__construct();
        $this->tourRepository = new TourRepository();
    }
    
    public function getData($tourId, $adults, $children, $bookingData) {
        if (!$tourId || $tourId <= 0) {
            return [
                'error' => 'Неверный идентификатор тура',
                'tour' => null
            ];
        }
        
        $tour = $this->tourRepository->findById($tourId);
        
        if (!$tour) {
            return [
                'error' => 'Тур не найден',
                'tour' => null
            ];
        }
        
        // Получаем данные из сессии
        $roomPrice = $bookingData['room_price'] ?? 0;
        $selectedServices = $bookingData['services'] ?? [];
        
        if (is_string($selectedServices)) {
            $decoded = json_decode($selectedServices, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $selectedServices = $decoded;
            } else {
                $selectedServices = [];
            }
        }
        
        // Рассчитываем цену
        $basePricePerPerson = (int)($tour['base_price'] ?? 0);
        $basePrice = $adults * $basePricePerPerson + $children * $basePricePerPerson * 0.5;
        
        $servicesPrice = $roomPrice;
        $servicesList = [];
        foreach ($selectedServices as $service) {
            if (isset($service['price'])) {
                $servicesPrice += (int)$service['price'];
            }
            if (isset($service['service'])) {
                $servicesList[] = $service['service'];
            }
        }
        
        $servicesText = !empty($servicesList) ? implode(', ', $servicesList) : '—';
        $totalPrice = $basePrice + $servicesPrice;
        
        // Получаем изображение тура
        $imageUrl = $tour['image_url'] ?? '';
        $isExternalUrl = !empty($imageUrl) && (substr($imageUrl, 0, 7) === 'http://' || substr($imageUrl, 0, 8) === 'https://');
        
        if (empty($imageUrl)) {
            $imageUrl = 'resources/images/tours/default_tour.png';
        } elseif (!$isExternalUrl) {
            $fullPath = __DIR__ . '/../../../public/' . $imageUrl;
            if (!file_exists($fullPath)) {
                $imageUrl = 'resources/images/tours/default_tour.png';
            }
        }
        
        return [
            'tour' => $tour,
            'error' => null,
            'adults' => $adults,
            'children' => $children,
            'basePrice' => $basePrice,
            'totalPrice' => $totalPrice,
            'servicesText' => $servicesText,
            'selectedServices' => $selectedServices,
            'imageUrl' => $imageUrl
        ];
    }
}

