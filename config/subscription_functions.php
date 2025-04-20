<?php
require_once 'database.php';
require_once 'db_functions.php';

function getAllSubscriptions() {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM subscription ORDER BY IS_ACTIVE DESC, SUB_NAME ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        return ['success' => true, 'subscriptions' => $result->fetch_all(MYSQLI_ASSOC)];
    } catch (Exception $e) {
        error_log("Error getting subscriptions: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error retrieving subscriptions'];
    }
}

function addSubscriptionPlan($data) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO subscription (SUB_NAME, DURATION, PRICE, IS_ACTIVE) VALUES (?, ?, ?, ?)");
        $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
        $stmt->bind_param("ssdi", $data['SUB_NAME'], $data['DURATION'], $data['PRICE'], $isActive);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Subscription plan added successfully'];
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        error_log("Error adding subscription: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to add subscription plan'];
    }
}

function updateSubscriptionPlan($data) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE subscription SET SUB_NAME=?, DURATION=?, PRICE=?, IS_ACTIVE=? WHERE SUB_ID=?");
        $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
        $stmt->bind_param("ssdii", $data['SUB_NAME'], $data['DURATION'], $data['PRICE'], $isActive, $data['SUB_ID']);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Subscription plan updated successfully'];
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        error_log("Error updating subscription: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update subscription plan'];
    }
}

function toggleSubscriptionStatus($subId) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE subscription SET IS_ACTIVE = NOT IS_ACTIVE WHERE SUB_ID = ?");
        $stmt->bind_param("i", $subId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Subscription status updated successfully'];
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        error_log("Error toggling subscription status: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update subscription status'];
    }
}

function deleteSubscription($subId) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM subscription WHERE SUB_ID = ?");
        $stmt->bind_param("i", $subId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Subscription deleted successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to delete subscription'];
        }
    } catch (Exception $e) {
        error_log("Error deleting subscription: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

function deleteSubscriptionById($id) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM subscription WHERE SUB_ID = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Subscription deleted successfully'];
        }
        return ['success' => false, 'message' => 'Failed to delete subscription'];
    } catch (Exception $e) {
        error_log("Error deleting subscription: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}
