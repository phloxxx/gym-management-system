<?php
require_once dirname(__DIR__) . '/connection/database.php';
require_once __DIR__ . '/db_functions.php';

function getAllPaymentMethods() {
    $conn = null;
    try {
        $conn = getConnection();
        if (!$conn) {
            throw new Exception("Database connection failed");
        }

        error_log("Database connection established successfully");

        $result = $conn->query("CALL sp_get_payment_methods()");
        
        if ($result === false) {
            throw new Exception("Failed to execute stored procedure: " . $conn->error);
        }

        $methods = [];
        while ($row = $result->fetch_assoc()) {
            $methods[] = [
                'PAYMENT_ID' => $row['PAYMENT_ID'],
                'PAY_METHOD' => $row['PAY_METHOD'],
                'IS_ACTIVE' => (bool)$row['IS_ACTIVE']
            ];
        }
        
        error_log("Retrieved " . count($methods) . " payment methods");
        return ['success' => true, 'data' => $methods];

    } catch (Exception $e) {
        error_log("Error in getAllPaymentMethods: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    } finally {
        if ($conn) {
            $conn->close();
            error_log("Database connection closed");
        }
    }
}

function getPaymentMethodById($paymentId) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_get_payment_method_by_id(?)");
        $stmt->bind_param("i", $paymentId);
        
        if (!$stmt->execute()) {
            throw new Exception("Database query failed: " . $conn->error);
        }
        
        $result = $stmt->get_result();
        $method = $result->fetch_assoc();
        
        if (!$method) {
            throw new Exception("Payment method not found");
        }
        
        $method['IS_ACTIVE'] = (bool)$method['IS_ACTIVE'];
        
        return ['success' => true, 'data' => $method];
    } catch (Exception $e) {
        error_log("Error getting payment method: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function createPaymentMethod($payMethod, $isActive) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_create_payment_method(?, ?)");
        $stmt->bind_param("si", $payMethod, $isActive);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create payment method: " . $conn->error);
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $paymentId = $row['payment_id'];
        
        return [
            'success' => true,
            'message' => 'Payment method created successfully',
            'data' => [
                'PAYMENT_ID' => $paymentId,
                'PAY_METHOD' => $payMethod,
                'IS_ACTIVE' => (bool)$isActive
            ]
        ];
    } catch (Exception $e) {
        error_log("Error creating payment method: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function updatePaymentMethod($paymentId, $payMethod, $isActive) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_update_payment_method(?, ?, ?)");
        $stmt->bind_param("isi", $paymentId, $payMethod, $isActive);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update payment method: " . $conn->error);
        }
        
        return [
            'success' => true,
            'message' => 'Payment method updated successfully',
            'data' => [
                'PAYMENT_ID' => $paymentId,
                'PAY_METHOD' => $payMethod,
                'IS_ACTIVE' => (bool)$isActive
            ]
        ];
    } catch (Exception $e) {
        error_log("Error updating payment method: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function deletePaymentMethod($paymentId) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_delete_payment_method(?)");
        $stmt->bind_param("i", $paymentId);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete payment method: " . $conn->error);
        }
        
        return ['success' => true, 'message' => 'Payment method deleted successfully'];
    } catch (Exception $e) {
        error_log("Error deleting payment method: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}