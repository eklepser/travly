<?php
require_once __DIR__ . '/../config/database.php';

class BookingRepository {
    private $pdo;
    
    public function __construct() {
        $this->pdo = createPDO();
    }

    public function create($data) {
        if (!$this->pdo) {
            return null;
        }
        
        try {
            $userId = (int)($data['user_id'] ?? 0);
            $tourId = (int)($data['tour_id'] ?? 0);
            $totalPrice = (float)($data['total_price'] ?? 0);
            $services = !empty($data['services']) ? json_encode($data['services'], JSON_UNESCAPED_UNICODE) : null;
            $status = $data['status'] ?? 'pending';
            
            if ($userId <= 0 || $tourId <= 0) {
                error_log("[BookingRepository] create: Invalid user_id or tour_id");
                return null;
            }
            
            if ($totalPrice < 0) {
                error_log("[BookingRepository] create: Invalid total_price");
                return null;
            }
            
            $sql = "
                INSERT INTO bookings (
                    user_id, tour_id, total_price, services, status
                )
                VALUES (
                    :user_id, :tour_id, :total_price, :services, :status
                )
            ";
            
            $params = [
                'user_id' => $userId,
                'tour_id' => $tourId,
                'total_price' => $totalPrice,
                'services' => $services,
                'status' => $status
            ];
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            if (!$result) {
                error_log("[BookingRepository] create: execute returned false");
                return null;
            }
            
            $bookingId = (int)$this->pdo->lastInsertId();
            
            if ($bookingId > 0) {
                return $bookingId;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("[BookingRepository] create failed: " . $e->getMessage());
            return null;
        }
    }

    public function linkTourists($bookingId, $touristIds) {
        if (!$this->pdo || $bookingId <= 0 || empty($touristIds)) {
            return false;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            foreach ($touristIds as $touristId) {
                $touristId = (int)$touristId;
                if ($touristId <= 0) {
                    continue;
                }
                
                $stmt = $this->pdo->prepare("
                    INSERT INTO booking_tourist (booking_id, tourist_id)
                    VALUES (:booking_id, :tourist_id)
                    ON CONFLICT (booking_id, tourist_id) DO NOTHING
                ");
                
                $stmt->execute([
                    'booking_id' => $bookingId,
                    'tourist_id' => $touristId
                ]);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("[BookingRepository] linkTourists failed: " . $e->getMessage());
            return false;
        }
    }
}

