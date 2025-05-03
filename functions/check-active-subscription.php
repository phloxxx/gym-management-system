<?php
require_once '../config/db_connection.php';

header('Content-Type: application/json');

// Get JSON data from POST request
$data = json_decode(file_get_contents('php://input'), true);

// Log the received data for debugging
error_log('Subscription check data received: ' . json_encode($data));

// Validate required fields
if (!isset($data['memberId']) || !isset($data['startDate']) || !isset($data['endDate'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields',
        'hasActiveSubscription' => false
    ]);
    exit;
}

$memberId = intval($data['memberId']);
$startDate = $data['startDate'];
$endDate = $data['endDate'];

try {
    $conn = getConnection();
    
    // Check if there's any active subscription that overlaps with the given date range
    $sql = "SELECT COUNT(*) as overlap_count 
            FROM member_subscription 
            WHERE MEMBER_ID = ? 
            AND IS_ACTIVE = 1
            AND (
                (? BETWEEN START_DATE AND END_DATE) OR  -- New start date is within existing subscription
                (? BETWEEN START_DATE AND END_DATE) OR  -- New end date is within existing subscription
                (START_DATE BETWEEN ? AND ?) OR         -- Existing start date is within new subscription
                (END_DATE BETWEEN ? AND ?)              -- Existing end date is within new subscription
            )";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }
    
    $stmt->bind_param("issssss", $memberId, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $hasActiveSubscription = ($row['overlap_count'] > 0);
    
    // If there's an active subscription, get the details for the error message
    $activeSubscriptionDetails = null;
    if ($hasActiveSubscription) {
        $detailSql = "SELECT 
                ms.START_DATE, 
                ms.END_DATE, 
                s.SUB_NAME 
            FROM member_subscription ms
            JOIN subscription s ON ms.SUB_ID = s.SUB_ID
            WHERE ms.MEMBER_ID = ? 
            AND ms.IS_ACTIVE = 1
            AND (
                (? BETWEEN ms.START_DATE AND ms.END_DATE) OR
                (? BETWEEN ms.START_DATE AND ms.END_DATE) OR
                (ms.START_DATE BETWEEN ? AND ?) OR
                (ms.END_DATE BETWEEN ? AND ?)
            )
            LIMIT 1";
        
        $detailStmt = $conn->prepare($detailSql);
        $detailStmt->bind_param("issssss", $memberId, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate);
        $detailStmt->execute();
        $activeSubscriptionDetails = $detailStmt->get_result()->fetch_assoc();
    }
    
    echo json_encode([
        'success' => true,
        'hasActiveSubscription' => $hasActiveSubscription,
        'activeSubscription' => $activeSubscriptionDetails
    ]);
    
} catch (Exception $e) {
    error_log('Error checking active subscription: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'hasActiveSubscription' => false
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
