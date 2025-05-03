<?php
// Include CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

// Include the deactivate-subscription function
require_once '../../functions/deactivate-subscription.php';

// Get JSON data from request body
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Validate required fields
if (!isset($data['member_id']) || !isset($data['sub_id']) || !isset($data['action'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit();
}

// Extract data
$memberId = (int)$data['member_id'];
$subId = (int)$data['sub_id'];
$action = $data['action'];

// Validate member_id and sub_id
if ($memberId <= 0 || $subId <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid member or subscription ID']);
    exit();
}

// Process based on action
if ($action === 'deactivate') {
    try {
        // Call deactivateSubscription function
        $success = deactivateSubscription($memberId, $subId);
        
        if ($success) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Subscription deactivated successfully']);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['status' => 'error', 'message' => 'Subscription not found or already inactive']);
        }
    } catch (Exception $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Unsupported action']);
}
?>
