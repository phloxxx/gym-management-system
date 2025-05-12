<?php
header('Content-Type: application/json');
require_once '../../config/db_functions.php';

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, OPTIONS");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use GET.']);
    exit;
}

// Get payment ID from query string
$paymentId = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;

if (!$paymentId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Payment ID is required.']);
    exit;
}

// Connect to database
try {
    $conn = getConnection();
    
    // Query to get payment method for the provided ID
    $query = "SELECT PAY_METHOD FROM payment WHERE PAYMENT_ID = ? AND IS_ACTIVE = 1";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $paymentId);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Get result
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Return the payment method
        echo json_encode([
            'status' => 'success',
            'payment_method' => $row['PAY_METHOD']
        ]);
    } else {
        // No payment method found with that ID
        echo json_encode([
            'status' => 'error', 
            'message' => 'Payment method not found.'
        ]);
    }
    
    // Close statement
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 