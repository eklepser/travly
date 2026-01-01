<?php
require_once __DIR__ . '/../Model.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../repositories/TourRepository.php';
require_once __DIR__ . '/../../repositories/HotelRepository.php';

class SearchModel extends Model {
    private $tourRepository;
    private $hotelRepository;
    
    public function __construct() {
        parent::__construct();
        $this->tourRepository = new TourRepository();
        $this->hotelRepository = new HotelRepository();
    }
    
    public function getData($filters = []) {
        $tours = $this->tourRepository->findByFilters($filters);
        
        $filterOptions = [
            'countries' => $this->tourRepository->getDistinctCountries(),
            'hotels' => $this->hotelRepository->findAllNames(),
            'maxCapacity' => $this->hotelRepository->getMaxCapacity(),
            'tourTypes' => $this->tourRepository->getDistinctTourTypes(),
            'allHotels' => $this->hotelRepository->findAllNames()
        ];
        
        // Если выбрана страна, фильтруем отели по стране
        if (!empty($filters['country'])) {
            $filterOptions['hotels'] = $this->hotelRepository->findByCountry($filters['country']);
        }
        
        return [
            'tours' => $tours,
            'filters' => $filters,
            'filterOptions' => $filterOptions
        ];
    }
}

