<?php
// Set headers
header('Content-Type: application/json');

// Create a test response
$response = [
    'status' => 'success',
    'message' => 'PHP server is working correctly',
    'timestamp' => date('Y-m-d H:i:s')
];

// Output JSON
echo json_encode($response);
?>
