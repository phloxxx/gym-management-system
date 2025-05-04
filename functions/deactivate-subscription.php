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
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Update the subscription status in member_subscription table
        $updateSql = "UPDATE member_subscription 
                      SET IS_ACTIVE = 0,
                          MODIFIED_DATE = CURRENT_DATE() 
                      WHERE MEMBER_ID = ? AND SUB_ID = ? AND IS_ACTIVE = 1";
        
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $memberId, $subId);
        
        if ($stmt->execute()) {
            // Check if any rows were affected
            if ($stmt->affected_rows > 0) {
                $success = true;
                
                // Log the deactivation in transaction_log table
                $logSql = "INSERT INTO transaction_log (TRANSACTION_ID, OPERATION, DESCRIPTION, MODIFIEDDATE) 
                           SELECT 
                               t.TRANSACTION_ID, 
                               'DEACTIVATED', 
                               CONCAT('Subscription ID: ', ?, ' deactivated for Member ID: ', ?),
                               CURRENT_DATE()
                           FROM transaction t
                           WHERE t.MEMBER_ID = ? AND t.SUB_ID = ?
                           ORDER BY t.TRANSACTION_ID DESC
                           LIMIT 1";
                           
                $logStmt = $conn->prepare($logSql);
                $logStmt->bind_param("iiii", $subId, $memberId, $memberId, $subId);
                $logStmt->execute();
                
                // Log this action to system logs
                error_log("Subscription deactivated: Member ID $memberId, Subscription ID $subId");
            } else {
                // No subscription found or it was already inactive
                error_log("No active subscription found for Member ID $memberId, Subscription ID $subId");
            }
        } else {
            throw new Exception("Failed to execute database query: " . $stmt->error);
        }
        
        // Commit transaction if everything is successful
        $conn->commit();
        
    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        error_log("Failed to deactivate subscription: " . $e->getMessage());
        throw $e;
    } finally {
        // Close connection
        if ($conn) {
            $conn->close();
        }
    }
    
    return $success;
}
?>