<?php
require_once '../connection/database.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['program_id'])) {
    try {
        $program_id = $_POST['program_id'];
        
        $sql = "SELECT COACH_ID, CONCAT(COACH_FNAME, ' ', COACH_LNAME) AS COACH_NAME 
                FROM coaches 
                WHERE PROGRAM_ID = :program_id 
                AND IS_ACTIVE = 1";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':program_id', $program_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $coaches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'coaches' => $coaches
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}
