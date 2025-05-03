<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
    $conn = getConnection();
    
    $query = "SELECT PAYMENT_ID, PAY_METHOD, IS_ACTIVE FROM payment ORDER BY PAYMENT_ID";
    $result = $conn->query($query);
    
    if ($result) {
        $payments = array();
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        
        echo json_encode([
            'status' => 'success',
            'payments' => $payments
        ]);
    } else {
        throw new Exception("Error executing query");
    }
    
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
