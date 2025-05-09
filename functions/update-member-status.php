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
?> 