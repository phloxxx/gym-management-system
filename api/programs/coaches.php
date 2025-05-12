<?php
header('Content-Type: application/json');

// Include function to get coaches by program
require_once '../../config/program_coach_functions.php';

// Check if program_id parameter is provided
if (!isset($_GET['program_id']) || empty($_GET['program_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Program ID is required'
    ]);
    exit;
}

$program_id = $_GET['program_id'];

// Get coaches by program ID using the dedicated function
$result = getCoachesByProgram($program_id);

if ($result['success']) {
    echo json_encode([
        'status' => 'success',
        'coaches' => $result['coaches']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching coaches: ' . $result['message']
    ]);
}
?>
