<?php
require_once __DIR__ . '/../config/database.php';

class TouristRepository {
    private $pdo;
    
    public function __construct() {
        $this->pdo = createPDO();
    }

    public function findOrCreate($data) {
        if (!$this->pdo) {
            error_log("[TouristRepository] findOrCreate: PDO is null");
            return null;
        }
        
        try {
            $firstName = trim($data['first_name'] ?? '');
            $lastName = trim($data['last_name'] ?? '');
            $dateOfBirth = $data['date_of_birth'] ?? null;
            $passportNumber = trim($data['passport_number'] ?? '');
            
            if (empty($firstName) || empty($lastName) || empty($dateOfBirth) || empty($passportNumber)) {
                error_log("[TouristRepository] findOrCreate: Missing required fields. first_name: " . ($firstName ?: 'empty') . ", last_name: " . ($lastName ?: 'empty') . ", date_of_birth: " . ($dateOfBirth ?: 'empty') . ", passport_number: " . ($passportNumber ?: 'empty'));
                return null;
            }
            
            // Ищем существующего туриста
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
            
            // Создаем нового туриста
            $touristId = $this->create($data);
            if (!$touristId) {
                error_log("[TouristRepository] findOrCreate: Failed to create tourist");
            }
            return $touristId;
            
        } catch (Exception $e) {
            error_log("[TouristRepository] findOrCreate failed: " . $e->getMessage());
            error_log("[TouristRepository] findOrCreate stack trace: " . $e->getTraceAsString());
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
            $passportIssueDate = isset($data['passport_issue_date']) && trim($data['passport_issue_date']) !== '' 
                ? trim($data['passport_issue_date']) 
                : null;
            $userId = isset($data['user_id']) ? (int)$data['user_id'] : null;
            
            // Правильная обработка булевых значений для PostgreSQL
            // Пустые строки, null, 0, '0', 'false' -> false
            // Все остальное -> true
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
                error_log("[TouristRepository] create: Missing required fields");
                return null;
            }
            
            if (empty($passportNumber)) {
                error_log("[TouristRepository] create: passport_number is required");
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
            
            // Убеждаемся, что булевые значения передаются как true/false, а не как строки
            // PostgreSQL требует именно boolean тип, а не строку
            $params = [
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => $dateOfBirth,
                'passport_number' => $passportNumber,
                'passport_issued_by' => $passportIssuedBy,
                'passport_issue_date' => $passportIssueDate,
                'is_orderer' => $isOrderer ? true : false,  // Явное приведение к boolean
                'is_child' => $isChild ? true : false        // Явное приведение к boolean
            ];
            
            error_log("[TouristRepository] create: Boolean values - is_orderer: " . ($isOrderer ? 'true' : 'false') . ", is_child: " . ($isChild ? 'true' : 'false'));
            
            error_log("[TouristRepository] create: Executing SQL with params: " . print_r($params, true));
            error_log("[TouristRepository] create: is_orderer type: " . gettype($isOrderer) . ", value: " . var_export($isOrderer, true));
            error_log("[TouristRepository] create: is_child type: " . gettype($isChild) . ", value: " . var_export($isChild, true));
            
            $stmt = $this->pdo->prepare($sql);
            
            // Явно привязываем параметры с указанием типов
            // Для PostgreSQL boolean значения передаем как integer (0/1), так как PDO::PARAM_BOOL может не работать
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':first_name', $firstName, PDO::PARAM_STR);
            $stmt->bindValue(':last_name', $lastName, PDO::PARAM_STR);
            $stmt->bindValue(':date_of_birth', $dateOfBirth, PDO::PARAM_STR);
            $stmt->bindValue(':passport_number', $passportNumber, PDO::PARAM_STR);
            $stmt->bindValue(':passport_issued_by', $passportIssuedBy, $passportIssuedBy !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':passport_issue_date', $passportIssueDate, $passportIssueDate !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            // Преобразуем boolean в integer для PostgreSQL (true -> 1, false -> 0)
            $stmt->bindValue(':is_orderer', $isOrderer ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':is_child', $isChild ? 1 : 0, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("[TouristRepository] create: execute returned false. Error: " . print_r($errorInfo, true));
                return null;
            }
            
            // Для PostgreSQL с RETURNING нужно использовать fetch()
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row === false) {
                error_log("[TouristRepository] create: fetch() returned false - no row returned");
                return null;
            }
            
            if (!isset($row['id'])) {
                error_log("[TouristRepository] create: Row returned but no 'id' field. Row: " . print_r($row, true));
                return null;
            }
            
            $touristId = (int)$row['id'];
            
            if ($touristId > 0) {
                error_log("[TouristRepository] create: Successfully created tourist with ID: {$touristId}");
                return $touristId;
            }
            
            error_log("[TouristRepository] create: Invalid ID returned: {$touristId}");
            return null;
            
        } catch (PDOException $e) {
            error_log("[TouristRepository] create failed (PDOException): " . $e->getMessage());
            error_log("[TouristRepository] create PDO error code: " . $e->getCode());
            error_log("[TouristRepository] create stack trace: " . $e->getTraceAsString());
            return null;
        } catch (Exception $e) {
            error_log("[TouristRepository] create failed: " . $e->getMessage());
            error_log("[TouristRepository] create stack trace: " . $e->getTraceAsString());
            return null;
        }
    }
}

