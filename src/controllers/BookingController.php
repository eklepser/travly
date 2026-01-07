<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/BookingModel.php';
require_once __DIR__ . '/../views/BookingView.php';
require_once __DIR__ . '/../utils/session-helper.php';
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../models/repositories/TouristRepository.php';
require_once __DIR__ . '/../models/repositories/BookingRepository.php';

class BookingController extends Controller {
    protected function initialize() {
        $this->model = new BookingModel();
        $this->view = null;
    }
    
    public function handle() {
        ensureSessionStarted();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'create-booking') {
            $this->handleCreateBooking();
            return;
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=auth');
            exit;
        }
        
        $tourId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
        $adults = isset($_GET['adults']) ? (int)$_GET['adults'] : (isset($_SESSION['booking_data']['adults']) ? (int)$_SESSION['booking_data']['adults'] : 1);
        $children = isset($_GET['children']) ? (int)$_GET['children'] : (isset($_SESSION['booking_data']['children']) ? (int)$_SESSION['booking_data']['children'] : 0);
        
        $bookingData = $_SESSION['booking_data'] ?? [];
        
        $data = $this->model->getData($tourId, $adults, $children, $bookingData);
        
        $this->view = new BookingView($data);
        $this->view->render();
    }
    
    private function handleCreateBooking() {
        header('Content-Type: application/json');
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
        
        if (!empty($touristsJson)) {
            $tourists = json_decode($touristsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $tourists = [];
            }
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
                $touristData['user_id'] = $userId;
                
                $processedData = $this->processTouristData($touristData);
                
                if (!$processedData) {
                    echo json_encode(['success' => false, 'message' => 'Ошибка при обработке данных туриста']);
                    exit;
                }
                
                $touristId = $touristRepo->findOrCreate($processedData);
                
                if (!$touristId) {
                    error_log("[BookingController] Failed to create tourist. Data: " . json_encode($processedData));
                    echo json_encode(['success' => false, 'message' => 'Ошибка при создании туриста']);
                    exit;
                }
                
                $touristIds[] = $touristId;
            }
            
            $services = [];
            if (isset($input['services'])) {
                if (is_array($input['services'])) {
                    $services = $input['services'];
                } else if (is_string($input['services'])) {
                    $services = json_decode($input['services'], true);
                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($services)) {
                        $services = [];
                    }
                }
            }
            
            $bookingId = $bookingRepo->create([
                'user_id' => $userId,
                'tour_id' => $tourId,
                'total_price' => $totalPrice,
                'services' => $services,
                'status' => 'pending'
            ]);
            
            if (!$bookingId) {
                echo json_encode(['success' => false, 'message' => 'Ошибка при создании бронирования']);
                exit;
            }
            
            $linkResult = $bookingRepo->linkTourists($bookingId, $touristIds);
            
            if (!$linkResult) {
                echo json_encode(['success' => false, 'message' => 'Ошибка при связывании туристов с бронированием']);
                exit;
            }
            
            unset($_SESSION['booking_data']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Бронирование создано успешно',
                'booking_id' => $bookingId,
                'redirect' => '?page=me'
            ]);
            
        } catch (Exception $e) {
            error_log("[BookingController] Create booking error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ошибка сервера']);
        }
        exit;
    }
    
    private function processTouristData($data) {
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        
        if (empty($firstName) || empty($lastName)) {
            return null;
        }
        
        $processed = [
            'user_id' => $data['user_id'] ?? null,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'is_orderer' => isset($data['is_orderer']) && ($data['is_orderer'] === true || $data['is_orderer'] === 1 || $data['is_orderer'] === '1'),
            'is_child' => isset($data['is_child']) && ($data['is_child'] === true || $data['is_child'] === 1 || $data['is_child'] === '1')
        ];
        
        if (isset($data['birthdate']) && !empty($data['birthdate'])) {
            $birthdate = trim($data['birthdate']);
            if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $birthdate, $matches)) {
                $processed['date_of_birth'] = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            } else {
                return null;
            }
        } else {
            return null;
        }
        
        if ($processed['is_child']) {
            $birthCertificate = trim($data['birth_certificate'] ?? '');
            if (empty($birthCertificate)) {
                return null;
            }
            $processed['passport_number'] = $birthCertificate;
            $processed['passport_issued_by'] = null;
            $processed['passport_issue_date'] = null;
        } else {
            $docSeries = trim($data['doc_series'] ?? '');
            $docNumber = trim($data['doc_number'] ?? '');
            
            if (empty($docSeries) || empty($docNumber)) {
                return null;
            }
            
            $processed['passport_number'] = $docSeries . ' ' . $docNumber;
            
            if (isset($data['doc_issuing_authority']) && !empty(trim($data['doc_issuing_authority']))) {
                $processed['passport_issued_by'] = trim($data['doc_issuing_authority']);
            } else {
                $processed['passport_issued_by'] = null;
            }
            
            if (isset($data['doc_issue_date']) && !empty(trim($data['doc_issue_date']))) {
                $issueDate = trim($data['doc_issue_date']);
                if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $issueDate, $matches)) {
                    $processed['passport_issue_date'] = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                } else {
                    $processed['passport_issue_date'] = null;
                }
            } else {
                $processed['passport_issue_date'] = null;
            }
        }
        
        return $processed;
    }
}

