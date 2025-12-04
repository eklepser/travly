<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../repositories/HotelRepository.php';
require_once __DIR__ . '/../repositories/TourRepository.php';

function handleAddTour() {
    header('Content-Type: application/json');
    
    $input = $_POST;
    
    if (empty($input)) {
        echo json_encode(['success' => false, 'message' => 'Неверный формат данных']);
        exit;
    }
    
    $hotelRepo = new HotelRepository();
    $tourRepo = new TourRepository();
    
    $hotelId = null;
    
    if (($input['hotel_mode'] ?? '') === 'existing') {
        $hotelId = (int)($input['existing_hotel_id'] ?? 0);
        if (!$hotelId) {
            echo json_encode(['success' => false, 'message' => 'Не выбран отель']);
            exit;
        }
    } else if (($input['hotel_mode'] ?? '') === 'new') {
        $hotelData = [
            'name' => trim($input['new_hotel_name'] ?? ''),
            'rating' => (float)($input['new_hotel_rating'] ?? 4),
            'max_capacity_per_room' => (int)($input['new_hotel_max_guests'] ?? 4),
            'country' => $input['country'] ?? '',
            'city' => $input['city'] ?? ''
        ];
        
        $hotelId = $hotelRepo->create($hotelData);
        if (!$hotelId) {
            echo json_encode(['success' => false, 'message' => 'Ошибка создания отеля']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Не выбран режим отеля']);
        exit;
    }
    
    $tourData = [
        'country' => trim($input['country'] ?? ''),
        'city' => trim($input['city'] ?? ''),
        'hotel_id' => $hotelId,
        'base_price' => (int)($input['base_price'] ?? 0),
        'departure_point' => trim($input['departure_point'] ?? 'Москва'),
        'departure_date' => $input['departure_date'] ?? $input['arrival_date'] ?? '',
        'arrival_point' => trim($input['arrival_point'] ?? $input['city'] ?? ''),
        'arrival_date' => $input['arrival_date'] ?? '',
        'return_point' => trim($input['return_point'] ?? $input['departure_point'] ?? 'Москва'),
        'return_date' => $input['return_date'] ?? '',
        'image_url' => !empty($input['image_url']) ? trim($input['image_url']) : null,
        'vacation_type' => $input['vacation_type'] ?? null
    ];
    
    $tourId = $tourRepo->create($tourData);
    
    if ($tourId) {
        echo json_encode(['success' => true, 'tour_id' => $tourId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка создания тура']);
    }
    exit;
}

function handleGetHotels() {
    header('Content-Type: application/json');
    $hotelRepo = new HotelRepository();
    
    $country = $_GET['country'] ?? null;
    if ($country) {
        $hotels = $hotelRepo->findByCountryWithDetails($country);
    } else {
        $hotels = $hotelRepo->findAll();
    }
    
    echo json_encode($hotels);
    exit;
}

function handleAddHotel() {
    header('Content-Type: application/json');
    
    $input = $_POST;
    
    if (empty($input)) {
        echo json_encode(['success' => false, 'message' => 'Неверный формат данных']);
        exit;
    }
    
    $hotelRepo = new HotelRepository();
    
    $hotelData = [
        'name' => trim($input['name'] ?? ''),
        'rating' => !empty($input['rating']) ? (float)$input['rating'] : null,
        'max_capacity_per_room' => (int)($input['max_capacity_per_room'] ?? 4)
    ];
    
    if (empty($hotelData['name'])) {
        echo json_encode(['success' => false, 'message' => 'Название отеля обязательно']);
        exit;
    }
    
    if ($hotelData['rating'] !== null) {
        if ($hotelData['rating'] < 1 || $hotelData['rating'] > 5) {
            echo json_encode(['success' => false, 'message' => 'Рейтинг должен быть от 1 до 5']);
            exit;
        }
    }
    
    if ($hotelData['max_capacity_per_room'] < 1 || $hotelData['max_capacity_per_room'] > 10) {
        echo json_encode(['success' => false, 'message' => 'Максимальная вместимость должна быть от 1 до 10']);
        exit;
    }
    
    $hotelId = $hotelRepo->create($hotelData);
    
    if ($hotelId) {
        echo json_encode(['success' => true, 'hotel_id' => $hotelId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка создания отеля']);
    }
    exit;
}

