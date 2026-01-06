<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/SearchModel.php';
require_once __DIR__ . '/../views/SearchView.php';
require_once __DIR__ . '/../models/repositories/TourRepository.php';
require_once __DIR__ . '/../models/repositories/HotelRepository.php';

class SearchController extends Controller {
    protected function initialize() {
        $this->model = new SearchModel();
        $this->view = null;
    }
    
    public function handle() {
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            
            if ($action === 'filter-options') {
                $this->handleFilterOptions();
                return;
            }
            
            if ($action === 'filter-tours') {
                $this->handleFilterTours();
                return;
            }
            
            if ($action === 'hotels-by-country') {
                $this->handleHotelsByCountry();
                return;
            }
        }
        
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
        
        require_once __DIR__ . '/../utils/auth-helper.php';
        $data['isAdmin'] = checkIsAdmin();
        
        $this->view = new SearchView($data);
        $this->view->render();
    }
    
    private function handleFilterOptions() {
        header('Content-Type: application/json');
        
        $tourRepository = new TourRepository();
        $hotelRepository = new HotelRepository();
        
        echo json_encode([
            'countries' => $tourRepository->getDistinctCountries(),
            'hotels' => $hotelRepository->findAllNames(),
            'maxCapacity' => $hotelRepository->getMaxCapacity(),
            'tourTypes' => $tourRepository->getDistinctTourTypes()
        ]);
        exit;
    }
    
    private function handleFilterTours() {
        header('Content-Type: application/json');
        
        $filters = [
            'vacation_type' => $_GET['vacation_type'] ?? $_POST['vacation_type'] ?? null,
            'country' => $_GET['country'] ?? $_POST['country'] ?? null,
            'min_price' => isset($_GET['min_price']) ? (int)$_GET['min_price'] : (isset($_POST['min_price']) ? (int)$_POST['min_price'] : null),
            'max_price' => isset($_GET['max_price']) ? (int)$_GET['max_price'] : (isset($_POST['max_price']) ? (int)$_POST['max_price'] : null),
            'min_nights' => isset($_GET['min_nights']) ? (int)$_GET['min_nights'] : (isset($_POST['min_nights']) ? (int)$_POST['min_nights'] : null),
            'max_nights' => isset($_GET['max_nights']) ? (int)$_GET['max_nights'] : (isset($_POST['max_nights']) ? (int)$_POST['max_nights'] : null),
            'min_guests' => isset($_GET['min_guests']) ? (int)$_GET['min_guests'] : (isset($_POST['min_guests']) ? (int)$_POST['min_guests'] : null),
            'min_rating' => isset($_GET['min_rating']) ? (float)$_GET['min_rating'] : (isset($_POST['min_rating']) ? (float)$_POST['min_rating'] : null),
            'hotel' => $_GET['hotel'] ?? $_POST['hotel'] ?? null,
            'sort' => $_GET['sort'] ?? $_POST['sort'] ?? 'newest'
        ];
        
        $tourRepository = new TourRepository();
        $tours = $tourRepository->findByFilters($filters);
        
        echo json_encode(['tours' => $tours]);
        exit;
    }
    
    private function handleHotelsByCountry() {
        header('Content-Type: application/json');
        
        $country = $_GET['country'] ?? $_POST['country'] ?? null;
        
        if (!$country) {
            echo json_encode(['hotels' => []]);
            exit;
        }
        
        $hotelRepository = new HotelRepository();
        $hotels = $hotelRepository->findByCountry($country);
        
        echo json_encode(['hotels' => $hotels]);
        exit;
    }
}

