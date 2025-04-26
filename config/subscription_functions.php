<?php
require_once dirname(__DIR__) . '/connection/database.php';
require_once __DIR__ . '/db_functions.php';

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
