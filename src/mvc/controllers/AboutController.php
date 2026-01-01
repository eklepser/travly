<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../models/AboutModel.php';
require_once __DIR__ . '/../views/AboutView.php';

class AboutController extends Controller {
    protected function initialize() {
        $this->model = new AboutModel();
        $this->view = null;
    }
    
    public function handle() {
        $data = $this->model->getData();
        
        $this->view = new AboutView($data);
        $this->view->render();
    }
}

