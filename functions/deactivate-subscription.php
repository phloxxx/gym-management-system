<?php
require_once '../config/db_connection.php';

// Set header to return JSON
header('Content-Type: application/json');

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

// Enhanced logging for debugging
error_log('Subscription status change data received: ' . json_encode($data));

// Validate required parameters
if (!isset($data['memberId']) || !isset($data['subId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters: memberId and/or subId'
    ]);
    exit;
}

$memberId = intval($data['memberId']);
$subId = intval($data['subId']);
$activate = isset($data['activate']) && $data['activate'] === true;

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
    error_log("Database connection established");
    
    // Begin transaction
    $conn->begin_transaction();
    
    // First check if the record exists
    $checkSql = "SELECT * FROM member_subscription 
                WHERE MEMBER_ID = ? AND SUB_ID = ?";
    $checkStmt = $conn->prepare($checkSql);
    
    if (!$checkStmt) {
        throw new Exception("Failed to prepare check statement: " . $conn->error);
    }
    
    $checkStmt->bind_param("ii", $memberId, $subId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("No subscription found for member $memberId and subscription $subId");
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'No subscription found with the provided IDs'
        ]);
        exit;
    }
    
    // Determine target status - 1 for activate, 0 for deactivate
    $targetStatus = $activate ? 1 : 0;
    
    // Update the IS_ACTIVE status to the target value
    $updateSql = "UPDATE member_subscription 
                 SET IS_ACTIVE = ? 
                 WHERE MEMBER_ID = ? AND SUB_ID = ?";
    
    $stmt = $conn->prepare($updateSql);
    if (!$stmt) {
        throw new Exception("Failed to prepare update statement: " . $conn->error);
    }
    
    $stmt->bind_param("iii", $targetStatus, $memberId, $subId);
    $success = $stmt->execute();
    
    if (!$success) {
        throw new Exception("Failed to execute update: " . $stmt->error);
    }
    
    // Add an extra record to transaction_log for better tracking
    $operation = $activate ? "ACTIVATE" : "DEACTIVATE";
    $logSql = "INSERT INTO transaction_log (OPERATION, DESCRIPTION, MODIFIEDDATE) 
               VALUES (?, ?, CURRENT_DATE)";
    
    $description = "$operation subscription $subId for Member $memberId";
    $logStmt = $conn->prepare($logSql);
    
    if ($logStmt) {
        $logStmt->bind_param("ss", $operation, $description);
        $logStmt->execute();
    }
    
    // Final verification that the update worked
    $verifySql = "SELECT IS_ACTIVE FROM member_subscription 
                 WHERE MEMBER_ID = ? AND SUB_ID = ?";
    $verifyStmt = $conn->prepare($verifySql);
    $verifyStmt->bind_param("ii", $memberId, $subId);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows > 0) {
        $row = $verifyResult->fetch_assoc();
        if ((int)$row['IS_ACTIVE'] !== $targetStatus) {
            error_log("WARNING: Status change did not succeed! IS_ACTIVE value: " . $row['IS_ACTIVE']);
            throw new Exception("Status change failed to update the database record properly.");
        } else {
            error_log("Verification successful: IS_ACTIVE is now " . $targetStatus);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    $actionWord = $activate ? "activated" : "deactivated";
    echo json_encode([
        'success' => true,
        'message' => "Subscription $actionWord successfully"
    ]);

} catch (Exception $e) {
    // Rollback on error
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
    }
    
    error_log('Status change error: ' . $e->getMessage());
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
