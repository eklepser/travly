<?php
require_once __DIR__ . '/../repositories/TourRepository.php';
require_once __DIR__ . '/../repositories/HotelRepository.php';

function getFilterOptions() {
    $tourRepository = new TourRepository();
    $hotelRepository = new HotelRepository();
    
    return [
        'countries' => $tourRepository->getDistinctCountries(),
        'hotels' => $hotelRepository->findAllNames(),
        'maxCapacity' => $hotelRepository->getMaxCapacity(),
        'tourTypes' => $tourRepository->getDistinctTourTypes()
    ];
}

