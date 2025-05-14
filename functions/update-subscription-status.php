<?php
require_once '../config/db_connection.php';

/**
 * Updates subscription statuses in the database to ensure consistency
 * - Makes expired subscriptions inactive
 * - Returns counts of updated records
 */
function updateSubscriptionStatuses() {
    $conn = getConnection();
    $updateCount = 0;
    
    try {
        // Start transaction for consistency
        $conn->begin_transaction();
        
        // Update all expired subscriptions (end date in the past) to inactive
        $updateSql = "UPDATE member_subscription 
                      SET IS_ACTIVE = 0 
                      WHERE END_DATE < CURRENT_DATE() 
                      AND IS_ACTIVE = 1";
        
        $stmt = $conn->prepare($updateSql);
        $stmt->execute();
        
        // Get count of affected rows
        $updateCount = $stmt->affected_rows;
        
        // Check if transaction_log table has a DESCRIPTION column
        $checkTableQuery = "SHOW COLUMNS FROM transaction_log LIKE 'DESCRIPTION'";
        $checkResult = $conn->query($checkTableQuery);
        $hasDescriptionColumn = ($checkResult && $checkResult->num_rows > 0);
        
        // Log the updates in transaction_log
        if ($updateCount > 0) {
            // Get IDs of affected subscriptions
            if ($hasDescriptionColumn) {
                $logSql = "INSERT INTO transaction_log 
                           (TRANSACTION_ID, OPERATION, DESCRIPTION, MODIFIEDDATE)
                           SELECT t.TRANSACTION_ID, 'AUTO-DEACTIVATED', 
                                  CONCAT('Automatically deactivated expired subscription for Member ID: ', t.MEMBER_ID), 
                                  CURRENT_DATE()
                           FROM transaction t
                           JOIN member_subscription ms ON t.MEMBER_ID = ms.MEMBER_ID AND t.SUB_ID = ms.SUB_ID
                           WHERE ms.END_DATE < CURRENT_DATE() 
                           AND ms.IS_ACTIVE = 0
                           ORDER BY t.TRANSACTION_ID DESC";
            } else {
                $logSql = "INSERT INTO transaction_log 
                           (TRANSACTION_ID, OPERATION, MODIFIEDDATE)
                           SELECT t.TRANSACTION_ID, 'AUTO-DEACTIVATED', CURRENT_DATE()
                           FROM transaction t
                           JOIN member_subscription ms ON t.MEMBER_ID = ms.MEMBER_ID AND t.SUB_ID = ms.SUB_ID
                           WHERE ms.END_DATE < CURRENT_DATE() 
                           AND ms.IS_ACTIVE = 0
                           ORDER BY t.TRANSACTION_ID DESC";
            }
            
            $logStmt = $conn->prepare($logSql);
            $logStmt->execute();
        }
        
        // Commit the transaction
        $conn->commit();
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        throw new Exception("Error updating subscription statuses: " . $e->getMessage());
    } finally {
        if ($conn) {
            $conn->close();
        }
    }
    
    return $updateCount;
}

// Process the request
header('Content-Type: application/json');

try {
    $updatedCount = updateSubscriptionStatuses();
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully updated subscription statuses.",
        'updated_count' => $updatedCount
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>