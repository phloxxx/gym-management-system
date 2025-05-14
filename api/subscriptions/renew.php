<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid input data');
    }

    // Validate required fields (remove END_DATE from required fields)
    if (!isset($data['MEMBER_ID']) || !isset($data['SUB_ID']) || 
        !isset($data['START_DATE']) || !isset($data['PAYMENT_ID']) || !isset($data['USER_ID'])) {
        throw new Exception('Missing required fields');
    }

    $conn = getConnection();
    
    // Get subscription duration to calculate end date
    $getDurationQuery = "SELECT DURATION FROM subscription WHERE SUB_ID = ?";
    $stmt = $conn->prepare($getDurationQuery);
    $stmt->bind_param('i', $data['SUB_ID']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Invalid subscription ID');
    }
    
    $subscriptionData = $result->fetch_assoc();
    $duration = $subscriptionData['DURATION']; // Duration in days
    
    // Calculate the end date based on start date and subscription duration
    $startDateTime = new DateTime($data['START_DATE']);
    $endDateTime = clone $startDateTime;
    $endDateTime->add(new DateInterval("P{$duration}D"));
    $calculatedEndDate = $endDateTime->format('Y-m-d');
      // Begin transaction to ensure data consistency
    $conn->begin_transaction();
    
    // First, deactivate any active subscriptions for this member
    $deactivateQuery = "UPDATE member_subscription SET IS_ACTIVE = 0 
                        WHERE MEMBER_ID = ? AND IS_ACTIVE = 1";
    $stmt = $conn->prepare($deactivateQuery);
    $stmt->bind_param('i', $data['MEMBER_ID']);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to deactivate existing subscriptions: ' . $stmt->error);
    }
    
    // Check if a record with the same member_id, sub_id, start_date, and end_date already exists
    $checkExistingQuery = "SELECT * FROM member_subscription 
                          WHERE MEMBER_ID = ? AND SUB_ID = ? AND START_DATE = ? AND END_DATE = ?";
    $checkStmt = $conn->prepare($checkExistingQuery);
    $checkStmt->bind_param('isss', 
        $data['MEMBER_ID'], 
        $data['SUB_ID'], 
        $data['START_DATE'], 
        $calculatedEndDate
    );
    $checkStmt->execute();
    $existingResult = $checkStmt->get_result();
    
    if ($existingResult->num_rows > 0) {
        // Update the existing record to be active
        $updateQuery = "UPDATE member_subscription SET IS_ACTIVE = 1 
                       WHERE MEMBER_ID = ? AND SUB_ID = ? AND START_DATE = ? AND END_DATE = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('isss', 
            $data['MEMBER_ID'], 
            $data['SUB_ID'], 
            $data['START_DATE'], 
            $calculatedEndDate
        );
        
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update existing subscription: ' . $updateStmt->error);
        }
    } else {
        // No existing record, insert a new one
        $insertQuery = "INSERT INTO member_subscription 
                      (MEMBER_ID, SUB_ID, START_DATE, END_DATE, IS_ACTIVE) 
                      VALUES (?, ?, ?, ?, 1)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param('isss', 
            $data['MEMBER_ID'], 
            $data['SUB_ID'], 
            $data['START_DATE'], 
            $calculatedEndDate  // Use calculated end date instead of user input
        );
        
        if (!$insertStmt->execute()) {
            throw new Exception('Failed to create new subscription: ' . $insertStmt->error);
        }
    }
      // Check if a transaction for this member and subscription was already created today
    $checkTransactionQuery = "SELECT TRANSACTION_ID FROM transaction 
                             WHERE MEMBER_ID = ? AND SUB_ID = ? AND DATE(TRANSAC_DATE) = CURRENT_DATE()";
    $checkTransacStmt = $conn->prepare($checkTransactionQuery);
    $checkTransacStmt->bind_param('ii', 
        $data['MEMBER_ID'], 
        $data['SUB_ID']
    );
    $checkTransacStmt->execute();
    $existingTransacResult = $checkTransacStmt->get_result();
    
    if ($existingTransacResult->num_rows === 0) {
        // No transaction exists for today, create a new one
        $today = date('Y-m-d');
        $insertTransacQuery = "INSERT INTO transaction 
                              (MEMBER_ID, SUB_ID, PAYMENT_ID, USER_ID, TRANSAC_DATE) 
                              VALUES (?, ?, ?, ?, ?)";
        $insertTransacStmt = $conn->prepare($insertTransacQuery);
        $insertTransacStmt->bind_param('iiiss', 
            $data['MEMBER_ID'], 
            $data['SUB_ID'], 
            $data['PAYMENT_ID'], 
            $data['USER_ID'], 
            $today
        );
        
        if (!$insertTransacStmt->execute()) {
            throw new Exception('Failed to create transaction record: ' . $insertTransacStmt->error);
        }
    } else {
        // Transaction already exists for today
        error_log("Found existing transaction for today for member ID: " . $data['MEMBER_ID'] . " and subscription ID: " . $data['SUB_ID']);
    }

    // Get subscription details to include in response
    $subQuery = "SELECT s.SUB_NAME, s.DURATION, s.PRICE 
                FROM subscription s 
                WHERE s.SUB_ID = ?";
    $stmt = $conn->prepare($subQuery);
    $stmt->bind_param('i', $data['SUB_ID']);
    $stmt->execute();
    $subResult = $stmt->get_result();
    
    $subscriptionDetails = null;
    if ($subResult->num_rows > 0) {
        $subscriptionDetails = $subResult->fetch_assoc();
    }
    
    // Get payment method details
    $payQuery = "SELECT p.PAY_METHOD 
                FROM payment p 
                WHERE p.PAYMENT_ID = ?";
    $stmt = $conn->prepare($payQuery);
    $stmt->bind_param('i', $data['PAYMENT_ID']);
    $stmt->execute();
    $payResult = $stmt->get_result();
    
    $paymentDetails = null;
    if ($payResult->num_rows > 0) {
        $paymentDetails = $payResult->fetch_assoc();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response with subscription details
    echo json_encode([
        'status' => 'success',
        'message' => 'Subscription renewed successfully',
        'subscription' => [
            'member_id' => $data['MEMBER_ID'],
            'subscription_id' => $data['SUB_ID'],
            'subscription_name' => $subscriptionDetails ? $subscriptionDetails['SUB_NAME'] : null,
            'duration' => $subscriptionDetails ? $subscriptionDetails['DURATION'] : null,
            'price' => $subscriptionDetails ? $subscriptionDetails['PRICE'] : null,
            'start_date' => $data['START_DATE'],
            'end_date' => $calculatedEndDate,  // Use calculated end date in response
            'payment_method' => $paymentDetails ? $paymentDetails['PAY_METHOD'] : null
        ]
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