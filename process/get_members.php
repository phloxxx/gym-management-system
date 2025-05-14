<?php
require_once '../config/db_connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access";
    exit;
}

try {
    $conn = getConnection();
    
    // Query to get all members without duplicates
    $sql = "SELECT DISTINCT m.MEMBER_ID, m.MEMBER_FNAME, m.MEMBER_LNAME, m.EMAIL, m.PHONE_NUMBER, 
            p.PROGRAM_NAME, m.IS_ACTIVE, m.JOINED_DATE
            FROM member m
            LEFT JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
            ORDER BY m.MEMBER_ID DESC";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Keep track of members we've already displayed to avoid duplicates
        $processedMembers = [];
        
        while ($row = $result->fetch_assoc()) {
            // Skip if we've already displayed this member
            if (in_array($row['MEMBER_ID'], $processedMembers)) {
                continue;
            }
            
            // Add to processed list
            $processedMembers[] = $row['MEMBER_ID'];
            
            $statusBadge = $row['IS_ACTIVE'] ? 
                '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>' : 
                '<span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Inactive</span>';
            
            $initials = strtoupper(substr($row['MEMBER_FNAME'], 0, 1) . substr($row['MEMBER_LNAME'], 0, 1));
            $fullName = htmlspecialchars($row['MEMBER_FNAME'] . ' ' . $row['MEMBER_LNAME']);
            
            echo "<tr class='hover:bg-gray-50'>";
            echo "<td class='px-4 py-3 whitespace-nowrap'>";
            echo "  <div class='flex items-center'>";
            echo "    <div class='w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-medium'>$initials</div>";
            echo "    <div class='ml-3'>";
            echo "      <div class='text-sm font-medium text-gray-900'>$fullName</div>";
            echo "      <div class='text-xs text-gray-500'>{$row['EMAIL']}</div>";
            echo "    </div>";
            echo "  </div>";
            echo "</td>";
            echo "<td class='px-4 py-3 whitespace-nowrap'>{$row['PHONE_NUMBER']}</td>";
            echo "<td class='px-4 py-3 whitespace-nowrap'>{$row['PROGRAM_NAME']}</td>";
            echo "<td class='px-4 py-3 whitespace-nowrap'>{$statusBadge}</td>";
            echo "<td class='px-4 py-3 whitespace-nowrap'>" . date('M d, Y', strtotime($row['JOINED_DATE'])) . "</td>";
            echo "<td class='px-4 py-3 whitespace-nowrap text-right text-sm font-medium'>";
            echo "  <div class='flex justify-end space-x-2'>";
            echo "    <button class='view-member text-blue-500 hover:text-blue-700' data-member-id='{$row['MEMBER_ID']}'>";
            echo "      <i class='fas fa-eye'></i>";
            echo "    </button>";
            echo "    <button class='edit-member text-green-500 hover:text-green-700' data-member-id='{$row['MEMBER_ID']}'>";
            echo "      <i class='fas fa-edit'></i>";
            echo "    </button>";
            echo "    <button class='delete-member text-red-500 hover:text-red-700' data-member-id='{$row['MEMBER_ID']}'>";
            echo "      <i class='fas fa-trash-alt'></i>";
            echo "    </button>";
            echo "  </div>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6' class='px-4 py-3 text-center'>No members found</td></tr>";
    }
    
} catch (Exception $e) {
    echo "<tr><td colspan='6' class='px-4 py-3 text-center text-red-500'>Error: " . $e->getMessage() . "</td></tr>";
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
