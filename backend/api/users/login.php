<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include database and functions
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->getConnection();

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

// Check if data is complete
if (!isset($data->username) || !isset($data->password)) {
    json_response(false, 'Username and password are required');
}

// Sanitize input
$username = sanitize_input($data->username);
$password = $data->password;
$remember = isset($data->remember) ? (bool)$data->remember : false;

// Validate input
if (empty($username) || empty($password)) {
    json_response(false, 'Username and password are required');
}

// Check if user exists
$stmt = $db->prepare("SELECT id, username, email, password, role FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() === 0) {
    json_response(false, 'Invalid username or password');
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verify password
if (!password_verify($password, $user['password'])) {
    json_response(false, 'Invalid username or password');
}

// Start session
session_start();
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];

// Set auth cookie if remember me is checked
if ($remember) {
    set_auth_cookie($user['id'], true);
}

// Return user data (excluding password)
unset($user['password']);
json_response(true, 'Login successful', $user);
