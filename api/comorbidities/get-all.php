<?php
// Include database connection
require_once '../../config/db_connection.php';

// Set headers for JSON response
header('Content-Type: application/json');

try {
    $conn = getConnection();
    
    // Prepare query to get all comorbidities
    $query = "SELECT * FROM comorbidity ORDER BY COMORB_NAME ASC";
    
    // Execute query
    $result = $conn->query($query);
    
    // Check for results
    if ($result) {
        // Fetch all comorbidities
        $comorbidities = $result->fetch_all(MYSQLI_ASSOC);
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'data' => $comorbidities
        ]);
    } else {
        throw new Exception("Failed to fetch comorbidities");
    }
    
    // Close connection
    $conn->close();
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
