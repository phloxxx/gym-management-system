<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid input data');
    }

    // Validate required fields
    if (!isset($data['MEMBER_ID']) || !isset($data['SUB_ID']) || 
        !isset($data['START_DATE']) || !isset($data['END_DATE']) || 
        !isset($data['PAYMENT_ID']) || !isset($data['USER_ID'])) {
        throw new Exception('Missing required fields');
    }

    $conn = getConnection();
    
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
    
    // Insert new subscription
    $insertQuery = "INSERT INTO member_subscription 
                    (MEMBER_ID, SUB_ID, START_DATE, END_DATE, IS_ACTIVE) 
                    VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param('isss', 
        $data['MEMBER_ID'], 
        $data['SUB_ID'], 
        $data['START_DATE'], 
        $data['END_DATE']
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create new subscription: ' . $stmt->error);
    }
    
    // Get the ID of the newly created subscription
    $newSubId = $conn->insert_id;
    
    // Create transaction record
    $today = date('Y-m-d');
    $insertTransacQuery = "INSERT INTO transaction 
                          (MEMBER_ID, SUB_ID, PAYMENT_ID, USER_ID, TRANSAC_DATE) 
                          VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertTransacQuery);
    $stmt->bind_param('iiiss', 
        $data['MEMBER_ID'], 
        $data['SUB_ID'], 
        $data['PAYMENT_ID'], 
        $data['USER_ID'], 
        $today
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create transaction record: ' . $stmt->error);
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
            'end_date' => $data['END_DATE'],
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