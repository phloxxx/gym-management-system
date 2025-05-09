<?php
// Turn off error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Add a log entry to verify changes are being applied
error_log("This version of deactivate-subscription.php was modified at " . date('Y-m-d H:i:s'));

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/update-member-status.php'; // Include member status update function

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
        $txnRow = $findTxnResult->fetch_assoc();
        $transactionId = $txnRow['TRANSACTION_ID'];
        
        // Log the deactivation in transaction_log
        $logSql = "INSERT INTO transaction_log (TRANSACTION_ID, OPERATION, MODIFIEDDATE) 
                  VALUES (?, 'DEACTIVATE', CURRENT_DATE)";
        
        $logStmt = $conn->prepare($logSql);
        if (!$logStmt) {
            throw new Exception("Failed to prepare log statement: " . $conn->error);
        }
        
        $logStmt->bind_param("i", $transactionId);
        $logStmt->execute();
        
        error_log("Transaction log created for deactivation");
    } else {
        error_log("No transaction found for this subscription");
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Update member status based on their active subscriptions
    $statusResult = updateMemberStatus($memberId);
    error_log("Member status updated: " . json_encode($statusResult));
    
    // Get member details for response
    $memberSql = "SELECT CONCAT(MEMBER_FNAME, ' ', MEMBER_LNAME) as memberName 
                  FROM member WHERE MEMBER_ID = ?";
    $memberStmt = $conn->prepare($memberSql);
    $memberStmt->bind_param("i", $memberId);
    $memberStmt->execute();
    $memberResult = $memberStmt->get_result();
    $memberRow = $memberResult->fetch_assoc();
    $memberName = $memberRow ? $memberRow['memberName'] : 'Member';
    
    // Get subscription details for response
    $subSql = "SELECT SUB_NAME FROM subscription WHERE SUB_ID = ?";
    $subStmt = $conn->prepare($subSql);
    $subStmt->bind_param("i", $subId);
    $subStmt->execute();
    $subResult = $subStmt->get_result();
    $subRow = $subResult->fetch_assoc();
    $subName = $subRow ? $subRow['SUB_NAME'] : 'Unknown subscription';
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => "Successfully deactivated {$subName} subscription for {$memberName}",
        'memberStatus' => $statusResult['status'],
        'hasActiveSubscriptions' => $statusResult['hasActiveSubscriptions']
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
    }
    
    error_log("Error in deactivation: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Close connection
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
