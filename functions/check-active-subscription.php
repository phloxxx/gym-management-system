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
        'hasOverlappingSubscription' => false
    ]);
    exit;
}

$memberId = intval($data['memberId']);
$startDate = $data['startDate'];
$endDate = $data['endDate'];
$renewalId = isset($data['renewalId']) ? intval($data['renewalId']) : null;

try {
    $conn = getConnection();
    
    // Build the SQL query to check for overlapping subscriptions
    // Exclude the subscription being renewed if renewalId is provided
    $sql = "SELECT ms.START_DATE, ms.END_DATE, ms.IS_ACTIVE, s.SUB_NAME, ms.SUB_ID
            FROM member_subscription ms
            JOIN subscription s ON ms.SUB_ID = s.SUB_ID
            WHERE ms.MEMBER_ID = ? AND (
                (? BETWEEN ms.START_DATE AND ms.END_DATE) OR  -- New start date is within existing subscription
                (? BETWEEN ms.START_DATE AND ms.END_DATE) OR  -- New end date is within existing subscription
                (ms.START_DATE BETWEEN ? AND ?) OR           -- Existing start date is within new subscription
                (ms.END_DATE BETWEEN ? AND ?)               -- Existing end date is within new subscription
            )";
    
    // If this is a renewal, exclude the subscription being renewed from the check
    if ($renewalId) {
        $sql .= " AND ms.SUB_ID != ?";
    }
    
    $sql .= " ORDER BY ms.IS_ACTIVE DESC, ms.END_DATE DESC LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare query: " . $conn->error);
    }
    
    if ($renewalId) {
        $stmt->bind_param("isssssi", 
            $memberId, 
            $startDate, $endDate, 
            $startDate, $endDate, 
            $startDate, $endDate,
            $renewalId
        );
    } else {
        $stmt->bind_param("issssss", 
            $memberId, 
            $startDate, $endDate, 
            $startDate, $endDate, 
            $startDate, $endDate
        );
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $hasOverlappingSubscription = ($result->num_rows > 0);
    
    // Get the details of any overlapping subscription for the error message
    $overlappingSubscriptionDetails = null;
    if ($hasOverlappingSubscription) {
        $overlappingSubscriptionDetails = $result->fetch_assoc();
    }
    
    echo json_encode([
        'success' => true,
        'hasOverlappingSubscription' => $hasOverlappingSubscription,
        'overlappingSubscription' => $overlappingSubscriptionDetails,
        'renewalId' => $renewalId,
        'message' => $hasOverlappingSubscription ? 
            "This date range overlaps with a " . 
            ($overlappingSubscriptionDetails['IS_ACTIVE'] ? "current" : "past") . 
            " subscription ({$overlappingSubscriptionDetails['SUB_NAME']}) from {$overlappingSubscriptionDetails['START_DATE']} to {$overlappingSubscriptionDetails['END_DATE']}." : 
            "No overlapping subscriptions found"
    ]);
    
} catch (Exception $e) {
    error_log('Error checking subscription overlap: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'hasOverlappingSubscription' => false
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
