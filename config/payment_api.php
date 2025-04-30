<?php
require_once 'payment_methods.php';
header('Content-Type: application/json');

// Enable CORS and handle preflight requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['id'])) {
                $response = getPaymentMethodById($_GET['id']);
            } else {
                $response = getAllPaymentMethods();
            }
            if (!$response['success']) {
                http_response_code(400);
            }
            echo json_encode($response);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['pay_method'])) {
                throw new Exception('Payment method name is required');
            }
            $response = createPaymentMethod(
                $data['pay_method'],
                $data['is_active'] ?? 1
            );
            echo json_encode($response);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['payment_id'], $data['pay_method'])) {
                throw new Exception('Payment ID and method name are required');
            }
            $response = updatePaymentMethod(
                $data['payment_id'],
                $data['pay_method'],
                $data['is_active'] ?? 1
            );
            echo json_encode($response);
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['payment_id'])) {
                throw new Exception('Payment ID is required');
            }
            $response = deletePaymentMethod($data['payment_id']);
            echo json_encode($response);
            break;

        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}