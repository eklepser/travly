<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../models/RegistrationModel.php';
require_once __DIR__ . '/../views/RegistrationView.php';

class RegistrationController extends Controller {
    protected function initialize() {
        $this->model = new RegistrationModel();
        $this->view = null;
    }
    
    public function handle() {
        $data = $this->model->getData();
        
        $this->view = new RegistrationView($data);
        $this->view->render();
    }
}

