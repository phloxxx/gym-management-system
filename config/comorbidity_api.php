<?php
header('Content-Type: application/json');
require_once 'comorbidity_functions.php';
require_once 'db_functions.php';

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
    $conn = getConnection();

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $result = getAllComorbidities();
            if (!$result['success']) {
                throw new Exception($result['message']);
            }
            echo json_encode($result);
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
                    
                    // Add explicit isActive conversion to integer
                    $isActive = isset($data['isActive']) ? ($data['isActive'] ? 1 : 0) : 1;
                    
                    echo json_encode(updateComorbidity(
                        $data['id'],
                        trim($data['name']),
                        $isActive
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
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to load comorbidities: ' . $e->getMessage()
    ]);
} catch (mysqli_sql_exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
