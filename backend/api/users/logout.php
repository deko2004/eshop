<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include functions
require_once '../../includes/functions.php';

// Start session
session_start();

// Clear session
$_SESSION = array();

// Destroy session
if (session_id() != '' || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}
session_destroy();

// Clear auth cookie
clear_auth_cookie();

// Return success response
json_response(true, 'Logout successful');
