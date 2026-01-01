<?php
require_once __DIR__ . '/../Model.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../repositories/TourRepository.php';
require_once __DIR__ . '/../../repositories/HotelRepository.php';

class MainModel extends Model {
    private $tourRepository;
    private $hotelRepository;
    
    public function __construct() {
        parent::__construct();
        $this->tourRepository = new TourRepository();
        $this->hotelRepository = new HotelRepository();
    }
    
    public function getData() {
        $beachTours = $this->tourRepository->findByFilters(['vacation_type' => 'beach']);
        $beachTours = array_slice($beachTours, 0, 6);
        
        $mountainTours = $this->tourRepository->findByFilters(['vacation_type' => 'mountain']);
        $mountainTours = array_slice($mountainTours, 0, 6);
        
        $excursionTours = $this->tourRepository->findByFilters(['vacation_type' => 'excursion']);
        $excursionTours = array_slice($excursionTours, 0, 6);
        
        $filterOptions = [
            'countries' => $this->tourRepository->getDistinctCountries(),
            'hotels' => $this->hotelRepository->findAllNames(),
            'maxCapacity' => $this->hotelRepository->getMaxCapacity(),
            'tourTypes' => $this->tourRepository->getDistinctTourTypes(),
            'allHotels' => $this->hotelRepository->findAllNames()
        ];
        
        return [
            'beachTours' => $beachTours,
            'mountainTours' => $mountainTours,
            'excursionTours' => $excursionTours,
            'filterOptions' => $filterOptions
        ];
    }
}

