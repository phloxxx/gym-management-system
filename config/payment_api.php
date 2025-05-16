<?php
require_once 'payment_methods.php';
header('Content-Type: application/json');

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $response = ['success' => false, 'message' => 'Invalid request'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $response = getPaymentMethodById($_GET['id']);
            } else {
                $response = getAllPaymentMethods();
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['pay_method']) || empty(trim($data['pay_method']))) {
                throw new Exception('Payment method name is required');
            }
            
            $response = createPaymentMethod(
                trim($data['pay_method']),
                isset($data['is_active']) ? (int)$data['is_active'] : 1
            );
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['payment_id']) || !isset($data['pay_method']) || empty(trim($data['pay_method']))) {
                throw new Exception('Payment ID and method name are required');
            }
            
            $response = updatePaymentMethod(
                (int)$data['payment_id'],
                trim($data['pay_method']),
                isset($data['is_active']) ? (int)$data['is_active'] : 1
            );
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['payment_id'])) {
                throw new Exception('Payment ID is required');
            }
            $response = deletePaymentMethod((int)$data['payment_id']);
            break;

        default:
            throw new Exception('Method not allowed');
    }

    if (!$response['success']) {
        http_response_code(400);
    }
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}