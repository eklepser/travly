<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../models/HelpModel.php';
require_once __DIR__ . '/../views/HelpView.php';

class HelpController extends Controller {
    protected function initialize() {
        $this->model = new HelpModel();
        $this->view = null;
    }
    
    public function handle() {
        $data = $this->model->getData();
        
        $this->view = new HelpView($data);
        $this->view->render();
    }
}

