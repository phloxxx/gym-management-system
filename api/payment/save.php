<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
    $conn = getConnection();
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['payMethod']) || empty($data['payMethod'])) {
        throw new Exception('Payment method name is required');
    }

    $paymentId = isset($data['paymentId']) ? $data['paymentId'] : null;
    $payMethod = $data['payMethod'];
    $isActive = isset($data['isActive']) ? ($data['isActive'] ? 1 : 0) : 1;
    
    if ($paymentId) {
        // Update existing payment method
        $stmt = $conn->prepare("UPDATE payment SET PAY_METHOD = ?, IS_ACTIVE = ? WHERE PAYMENT_ID = ?");
        $stmt->bind_param("sii", $payMethod, $isActive, $paymentId);
    } else {
        // Insert new payment method
        $stmt = $conn->prepare("INSERT INTO payment (PAY_METHOD, IS_ACTIVE) VALUES (?, ?)");
        $stmt->bind_param("si", $payMethod, $isActive);
    }
    
    if ($stmt->execute()) {
        $response = [
            'status' => 'success',
            'message' => $paymentId ? 'Payment method updated successfully' : 'Payment method added successfully',
            'paymentId' => $paymentId ?? $conn->insert_id
        ];
    } else {
        throw new Exception($stmt->error);
    }
    
    echo json_encode($response);
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
