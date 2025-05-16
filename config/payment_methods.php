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

        $sql = "SELECT PAYMENT_ID, PAY_METHOD, IS_ACTIVE FROM payment ORDER BY PAYMENT_ID";
        $result = $conn->query($sql);
        
        if ($result === false) {
            throw new Exception("Failed to fetch payment methods: " . $conn->error);
        }

        $methods = [];
        while ($row = $result->fetch_assoc()) {
            $methods[] = [
                'PAYMENT_ID' => $row['PAYMENT_ID'],
                'PAY_METHOD' => $row['PAY_METHOD'],
                'IS_ACTIVE' => (bool)$row['IS_ACTIVE']
            ];
        }
        
        return ['success' => true, 'data' => $methods];

    } catch (Exception $e) {
        error_log("Error in getAllPaymentMethods: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    } finally {
        if ($conn) {
            $conn->close();
        }
    }
}

function getPaymentMethodById($paymentId) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT PAYMENT_ID, PAY_METHOD, IS_ACTIVE FROM payment WHERE PAYMENT_ID = ?");
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
    $conn = null;
    try {
        $conn = getConnection();
        $conn->begin_transaction();

        // Check for duplicate payment method
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM payment WHERE PAY_METHOD = ?");
        if (!$checkStmt) {
            throw new Exception("Failed to prepare duplicate check statement");
        }
        
        $checkStmt->bind_param("s", $payMethod);
        if (!$checkStmt->execute()) {
            throw new Exception("Failed to check for duplicate payment method");
        }
        
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            throw new Exception("Payment method already exists");
        }

        // Create new payment method
        $stmt = $conn->prepare("INSERT INTO payment (PAY_METHOD, IS_ACTIVE) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception("Failed to prepare insert statement");
        }

        $stmt->bind_param("si", $payMethod, $isActive);
        if (!$stmt->execute()) {
            throw new Exception("Failed to create payment method");
        }

        $paymentId = $stmt->insert_id;
        $conn->commit();

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
        if ($conn) {
            $conn->rollback();
        }
        error_log("Error creating payment method: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    } finally {
        if ($conn) {
            $conn->close();
        }
    }
}

function updatePaymentMethod($paymentId, $payMethod, $isActive) {
    $conn = null;
    try {
        $conn = getConnection();
        $conn->begin_transaction();

        // Check for duplicate payment method
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM payment WHERE PAY_METHOD = ? AND PAYMENT_ID != ?");
        if (!$checkStmt) {
            throw new Exception("Failed to prepare duplicate check statement");
        }

        $checkStmt->bind_param("si", $payMethod, $paymentId);
        if (!$checkStmt->execute()) {
            throw new Exception("Failed to check for duplicate payment method");
        }

        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            throw new Exception("Payment method already exists");
        }

        // Update payment method
        $stmt = $conn->prepare("UPDATE payment SET PAY_METHOD = ?, IS_ACTIVE = ? WHERE PAYMENT_ID = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare update statement");
        }

        $stmt->bind_param("sii", $payMethod, $isActive, $paymentId);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update payment method");
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception("Payment method not found");
        }

        $conn->commit();

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
        if ($conn) {
            $conn->rollback();
        }
        error_log("Error updating payment method: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    } finally {
        if ($conn) {
            $conn->close();
        }
    }
}

function deletePaymentMethod($paymentId) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM payment WHERE PAYMENT_ID = ?");
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