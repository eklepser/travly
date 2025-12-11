<?php
require_once __DIR__ . '/../config/database.php';

class UserRepository {
    private $pdo;
    
    public function __construct() {
        $this->pdo = createPDO();
    }
    
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
    
    public function create($data) {
        if (!$this->pdo) {
            return false;
        }
        
        try {
            $fullName = trim($data['full_name'] ?? '');
            $email = !empty($data['email']) ? trim($data['email']) : null;
            $phone = !empty($data['phone']) ? trim($data['phone']) : null;
            $passwordHash = $data['password_hash'] ?? '';
            
            if (empty($email) && empty($phone)) {
                error_log("[UserRepository] create: email or phone is required");
                return false;
            }
            
            if (empty($fullName) || empty($passwordHash)) {
                error_log("[UserRepository] create: full_name and password_hash are required");
                return false;
            }
            
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
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
