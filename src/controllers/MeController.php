<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/MeModel.php';
require_once __DIR__ . '/../views/MeView.php';
require_once __DIR__ . '/../utils/session-helper.php';
require_once __DIR__ . '/../utils/auth-helper.php';

class MeController extends Controller {
    protected function initialize() {
        $this->model = new MeModel();
        $this->view = null;
    }
    
    public function handle() {
        ensureSessionStarted();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=auth');
            exit;
        }
        
        $data = $this->model->getData($_SESSION['user_id']);
        
        require_once __DIR__ . '/../utils/auth-helper.php';
        $data['isAdmin'] = checkIsAdmin();
        
        $this->view = new MeView($data);
        $this->view->render();
    }
}

