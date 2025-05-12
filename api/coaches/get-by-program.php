<?php
require_once '../../config/db_connection.php';

header('Content-Type: application/json');

// Get program ID from request
$programId = isset($_GET['program_id']) ? $_GET['program_id'] : null;

if (!$programId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Program ID is required'
    ]);
    exit;
}

try {
    $conn = getConnection();
    
    // Get only coaches assigned to the specified program
    $sql = "SELECT c.COACH_ID, c.COACH_FNAME, c.COACH_LNAME, c.GENDER 
            FROM coach c
            JOIN program_coach pc ON c.COACH_ID = pc.COACH_ID
            WHERE pc.PROGRAM_ID = ? AND c.IS_ACTIVE = 1
            ORDER BY c.COACH_FNAME";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $programId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $coaches = [];
        while ($row = $result->fetch_assoc()) {
            $coaches[] = $row;
        }
        
        echo json_encode([
            'status' => 'success',
            'coaches' => $coaches
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'coaches' => []
        ]);
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching coaches: ' . $e->getMessage()
    ]);
}
?> 