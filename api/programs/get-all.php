<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
    $conn = getConnection();
    
    $query = "SELECT PROGRAM_ID, PROGRAM_NAME FROM program WHERE IS_ACTIVE = 1 ORDER BY PROGRAM_NAME";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $programs = array();
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'programs' => $programs
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
