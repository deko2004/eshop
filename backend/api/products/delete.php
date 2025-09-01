<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
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

// Check if ID is provided
if (!isset($data->id)) {
    // Set response code - 400 Bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array('message' => 'Unable to delete product. No ID provided.'));
    exit;
}

// Sanitize input
$id = (int)$data->id;

// Validate input
if ($id <= 0) {
    // Set response code - 400 Bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array('message' => 'Unable to delete product. Invalid ID.'));
    exit;
}

// Check if product exists
$check_stmt = $db->prepare("SELECT id FROM products WHERE id = ?");
$check_stmt->execute([$id]);

if ($check_stmt->rowCount() === 0) {
    // Set response code - 404 Not found
    http_response_code(404);
    
    // Tell the user
    echo json_encode(array('message' => 'Product not found.'));
    exit;
}

// Delete product
try {
    // Prepare query
    $query = "DELETE FROM products WHERE id = ?";
    
    // Prepare statement
    $stmt = $db->prepare($query);
    
    // Bind ID
    $stmt->bindParam(1, $id);
    
    // Execute query
    if ($stmt->execute()) {
        // Set response code - 200 OK
        http_response_code(200);
        
        // Tell the user
        echo json_encode(array('message' => 'Product was deleted successfully.'));
    } else {
        // Set response code - 503 Service unavailable
        http_response_code(503);
        
        // Tell the user
        echo json_encode(array('message' => 'Unable to delete product.'));
    }
} catch (PDOException $e) {
    // Set response code - 503 Service unavailable
    http_response_code(503);
    
    // Tell the user
    echo json_encode(array('message' => 'Database error: ' . $e->getMessage()));
}
?>
