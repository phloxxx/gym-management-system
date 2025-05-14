<?php
// Simple test script to check subscription display query
include_once '../includes/db_connection.php';
include_once '../functions/transaction-functions.php'; 

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Subscription Query Test</h1>";

// Get a database connection
$conn = getConnection();

// Execute the de-duplicated query
$sql = "WITH LatestTransactions AS (
            SELECT 
                t.MEMBER_ID,
                t.SUB_ID,
                t.TRANSACTION_ID,
                t.TRANSAC_DATE,
                ROW_NUMBER() OVER (PARTITION BY t.MEMBER_ID, t.SUB_ID ORDER BY t.TRANSAC_DATE DESC) as rn
            FROM transaction t
        ),
        RankedSubscriptions AS (
            SELECT 
                ms.MEMBER_ID,
                ms.SUB_ID,
                ms.START_DATE,
                ms.END_DATE,
                ms.IS_ACTIVE,
                ROW_NUMBER() OVER (
                    PARTITION BY ms.MEMBER_ID, ms.SUB_ID 
                    ORDER BY 
                        ms.IS_ACTIVE DESC,                -- Active subscriptions first
                        ms.END_DATE DESC,                 -- Latest end date next
                        ms.START_DATE DESC                -- Then latest start date
                ) as sub_rank
            FROM member_subscription ms
        )
        SELECT 
            m.MEMBER_ID, 
            m.MEMBER_FNAME, 
            m.MEMBER_LNAME, 
            s.SUB_ID,
            s.SUB_NAME, 
            rs.START_DATE, 
            rs.END_DATE, 
            rs.IS_ACTIVE,
            lt.TRANSACTION_ID,
            lt.TRANSAC_DATE as PAID_DATE,
            ROW_NUMBER() OVER (
                PARTITION BY m.MEMBER_ID, s.SUB_ID
                ORDER BY rs.IS_ACTIVE DESC, rs.END_DATE DESC
            ) as display_rank
        FROM member m
        JOIN RankedSubscriptions rs ON m.MEMBER_ID = rs.MEMBER_ID AND rs.sub_rank = 1
        JOIN subscription s ON rs.SUB_ID = s.SUB_ID
        LEFT JOIN LatestTransactions lt ON rs.MEMBER_ID = lt.MEMBER_ID 
                                      AND rs.SUB_ID = lt.SUB_ID 
                                      AND lt.rn = 1
        ORDER BY m.MEMBER_ID, s.SUB_ID, rs.IS_ACTIVE DESC";

$result = $conn->query($sql);

echo "<h2>Results:</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f2f2f2;'>
        <th style='padding: 8px; text-align: left;'>Member ID</th>
        <th style='padding: 8px; text-align: left;'>Member Name</th>
        <th style='padding: 8px; text-align: left;'>Subscription</th>
        <th style='padding: 8px; text-align: left;'>Start Date</th>
        <th style='padding: 8px; text-align: left;'>End Date</th>
        <th style='padding: 8px; text-align: left;'>Active</th>
        <th style='padding: 8px; text-align: left;'>Transaction Date</th>
        <th style='padding: 8px; text-align: left;'>Display Rank</th>
      </tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $activeClass = $row['IS_ACTIVE'] ? "background-color:#d4edda;" : "background-color:#f8d7da;";
        echo "<tr style='{$activeClass}'>";
        echo "<td style='padding: 8px;'>" . $row['MEMBER_ID'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['MEMBER_FNAME'] . " " . $row['MEMBER_LNAME'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['SUB_NAME'] . " (ID: " . $row['SUB_ID'] . ")</td>";
        echo "<td style='padding: 8px;'>" . $row['START_DATE'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['END_DATE'] . "</td>";
        echo "<td style='padding: 8px;'>" . ($row['IS_ACTIVE'] ? "Yes" : "No") . "</td>";
        echo "<td style='padding: 8px;'>" . ($row['PAID_DATE'] ?? "None") . "</td>";
        echo "<td style='padding: 8px;'>" . $row['display_rank'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8' style='padding: 8px; text-align: center;'>No results found</td></tr>";
}

echo "</table>";

// Also fetch records from the getActiveSubscriptions function
echo "<h2>getActiveSubscriptions() Function Results:</h2>";
$activeSubscriptions = getActiveSubscriptions();

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f2f2f2;'>
        <th style='padding: 8px; text-align: left;'>Member ID</th>
        <th style='padding: 8px; text-align: left;'>Member Name</th>
        <th style='padding: 8px; text-align: left;'>Subscription</th>
        <th style='padding: 8px; text-align: left;'>Start Date</th>
        <th style='padding: 8px; text-align: left;'>End Date</th>
        <th style='padding: 8px; text-align: left;'>Active</th>
        <th style='padding: 8px; text-align: left;'>Transaction Date</th>
      </tr>";

if (count($activeSubscriptions) > 0) {
    foreach ($activeSubscriptions as $sub) {
        $activeClass = $sub['IS_ACTIVE'] ? "background-color:#d4edda;" : "background-color:#f8d7da;";
        echo "<tr style='{$activeClass}'>";
        echo "<td style='padding: 8px;'>" . $sub['MEMBER_ID'] . "</td>";
        echo "<td style='padding: 8px;'>" . $sub['MEMBER_FNAME'] . " " . $sub['MEMBER_LNAME'] . "</td>";
        echo "<td style='padding: 8px;'>" . $sub['SUB_NAME'] . " (ID: " . $sub['SUB_ID'] . ")</td>";
        echo "<td style='padding: 8px;'>" . $sub['START_DATE'] . "</td>";
        echo "<td style='padding: 8px;'>" . $sub['END_DATE'] . "</td>";
        echo "<td style='padding: 8px;'>" . ($sub['IS_ACTIVE'] ? "Yes" : "No") . "</td>";
        echo "<td style='padding: 8px;'>" . ($sub['PAID_DATE'] ?? "None") . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7' style='padding: 8px; text-align: center;'>No results found</td></tr>";
}

echo "</table>";

$conn->close();
?>
