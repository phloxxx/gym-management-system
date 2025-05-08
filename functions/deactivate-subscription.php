<?php
require_once __DIR__ . '/../config/db_connection.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['memberId']) || !isset($data['subId'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

$memberId = intval($data['memberId']);
$subId = intval($data['subId']);

try {
    $conn = getConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    // Update the subscription status
    $updateSql = "UPDATE member_subscription 
                  SET IS_ACTIVE = 0,
                      MODIFIED_DATE = CURRENT_TIMESTAMP 
                  WHERE MEMBER_ID = ? 
                  AND SUB_ID = ? 
                  AND IS_ACTIVE = 1";
    
    $stmt = $conn->prepare($updateSql);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $memberId, $subId);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        // Log the deactivation in transaction_log
        $logSql = "INSERT INTO transaction_log 
                   (TRANSACTION_ID, OPERATION, DESCRIPTION, MODIFIEDDATE) 
                   SELECT 
                       t.TRANSACTION_ID,
                       'DEACTIVATED',
                       CONCAT('Subscription deactivated for Member ID: ', ?, ' | Subscription ID: ', ?),
                       CURRENT_TIMESTAMP
                   FROM transaction t 
                   WHERE t.MEMBER_ID = ? 
                   AND t.SUB_ID = ? 
                   ORDER BY t.TRANSACTION_ID DESC 
                   LIMIT 1";
        
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param("iiii", $memberId, $subId, $memberId, $subId);
        $logStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Subscription deactivated successfully'
        ]);
    } else {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'No active subscription found or already deactivated'
        ]);
    }
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    error_log("Error deactivating subscription: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to deactivate subscription: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
