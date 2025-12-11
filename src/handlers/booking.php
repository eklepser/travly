<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../repositories/TouristRepository.php';
require_once __DIR__ . '/../repositories/BookingRepository.php';

function handleCreateBooking() {
    header('Content-Type: application/json');
    
    require_once __DIR__ . '/../utils/session-helper.php';
    ensureSessionStarted();
    
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
    
    // Логируем входящие данные для отладки
    error_log("[handleCreateBooking] Received POST data: " . print_r($input, true));
    error_log("[handleCreateBooking] Tourists JSON: " . $touristsJson);
    
    if (!empty($touristsJson)) {
        $tourists = json_decode($touristsJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("[handleCreateBooking] JSON decode error: " . json_last_error_msg());
            error_log("[handleCreateBooking] Raw JSON: " . $touristsJson);
        }
        if (!is_array($tourists)) {
            error_log("[handleCreateBooking] Decoded tourists is not an array: " . gettype($tourists));
            $tourists = [];
        } else {
            error_log("[handleCreateBooking] Decoded tourists count: " . count($tourists));
            error_log("[handleCreateBooking] Decoded tourists: " . print_r($tourists, true));
        }
    }
    
    if ($tourId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Неверный ID тура']);
        exit;
    }
    
    if (empty($tourists)) {
        error_log("[handleCreateBooking] No tourists data provided");
        echo json_encode(['success' => false, 'message' => 'Не указаны данные туристов. Проверьте, что все поля формы заполнены.']);
        exit;
    }
    
    try {
        $touristRepo = new TouristRepository();
        $bookingRepo = new BookingRepository();
        
        $touristIds = [];
        
        foreach ($tourists as $index => $touristData) {
            error_log("[handleCreateBooking] Processing tourist #{$index}: " . print_r($touristData, true));
            
            $firstName = trim($touristData['first_name'] ?? '');
            $lastName = trim($touristData['last_name'] ?? '');
            $middleName = trim($touristData['middle_name'] ?? '');
            $birthDateStr = trim($touristData['birthdate'] ?? '');
            
            // Правильная обработка булевых значений
            // В JSON из JavaScript true/false приходят как 1/0 или true/false
            $isOrderer = false;
            if (isset($touristData['is_orderer'])) {
                $value = $touristData['is_orderer'];
                $isOrderer = ($value === true || $value === 1 || $value === '1' || $value === 'true');
            }
            
            $isChild = false;
            if (isset($touristData['is_child'])) {
                $value = $touristData['is_child'];
                $isChild = ($value === true || $value === 1 || $value === '1' || $value === 'true');
            }
            
            error_log("[handleCreateBooking] Tourist #{$index} parsed: first_name='{$firstName}', last_name='{$lastName}', birthdate='{$birthDateStr}', is_child=" . ($isChild ? 'true' : 'false'));
            
            if (empty($firstName) || empty($lastName) || empty($birthDateStr)) {
                error_log("[handleCreateBooking] Tourist #{$index} - Missing required fields. first_name: " . ($firstName ?: 'empty') . ", last_name: " . ($lastName ?: 'empty') . ", birthdate: " . ($birthDateStr ?: 'empty'));
                continue;
            }
            
            $birthDate = parseDate($birthDateStr);
            if (!$birthDate) {
                error_log("[handleCreateBooking] Invalid birthdate: " . $birthDateStr);
                continue;
            }
            
            $passportNumber = '';
            $passportIssuedBy = null;
            $passportIssueDate = null;
            
            if ($isChild) {
                $birthCertificate = trim($touristData['birth_certificate'] ?? '');
                if (empty($birthCertificate)) {
                    error_log("[handleCreateBooking] Child missing birth certificate: " . print_r($touristData, true));
                    continue;
                }
                $passportNumber = $birthCertificate;
            } else {
                $passportSeries = trim($touristData['doc_series'] ?? '');
                $passportNum = trim($touristData['doc_number'] ?? '');
                $passportIssueDateStr = trim($touristData['doc_issue_date'] ?? '');
                $passportIssuedByStr = trim($touristData['doc_issuing_authority'] ?? '');
                
                if (empty($passportSeries) || empty($passportNum)) {
                    error_log("[handleCreateBooking] Adult missing passport data: " . print_r($touristData, true));
                    continue;
                }
                
                $passportNumber = $passportSeries . ' ' . $passportNum;
                
                // Конвертируем пустые строки в null
                $passportIssuedBy = !empty($passportIssuedByStr) ? $passportIssuedByStr : null;
                
                if (!empty($passportIssueDateStr)) {
                    $passportIssueDate = parseDate($passportIssueDateStr);
                } else {
                    $passportIssueDate = null;
                }
            }
            
            // Убеждаемся, что булевые значения передаются как true/false, а не как строки или пустые значения
            $touristDataForDb = [
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => $birthDate,
                'passport_number' => $passportNumber,
                'passport_issued_by' => $passportIssuedBy,
                'passport_issue_date' => $passportIssueDate,
                'is_orderer' => $isOrderer ? true : false,  // Явное приведение к boolean
                'is_child' => $isChild ? true : false        // Явное приведение к boolean
            ];
            
            error_log("[handleCreateBooking] Tourist #{$index} - Calling findOrCreate with data: " . print_r($touristDataForDb, true));
            
            try {
                $touristId = $touristRepo->findOrCreate($touristDataForDb);
                
                if ($touristId) {
                    error_log("[handleCreateBooking] Tourist #{$index} - Successfully created/found with ID: {$touristId}");
                    $touristIds[] = $touristId;
                } else {
                    $errorMsg = "[handleCreateBooking] Tourist #{$index} - Failed to create/find tourist. Original data: " . print_r($touristData, true) . " DB data: " . print_r($touristDataForDb, true);
                    error_log($errorMsg);
                    // Сохраняем детали ошибки для отладки
                    if (!isset($GLOBALS['booking_errors'])) {
                        $GLOBALS['booking_errors'] = [];
                    }
                    $GLOBALS['booking_errors'][] = "Турист " . ($index + 1) . ": не удалось создать запись. Проверьте логи.";
                }
            } catch (Exception $e) {
                $errorMsg = "[handleCreateBooking] Tourist #{$index} - Exception: " . $e->getMessage();
                error_log($errorMsg);
                error_log("[handleCreateBooking] Tourist #{$index} - Stack trace: " . $e->getTraceAsString());
                
                if (!isset($GLOBALS['booking_errors'])) {
                    $GLOBALS['booking_errors'] = [];
                }
                $GLOBALS['booking_errors'][] = "Турист " . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        if (empty($touristIds)) {
            $errorDetails = [];
            $processedCount = 0;
            
            // Используем сохраненные ошибки, если есть
            if (isset($GLOBALS['booking_errors']) && !empty($GLOBALS['booking_errors'])) {
                $errorDetails = $GLOBALS['booking_errors'];
            } else {
                foreach ($tourists as $index => $touristData) {
                    $processedCount++;
                    $firstName = trim($touristData['first_name'] ?? '');
                    $lastName = trim($touristData['last_name'] ?? '');
                    $birthDateStr = trim($touristData['birthdate'] ?? '');
                    $isChild = isset($touristData['is_child']) ? (bool)$touristData['is_child'] : false;
                    
                    if (empty($firstName) || empty($lastName) || empty($birthDateStr)) {
                        $errorDetails[] = "Турист " . ($index + 1) . ": не заполнены обязательные поля (имя, фамилия, дата рождения)";
                        continue;
                    }
                    
                    $birthDate = parseDate($birthDateStr);
                    if (!$birthDate) {
                        $errorDetails[] = "Турист " . ($index + 1) . ": неверный формат даты рождения";
                        continue;
                    }
                    
                    if ($isChild) {
                        $birthCertificate = trim($touristData['birth_certificate'] ?? '');
                        if (empty($birthCertificate)) {
                            $errorDetails[] = "Турист " . ($index + 1) . " (ребенок): не указано свидетельство о рождении";
                            continue;
                        }
                    } else {
                        $passportSeries = trim($touristData['doc_series'] ?? '');
                        $passportNum = trim($touristData['doc_number'] ?? '');
                        if (empty($passportSeries) || empty($passportNum)) {
                            $errorDetails[] = "Турист " . ($index + 1) . ": не указаны серия и номер документа";
                            continue;
                        }
                    }
                    
                    $errorDetails[] = "Турист " . ($index + 1) . ": ошибка при сохранении в базу данных";
                }
            }
            
            $message = 'Не удалось создать записи туристов. ';
            if (!empty($errorDetails)) {
                $message .= implode('; ', $errorDetails);
            } else {
                $message .= 'Обработано туристов: ' . $processedCount . ', создано: 0';
            }
            
            // Для отладки на localhost добавляем больше информации
            $debugInfo = [];
            if (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
                $debugInfo['tourists_received'] = count($tourists);
                $debugInfo['tourists_created'] = count($touristIds);
                $debugInfo['last_tourist_data'] = end($tourists);
            }
            
            error_log("[handleCreateBooking] Failed to create tourists. Details: " . implode('; ', $errorDetails));
            echo json_encode([
                'success' => false, 
                'message' => $message,
                'debug' => $debugInfo
            ]);
            exit;
        }
        
        // Обрабатываем услуги
        $services = null;
        if (isset($input['services'])) {
            $servicesData = $input['services'];
            // Если это JSON строка, декодируем
            if (is_string($servicesData)) {
                $decoded = json_decode($servicesData, true);
                $services = is_array($decoded) ? $decoded : $servicesData;
            } elseif (is_array($servicesData)) {
                $services = $servicesData;
            }
        }
        
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
            // Не прерываем выполнение, так как бронирование уже создано
            // Можно попробовать удалить бронирование или оставить его без туристов
        }
        
        echo json_encode([
            'success' => true,
            'booking_id' => $bookingId,
            'message' => 'Бронирование успешно создано'
        ]);
        
    } catch (PDOException $e) {
        error_log("[handleCreateBooking] PDOException: " . $e->getMessage());
        error_log("[handleCreateBooking] PDO error code: " . $e->getCode());
        error_log("[handleCreateBooking] Stack trace: " . $e->getTraceAsString());
        $errorMessage = 'Ошибка базы данных: ' . $e->getMessage();
        // В режиме разработки показываем детали ошибки
        if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
            $errorMessage .= ' (Код: ' . $e->getCode() . ')';
        }
        echo json_encode(['success' => false, 'message' => $errorMessage]);
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



