<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../repositories/TouristRepository.php';
require_once __DIR__ . '/../repositories/BookingRepository.php';

function handleCreateBooking() {
    header('Content-Type: application/json');
    
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
        exit;
    }
    
    $userId = (int)$_SESSION['user_id'];
    $input = $_POST;
    
    if (empty($input)) {
        echo json_encode(['success' => false, 'message' => 'Неверный формат данных']);
        exit;
    }
    
    $tourId = isset($input['tour_id']) ? (int)$input['tour_id'] : 0;
    $totalPrice = isset($input['total_price']) ? (float)$input['total_price'] : 0;
    
    $touristsJson = $input['tourists'] ?? '';
    $tourists = [];
    if (!empty($touristsJson)) {
        $tourists = json_decode($touristsJson, true);
        if (!is_array($tourists)) {
            $tourists = [];
        }
    }
    
    if ($tourId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Неверный ID тура']);
        exit;
    }
    
    if (empty($tourists)) {
        echo json_encode(['success' => false, 'message' => 'Не указаны данные туристов']);
        exit;
    }
    
    try {
        $touristRepo = new TouristRepository();
        $bookingRepo = new BookingRepository();
        
        $touristIds = [];
        
        foreach ($tourists as $touristData) {
            $firstName = trim($touristData['first_name'] ?? '');
            $lastName = trim($touristData['last_name'] ?? '');
            $middleName = trim($touristData['middle_name'] ?? '');
            $birthDateStr = trim($touristData['birthdate'] ?? '');
            $isOrderer = isset($touristData['is_orderer']) ? (bool)$touristData['is_orderer'] : false;
            $isChild = isset($touristData['is_child']) ? (bool)$touristData['is_child'] : false;
            
            if (empty($firstName) || empty($lastName) || empty($birthDateStr)) {
                continue;
            }
            
            $birthDate = parseDate($birthDateStr);
            if (!$birthDate) {
                error_log("[handleCreateBooking] Invalid birthdate: " . $birthDateStr);
                continue;
            }
            
            if ($isChild && empty($touristData['birth_certificate'])) {
                error_log("[handleCreateBooking] Child must have birth certificate");
                continue;
            }
            
            $passportNumber = '';
            $passportIssuedBy = null;
            $passportIssueDate = null;
            
            if ($isChild) {
                $birthCertificate = trim($touristData['birth_certificate'] ?? '');
                if (empty($birthCertificate)) {
                    error_log("[handleCreateBooking] Child missing birth certificate");
                    continue;
                }
                $passportNumber = $birthCertificate;
            } else {
                $passportSeries = trim($touristData['doc_series'] ?? '');
                $passportNum = trim($touristData['doc_number'] ?? '');
                $passportIssueDateStr = trim($touristData['doc_issue_date'] ?? '');
                $passportIssuedBy = trim($touristData['doc_issuing_authority'] ?? '');
                
                if (empty($passportSeries) || empty($passportNum)) {
                    continue;
                }
                
                $passportNumber = $passportSeries . ' ' . $passportNum;
                
                if (!empty($passportIssueDateStr)) {
                    $passportIssueDate = parseDate($passportIssueDateStr);
                }
            }
            
            $touristDataForDb = [
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => $birthDate,
                'passport_number' => $passportNumber,
                'passport_issued_by' => $passportIssuedBy,
                'passport_issue_date' => $passportIssueDate,
                'is_orderer' => $isOrderer,
                'is_child' => $isChild
            ];
            
            $touristId = $touristRepo->findOrCreate($touristDataForDb);
            
            if ($touristId) {
                $touristIds[] = $touristId;
            } else {
                error_log("[handleCreateBooking] Failed to create/find tourist: " . print_r($touristData, true));
            }
        }
        
        if (empty($touristIds)) {
            echo json_encode(['success' => false, 'message' => 'Не удалось создать записи туристов']);
            exit;
        }
        
        $services = isset($input['services']) ? $input['services'] : null;
        
        $bookingData = [
            'user_id' => $userId,
            'tour_id' => $tourId,
            'total_price' => $totalPrice,
            'services' => $services,
            'status' => 'pending'
        ];
        
        $bookingId = $bookingRepo->create($bookingData);
        
        if (!$bookingId) {
            echo json_encode(['success' => false, 'message' => 'Ошибка создания бронирования']);
            exit;
        }
        
        $linkResult = $bookingRepo->linkTourists($bookingId, $touristIds);
        
        if (!$linkResult) {
            error_log("[handleCreateBooking] Failed to link tourists to booking");
        }
        
        echo json_encode([
            'success' => true,
            'booking_id' => $bookingId,
            'message' => 'Бронирование успешно создано'
        ]);
        
    } catch (Exception $e) {
        error_log("[handleCreateBooking] Exception: " . $e->getMessage());
        error_log("[handleCreateBooking] Stack trace: " . $e->getTraceAsString());
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
    }
    
    exit;
}

function parseDate($dateStr) {
    if (empty($dateStr)) {
        return null;
    }
    
    $formats = ['d.m.Y', 'Y-m-d', 'd/m/Y'];
    
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $dateStr);
        if ($date && $date->format($format) === $dateStr) {
            return $date->format('Y-m-d');
        }
    }
    
    $timestamp = strtotime($dateStr);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }
    
    return null;
}



