<?php
require_once __DIR__ . '/../config/db_connection.php';

/**
 * Deactivates a subscription for a specific member
 * 
 * @param int $memberId The ID of the member
 * @param int $subId The ID of the subscription
 * @return bool True if successful, false otherwise
 */
function deactivateSubscription($memberId, $subId) {
    $conn = getConnection();
    $success = false;
    
    error_log("deactivateSubscription() called with memberId: $memberId, subId: $subId");
    
    try {
        // Start transaction
        $conn->begin_transaction();
        error_log("Transaction started");
        
        // Check if the subscription exists and is active
        $checkSql = "SELECT * FROM member_subscription WHERE MEMBER_ID = ? AND SUB_ID = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $memberId, $subId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            error_log("No subscription found for memberId: $memberId, subId: $subId");
            throw new Exception("No subscription found for this member");
        }
        
        $subscription = $result->fetch_assoc();
        error_log("Found subscription: " . json_encode($subscription));
        
        if ($subscription['IS_ACTIVE'] == 0) {
            error_log("Subscription is already inactive");
            throw new Exception("Subscription is already inactive");
        }
        
        // Update the subscription status in member_subscription table
        $updateSql = "UPDATE member_subscription 
                      SET IS_ACTIVE = 0 
                      WHERE MEMBER_ID = ? AND SUB_ID = ?";
        
        error_log("Preparing update query: $updateSql with memberId: $memberId, subId: $subId");
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $memberId, $subId);
        
        if ($stmt->execute()) {
            error_log("Update query executed. Affected rows: " . $stmt->affected_rows);
            
            // Check if any rows were affected
            if ($stmt->affected_rows > 0) {
                $success = true;
                error_log("Update successful, subscription deactivated");
                
                // Log the deactivation in transaction_log table
                $logSql = "INSERT INTO transaction_log (TRANSACTION_ID, OPERATION, MODIFIEDDATE) 
                           SELECT t.TRANSACTION_ID, 'DEACTIVATED', CURRENT_DATE()
                           FROM transaction t
                           WHERE t.MEMBER_ID = ? AND t.SUB_ID = ?
                           ORDER BY t.TRANSACTION_ID DESC
                           LIMIT 1";
                
                error_log("Preparing log query: $logSql");          
                $logStmt = $conn->prepare($logSql);
                $logStmt->bind_param("ii", $memberId, $subId);
                $logResult = $logStmt->execute();
                
                if ($logResult) {
                    error_log("Log entry created successfully. Affected rows: " . $logStmt->affected_rows);
                } else {
                    error_log("Failed to create log entry: " . $logStmt->error);
                }
            } else {
                // No subscription found or it was already inactive
                error_log("No rows affected by update - subscription may already be inactive");
                throw new Exception("No active subscription found for this member");
            }
        } else {
            error_log("Failed to execute update query: " . $stmt->error);
            throw new Exception("Failed to execute database query: " . $stmt->error);
        }
        
        // Commit transaction if everything is successful
        $conn->commit();
        error_log("Transaction committed");
        
    } catch (Exception $e) {
        // Roll back transaction on error
        if ($conn) {
            $conn->rollback();
            error_log("Transaction rolled back due to error: " . $e->getMessage());
        }
        throw new Exception("Failed to deactivate subscription: " . $e->getMessage());
    } finally {
        // Close connection
        if ($conn) {
            $conn->close();
            error_log("Database connection closed");
        }
    }
    
    return $success;
}

// Handle POST requests directly
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Get JSON data from request body
        $rawInput = file_get_contents('php://input');
        error_log("Raw POST input: " . $rawInput);
        
        $requestData = json_decode($rawInput, true);
        
        // Log the received data for debugging
        error_log('Deactivation request data: ' . json_encode($requestData));
        
        // Validate required fields
        if (!isset($requestData['memberId']) || !isset($requestData['subId'])) {
            error_log("Missing required fields: memberId and/or subId");
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields: memberId and/or subId'
            ]);
            exit;
        }
        
        $memberId = intval($requestData['memberId']);
        $subId = intval($requestData['subId']);
        
        error_log("Processed memberId: $memberId, subId: $subId");
        
        // Validate IDs
        if ($memberId <= 0 || $subId <= 0) {
            error_log("Invalid member ID or subscription ID: memberId=$memberId, subId=$subId");
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid member ID or subscription ID'
            ]);
            exit;
        }
        
        // Call deactivation function
        $success = deactivateSubscription($memberId, $subId);
        
        if ($success) {
            error_log("Deactivation successful");
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Subscription deactivated successfully'
            ]);
        } else {
            error_log("Deactivation failed - subscription not found or already inactive");
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Subscription not found or already inactive'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Exception caught in deactivation endpoint: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>
