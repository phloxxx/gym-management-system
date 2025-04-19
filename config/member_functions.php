<?php
require_once 'database.php';

function getConnection() {
    try {
        $host = "localhost";
        $username = "root";
        $password = "";
        $database = "gymaster";
        
        $conn = new mysqli($host, $username, $password, $database);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Check for member table
        $tableCheck = $conn->query("SHOW TABLES LIKE 'member'");
        if ($tableCheck->num_rows == 0) {
            // Create member table if it doesn't exist
            $sql = "CREATE TABLE IF NOT EXISTS `member` (
                `MEMBER_ID` int(11) NOT NULL AUTO_INCREMENT,
                `MEMBER_FNAME` varchar(50) NOT NULL,
                `MEMBER_LNAME` varchar(30) NOT NULL,
                `EMAIL` varchar(100) NOT NULL,
                `PHONE_NUMBER` varchar(20) NOT NULL,
                `PROGRAM_ID` int(11) NOT NULL,
                `COACH_ID` int(11) NOT NULL,
                `SUB_ID` int(11) NOT NULL,
                `START_DATE` date NOT NULL,
                `END_DATE` date NOT NULL,
                `PAYMENT_ID` int(11) NOT NULL,
                `TRANSAC_DATE` date NOT NULL,
                `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`MEMBER_ID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            // Create member_comorbidity junction table
            $sql2 = "CREATE TABLE IF NOT EXISTS `member_comorbidity` (
                `MEMBER_ID` int(11) NOT NULL,
                `COMORBIDITY_ID` int(11) NOT NULL,
                PRIMARY KEY (`MEMBER_ID`, `COMORBIDITY_ID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            if (!$conn->query($sql) || !$conn->query($sql2)) {
                throw new Exception("Table creation failed: " . $conn->error);
            }
        }
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        throw $e;
    }
}

function addMember($data) {
    try {
        $conn = getConnection();
        
        // Begin transaction
        $conn->begin_transaction();
        
        // Insert member
        $stmt = $conn->prepare("INSERT INTO member (
            MEMBER_FNAME, MEMBER_LNAME, EMAIL, PHONE_NUMBER, 
            PROGRAM_ID, COACH_ID, SUB_ID, START_DATE, 
            END_DATE, PAYMENT_ID, TRANSAC_DATE, IS_ACTIVE
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE(), ?)");
        
        $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
        
        $stmt->bind_param("ssssiiiissi", 
            $data['MEMBER_FNAME'],
            $data['MEMBER_LNAME'],
            $data['EMAIL'],
            $data['PHONE_NUMBER'],
            $data['PROGRAM_ID'],
            $data['COACH_ID'],
            $data['SUB_ID'],
            $data['START_DATE'],
            $data['END_DATE'],
            $data['PAYMENT_ID'],
            $isActive
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to add member: " . $stmt->error);
        }
        
        $memberId = $conn->insert_id;
        
        // Add comorbidities if any
        if (!empty($data['COMORBIDITIES'])) {
            $comorbidities = is_array($data['COMORBIDITIES']) ? 
                            $data['COMORBIDITIES'] : 
                            [$data['COMORBIDITIES']];
            
            $stmt = $conn->prepare("INSERT INTO member_comorbidity (MEMBER_ID, COMORBIDITY_ID) VALUES (?, ?)");
            
            foreach ($comorbidities as $comorbidityId) {
                $stmt->bind_param("ii", $memberId, $comorbidityId);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to add comorbidity: " . $stmt->error);
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        return ['success' => true, 'message' => 'Member added successfully'];
        
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
        }
        error_log("Error adding member: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()];
    }
}

function updateMember($data) {
    try {
        $conn = getConnection();
        
        // Begin transaction
        $conn->begin_transaction();
        
        // Build update query dynamically based on provided fields
        $updates = [];
        $params = [];
        $types = '';
        
        $fields = [
            'MEMBER_FNAME' => 's',
            'MEMBER_LNAME' => 's',
            'EMAIL' => 's',
            'PHONE_NUMBER' => 's',
            'PROGRAM_ID' => 'i',
            'COACH_ID' => 'i',
            'IS_ACTIVE' => 'i'
        ];
        
        foreach ($fields as $field => $type) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
                $types .= $type;
            }
        }
        
        if (empty($updates)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }
        
        // Add member ID to parameters
        $params[] = $data['MEMBER_ID'];
        $types .= 'i';
        
        $sql = "UPDATE member SET " . implode(', ', $updates) . " WHERE MEMBER_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update member: " . $stmt->error);
        }
        
        // Update comorbidities if provided
        if (isset($data['COMORBIDITIES'])) {
            // Remove existing comorbidities
            $stmt = $conn->prepare("DELETE FROM member_comorbidity WHERE MEMBER_ID = ?");
            $stmt->bind_param("i", $data['MEMBER_ID']);
            $stmt->execute();
            
            // Add new comorbidities
            if (!empty($data['COMORBIDITIES'])) {
                $stmt = $conn->prepare("INSERT INTO member_comorbidity (MEMBER_ID, COMORBIDITY_ID) VALUES (?, ?)");
                foreach ($data['COMORBIDITIES'] as $comorbidityId) {
                    $stmt->bind_param("ii", $data['MEMBER_ID'], $comorbidityId);
                    $stmt->execute();
                }
            }
        }
        
        $conn->commit();
        return ['success' => true, 'message' => 'Member updated successfully'];
        
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
        }
        error_log("Error updating member: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

function deleteMember($memberId) {
    try {
        $conn = getConnection();
        
        // Begin transaction
        $conn->begin_transaction();
        
        // Delete comorbidities first
        $stmt = $conn->prepare("DELETE FROM member_comorbidity WHERE MEMBER_ID = ?");
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        
        // Delete member
        $stmt = $conn->prepare("DELETE FROM member WHERE MEMBER_ID = ?");
        $stmt->bind_param("i", $memberId);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete member");
        }
        
        $conn->commit();
        return ['success' => true, 'message' => 'Member deleted successfully'];
        
    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
        }
        error_log("Error deleting member: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

function getMember($memberId) {
    try {
        $conn = getConnection();
        
        // Get member details
        $stmt = $conn->prepare("
            SELECT m.*, GROUP_CONCAT(mc.COMORBIDITY_ID) as comorbidities
            FROM member m
            LEFT JOIN member_comorbidity mc ON m.MEMBER_ID = mc.MEMBER_ID
            WHERE m.MEMBER_ID = ?
            GROUP BY m.MEMBER_ID
        ");
        
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($member = $result->fetch_assoc()) {
            // Convert comorbidities string to array
            $member['comorbidities'] = $member['comorbidities'] ? 
                explode(',', $member['comorbidities']) : [];
            return ['success' => true, 'member' => $member];
        }
        
        return ['success' => false, 'message' => 'Member not found'];
        
    } catch (Exception $e) {
        error_log("Error fetching member: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

function getAllMembers() {
    try {
        $conn = getConnection();
        
        $sql = "
            SELECT m.*, GROUP_CONCAT(mc.COMORBIDITY_ID) as comorbidities
            FROM member m
            LEFT JOIN member_comorbidity mc ON m.MEMBER_ID = mc.MEMBER_ID
            GROUP BY m.MEMBER_ID
            ORDER BY m.MEMBER_ID DESC
        ";
        
        $result = $conn->query($sql);
        
        $members = [];
        while ($row = $result->fetch_assoc()) {
            $row['comorbidities'] = $row['comorbidities'] ? 
                explode(',', $row['comorbidities']) : [];
            $members[] = $row;
        }
        
        return ['success' => true, 'members' => $members];
        
    } catch (Exception $e) {
        error_log("Error fetching members: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}
