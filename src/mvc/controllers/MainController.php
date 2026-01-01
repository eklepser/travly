<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../models/MainModel.php';
require_once __DIR__ . '/../views/MainView.php';

class MainController extends Controller {
    protected function initialize() {
        $this->model = new MainModel();
        $this->view = null; // Создадим после получения данных
    }
    
    public function handle() {
        $data = $this->model->getData();
        
        // Добавляем информацию об админе
        require_once __DIR__ . '/../../utils/auth-helper.php';
        $data['isAdmin'] = checkIsAdmin();
        
        $this->view = new MainView($data);
        $this->view->render();
    }
}

