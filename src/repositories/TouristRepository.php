<?php
require_once __DIR__ . '/../config/database.php';

class TouristRepository {
    private $pdo;
    
    public function __construct() {
        $this->pdo = createPDO();
    }

    public function findOrCreate($data) {
        if (!$this->pdo) {
            return null;
        }
        
        try {
            $firstName = trim($data['first_name'] ?? '');
            $lastName = trim($data['last_name'] ?? '');
            $dateOfBirth = $data['date_of_birth'] ?? null;
            $passportNumber = trim($data['passport_number'] ?? '');
            
            if (empty($firstName) || empty($lastName) || empty($dateOfBirth) || empty($passportNumber)) {
                error_log("[TouristRepository] findOrCreate: Missing required fields");
                return null;
            }
            
            $stmt = $this->pdo->prepare("
                SELECT id FROM tourists 
                WHERE LOWER(TRIM(first_name)) = LOWER(TRIM(:first_name))
                AND LOWER(TRIM(last_name)) = LOWER(TRIM(:last_name))
                AND date_of_birth = :date_of_birth
                AND passport_number = :passport_number
                LIMIT 1
            ");
            
            $stmt->execute([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => $dateOfBirth,
                'passport_number' => $passportNumber
            ]);
            
            $existing = $stmt->fetch();
            
            if ($existing) {
                return (int)$existing['id'];
            }
            
            return $this->create($data);
            
        } catch (Exception $e) {
            error_log("[TouristRepository] findOrCreate failed: " . $e->getMessage());
            return null;
        }
    }

    public function create($data) {
        if (!$this->pdo) {
            return null;
        }
        
        try {
            $firstName = trim($data['first_name'] ?? '');
            $lastName = trim($data['last_name'] ?? '');
            $dateOfBirth = $data['date_of_birth'] ?? null;
            $passportNumber = trim($data['passport_number'] ?? '');
            $passportIssuedBy = !empty($data['passport_issued_by']) ? trim($data['passport_issued_by']) : null;
            $passportIssueDate = !empty($data['passport_issue_date']) ? $data['passport_issue_date'] : null;
            $userId = isset($data['user_id']) ? (int)$data['user_id'] : null;
            $isOrderer = isset($data['is_orderer']) ? (bool)$data['is_orderer'] : false;
            $isChild = isset($data['is_child']) ? (bool)$data['is_child'] : false;
            
            if (empty($firstName) || empty($lastName) || empty($dateOfBirth)) {
                error_log("[TouristRepository] create: Missing required fields");
                return null;
            }
            
            $sql = "
                INSERT INTO tourists (
                    user_id, first_name, last_name, date_of_birth,
                    passport_number, passport_issued_by, passport_issue_date,
                    is_orderer, is_child
                )
                VALUES (
                    :user_id, :first_name, :last_name, :date_of_birth,
                    :passport_number, :passport_issued_by, :passport_issue_date,
                    :is_orderer, :is_child
                )
            ";
            
            $params = [
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => $dateOfBirth,
                'passport_number' => $passportNumber,
                'passport_issued_by' => $passportIssuedBy,
                'passport_issue_date' => $passportIssueDate,
                'is_orderer' => $isOrderer,
                'is_child' => $isChild
            ];
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                error_log("[TouristRepository] create: execute returned false");
                return null;
            }
            
            $touristId = (int)$this->pdo->lastInsertId();
            
            if ($touristId > 0) {
                return $touristId;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("[TouristRepository] create failed: " . $e->getMessage());
            return null;
        }
    }
}

