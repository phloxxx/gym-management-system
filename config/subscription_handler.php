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
            $stmt = $conn->prepare("INSERT INTO subscription (SUB_NAME, DURATION, PRICE, IS_ACTIVE) VALUES (?, ?, ?, ?)");
            $isActive = isset($_POST['IS_ACTIVE']) ? 1 : 0;
            $stmt->bind_param("ssdi", $_POST['SUB_NAME'], $_POST['DURATION'], $_POST['PRICE'], $isActive);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Subscription added successfully']);
            } else {
                throw new Exception('Failed to add subscription');
            }
            break;

        case 'update':
            $stmt = $conn->prepare("UPDATE subscription SET SUB_NAME=?, DURATION=?, PRICE=?, IS_ACTIVE=? WHERE SUB_ID=?");
            $isActive = isset($_POST['IS_ACTIVE']) ? 1 : 0;
            $stmt->bind_param("ssdii", $_POST['SUB_NAME'], $_POST['DURATION'], $_POST['PRICE'], $isActive, $_POST['SUB_ID']);
            
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
            $result = deleteSubscriptionById($_POST['SUB_ID']);
            echo json_encode($result);
            break;

        case 'get':
            $stmt = $conn->prepare("SELECT * FROM subscription WHERE SUB_ID = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $subscription = $result->fetch_assoc();
            
            echo json_encode(['success' => true, 'subscription' => $subscription]);
            break;

        case 'getAll':
            $result = getAllSubscriptions();
            echo json_encode($result);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
