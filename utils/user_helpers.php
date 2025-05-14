<?php
/**
 * User Helper Functions
 * Provides utility functions for working with user IDs and authentication
 */

/**
 * Gets a valid user ID from the database
 * First tries to use the provided ID, then looks for an admin, then any active user
 * @param mysqli $conn Database connection
 * @param int|null $preferredUserId The user ID to try first (optional)
 * @return int|null A valid user ID or null if none found
 */
function getValidUserId($conn, $preferredUserId = null) {
    // First check if the preferred user ID exists and is valid
    if ($preferredUserId !== null) {
        $stmt = $conn->prepare("SELECT USER_ID FROM user WHERE USER_ID = ? AND IS_ACTIVE = 1 LIMIT 1");
        $stmt->bind_param("i", $preferredUserId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['USER_ID'];
        }
    }
    
    // Then try to find an admin user
    $adminResult = $conn->query("SELECT USER_ID FROM user WHERE USER_TYPE = 'ADMINISTRATOR' AND IS_ACTIVE = 1 LIMIT 1");
    if ($adminResult && $adminResult->num_rows > 0) {
        $row = $adminResult->fetch_assoc();
        return $row['USER_ID'];
    }
    
    // Finally try any active user
    $anyResult = $conn->query("SELECT USER_ID FROM user WHERE IS_ACTIVE = 1 LIMIT 1");
    if ($anyResult && $anyResult->num_rows > 0) {
        $row = $anyResult->fetch_assoc();
        return $row['USER_ID'];
    }
    
    // No valid user found
    return null;
}

/**
 * Checks if a session is active and contains a valid user ID
 * @return bool True if session has valid user ID, false otherwise
 */
function hasValidSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Gets the current user ID from the session
 * @return int|null User ID if session exists, null otherwise
 */
function getCurrentUserId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}
