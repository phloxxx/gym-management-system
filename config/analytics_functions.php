<?php

/**
 * Analytics Functions for Gym Management System
 * Contains functions for fetching statistics and data for dashboard displays
 */

/**
 * Get count of active members
 */
function getActiveMembersCount() {
    // In a real implementation, this would query the database
    // For demonstration, returning a sample value
    return 245;
}

/**
 * Get monthly revenue
 */
function getMonthlyRevenue() {
    // In a real implementation, this would calculate from transaction records
    // For demonstration, returning a sample value
    return 12580.50;
}

/**
 * Get count of active programs
 */
function getActiveProgramsCount() {
    // In a real implementation, this would query the database
    // For demonstration, returning a sample value
    return 15;
}

/**
 * Get count of staff members
 */
function getStaffCount() {
    // In a real implementation, this would query the database
    // For demonstration, returning a sample value
    return 12;
}

/**
 * Get membership growth data for chart display
 */
function getMembershipGrowthData() {
    // In a real implementation, this would query the database for monthly growth
    // For demonstration, returning sample data
    return [30, 42, 53, 48, 60, 67];
}

/**
 * Get subscription distribution data for chart display
 */
function getSubscriptionDistribution() {
    // In a real implementation, this would query the database
    // For demonstration, returning sample data
    return [
        "Monthly" => 45,
        "Quarterly" => 30,
        "Annual" => 20,
        "Premium" => 5
    ];
}

/**
 * Get recent members for dashboard display
 */
function getRecentMembers() {
    // In a real implementation, this would query the database
    // For demonstration, returning sample data
    return [
        [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'program' => 'Strength Training',
            'status' => 'Active',
            'joined' => '2023-11-15',
        ],
        [
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'program' => 'Yoga & Wellness',
            'status' => 'Active',
            'joined' => '2023-11-12',
        ],
        [
            'name' => 'Robert Johnson',
            'email' => 'robert.j@example.com',
            'program' => 'CrossFit',
            'status' => 'Active',
            'joined' => '2023-11-10',
        ],
        [
            'name' => 'Emily Wilson',
            'email' => 'emily.w@example.com',
            'program' => 'Weight Loss',
            'status' => 'Active',
            'joined' => '2023-11-08',
        ],
    ];
}

/**
 * Get recent transactions for dashboard display
 */
function getRecentTransactions() {
    // In a real implementation, this would query the database
    // For demonstration, returning sample data
    return [
        [
            'description' => 'Membership Renewal',
            'details' => 'John Doe - Monthly Plan',
            'date' => '2023-11-15',
            'amount' => '$49.99',
            'status' => 'Completed',
        ],
        [
            'description' => 'New Membership',
            'details' => 'Jane Smith - Annual Plan',
            'date' => '2023-11-12',
            'amount' => '$499.99',
            'status' => 'Completed',
        ],
        [
            'description' => 'Personal Training',
            'details' => 'Robert Johnson - 5 Sessions',
            'date' => '2023-11-10',
            'amount' => '$250.00',
            'status' => 'Completed',
        ],
        [
            'description' => 'Membership Renewal',
            'details' => 'Emily Wilson - Quarterly Plan',
            'date' => '2023-11-08',
            'amount' => '$129.99',
            'status' => 'Completed',
        ],
    ];
}

/**
 * Get count of inactive subscriptions
 */
function getInactiveSubscriptionsCount() {
    // In a real implementation, this would query the database
    return 18;
}

/**
 * Get subscription statuses distribution for chart display
 */
function getSubscriptionStatusDistribution() {
    // In a real implementation, this would query the database
    // For demonstration, returning sample data
    return [
        "Active" => 75,
        "Inactive" => 18,
        "Expiring Soon" => 7
    ];
}

/**
 * Track deactivation events for analytics
 * @param int $memberId The member ID
 * @param int $subscriptionId The subscription ID
 * @return bool Success status
 */
function trackDeactivationEvent($memberId, $subscriptionId) {
    // In a real implementation, this would log to the database
    // For demonstration, just logging to the error log
    error_log("Deactivation event tracked: Member ID $memberId, Subscription ID $subscriptionId");
    return true;
}
?>
require_once dirname(__DIR__) . '/connection/database.php';

function executeStoredProcedure($procedureName) {
    global $conn;
    try {
        // Prepare and execute
        $stmt = $conn->prepare("CALL " . $procedureName);
        if (!$stmt) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }
        if (!$stmt->execute()) {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
        
        // Get the result
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        // Clean up
        $stmt->close();
        while ($conn->more_results()) {
            $conn->next_result();
            if ($moreResult = $conn->store_result()) {
                $moreResult->free();
            }
        }
        
        return $data;
    } catch (mysqli_sql_exception $e) {
        error_log("Error in $procedureName: " . $e->getMessage());
        return null;
    }
}

function executeStoredProcedureWithMultipleRows($procedureName) {
    global $conn;
    try {
        // Prepare and execute
        $stmt = $conn->prepare("CALL " . $procedureName);
        if (!$stmt) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }
        if (!$stmt->execute()) {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
        
        // Get all results
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        // Clean up
        $stmt->close();
        while ($conn->more_results()) {
            $conn->next_result();
            if ($moreResult = $conn->store_result()) {
                $moreResult->free();
            }
        }
        
        return $data;
    } catch (mysqli_sql_exception $e) {
        error_log("Error in $procedureName: " . $e->getMessage());
        return [];
    }
}

function getActiveMembersCount() {
    $result = executeStoredProcedure("GetActiveMembersCount");
    return $result['count'] ?? 0;
}

function getMonthlyRevenue() {
    $result = executeStoredProcedure("GetMonthlyRevenue");
    return $result['revenue'] ?? 0;
}

function getActiveProgramsCount() {
    $result = executeStoredProcedure("GetActiveProgramsCount");
    return $result['count'] ?? 0;
}

function getStaffCount() {
    $result = executeStoredProcedure("GetStaffCount");
    return $result['count'] ?? 0;
}

function getMembershipGrowthData() {
    $months = array_fill(0, 6, 0);
    $results = executeStoredProcedureWithMultipleRows("GetMembershipGrowthData");
    
    foreach ($results as $row) {
        $monthIndex = (int)$row['month'] - 1;
        if ($monthIndex >= 0 && $monthIndex < 6) {
            $months[$monthIndex] = (int)$row['count'];
        }
    }
    return array_values($months);
}

function getSubscriptionDistribution() {
    $results = executeStoredProcedureWithMultipleRows("GetSubscriptionDistribution");
    $data = [];
    foreach ($results as $row) {
        $data[$row['SUB_NAME']] = (int)$row['count'];
    }
    return $data;
}

function getRecentMembers() {
    return executeStoredProcedureWithMultipleRows("GetRecentMembers");
}

function getRecentTransactions() {
    return executeStoredProcedureWithMultipleRows("GetRecentTransactions");
}
