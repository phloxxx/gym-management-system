<?php
require_once '../config/db_connection.php';

header('Content-Type: application/json');

try {
    $conn = getConnection();
    
    // Query to get count of subscriptions expiring in next 7 days
    $sql = "SELECT COUNT(*) as count 
            FROM member_subscription 
            WHERE END_DATE BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
            AND IS_ACTIVE = 1";
    
    $result = $conn->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo json_encode(['count' => $row['count']]);
    } else {
        throw new Exception("Error executing query: " . $conn->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
