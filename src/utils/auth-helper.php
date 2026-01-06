<?php

function checkIsAdmin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    require_once __DIR__ . '/../core/database.php';
    require_once __DIR__ . '/../models/repositories/UserRepository.php';
    
    $userRepo = new UserRepository();
    $user = $userRepo->findById($_SESSION['user_id']);
    
    if (!$user || !isset($user['is_admin'])) {
        return false;
    }
    
    return ($user['is_admin'] == 1 || $user['is_admin'] === true || $user['is_admin'] === '1');
}

