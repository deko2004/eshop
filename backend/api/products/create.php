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

// Check if user is logged in and is admin
if (!is_admin()) {
    // Set response code - 403 Forbidden
    http_response_code(403);
    
    // Tell the user
    echo json_encode(array('message' => 'Access denied. Admin privileges required.'));
    exit;
}

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

// Check if data is complete
if (
    !isset($data->name) || 
    !isset($data->price) || 
    !isset($data->image) || 
    !isset($data->description) || 
    !isset($data->features) || 
    !isset($data->specifications)
) {
    // Set response code - 400 Bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array('message' => 'Unable to create product. Data is incomplete.'));
    exit;
}

// Sanitize input
$name = sanitize_input($data->name);
$price = (float)$data->price;
$image = sanitize_input($data->image);
$description = sanitize_input($data->description);

// Validate input
if (empty($name) || $price <= 0 || empty($image) || empty($description)) {
    // Set response code - 400 Bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array('message' => 'Unable to create product. Data is invalid.'));
    exit;
}

// Prepare features and specifications for database
$features = json_encode($data->features);
$specifications = json_encode($data->specifications);

// Create product
try {
    // Prepare query
    $query = "INSERT INTO products (name, price, image, description, features, specifications) 
              VALUES (:name, :price, :image, :description, :features, :specifications)";
    
    // Prepare statement
    $stmt = $db->prepare($query);
    
    // Bind parameters
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':image', $image);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':features', $features);
    $stmt->bindParam(':specifications', $specifications);
    
    // Execute query
    if ($stmt->execute()) {
        // Get the ID of the newly created product
        $product_id = $db->lastInsertId();
        
        // Set response code - 201 Created
        http_response_code(201);
        
        // Tell the user
        echo json_encode(array(
            'message' => 'Product was created successfully.',
            'id' => $product_id
        ));
    } else {
        // Set response code - 503 Service unavailable
        http_response_code(503);
        
        // Tell the user
        echo json_encode(array('message' => 'Unable to create product.'));
    }
} catch (PDOException $e) {
    // Set response code - 503 Service unavailable
    http_response_code(503);
    
    // Tell the user
    echo json_encode(array('message' => 'Database error: ' . $e->getMessage()));
}
?>
