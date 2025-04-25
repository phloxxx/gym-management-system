<?php
require_once dirname(__DIR__) . '/connection/database.php';

function getActiveMembersCount() {
    global $conn;
    try {
        $sql = "SELECT COUNT(*) as count FROM member WHERE IS_ACTIVE = 1";
        if ($result = $conn->query($sql)) {
            return $result->fetch_assoc()['count'] ?? 0;
        }
        return 0;
    } catch (mysqli_sql_exception $e) {
        error_log("Error in getActiveMembersCount: " . $e->getMessage());
        return 0;
    }
}

function getMonthlyRevenue() {
    global $conn;
    try {
        $sql = "SELECT SUM(s.PRICE) as revenue 
                FROM transaction t 
                JOIN subscription s ON t.SUB_ID = s.SUB_ID 
                WHERE MONTH(t.TRANSAC_DATE) = MONTH(CURRENT_DATE()) 
                AND YEAR(t.TRANSAC_DATE) = YEAR(CURRENT_DATE())";
        if ($result = $conn->query($sql)) {
            return $result->fetch_assoc()['revenue'] ?? 0;
        }
        return 0;
    } catch (mysqli_sql_exception $e) {
        error_log("Error in getMonthlyRevenue: " . $e->getMessage());
        return 0;
    }
}

function getActiveProgramsCount() {
    global $conn;
    try {
        $sql = "SELECT COUNT(*) as count FROM program WHERE IS_ACTIVE = 1";
        if ($result = $conn->query($sql)) {
            return $result->fetch_assoc()['count'] ?? 0;
        }
        return 0;
    } catch (mysqli_sql_exception $e) {
        error_log("Error in getActiveProgramsCount: " . $e->getMessage());
        return 0;
    }
}

function getStaffCount() {
    global $conn;
    try {
        $sql = "SELECT COUNT(*) as count FROM user WHERE USER_TYPE = 'STAFF' AND IS_ACTIVE = 1";
        if ($result = $conn->query($sql)) {
            return $result->fetch_assoc()['count'] ?? 0;
        }
        return 0;
    } catch (mysqli_sql_exception $e) {
        error_log("Error in getStaffCount: " . $e->getMessage());
        return 0;
    }
}

function getMembershipGrowthData() {
    global $conn;
    try {
        $months = array_fill(0, 6, 0);
        
        $sql = "SELECT MONTH(JOINED_DATE) as month, COUNT(*) as count 
                FROM member 
                WHERE JOINED_DATE >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                GROUP BY MONTH(JOINED_DATE)
                ORDER BY JOINED_DATE";
                
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $monthIndex = (int)$row['month'] - 1;
                if ($monthIndex >= 0 && $monthIndex < 6) {
                    $months[$monthIndex] = (int)$row['count'];
                }
            }
            return array_values($months);
        }
        return $months;
    } catch (mysqli_sql_exception $e) {
        error_log("Error in getMembershipGrowthData: " . $e->getMessage());
        return array_fill(0, 6, 0);
    }
}

function getSubscriptionDistribution() {
    global $conn;
    try {
        $sql = "SELECT s.SUB_NAME, COUNT(ms.MEMBER_ID) as count
                FROM subscription s
                LEFT JOIN member_subscription ms ON s.SUB_ID = ms.SUB_ID
                WHERE s.IS_ACTIVE = 1 AND (ms.IS_ACTIVE = 1 OR ms.IS_ACTIVE IS NULL)
                GROUP BY s.SUB_NAME";
        $result = $conn->query($sql);
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[$row['SUB_NAME']] = (int)$row['count'];
        }
        return $data;
    } catch (mysqli_sql_exception $e) {
        error_log("Error in getSubscriptionDistribution: " . $e->getMessage());
        return [];
    }
}

function getRecentMembers() {
    global $conn;
    try {
        $sql = "SELECT m.MEMBER_FNAME, m.MEMBER_LNAME, m.EMAIL, p.PROGRAM_NAME, 
                m.IS_ACTIVE, m.JOINED_DATE
                FROM member m
                LEFT JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
                ORDER BY m.JOINED_DATE DESC 
                LIMIT 3";
        $result = $conn->query($sql);
        $members = [];
        while($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
        return $members;
    } catch (mysqli_sql_exception $e) {
        error_log("Error in getRecentMembers: " . $e->getMessage());
        return [];
    }
}

function getRecentTransactions() {
    global $conn;
    try {
        $sql = "SELECT t.TRANSACTION_ID, m.MEMBER_FNAME, m.MEMBER_LNAME,
                s.SUB_NAME, s.PRICE, t.TRANSAC_DATE, p.PAY_METHOD
                FROM transaction t
                JOIN member m ON t.MEMBER_ID = m.MEMBER_ID
                JOIN subscription s ON t.SUB_ID = s.SUB_ID
                JOIN payment p ON t.PAYMENT_ID = p.PAYMENT_ID
                ORDER BY t.TRANSAC_DATE DESC
                LIMIT 3";
        $result = $conn->query($sql);
        $transactions = [];
        while($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        return $transactions;
    } catch (mysqli_sql_exception $e) {
        error_log("Error in getRecentTransactions: " . $e->getMessage());
        return [];
    }
}
