<?php
// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to generate JSON response
function json_response($status, $message, $data = null) {
    header('Content-Type: application/json');
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

// Function to check if user is logged in
function is_logged_in() {
    session_start();
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function is_admin() {
    session_start();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Function to generate a random token
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

// Function to validate email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate password strength
function is_valid_password($password) {
    // At least 8 characters, contains at least one uppercase letter, one lowercase letter, and one number
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

// Function to set authentication cookie
function set_auth_cookie($user_id, $remember = false) {
    $token = generate_token();
    $expiry = $remember ? time() + (86400 * 30) : 0; // 30 days if remember me is checked
    
    setcookie('auth_token', $token, $expiry, '/', '', false, true);
    
    // Store token in database
    global $db;
    $stmt = $db->prepare("UPDATE users SET auth_token = ? WHERE id = ?");
    $stmt->execute([$token, $user_id]);
}

// Function to clear authentication cookie
function clear_auth_cookie() {
    setcookie('auth_token', '', time() - 3600, '/', '', false, true);
}
?>
