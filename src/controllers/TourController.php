<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/TourModel.php';
require_once __DIR__ . '/../views/TourView.php';

class TourController extends Controller {
    protected function initialize() {
        $this->model = new TourModel();
        $this->view = null;
    }
    
    public function handle() {
        $tourId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : false;
        
        $data = $this->model->getData($tourId);
        
        $this->view = new TourView($data);
        $this->view->render();
    }
}

