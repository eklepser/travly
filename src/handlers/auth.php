<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../repositories/user-repository.php';

function handleRegister() {
    header('Content-Type: application/json');
    
    require_once __DIR__ . '/../utils/session-helper.php';
    ensureSessionStarted();
    
    try {
        $input = $_POST;
        
        if (empty($input)) {
            echo json_encode(['success' => false, 'message' => 'Неверный формат данных']);
            exit;
        }
        
        $firstName = trim($input['first_name'] ?? '');
        $lastName = trim($input['last_name'] ?? '');
        $emailOrPhone = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';
        
        if (empty($firstName) || empty($lastName)) {
            echo json_encode(['success' => false, 'message' => 'Заполните имя и фамилию']);
            exit;
        }
        
        if (empty($emailOrPhone)) {
            echo json_encode(['success' => false, 'message' => 'Укажите email или номер телефона']);
            exit;
        }
        
        if (empty($password) || strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Пароль должен быть не менее 6 символов']);
            exit;
        }
        
        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Пароли не совпадают']);
            exit;
        }
        
        $userRepo = new UserRepository();
        
        $existing = $userRepo->findByEmailOrPhone($emailOrPhone);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Пользователь с таким email или телефоном уже существует']);
            exit;
        }
        
        $email = null;
        $phone = null;
        
        if (strpos($emailOrPhone, '@') !== false) {
            if (!filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Неверный формат email']);
                exit;
            }
            $email = $emailOrPhone;
        } else {
            $phone = preg_replace('/[^0-9+]/', '', $emailOrPhone);
            if (empty($phone)) {
                echo json_encode(['success' => false, 'message' => 'Неверный формат телефона']);
                exit;
            }
        }
        
        $fullName = $lastName . ' ' . $firstName;
        $passwordHash = $userRepo->hashPassword($password);
        
        $userData = [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => $passwordHash
        ];
        
        $userId = $userRepo->create($userData);
        
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Ошибка при создании пользователя']);
            exit;
        }
        
        $_SESSION['user_id'] = $userId;
        $user = $userRepo->findById($userId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Регистрация успешна',
            'user' => [
                'id' => $userId,
                'full_name' => $user['full_name'] ?? $fullName
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
        exit;
    }
}

function handleLogin() {
    header('Content-Type: application/json');
    
    require_once __DIR__ . '/../utils/session-helper.php';
    ensureSessionStarted();
    
    try {
        $input = $_POST;
        
        if (empty($input)) {
            echo json_encode(['success' => false, 'message' => 'Неверный формат данных']);
            exit;
        }
        
        $emailOrPhone = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        
        if (empty($emailOrPhone)) {
            echo json_encode(['success' => false, 'message' => 'Укажите email или номер телефона']);
            exit;
        }
        
        if (empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Введите пароль']);
            exit;
        }
        
        $userRepo = new UserRepository();
        
        $user = $userRepo->findByEmailOrPhone($emailOrPhone);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Неверный email/телефон или пароль']);
            exit;
        }
        
        if (!$userRepo->verifyPassword($password, $user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Неверный email/телефон или пароль']);
            exit;
        }
        
        $_SESSION['user_id'] = (int)$user['id'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Вход выполнен успешно',
            'user' => [
                'id' => (int)$user['id'],
                'full_name' => $user['full_name']
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
        exit;
    }
}

function handleLogout() {
    require_once __DIR__ . '/../utils/session-helper.php';
    ensureSessionStarted();
    session_destroy();
    header('Location: /');
    exit;
}