<?php
require_once __DIR__ . '/../repositories/HotelRepository.php';

function getHotelsByCountry($country) {
    $hotelRepository = new HotelRepository();
    return $hotelRepository->findByCountry($country);
}

