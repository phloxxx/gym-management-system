<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
    // Get member ID from URL parameter
    $memberId = isset($_GET['id']) ? intval($_GET['id']) : null;
    if (!$memberId) {
        throw new Exception('Member ID is required');
    }

    $conn = getConnection();
    
    // Check if the member has any active subscriptions
    $query = "SELECT COUNT(*) as active_count 
              FROM member_subscription 
              WHERE MEMBER_ID = ? 
              AND IS_ACTIVE = 1 
              AND CURRENT_DATE() <= END_DATE";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // Determine if member has active subscriptions
    $hasActiveSubscriptions = ($row['active_count'] > 0);
    
    echo json_encode([
        'status' => 'success',
        'has_active_subscription' => $hasActiveSubscriptions,
        'active_count' => $row['active_count']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}