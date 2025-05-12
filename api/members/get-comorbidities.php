<?php
require_once '../../config/db_connection.php';

header('Content-Type: application/json');

// Get member ID from request
$memberId = isset($_GET['member_id']) ? $_GET['member_id'] : null;

if (!$memberId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Member ID is required'
    ]);
    exit;
}

try {
    $conn = getConnection();
    
    // Get comorbidities for the member
    $sql = "SELECT c.COMOR_ID, c.COMOR_NAME 
            FROM comorbidities c
            JOIN member_comorbidities mc ON c.COMOR_ID = mc.COMOR_ID
            WHERE mc.MEMBER_ID = ?
            ORDER BY c.COMOR_NAME";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $comorbidities = [];
        while ($row = $result->fetch_assoc()) {
            $comorbidities[] = $row;
        }
        
        echo json_encode([
            'status' => 'success',
            'comorbidities' => $comorbidities
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'comorbidities' => []
        ]);
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching comorbidities: ' . $e->getMessage()
    ]);
}
?> 