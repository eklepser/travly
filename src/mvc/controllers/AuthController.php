<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../models/AuthModel.php';
require_once __DIR__ . '/../views/AuthView.php';

class AuthController extends Controller {
    protected function initialize() {
        $this->model = new AuthModel();
        $this->view = null;
    }
    
    public function handle() {
        $data = $this->model->getData();
        
        $this->view = new AuthView($data);
        $this->view->render();
    }
}

