<?php
// Include database connection
require_once '../config/db_connection.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON data from request body
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Validate the required fields
if (!isset($data['name']) || !isset($data['duration']) || !isset($data['price'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Sanitize inputs
$name = trim($data['name']);
$duration = intval($data['duration']);
$price = floatval($data['price']);

// Validate inputs
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Plan name cannot be empty']);
    exit;
}

if ($duration <= 0) {
    echo json_encode(['success' => false, 'message' => 'Duration must be a positive number']);
    exit;
}

if ($price < 0) {
    echo json_encode(['success' => false, 'message' => 'Price cannot be negative']);
    exit;
}

try {
    // Prepare SQL statement to insert new subscription plan
    $stmt = $conn->prepare("INSERT INTO subscription (SUB_NAME, DURATION, PRICE, IS_ACTIVE) VALUES (?, ?, ?, 1)");
    $stmt->execute([$name, $duration, $price]);
    
    // Get the inserted ID
    $newPlanId = $conn->lastInsertId();
    
    // Return success response with new plan ID
    echo json_encode([
        'success' => true, 
        'message' => 'Subscription plan added successfully',
        'plan' => [
            'id' => $newPlanId,
            'name' => $name,
            'duration' => $duration,
            'price' => $price
        ]
    ]);
} catch (PDOException $e) {
    // Log error
    error_log("Database error: " . $e->getMessage());
    
    // Return error message
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 