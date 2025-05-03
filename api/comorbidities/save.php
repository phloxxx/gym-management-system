<?php
// Include database connection
require_once '../../config/db_connection.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['name']) || empty($data['name'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Name is required'
    ]);
    exit;
}

try {
    $conn = getConnection();
    
    // Check if we're updating or adding
    if (isset($data['id']) && !empty($data['id'])) {
        // Updating existing comorbidity
        $query = "UPDATE comorbidity SET 
                    COMORB_NAME = ?,
                    IS_ACTIVE = ?
                  WHERE COMORB_ID = ?";
        
        $stmt = $conn->prepare($query);
        
        // Convert status to int
        $isActive = isset($data['status']) && $data['status'] ? 1 : 0;
        
        $stmt->bind_param("sii", $data['name'], $isActive, $data['id']);
        
        // Execute query
        $stmt->execute();
        
        // Check if anything was updated
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Comorbidity updated successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No changes made or comorbidity not found'
            ]);
        }
    } else {
        // Adding new comorbidity
        $query = "INSERT INTO comorbidity (COMORB_NAME, IS_ACTIVE) VALUES (?, ?)";
        
        $stmt = $conn->prepare($query);
        
        // Convert status to int (default to active)
        $isActive = isset($data['status']) ? ($data['status'] ? 1 : 0) : 1;
        
        $stmt->bind_param("si", $data['name'], $isActive);
        
        // Execute query
        $stmt->execute();
        
        // Check if insertion was successful
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Comorbidity added successfully',
                'id' => $conn->insert_id
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to add comorbidity'
            ]);
        }
    }
    
    // Close connection
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
