<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
    // Get subscription ID from URL parameter
    $subscriptionId = isset($_GET['id']) ? intval($_GET['id']) : null;
    if (!$subscriptionId) {
        throw new Exception('Subscription ID is required');
    }

    $conn = getConnection();
    
    // Query to get subscription details
    $query = "SELECT SUB_ID, SUB_NAME, DURATION, PRICE, IS_ACTIVE FROM subscription WHERE SUB_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $subscriptionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Subscription not found');
    }
    
    $subscription = $result->fetch_assoc();
    
    echo json_encode([
        'status' => 'success',
        'subscription' => $subscription
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
