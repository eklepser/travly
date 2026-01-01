<?php
require_once __DIR__ . '/../Model.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../repositories/TourRepository.php';

class TourModel extends Model {
    private $tourRepository;
    
    public function __construct() {
        parent::__construct();
        $this->tourRepository = new TourRepository();
    }
    
    public function getData($tourId) {
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
        
        // Парсим дополнительные услуги
        $services = !empty($tour['additional_services']) 
            ? json_decode($tour['additional_services'], true) 
            : [];
        
        // Парсим удобства отеля
        $amenities = $tour['amenities'] ?? [];
        if (is_string($amenities) && $amenities !== '') {
            $amenities = trim($amenities, '{}');
            $amenities = $amenities === '' ? [] : array_map('trim', explode(',', $amenities));
        }
        
        return [
            'tour' => $tour,
            'services' => $services,
            'amenities' => $amenities,
            'error' => null
        ];
    }
}

