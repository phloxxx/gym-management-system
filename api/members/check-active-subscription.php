<?php
require_once '../../config/db_connection.php';
header('Content-Type: application/json');

// Get member ID from request
$memberId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$memberId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Member ID is required'
    ]);
    exit;
}

try {
    $conn = getConnection();
    
    // Query to check if member has any active subscriptions
    $sql = "SELECT COUNT(*) as active_count 
            FROM member_subscription 
            WHERE MEMBER_ID = ? AND IS_ACTIVE = 1 
            AND END_DATE >= CURDATE()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    // Response with subscription status
    echo json_encode([
        'status' => 'success',
        'has_active_subscription' => ($data['active_count'] > 0)
    ]);
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 