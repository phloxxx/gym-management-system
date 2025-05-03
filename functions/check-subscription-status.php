<?php
require_once '../config/db_connection.php';

header('Content-Type: application/json');

// Validate required parameters
if (!isset($_GET['memberId']) || !isset($_GET['subId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters: memberId and/or subId'
    ]);
    exit;
}

$memberId = intval($_GET['memberId']);
$subId = intval($_GET['subId']);

// Validate parameters
if ($memberId <= 0 || $subId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid member ID or subscription ID'
    ]);
    exit;
}

try {
    $conn = getConnection();
    
    // Query to get subscription status
    $sql = "SELECT IS_ACTIVE FROM member_subscription 
            WHERE MEMBER_ID = ? AND SUB_ID = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $memberId, $subId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Subscription not found'
        ]);
        exit;
    }
    
    $row = $result->fetch_assoc();
    $isActive = (int)$row['IS_ACTIVE'] === 1;
    
    echo json_encode([
        'success' => true,
        'isActive' => $isActive,
        'message' => $isActive ? 'Subscription is active' : 'Subscription is inactive'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
