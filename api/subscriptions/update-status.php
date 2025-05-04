<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Return error for removed functionality
http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'This feature has been removed']);
exit();
?>