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
                $touristId = $touristRepo->findOrCreate($touristData);
                
                if (!$touristId) {
                    echo json_encode(['success' => false, 'message' => 'Ошибка при обработке данных туриста']);
                    exit;
                }
                
                $touristIds[] = $touristId;
            }
            
            $services = [];
            if (isset($input['services']) && is_array($input['services'])) {
                $services = $input['services'];
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
}

