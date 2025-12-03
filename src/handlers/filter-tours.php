<?php
require_once __DIR__ . '/../repositories/TourRepository.php';

function getFilteredTours($filters = []) {
    $tourRepository = new TourRepository();
    return $tourRepository->findByFilters($filters);
}

