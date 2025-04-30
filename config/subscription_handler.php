<?php
session_start();
require_once 'db_functions.php';
require_once 'subscription_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $conn = getConnection();
    
    switch ($action) {
        case 'add':
            $stmt = $conn->prepare("CALL sp_AddSubscription(?, ?, ?, ?)");
            $isActive = isset($_POST['IS_ACTIVE']) ? 1 : 0;
            $stmt->bind_param("ssdi", $_POST['SUB_NAME'], $_POST['DURATION'], $_POST['PRICE'], $isActive);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Subscription added successfully']);
            } else {
                throw new Exception('Failed to add subscription');
            }
            break;

        case 'update':
            $stmt = $conn->prepare("CALL sp_UpdateSubscription(?, ?, ?, ?, ?)");
            $isActive = isset($_POST['IS_ACTIVE']) ? 1 : 0;
            $stmt->bind_param("issdi", $_POST['SUB_ID'], $_POST['SUB_NAME'], $_POST['DURATION'], $_POST['PRICE'], $isActive);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Subscription updated successfully']);
            } else {
                throw new Exception('Failed to update subscription');
            }
            break;

        case 'delete':
            if (!isset($_POST['SUB_ID'])) {
                echo json_encode(['success' => false, 'message' => 'Subscription ID is required']);
                exit;
            }
            $stmt = $conn->prepare("CALL sp_DeleteSubscription(?)");
            $stmt->bind_param("i", $_POST['SUB_ID']);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Subscription deleted successfully']);
            } else {
                throw new Exception('Failed to delete subscription');
            }
            break;

        case 'get':
            $stmt = $conn->prepare("CALL sp_GetSubscriptionById(?)");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $subscription = $result->fetch_assoc();
            
            echo json_encode(['success' => true, 'subscription' => $subscription]);
            break;

        case 'getAll':
            $stmt = $conn->prepare("CALL sp_GetAllSubscriptions()");
            $stmt->execute();
            $result = $stmt->get_result();
            $subscriptions = $result->fetch_all(MYSQLI_ASSOC);
            
            echo json_encode(['success' => true, 'subscriptions' => $subscriptions]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
