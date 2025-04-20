<?php
header('Content-Type: application/json');
require_once 'comorbidity_functions.php';

// Handle CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Session validation (if needed)
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            echo json_encode(getAllComorbidities());
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['action'])) {
                throw new Exception('Action not specified');
            }
            
            switch ($data['action']) {
                case 'add':
                    if (!isset($data['name']) || empty(trim($data['name']))) {
                        throw new Exception('Comorbidity name is required');
                    }
                    echo json_encode(addComorbidity(
                        trim($data['name']), 
                        isset($data['isActive']) ? (int)$data['isActive'] : 1
                    ));
                    break;
                    
                case 'update':
                    if (!isset($data['id'], $data['name']) || empty(trim($data['name']))) {
                        throw new Exception('Comorbidity ID and name are required');
                    }
                    echo json_encode(updateComorbidity(
                        $data['id'],
                        trim($data['name']),
                        isset($data['isActive']) ? (int)$data['isActive'] : 1
                    ));
                    break;

                case 'delete':
                    if (!isset($data['id'])) {
                        throw new Exception('Comorbidity ID is required for deletion');
                    }
                    echo json_encode(deleteComorbidity($data['id']));
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    error_log("Comorbidity API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
