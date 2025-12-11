<?php
require_once __DIR__ . '/../config/database.php';

class UserRepository {
    private $pdo;
    
    public function __construct() {
        $this->pdo = createPDO();
    }
    
    /**
     * Найти пользователя по email или телефону
     * @param string $emailOrPhone Email или телефон
     * @return array|null
     */
    public function findByEmailOrPhone($emailOrPhone) {
        if (!$this->pdo || empty($emailOrPhone)) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, email, phone, full_name, password_hash, is_admin
                FROM users
                WHERE email = :emailOrPhone OR phone = :emailOrPhone
                LIMIT 1
            ");
            $stmt->execute(['emailOrPhone' => trim($emailOrPhone)]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? $user : null;
        } catch (Exception $e) {
            error_log("[UserRepository] findByEmailOrPhone failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Найти пользователя по ID
     * @param int $userId
     * @return array|null
     */
    public function findById($userId) {
        if (!$this->pdo || $userId <= 0) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, email, phone, full_name, created_at, is_admin
                FROM users
                WHERE id = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => (int)$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? $user : null;
        } catch (Exception $e) {
            error_log("[UserRepository] findById failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Создать нового пользователя
     * @param array $data Данные пользователя
     * @return int|false ID созданного пользователя или false при ошибке
     */
    public function create($data) {
        if (!$this->pdo) {
            return false;
        }
        
        try {
            $fullName = trim($data['full_name'] ?? '');
            $email = !empty($data['email']) ? trim($data['email']) : null;
            $phone = !empty($data['phone']) ? trim($data['phone']) : null;
            $passwordHash = $data['password_hash'] ?? '';
            
            // Проверка: должен быть указан email или phone
            if (empty($email) && empty($phone)) {
                error_log("[UserRepository] create: email or phone is required");
                return false;
            }
            
            if (empty($fullName) || empty($passwordHash)) {
                error_log("[UserRepository] create: full_name and password_hash are required");
                return false;
            }
            
            // Проверка на существование пользователя с таким email или phone
            if ($email) {
                $existing = $this->findByEmailOrPhone($email);
                if ($existing) {
                    error_log("[UserRepository] create: user with email already exists");
                    return false;
                }
            }
            
            if ($phone) {
                $existing = $this->findByEmailOrPhone($phone);
                if ($existing) {
                    error_log("[UserRepository] create: user with phone already exists");
                    return false;
                }
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO users (full_name, email, phone, password_hash)
                VALUES (:full_name, :email, :phone, :password_hash)
            ");
            
            $result = $stmt->execute([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => $passwordHash
            ]);
            
            if (!$result) {
                error_log("[UserRepository] create: execute returned false");
                return false;
            }
            
            $userId = (int)$this->pdo->lastInsertId();
            
            if ($userId > 0) {
                return $userId;
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("[UserRepository] create failed (PDOException): " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("[UserRepository] create failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Проверить пароль пользователя
     * @param string $password Пароль в открытом виде
     * @param string $hash Хеш пароля из БД
     * @return bool
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Создать хеш пароля
     * @param string $password Пароль в открытом виде
     * @return string
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

