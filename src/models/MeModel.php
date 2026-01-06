<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/repositories/UserRepository.php';
require_once __DIR__ . '/repositories/BookingRepository.php';
require_once __DIR__ . '/../utils/session-helper.php';
require_once __DIR__ . '/../utils/auth-helper.php';

class MeModel extends Model {
    private $userRepository;
    private $bookingRepository;
    
    public function __construct() {
        parent::__construct();
        ensureSessionStarted();
        $this->userRepository = new UserRepository();
        $this->bookingRepository = new BookingRepository();
    }
    
    public function getData($userId) {
        if (!$userId) {
            return [
                'user' => null,
                'activeBookings' => [],
                'pastBookings' => []
            ];
        }
        
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            return [
                'user' => null,
                'activeBookings' => [],
                'pastBookings' => []
            ];
        }
        
        $nameParts = explode(' ', trim($user['full_name']), 2);
        $lastName = $nameParts[0] ?? '';
        $firstName = $nameParts[1] ?? '';
        
        $phone = $user['phone'] ?? '—';
        $email = $user['email'] ?? '—';
        
        $registrationDate = '—';
        if (!empty($user['created_at'])) {
            $date = new DateTime($user['created_at']);
            $registrationDate = $date->format('d.m.Y');
        }
        
        $bookings = $this->bookingRepository->getUserBookings($userId);
        $activeBookings = $bookings['active'] ?? [];
        $pastBookings = $bookings['past'] ?? [];
        
        return [
            'user' => [
                'id' => $user['id'],
                'lastName' => $lastName,
                'firstName' => $firstName,
                'phone' => $phone,
                'email' => $email,
                'registrationDate' => $registrationDate
            ],
            'activeBookings' => $activeBookings,
            'pastBookings' => $pastBookings
        ];
    }
}

