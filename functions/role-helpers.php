<?php
// filepath: c:\xampp2\htdocs\gym-management-system\functions\role-helpers.php
/**
 * Role Helper Functions
 * Contains utility functions related to user roles and permissions
 */

/**
 * Checks if the current user has access to transaction and member management features
 * Returns true if the user is an admin or staff, false otherwise
 * 
 * @return boolean Whether the user has management access
 */
function hasManagementAccess() {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    $role = strtolower($_SESSION['role']);
    return ($role === 'administrator' || $role === 'staff');
}

/**
 * Checks if user has access to specific page and redirects to login if not
 *
 * @param array $allowedRoles Array of roles that have access
 * @return void
 */
function checkPageAccess($allowedRoles = ['administrator', 'staff']) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../login.php");
        exit();
    }
    
    $userRole = strtolower($_SESSION['role'] ?? '');
    
    if (!in_array($userRole, $allowedRoles)) {
        header("Location: ../../login.php");
        exit();
    }
}
