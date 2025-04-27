<?php
require_once dirname(__DIR__) . '/connection/database.php';
require_once __DIR__ . '/db_functions.php';

function getAllPaymentMethods() {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_get_payment_methods()");
        if (!$stmt->execute()) {
            throw new Exception("Database query failed: " . $conn->error);
        }
        
        $result = $stmt->get_result();
        $methods = [];
        while ($row = $result->fetch_assoc()) {
            $methods[] = [
                'PAYMENT_ID' => $row['PAYMENT_ID'],
                'PAY_METHOD' => $row['PAY_METHOD'],
                'IS_ACTIVE' => (bool)$row['IS_ACTIVE'],
                'ICON' => $row['ICON'] ?? 'fa-credit-card'
            ];
        }
        
        return ['success' => true, 'data' => $methods];
    } catch (Exception $e) {
        error_log("Error getting payment methods: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getPaymentMethodById($paymentId) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_get_payment_method_by_id(?)");
        $stmt->bind_param("i", $paymentId);
        
        if (!$stmt->execute()) {
            throw new Exception("Database query failed: " . $conn->error);
        }
        
        $result = $stmt->get_result();
        $method = $result->fetch_assoc();
        
        return ['success' => true, 'data' => $method];
    } catch (Exception $e) {
        error_log("Error getting payment method: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function createPaymentMethod($payMethod, $isActive) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_create_payment_method(?, ?)");
        $stmt->bind_param("si", $payMethod, $isActive);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $paymentId = $result->fetch_assoc()['payment_id'];
            return ['success' => true, 'data' => ['payment_id' => $paymentId], 'message' => 'Payment method created successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to create payment method'];
        }
    } catch (Exception $e) {
        error_log("Error creating payment method: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

function updatePaymentMethod($paymentId, $payMethod, $isActive, $icon = null) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_update_payment_method(?, ?, ?, ?)");
        $stmt->bind_param("isis", $paymentId, $payMethod, $isActive, $icon);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update payment method: " . $conn->error);
        }
        
        return ['success' => true, 'message' => 'Payment method updated successfully'];
    } catch (Exception $e) {
        error_log("Error updating payment method: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// API endpoint handling
if ($_SERVER['REQUEST_METHOD']) {
    header('Content-Type: application/json');
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $response = isset($_GET['payment_id']) 
                ? getPaymentMethodById($_GET['payment_id'])
                : getAllPaymentMethods();
            echo json_encode($response);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents("php://input"));
            if (!isset($data->pay_method)) {
                echo json_encode(['success' => false, 'message' => 'Payment method name is required']);
                break;
            }
            $response = createPaymentMethod($data->pay_method, $data->is_active);
            echo json_encode($response);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents("php://input"));
            if (!isset($data->payment_id)) {
                echo json_encode(['success' => false, 'message' => 'Payment ID is required']);
                break;
            }
            
            if (!isset($data->pay_method)) {
                echo json_encode(['success' => false, 'message' => 'Payment method name is required']);
                break;
            }
            
            $response = updatePaymentMethod(
                $data->payment_id,
                $data->pay_method,
                $data->is_active,
                $data->icon ?? null
            );
            echo json_encode($response);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
}
?>
