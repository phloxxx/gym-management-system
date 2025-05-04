<?php
require_once '../config/db_connection.php';

header('Content-Type: application/json');

try {
    $conn = getConnection();
    
    // Query to count subscriptions expiring in the next 7 days
    $sql = "SELECT COUNT(*) as expiringCount
            FROM member_subscription 
            WHERE IS_ACTIVE = 1 
            AND END_DATE BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)";
    
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'count' => (int)$row['expiringCount']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error getting expiring count: ' . $e->getMessage(),
        'count' => 0
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>