<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../models/SearchModel.php';
require_once __DIR__ . '/../views/SearchView.php';

class SearchController extends Controller {
    protected function initialize() {
        $this->model = new SearchModel();
        $this->view = null;
    }
    
    public function handle() {
        $filters = [
            'vacation_type' => $_GET['vacation_type'] ?? null,
            'country' => $_GET['country'] ?? null,
            'min_price' => isset($_GET['min_price']) ? (int)$_GET['min_price'] : null,
            'max_price' => isset($_GET['max_price']) ? (int)$_GET['max_price'] : null,
            'min_nights' => isset($_GET['min_nights']) ? (int)$_GET['min_nights'] : null,
            'max_nights' => isset($_GET['max_nights']) ? (int)$_GET['max_nights'] : null,
            'min_guests' => isset($_GET['min_guests']) ? (int)$_GET['min_guests'] : null,
            'min_rating' => isset($_GET['min_rating']) ? (float)$_GET['min_rating'] : null,
            'hotel' => $_GET['hotel'] ?? null,
            'sort' => $_GET['sort'] ?? 'newest'
        ];
        
        $data = $this->model->getData($filters);
        
        require_once __DIR__ . '/../../utils/auth-helper.php';
        $data['isAdmin'] = checkIsAdmin();
        
        $this->view = new SearchView($data);
        $this->view->render();
    }
}

