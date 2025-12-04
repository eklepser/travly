<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../repositories/HotelRepository.php';
require_once __DIR__ . '/../repositories/TourRepository.php';

function handleAddTour() {
    header('Content-Type: application/json');
    
    // Режим отладки (можно отключить в продакшене)
    $debugMode = true; // Установите false для продакшена
    
    try {
        $input = $_POST;
        
        if ($debugMode) {
            error_log("[handleAddTour] POST data: " . print_r($input, true));
        }
        
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
            
            if (empty($hotelData['name'])) {
                echo json_encode(['success' => false, 'message' => 'Название отеля обязательно']);
                exit;
            }
            
            try {
                $hotelId = $hotelRepo->create($hotelData);
                if (!$hotelId) {
                    throw new Exception('Метод create вернул false без исключения');
                }
            } catch (Exception $e) {
                error_log("[handleAddTour] Hotel creation failed: " . $e->getMessage());
                $response = ['success' => false, 'message' => 'Ошибка создания отеля: ' . $e->getMessage()];
                if ($debugMode) {
                    $response['debug'] = [
                        'hotel_data' => $hotelData,
                        'exception' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ];
                }
                echo json_encode($response);
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
        
        // Валидация обязательных полей
        if (empty($tourData['country'])) {
            echo json_encode(['success' => false, 'message' => 'Страна обязательна для заполнения']);
            exit;
        }
        
        if (empty($tourData['city'])) {
            echo json_encode(['success' => false, 'message' => 'Город обязателен для заполнения']);
            exit;
        }
        
        if (empty($tourData['arrival_date'])) {
            echo json_encode(['success' => false, 'message' => 'Дата заезда обязательна']);
            exit;
        }
        
        if (empty($tourData['return_date'])) {
            echo json_encode(['success' => false, 'message' => 'Дата выезда обязательна']);
            exit;
        }
        
        if ($tourData['base_price'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Базовая цена должна быть больше 0']);
            exit;
        }
        
        try {
            $tourId = $tourRepo->create($tourData);
            
            if ($tourId) {
                echo json_encode(['success' => true, 'tour_id' => $tourId]);
            } else {
                throw new Exception('Метод create вернул false без исключения');
            }
        } catch (Exception $e) {
            error_log("[handleAddTour] Tour creation failed: " . $e->getMessage());
            $response = ['success' => false, 'message' => 'Ошибка создания тура: ' . $e->getMessage()];
            if ($debugMode) {
                $response['debug'] = [
                    'tour_data' => $tourData,
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ];
            }
            echo json_encode($response);
        }
    } catch (Exception $e) {
        error_log("[handleAddTour] Exception: " . $e->getMessage());
        error_log("[handleAddTour] Stack trace: " . $e->getTraceAsString());
        $response = ['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        if ($debugMode) {
            $response['debug'] = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }
        echo json_encode($response);
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

function handleDeleteTour() {
    header('Content-Type: application/json');
    
    try {
        $tourId = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
        
        if ($tourId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Неверный ID тура']);
            exit;
        }
        
        $tourRepo = new TourRepository();
        
        // Проверяем, существует ли тур
        $tour = $tourRepo->findById($tourId);
        if (!$tour) {
            echo json_encode(['success' => false, 'message' => 'Тур не найден']);
            exit;
        }
        
        // Удаляем тур
        $result = $tourRepo->delete($tourId);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Тур успешно удален']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка при удалении тура']);
        }
    } catch (Exception $e) {
        error_log("[handleDeleteTour] Exception: " . $e->getMessage());
        error_log("[handleDeleteTour] Stack trace: " . $e->getTraceAsString());
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
    exit;
}

