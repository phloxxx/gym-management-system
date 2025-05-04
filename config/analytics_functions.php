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
