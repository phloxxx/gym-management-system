<?php
require_once dirname(__DIR__) . '/connection/database.php';
require_once __DIR__ . '/db_functions.php';

function getAllSubscriptions() {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_GetAllSubscriptions()");
        $stmt->execute();
        $result = $stmt->get_result();
        $subscriptions = $result->fetch_all(MYSQLI_ASSOC);
        
        return ['success' => true, 'subscriptions' => $subscriptions];
    } catch (Exception $e) {
        error_log("Error fetching subscriptions: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to fetch subscriptions'];
    }
}

function getSubscriptionById($id) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_GetSubscriptionById(?)");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $subscription = $result->fetch_assoc();
        
        return ['success' => true, 'subscription' => $subscription];
    } catch (Exception $e) {
        error_log("Error fetching subscription: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to fetch subscription'];
    }
}

function addSubscription($subName, $duration, $price, $isActive) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_AddSubscription(?, ?, ?, ?)");
        $stmt->bind_param("sidi", $subName, $duration, $price, $isActive);
        $stmt->execute();
        $result = $stmt->get_result();
        $newId = $result->fetch_assoc()['SUB_ID'];
        
        return ['success' => true, 'message' => 'Subscription added successfully', 'id' => $newId];
    } catch (Exception $e) {
        error_log("Error adding subscription: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to add subscription'];
    }
}

function updateSubscription($subId, $subName, $duration, $price, $isActive) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_UpdateSubscription(?, ?, ?, ?, ?)");
        $stmt->bind_param("isidi", $subId, $subName, $duration, $price, $isActive);
        $stmt->execute();
        
        return ['success' => true, 'message' => 'Subscription updated successfully'];
    } catch (Exception $e) {
        error_log("Error updating subscription: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update subscription'];
    }
}

function deleteSubscriptionById($id) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_DeleteSubscription(?)");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        return ['success' => true, 'message' => 'Subscription deleted successfully'];
    } catch (Exception $e) {
        error_log("Error deleting subscription: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete subscription'];
    }
}

function toggleSubscriptionStatus($subId) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("CALL sp_ToggleSubscriptionStatus(?)");
        $stmt->bind_param("i", $subId);
        $stmt->execute();
        
        return ['success' => true, 'message' => 'Subscription status updated successfully'];
    } catch (Exception $e) {
        error_log("Error toggling subscription status: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update subscription status'];
    }
}
