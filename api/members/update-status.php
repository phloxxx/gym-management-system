<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
    // Get member ID from URL parameter
    $memberId = isset($_GET['id']) ? intval($_GET['id']) : null;
    if (!$memberId) {
        throw new Exception('Member ID is required');
    }

    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['status'])) {
        throw new Exception('Invalid input data. Status is required.');
    }

    // Convert status to integer (0 or 1)
    $isActive = $data['status'] ? 1 : 0;
    $forceUpdate = isset($data['force']) && $data['force'] ? true : false;

    $conn = getConnection();
    
    // Begin transaction to ensure data consistency
    $conn->begin_transaction();
    
    // If we're deactivating and not forcing the update, check for active subscriptions
    if ($isActive === 0 && !$forceUpdate) {
        $checkQuery = "SELECT COUNT(*) as active_count 
                      FROM member_subscription 
                      WHERE MEMBER_ID = ? 
                      AND IS_ACTIVE = 1 
                      AND CURRENT_DATE() <= END_DATE";
        
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param('i', $memberId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['active_count'] > 0) {
            // Return a warning that requires explicit confirmation
            echo json_encode([
                'status' => 'warning',
                'message' => 'Member has active subscriptions. Confirmation required to deactivate.',
                'requires_confirmation' => true
            ]);
            exit;
        }
    }
    
    // Update member status
    $updateQuery = "UPDATE member SET IS_ACTIVE = ? WHERE MEMBER_ID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('ii', $isActive, $memberId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update member status: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Member not found or status already set to the requested value');
    }
    
    // If deactivating member, also deactivate all their subscriptions
    if ($isActive === 0) {
        $deactivateSubsQuery = "UPDATE member_subscription SET IS_ACTIVE = 0 WHERE MEMBER_ID = ?";
        $subStmt = $conn->prepare($deactivateSubsQuery);
        $subStmt->bind_param('i', $memberId);
        
        if (!$subStmt->execute()) {
            // Rollback if there's an error
            $conn->rollback();
            throw new Exception('Failed to deactivate member subscriptions: ' . $subStmt->error);
        }
        
        $subsDeactivated = $subStmt->affected_rows;
    } else {
        $subsDeactivated = 0;
    }
    
    // Commit transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Member status updated successfully' . 
                    ($isActive === 0 && $subsDeactivated > 0 ? " ($subsDeactivated subscriptions deactivated)" : ''),
        'is_active' => $isActive,
        'subscriptions_deactivated' => $subsDeactivated
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 