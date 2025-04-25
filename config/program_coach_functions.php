<?php
require_once dirname(__DIR__) . '/connection/database.php';

function getAllPrograms() {
    global $conn;
    $sql = "SELECT * FROM program ORDER BY PROGRAM_NAME";
    $result = $conn->query($sql);
    
    if ($result) {
        return ['success' => true, 'programs' => $result->fetch_all(MYSQLI_ASSOC)];
    }
    return ['success' => false, 'message' => $conn->error];
}

function getAllCoaches() {
    global $conn;
    $sql = "SELECT c.*, GROUP_CONCAT(p.PROGRAM_NAME) as SPECIALIZATION 
            FROM coach c 
            LEFT JOIN program_coach pc ON c.COACH_ID = pc.COACH_ID 
            LEFT JOIN program p ON pc.PROGRAM_ID = p.PROGRAM_ID 
            GROUP BY c.COACH_ID 
            ORDER BY c.COACH_FNAME";
    $result = $conn->query($sql);
    
    if ($result) {
        return ['success' => true, 'coaches' => $result->fetch_all(MYSQLI_ASSOC)];
    }
    return ['success' => false, 'message' => $conn->error];
}

function addProgram($data) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO program (PROGRAM_NAME, IS_ACTIVE) VALUES (?, ?)");
    $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
    $stmt->bind_param("si", $data['PROGRAM_NAME'], $isActive);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Program added successfully'];
    }
    return ['success' => false, 'message' => $stmt->error];
}

function editProgram($data) {
    global $conn;
    $stmt = $conn->prepare("UPDATE program SET PROGRAM_NAME = ?, IS_ACTIVE = ? WHERE PROGRAM_ID = ?");
    $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
    $stmt->bind_param("sii", $data['PROGRAM_NAME'], $isActive, $data['PROGRAM_ID']);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Program updated successfully'];
    }
    return ['success' => false, 'message' => $stmt->error];
}

function deleteProgram($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM program WHERE PROGRAM_ID = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Program deleted successfully'];
    }
    return ['success' => false, 'message' => $stmt->error];
}

function addCoach($data) {
    global $conn;
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("INSERT INTO coach (COACH_FNAME, COACH_LNAME, EMAIL, PHONE_NUMBER, GENDER, IS_ACTIVE) VALUES (?, ?, ?, ?, ?, ?)");
        $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
        $stmt->bind_param("sssssi", 
            $data['COACH_FNAME'], 
            $data['COACH_LNAME'], 
            $data['EMAIL'], 
            $data['PHONE_NUMBER'], 
            $data['GENDER'],
            $isActive
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error adding coach");
        }
        
        $coachId = $conn->insert_id;
        
        // Add program assignments
        if (!empty($data['PROGRAM_ASSIGNMENTS'])) {
            $assignments = json_decode($data['PROGRAM_ASSIGNMENTS'], true);
            $stmt = $conn->prepare("INSERT INTO program_coach (PROGRAM_ID, COACH_ID) VALUES (?, ?)");
            
            foreach ($assignments as $programId) {
                $stmt->bind_param("ii", $programId, $coachId);
                if (!$stmt->execute()) {
                    throw new Exception("Error assigning program");
                }
            }
        }
        
        $conn->commit();
        return ['success' => true, 'message' => 'Coach added successfully'];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function editCoach($data) {
    global $conn;
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("UPDATE coach SET COACH_FNAME = ?, COACH_LNAME = ?, EMAIL = ?, PHONE_NUMBER = ?, GENDER = ?, IS_ACTIVE = ? WHERE COACH_ID = ?");
        $isActive = isset($data['IS_ACTIVE']) ? 1 : 0;
        $stmt->bind_param("sssssii", 
            $data['COACH_FNAME'], 
            $data['COACH_LNAME'], 
            $data['EMAIL'], 
            $data['PHONE_NUMBER'], 
            $data['GENDER'],
            $isActive,
            $data['COACH_ID']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating coach");
        }
        
        // Update program assignments
        $stmt = $conn->prepare("DELETE FROM program_coach WHERE COACH_ID = ?");
        $stmt->bind_param("i", $data['COACH_ID']);
        $stmt->execute();
        
        if (!empty($data['PROGRAM_ASSIGNMENTS'])) {
            $assignments = json_decode($data['PROGRAM_ASSIGNMENTS'], true);
            $stmt = $conn->prepare("INSERT INTO program_coach (PROGRAM_ID, COACH_ID) VALUES (?, ?)");
            
            foreach ($assignments as $programId) {
                $stmt->bind_param("ii", $programId, $data['COACH_ID']);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating program assignments");
                }
            }
        }
        
        $conn->commit();
        return ['success' => true, 'message' => 'Coach updated successfully'];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function deleteCoach($id) {
    global $conn;
    $conn->begin_transaction();
    
    try {
        // Delete program assignments first
        $stmt = $conn->prepare("DELETE FROM program_coach WHERE COACH_ID = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Error removing program assignments");
        }
        
        // Delete coach
        $stmt = $conn->prepare("DELETE FROM coach WHERE COACH_ID = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting coach");
        }
        
        $conn->commit();
        return ['success' => true, 'message' => 'Coach deleted successfully'];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
?>
