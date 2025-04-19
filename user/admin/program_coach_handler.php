<?php
header('Content-Type: application/json');
require_once '../../config/program_coach_functions.php';

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'get_programs':
        $response = getAllPrograms();
        break;
        
    case 'get_coaches':
        $response = getAllCoaches();
        break;
        
    case 'add_program':
        $response = addProgram($_POST);
        break;
        
    case 'edit_program':
        $response = editProgram($_POST);
        break;
        
    case 'delete_program':
        $response = deleteProgram($_POST['PROGRAM_ID']);
        break;
        
    case 'add_coach':
        $response = addCoach($_POST);
        break;
        
    case 'edit_coach':
        $response = editCoach($_POST);
        break;
        
    case 'delete_coach':
        $response = deleteCoach($_POST['COACH_ID']);
        break;
}

echo json_encode($response);
