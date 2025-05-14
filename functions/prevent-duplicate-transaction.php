<?php
/**
 * Utility functions to prevent duplicate transactions 
 */

/**
 * Checks if a transaction for the given member and subscription already exists for today
 *
 * @param int $memberId The member's ID
 * @param int $subscriptionId The subscription ID
 * @return bool True if a duplicate exists, false otherwise
 */
function isDuplicateTransaction($memberId, $subscriptionId) {
    $conn = getConnection();
    
    try {
        $query = "SELECT TRANSACTION_ID FROM transaction 
                  WHERE MEMBER_ID = ? AND SUB_ID = ? AND DATE(TRANSAC_DATE) = CURRENT_DATE()";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $memberId, $subscriptionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return ($result->num_rows > 0);
    } catch (Exception $e) {
        error_log("Error checking for duplicate transactions: " . $e->getMessage());
        // On error, return false to allow the transaction to proceed
        return false;
    } finally {
        if ($conn) {
            $conn->close();
        }
    }
}

/**
 * Creates a transaction lock for a specific request to prevent duplicates
 * 
 * @param string $requestId Unique identifier for the transaction request
 * @return bool True if lock was created, false if request already processed
 */
function createTransactionLock($requestId) {
    $lockFile = sys_get_temp_dir() . "/transaction_{$requestId}.lock";
    
    // Check if this request ID has already been processed
    if (file_exists($lockFile)) {
        return false; // Request with this ID was already processed
    }
    
    // Create lock file with current timestamp
    file_put_contents($lockFile, time());
    
    // Set expiration (5 minutes)
    touch($lockFile, time() + 300);
    
    return true;
}

/**
 * Updates the member's active status based on their subscription status
 *
 * @param int $memberId The member's ID
 * @return bool True if the member was updated, false otherwise
 */
function updateMemberActivationStatus($memberId) {
    $conn = getConnection();
    
    try {
        // Check if member has any active subscriptions
        $checkQuery = "SELECT COUNT(*) as active_count 
                      FROM member_subscription 
                      WHERE MEMBER_ID = ? 
                      AND IS_ACTIVE = 1 
                      AND CURRENT_DATE() <= END_DATE";
        
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // Set member to active if they have active subscriptions
        $hasActiveSubscriptions = ($row['active_count'] > 0);
        $newStatus = $hasActiveSubscriptions ? 1 : 0;
        
        // Update member status
        $updateQuery = "UPDATE member SET IS_ACTIVE = ? WHERE MEMBER_ID = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ii", $newStatus, $memberId);
        $updateStmt->execute();
        
        return ($updateStmt->affected_rows > 0);
        
    } catch (Exception $e) {
        error_log("Error updating member activation status: " . $e->getMessage());
        return false;
    } finally {
        if ($conn) {
            $conn->close();
        }
    }
}
?>
