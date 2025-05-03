<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection
include_once '../../includes/db_connection.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure required data is not empty
if(
    !empty($data->SUB_ID) && 
    isset($data->IS_ACTIVE) // Allow 0 as valid value
){
    try {
        // Create update query
        $query = "UPDATE subscription SET
                  IS_ACTIVE = :is_active
                  WHERE SUB_ID = :sub_id";

        // Prepare statement
        $stmt = $conn->prepare($query);

        // Bind values
        $stmt->bindParam(":is_active", $data->IS_ACTIVE);
        $stmt->bindParam(":sub_id", $data->SUB_ID);

        // Execute query
        if($stmt->execute()){
            // Return success response
            http_response_code(200);
            echo json_encode(array("status" => "success", "message" => "Subscription status was updated."));
        } else {
            // Return error response
            http_response_code(503);
            echo json_encode(array("status" => "error", "message" => "Unable to update subscription status."));
        }

    } catch(PDOException $e) {
        // Return error response
        http_response_code(500);
        echo json_encode(array(
            "status" => "error",
            "message" => "Database error: " . $e->getMessage()
        ));
    }
}
else {
    // Return error response if required data is missing
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Unable to update subscription status. Required data is missing."));
}
?>
