<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../repositories/HotelRepository.php';
require_once __DIR__ . '/../repositories/TourRepository.php';

function validateTourData($tourData) {
    if (empty($tourData['country'])) {
        return 'Страна обязательна для заполнения';
    }
    if (empty($tourData['city'])) {
        return 'Город обязателен для заполнения';
    }
    if (empty($tourData['arrival_date'])) {
        return 'Дата заезда обязательна';
    }
    if (empty($tourData['return_date'])) {
        return 'Дата выезда обязательна';
    }
    if ($tourData['base_price'] <= 0) {
        return 'Базовая цена должна быть больше 0';
    }
    return null;
}

function handleAddTour() {
    header('Content-Type: application/json');
    $debugMode = true;
    
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
        
        $validationError = validateTourData($tourData);
        if ($validationError) {
            echo json_encode(['success' => false, 'message' => $validationError]);
            exit;
        }
        
        if (!empty($input['additional_services'])) {
            $additionalServices = trim($input['additional_services']);
            try {
                json_decode($additionalServices, true);
                $tourData['additional_services'] = $additionalServices;
            } catch (Exception $e) {
                $tourData['additional_services'] = $additionalServices;
            }
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
        
        $tour = $tourRepo->findById($tourId);
        if (!$tour) {
            echo json_encode(['success' => false, 'message' => 'Тур не найден']);
            exit;
        }
        
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

function handleGetTour() {
    header('Content-Type: application/json');
    
    try {
        $tourId = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;
        
        if ($tourId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Неверный ID тура']);
            exit;
        }
        
        $tourRepo = new TourRepository();
        $tour = $tourRepo->findById($tourId);
        
        if (!$tour) {
            echo json_encode(['success' => false, 'message' => 'Тур не найден']);
            exit;
        }
        
        $tourData = [
            'tour_id' => $tour['id'],
            'vacation_type' => $tour['tour_type'] ?? '',
            'country' => $tour['country'] ?? '',
            'city' => $tour['location'] ?? '',
            'hotel_id' => $tour['hotel_id'] ?? null,
            'hotel_name' => $tour['hotel_name'] ?? '',
            'hotel_rating' => $tour['hotel_rating'] ?? null,
            'max_capacity_per_room' => $tour['max_capacity_per_room'] ?? 4,
            'departure_point' => $tour['departure_point'] ?? '',
            'departure_date' => $tour['departure_date'] ?? '',
            'arrival_date' => $tour['arrival_date'] ?? '',
            'return_date' => $tour['return_date'] ?? '',
            'base_price' => $tour['base_price'] ?? 0,
            'image_url' => $tour['image_url'] ?? '',
            'additional_services' => $tour['additional_services'] ?? ''
        ];
        
        echo json_encode(['success' => true, 'tour' => $tourData]);
    } catch (Exception $e) {
        error_log("[handleGetTour] Exception: " . $e->getMessage());
        error_log("[handleGetTour] Stack trace: " . $e->getTraceAsString());
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
    exit;
}

function handleUpdateTour() {
    header('Content-Type: application/json');
    $debugMode = true;
    
    try {
        $input = $_POST;
        
        if ($debugMode) {
            error_log("[handleUpdateTour] POST data: " . print_r($input, true));
        }
        
        if (empty($input)) {
            echo json_encode(['success' => false, 'message' => 'Неверный формат данных']);
            exit;
        }
        
        $tourId = isset($input['tour_id']) ? (int)$input['tour_id'] : 0;
        if ($tourId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Неверный ID тура']);
            exit;
        }
        
        $tourRepo = new TourRepository();
        
        $existingTour = $tourRepo->findById($tourId);
        if (!$existingTour) {
            echo json_encode(['success' => false, 'message' => 'Тур не найден']);
            exit;
        }
        
        $hotelId = null;
        
        if (($input['hotel_mode'] ?? '') === 'existing') {
            $hotelId = (int)($input['existing_hotel_id'] ?? 0);
            if (!$hotelId) {
                echo json_encode(['success' => false, 'message' => 'Не выбран отель']);
                exit;
            }
        } else if (($input['hotel_mode'] ?? '') === 'new') {
            $hotelRepo = new HotelRepository();
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
                error_log("[handleUpdateTour] Hotel creation failed: " . $e->getMessage());
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
            $hotelId = (int)($existingTour['hotel_id'] ?? 0);
            if ($hotelId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Не выбран режим отеля']);
                exit;
            }
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
        
        $validationError = validateTourData($tourData);
        if ($validationError) {
            echo json_encode(['success' => false, 'message' => $validationError]);
            exit;
        }
        
        if (!empty($input['additional_services'])) {
            $additionalServices = trim($input['additional_services']);
            try {
                json_decode($additionalServices, true);
                $tourData['additional_services'] = $additionalServices;
            } catch (Exception $e) {
                $tourData['additional_services'] = $additionalServices;
            }
        }
        
        try {
            $result = $tourRepo->update($tourId, $tourData);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Тур успешно обновлен', 'tour_id' => $tourId]);
            } else {
                throw new Exception('Метод update вернул false без исключения');
            }
        } catch (Exception $e) {
            error_log("[handleUpdateTour] Tour update failed: " . $e->getMessage());
            $response = ['success' => false, 'message' => 'Ошибка обновления тура: ' . $e->getMessage()];
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
        error_log("[handleUpdateTour] Exception: " . $e->getMessage());
        error_log("[handleUpdateTour] Stack trace: " . $e->getTraceAsString());
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
