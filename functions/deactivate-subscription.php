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
                      SET IS_ACTIVE = 0 
                      WHERE MEMBER_ID = ? AND SUB_ID = ?";
        
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $memberId, $subId);
        
        if ($stmt->execute()) {
            // Check if any rows were affected
            if ($stmt->affected_rows > 0) {
                $success = true;
                
                // Log the deactivation in transaction_log table
                $logSql = "INSERT INTO transaction_log (TRANSACTION_ID, OPERATION, MODIFIEDDATE) 
                           SELECT t.TRANSACTION_ID, 'DEACTIVATED', CURRENT_DATE()
                           FROM transaction t
                           WHERE t.MEMBER_ID = ? AND t.SUB_ID = ?
                           ORDER BY t.TRANSACTION_ID DESC
                           LIMIT 1";
                           
                $logStmt = $conn->prepare($logSql);
                $logStmt->bind_param("ii", $memberId, $subId);
                $logStmt->execute();
            } else {
                // No subscription found or it was already inactive
                throw new Exception("No active subscription found for this member");
            }
        } else {
            throw new Exception("Failed to execute database query");
        }
        
        // Commit transaction if everything is successful
        $conn->commit();
        
    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        throw new Exception("Failed to deactivate subscription: " . $e->getMessage());
    } finally {
        // Close connection
        if ($conn) {
            $conn->close();
        }
    }
    
    return $success;
}
?>
