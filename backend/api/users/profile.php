<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, PUT');
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

// Handle GET request (get user profile)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->prepare("SELECT id, firstname, lastname, username, email, age, wilaya, telephone, address, sex, role, created_at 
                          FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    if ($stmt->rowCount() === 0) {
        json_response(false, 'User not found');
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    json_response(true, 'User profile retrieved successfully', $user);
}

// Handle PUT request (update user profile)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Get raw posted data
    $data = json_decode(file_get_contents("php://input"));
    
    // Check if data is complete
    if (
        !isset($data->firstname) || 
        !isset($data->lastname) || 
        !isset($data->email) || 
        !isset($data->age) ||
        !isset($data->wilaya) ||
        !isset($data->telephone) ||
        !isset($data->address)
    ) {
        json_response(false, 'Missing required fields');
    }
    
    // Sanitize input
    $firstname = sanitize_input($data->firstname);
    $lastname = sanitize_input($data->lastname);
    $email = sanitize_input($data->email);
    $age = (int)$data->age;
    $wilaya = sanitize_input($data->wilaya);
    $telephone = sanitize_input($data->telephone);
    $address = sanitize_input($data->address);
    
    // Validate input
    if (empty($firstname) || empty($lastname) || empty($email)) {
        json_response(false, 'All fields are required');
    }
    
    if (!is_valid_email($email)) {
        json_response(false, 'Invalid email format');
    }
    
    if ($age < 18) {
        json_response(false, 'You must be at least 18 years old');
    }
    
    // Check if email already exists (for another user)
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        json_response(false, 'Email already exists');
    }
    
    // Update user
    try {
        $stmt = $db->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, age = ?, wilaya = ?, telephone = ?, address = ? 
                              WHERE id = ?");
        
        $result = $stmt->execute([
            $firstname, 
            $lastname, 
            $email, 
            $age, 
            $wilaya, 
            $telephone, 
            $address, 
            $user_id
        ]);
        
        if ($result) {
            // Get updated user data
            $stmt = $db->prepare("SELECT id, firstname, lastname, username, email, age, wilaya, telephone, address, sex, role, created_at 
                                  FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            json_response(true, 'Profile updated successfully', $user);
        } else {
            json_response(false, 'Failed to update profile');
        }
    } catch (PDOException $e) {
        json_response(false, 'Database error: ' . $e->getMessage());
    }
}

// If method is not GET or PUT
json_response(false, 'Invalid request method');
?>
