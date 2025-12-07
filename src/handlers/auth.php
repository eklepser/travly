<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';

/**
 * Обработка регистрации
 */
function handleRegister() {
    // Устанавливаем заголовок ДО любых возможных выводов
    header('Content-Type: application/json');
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Включаем отображение ошибок для отладки (в продакшене убрать)
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
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
        
        // Валидация
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
        
        // Проверяем, существует ли пользователь
        $existing = $userRepo->findByEmailOrPhone($emailOrPhone);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Пользователь с таким email или телефоном уже существует']);
            exit;
        }
        
        // Определяем email и phone
        $email = null;
        $phone = null;
        
        if (strpos($emailOrPhone, '@') !== false) {
            // Это email
            if (!filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Неверный формат email']);
                exit;
            }
            $email = $emailOrPhone;
        } else {
            // Это телефон
            $phone = preg_replace('/[^0-9+]/', '', $emailOrPhone);
            if (empty($phone)) {
                echo json_encode(['success' => false, 'message' => 'Неверный формат телефона']);
                exit;
            }
        }
        
        // Создаем пользователя
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
        
        // Автоматически авторизуем пользователя
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
        error_log("[handleRegister] Exception: " . $e->getMessage());
        error_log("[handleRegister] Stack trace: " . $e->getTraceAsString());
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
        exit;
    } catch (Error $e) {
        error_log("[handleRegister] Fatal error: " . $e->getMessage());
        error_log("[handleRegister] Stack trace: " . $e->getTraceAsString());
        echo json_encode(['success' => false, 'message' => 'Критическая ошибка: ' . $e->getMessage()]);
        exit;
    }
}

/**
 * Обработка авторизации
 */
function handleLogin() {
    // Устанавливаем заголовок ДО любых возможных выводов
    header('Content-Type: application/json');
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Включаем отображение ошибок для отладки (в продакшене убрать)
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
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
        
        // Ищем пользователя
        $user = $userRepo->findByEmailOrPhone($emailOrPhone);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Неверный email/телефон или пароль']);
            exit;
        }
        
        // Проверяем пароль
        if (!$userRepo->verifyPassword($password, $user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Неверный email/телефон или пароль']);
            exit;
        }
        
        // Авторизуем пользователя
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
        error_log("[handleLogin] Exception: " . $e->getMessage());
        error_log("[handleLogin] Stack trace: " . $e->getTraceAsString());
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
        exit;
    } catch (Error $e) {
        error_log("[handleLogin] Fatal error: " . $e->getMessage());
        error_log("[handleLogin] Stack trace: " . $e->getTraceAsString());
        echo json_encode(['success' => false, 'message' => 'Критическая ошибка: ' . $e->getMessage()]);
        exit;
    }
}

/**
 * Выход из системы
 */
function handleLogout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_destroy();
    header('Location: /');
    exit;
}

