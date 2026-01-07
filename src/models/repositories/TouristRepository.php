<?php
require_once __DIR__ . '/../../core/database.php';

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
                return null;
            }
            
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOfBirth)) {
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
            
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing && isset($existing['id'])) {
                return (int)$existing['id'];
            }
            
            $touristId = $this->create($data);
            return $touristId;
            
        } catch (Exception $e) {
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
            $passportIssuedBy = isset($data['passport_issued_by']) && trim($data['passport_issued_by']) !== '' 
                ? trim($data['passport_issued_by']) 
                : null;
            $passportIssueDate = null;
            if (isset($data['passport_issue_date']) && trim($data['passport_issue_date']) !== '') {
                $dateStr = trim($data['passport_issue_date']);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                    $passportIssueDate = $dateStr;
                }
            }
            $userId = isset($data['user_id']) ? (int)$data['user_id'] : null;
            
            $isOrderer = false;
            if (isset($data['is_orderer'])) {
                $value = $data['is_orderer'];
                if ($value === true || $value === 1 || $value === '1' || $value === 'true') {
                    $isOrderer = true;
                } elseif ($value === false || $value === 0 || $value === '0' || $value === 'false' || $value === '' || $value === null) {
                    $isOrderer = false;
                } else {
                    $isOrderer = (bool)$value;
                }
            }
            
            $isChild = false;
            if (isset($data['is_child'])) {
                $value = $data['is_child'];
                if ($value === true || $value === 1 || $value === '1' || $value === 'true') {
                    $isChild = true;
                } elseif ($value === false || $value === 0 || $value === '0' || $value === 'false' || $value === '' || $value === null) {
                    $isChild = false;
                } else {
                    $isChild = (bool)$value;
                }
            }
            
            if (empty($firstName) || empty($lastName) || empty($dateOfBirth)) {
                return null;
            }
            
            if (empty($passportNumber)) {
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
                RETURNING id
            ";
            
            $params = [
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => $dateOfBirth,
                'passport_number' => $passportNumber,
                'passport_issued_by' => $passportIssuedBy,
                'passport_issue_date' => $passportIssueDate,
                'is_orderer' => $isOrderer ? true : false,
                'is_child' => $isChild ? true : false
            ];
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':first_name', $firstName, PDO::PARAM_STR);
            $stmt->bindValue(':last_name', $lastName, PDO::PARAM_STR);
            $stmt->bindValue(':date_of_birth', $dateOfBirth, PDO::PARAM_STR);
            $stmt->bindValue(':passport_number', $passportNumber, PDO::PARAM_STR);
            $stmt->bindValue(':passport_issued_by', $passportIssuedBy, $passportIssuedBy !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':passport_issue_date', $passportIssueDate, $passportIssueDate !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':is_orderer', $isOrderer ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':is_child', $isChild ? 1 : 0, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if (!$result) {
                return null;
            }
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row === false) {
                return null;
            }
            
            if (!isset($row['id'])) {
                return null;
            }
            
            $touristId = (int)$row['id'];
            
            if ($touristId > 0) {
                return $touristId;
            }
            
            return null;
            
        } catch (PDOException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
}

