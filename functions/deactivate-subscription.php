<?php
// Turn off error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Add a log entry to verify changes are being applied
error_log("This version of deactivate-subscription.php was modified at " . date('Y-m-d H:i:s'));

require_once __DIR__ . '/../config/db_connection.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Log incoming request data
error_log("Deactivation request received: " . json_encode($data));

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

// Important: logging exactly what data was received
error_log("Processing deactivation for Member ID: $memberId, Subscription ID: $subId");

try {
    $conn = getConnection();
    
    // Start transaction
    $conn->begin_transaction();
    error_log("Transaction started for deactivation");
    
    // Update the subscription status
    $sql = "UPDATE member_subscription SET IS_ACTIVE = 0 WHERE MEMBER_ID = ? AND SUB_ID = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $memberId, $subId);
    $stmt->execute();
    
    // If no rows were affected, the membership might not exist
    if ($stmt->affected_rows <= 0) {
        error_log("No rows affected when updating subscription. It may not exist or is already inactive.");
        throw new Exception("Subscription not found or already inactive");
    }
    
    error_log("Successfully deactivated subscription. Rows affected: " . $stmt->affected_rows);
    
    // First, find the most recent transaction for this member and subscription
    $findTxnSql = "SELECT TRANSACTION_ID FROM transaction 
                  WHERE MEMBER_ID = ? AND SUB_ID = ?
                  ORDER BY TRANSAC_DATE DESC LIMIT 1";
    
    $findTxnStmt = $conn->prepare($findTxnSql);
    if (!$findTxnStmt) {
        throw new Exception("Failed to prepare find transaction statement: " . $conn->error);
    }
    
    $findTxnStmt->bind_param("ii", $memberId, $subId);
    $findTxnStmt->execute();
    $findTxnResult = $findTxnStmt->get_result();
    
    if ($findTxnResult->num_rows > 0) {
        // We found a transaction ID to use
        $txnRow = $findTxnResult->fetch_assoc();
        $transactionId = $txnRow['TRANSACTION_ID'];
        
        // Now create a transaction log entry
        $operation = "DEACTIVATE";
        
        $logSql = "INSERT INTO transaction_log (TRANSACTION_ID, OPERATION, MODIFIEDDATE) 
                  VALUES (?, ?, CURRENT_DATE)";
        
        $logStmt = $conn->prepare($logSql);
        if (!$logStmt) {
            throw new Exception("Failed to prepare log statement: " . $conn->error);
        }
        
        $logStmt->bind_param("is", $transactionId, $operation);
        $logStmt->execute();
    } else {
        // No transaction found for this member/subscription
        error_log("No transaction found for member ID $memberId and subscription ID $subId");
        // This is not a critical error, so we don't throw an exception
        // We'll just skip the transaction log entry
    }
    
    // Commit the transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Subscription deactivated successfully'
    ]);
    
} catch (Exception $e) {
    // Roll back the transaction in case of error
    if (isset($conn) && !$conn->connect_error) {
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
