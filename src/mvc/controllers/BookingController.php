<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../models/BookingModel.php';
require_once __DIR__ . '/../views/BookingView.php';
require_once __DIR__ . '/../../utils/session-helper.php';

class BookingController extends Controller {
    protected function initialize() {
        $this->model = new BookingModel();
        $this->view = null;
    }
    
    public function handle() {
        ensureSessionStarted();
        
        $tourId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
        $adults = isset($_GET['adults']) ? (int)$_GET['adults'] : (isset($_SESSION['booking_data']['adults']) ? (int)$_SESSION['booking_data']['adults'] : 1);
        $children = isset($_GET['children']) ? (int)$_GET['children'] : (isset($_SESSION['booking_data']['children']) ? (int)$_SESSION['booking_data']['children'] : 0);
        
        $bookingData = $_SESSION['booking_data'] ?? [];
        
        $data = $this->model->getData($tourId, $adults, $children, $bookingData);
        
        $this->view = new BookingView($data);
        $this->view->render();
    }
}

