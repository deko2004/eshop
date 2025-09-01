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

// Check if user is logged in
if (!is_logged_in()) {
    json_response(false, 'Unauthorized access');
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

// Check if data is complete
if (!isset($data->current_password) || !isset($data->new_password) || !isset($data->confirm_password)) {
    json_response(false, 'All fields are required');
}

// Sanitize input
$current_password = $data->current_password;
$new_password = $data->new_password;
$confirm_password = $data->confirm_password;

// Validate input
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    json_response(false, 'All fields are required');
}

if ($new_password !== $confirm_password) {
    json_response(false, 'New passwords do not match');
}

if (!is_valid_password($new_password)) {
    json_response(false, 'Password must be at least 8 characters and include uppercase, lowercase, and numbers');
}

// Get current user password
$stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verify current password
if (!password_verify($current_password, $user['password'])) {
    json_response(false, 'Current password is incorrect');
}

// Hash new password
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update password
try {
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $result = $stmt->execute([$password_hash, $user_id]);
    
    if ($result) {
        json_response(true, 'Password updated successfully');
    } else {
        json_response(false, 'Failed to update password');
    }
} catch (PDOException $e) {
    json_response(false, 'Database error: ' . $e->getMessage());
}
?>
