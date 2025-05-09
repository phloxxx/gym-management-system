<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'program_coach_functions.php';

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

try {
    switch ($action) {
        case 'get_programs':
            $response = getAllPrograms();
            break;
            
        case 'get_coaches':
            $response = getAllCoaches();
            break;
            
        case 'add_program':
        case 'update_program':
            $response = $action === 'add_program' ? addProgram($_POST) : editProgram($_POST);
            break;
            
        case 'delete_program':
            $response = deleteProgram($_POST['PROGRAM_ID']);
            break;
            
        case 'add_coach':
        case 'update_coach':
            $response = $action === 'add_coach' ? addCoach($_POST) : editCoach($_POST);
            break;
            
        case 'delete_coach':
            $response = deleteCoach($_POST['COACH_ID']);
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
    error_log("Error in program_coach_handler: " . $e->getMessage());
}

echo json_encode($response);
