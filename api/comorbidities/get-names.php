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

// Get member ID from query string
$memberId = isset($_GET['member_id']) ? intval($_GET['member_id']) : 0;

if (!$memberId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Member ID is required.']);
    exit;
}

// Connect to database
try {
    $conn = getConnection();
    
    // Query to get comorbidities for a specific member
    $query = "SELECT c.COMOR_ID, c.COMOR_NAME 
              FROM comorbidities c
              JOIN member_comorbidities mc ON c.COMOR_ID = mc.COMOR_ID
              WHERE mc.MEMBER_ID = ? AND c.IS_ACTIVE = 1";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $memberId);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Get result
    $result = $stmt->get_result();
    $comorbidities = [];
    
    while ($row = $result->fetch_assoc()) {
        $comorbidities[] = $row;
    }
    
    // Close statement
    $stmt->close();
    
    // Return the comorbidities data
    echo json_encode([
        'status' => 'success',
        'comorbidities' => $comorbidities
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 