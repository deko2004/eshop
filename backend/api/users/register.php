<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include database and functions
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Instantiate DB & connect
try {
    $database = new Database();
    $db = $database->getConnection();

    // Test database connection
    if (!$db) {
        error_log("Register API - Database connection failed");
        json_response(false, 'Database connection error');
    }
} catch (Exception $e) {
    error_log("Register API - Database exception: " . $e->getMessage());
    json_response(false, 'Database error: ' . $e->getMessage());
}

// Enable error logging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log request for debugging
$raw_input = file_get_contents("php://input");
error_log("Register API - Raw input: " . $raw_input);

// Get raw posted data
$data = json_decode($raw_input);

// Check if JSON is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Register API - JSON decode error: " . json_last_error_msg());
    json_response(false, 'Invalid JSON data: ' . json_last_error_msg());
}

// Check if data is complete
if (
    !isset($data->firstname) ||
    !isset($data->lastname) ||
    !isset($data->username) ||
    !isset($data->email) ||
    !isset($data->password) ||
    !isset($data->confirm_password) ||
    !isset($data->age) ||
    !isset($data->wilaya) ||
    !isset($data->telephone) ||
    !isset($data->address) ||
    !isset($data->sex)
) {
    json_response(false, 'Missing required fields');
}

// Sanitize input
$firstname = sanitize_input($data->firstname);
$lastname = sanitize_input($data->lastname);
$username = sanitize_input($data->username);
$email = sanitize_input($data->email);
$password = $data->password;
$confirm_password = $data->confirm_password;
$age = (int)$data->age;
$wilaya = sanitize_input($data->wilaya);
$telephone = sanitize_input($data->telephone);
$address = sanitize_input($data->address);
$sex = sanitize_input($data->sex);

// Validate input
if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($password)) {
    json_response(false, 'All fields are required');
}

if (!is_valid_email($email)) {
    json_response(false, 'Invalid email format');
}

if ($password !== $confirm_password) {
    json_response(false, 'Passwords do not match');
}

if (!is_valid_password($password)) {
    json_response(false, 'Password must be at least 8 characters and include uppercase, lowercase, and numbers');
}

if ($age < 18) {
    json_response(false, 'You must be at least 18 years old');
}

if ($sex !== 'male' && $sex !== 'female') {
    json_response(false, 'Invalid gender selection');
}

// Check if username already exists
$stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() > 0) {
    json_response(false, 'Username already exists');
}

// Check if email already exists
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() > 0) {
    json_response(false, 'Email already exists');
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Create user
try {
    $stmt = $db->prepare("INSERT INTO users (firstname, lastname, username, email, password, age, wilaya, telephone, address, sex)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $result = $stmt->execute([
        $firstname,
        $lastname,
        $username,
        $email,
        $password_hash,
        $age,
        $wilaya,
        $telephone,
        $address,
        $sex
    ]);

    if ($result) {
        json_response(true, 'User registered successfully');
    } else {
        json_response(false, 'Failed to register user');
    }
} catch (PDOException $e) {
    json_response(false, 'Database error: ' . $e->getMessage());
}
