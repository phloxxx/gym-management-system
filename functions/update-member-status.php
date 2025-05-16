<?php
// This file updates a member's active status based on their subscription status
require_once __DIR__ . '/../config/db_connection.php';

/**
 * Updates a member's IS_ACTIVE status based on whether they have any active subscriptions
 * @param int $memberId The ID of the member to update
 * @return array Result indicating success or failure
 */
function updateMemberStatus($memberId) {
    try {
        // Validate member ID
        $memberId = intval($memberId);
        if ($memberId <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid member ID'
            ];
        }

        $conn = getConnection();
        
        // Check if the member has any active subscriptions
        $query = "SELECT COUNT(*) as active_count 
                  FROM member_subscription 
                  WHERE MEMBER_ID = ? 
                  AND IS_ACTIVE = 1 
                  AND CURRENT_DATE() <= END_DATE";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // Determine if member should be active based on subscription count
        $hasActiveSubscriptions = ($row['active_count'] > 0);
        $newStatus = $hasActiveSubscriptions ? 1 : 0;
        
        // Update the member's IS_ACTIVE status
        $updateQuery = "UPDATE member SET IS_ACTIVE = ? WHERE MEMBER_ID = ?";
        $updateStmt = $conn->prepare($updateQuery);
        
        if (!$updateStmt) {
            throw new Exception("Failed to prepare update statement: " . $conn->error);
        }
        
        $updateStmt->bind_param("ii", $newStatus, $memberId);
        $updateStmt->execute();
        
        // Success message
        return [
            'success' => true,
            'status' => $newStatus, 
            'message' => 'Member status updated successfully',
            'hasActiveSubscriptions' => $hasActiveSubscriptions
        ];
        
    } catch (Exception $e) {
        error_log("Error updating member status: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

/**
 * Updates all members' active status based on whether they have active subscriptions
 * This can be run as a scheduled task or manually
 *
 * @return array Result with count of updated members
 */
function updateAllMembersStatus() {
    $conn = getConnection();
    $updatedCount = 0;
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // First, get all members with active subscriptions
        $activeMembersSql = "SELECT DISTINCT ms.MEMBER_ID 
                            FROM member_subscription ms 
                            WHERE ms.IS_ACTIVE = 1 
                            AND CURRENT_DATE() <= ms.END_DATE";
        
        $activeMembersResult = $conn->query($activeMembersSql);
        $activeMembers = [];
        
        while ($row = $activeMembersResult->fetch_assoc()) {
            $activeMembers[] = $row['MEMBER_ID'];
        }
        
        // Update all members to inactive first
        $inactiveUpdateSql = "UPDATE member SET IS_ACTIVE = 0";
        $conn->query($inactiveUpdateSql);
        
        // Then set members with active subscriptions to active
        if (!empty($activeMembers)) {
            $placeholders = implode(',', array_fill(0, count($activeMembers), '?'));
            $activeUpdateSql = "UPDATE member SET IS_ACTIVE = 1 WHERE MEMBER_ID IN ($placeholders)";
            
            $stmt = $conn->prepare($activeUpdateSql);
            
            // Create parameter binding
            $types = str_repeat('i', count($activeMembers));
            $stmt->bind_param($types, ...$activeMembers);
            
            $stmt->execute();
            $updatedCount = $stmt->affected_rows;
        }
        
        // Commit changes
        $conn->commit();
        
        return [
            'success' => true,
            'updated_count' => $updatedCount,
            'total_active' => count($activeMembers)
        ];
        
    } catch (Exception $e) {
        if ($conn) {
            $conn->rollback();
        }
        error_log("Error updating members status: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    } finally {
        if ($conn) {
            $conn->close();
        }
    }
}

// Handle direct API calls to this endpoint
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header('Content-Type: application/json');
    
    // Accept both GET and POST requests
    $memberId = isset($_GET['memberId']) ? $_GET['memberId'] : null;
    
    if (!$memberId && isset($_POST['memberId'])) {
        $memberId = $_POST['memberId'];
    }
    
    // Also check JSON data
    if (!$memberId) {
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['memberId'])) {
            $memberId = $data['memberId'];
        }
    }
    
    if (!$memberId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameter: memberId'
        ]);
        exit;
    }
    
    // Call the function and output the result
    $result = updateMemberStatus($memberId);
    
    if (!$result['success']) {
        http_response_code(500);
    }
    
    echo json_encode($result);
}

// If this file is executed directly, run the update
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header('Content-Type: application/json');
    echo json_encode(updateAllMembersStatus());
}
?>