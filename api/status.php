<?php
// Simple API endpoint to verify the restructured copy is reachable
header('Content-Type: application/json; charset=utf-8');
http_response_code(200);
echo json_encode([ 'status' => 'ok', 'message' => 'restructured API reachable' ]);
?>
