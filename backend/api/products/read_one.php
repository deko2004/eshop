<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include database
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->getConnection();

// Get ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate ID
if ($id <= 0) {
    // Set response code - 400 Bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array('message' => 'Invalid product ID.'));
    exit;
}

// Prepare query
$query = "SELECT * FROM products WHERE id = :id";

// Prepare statement
$stmt = $db->prepare($query);

// Bind ID
$stmt->bindParam(':id', $id);

// Execute query
$stmt->execute();

// Check if product exists
if ($stmt->rowCount() > 0) {
    // Fetch product
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Extract row
    extract($row);
    
    // Convert JSON strings to arrays
    $features = json_decode($features, true);
    $specifications = json_decode($specifications, true);
    
    // Create product array
    $product_arr = array(
        'id' => $id,
        'name' => $name,
        'price' => $price,
        'image' => $image,
        'description' => $description,
        'features' => $features,
        'specifications' => $specifications,
        'created_at' => $created_at,
        'updated_at' => $updated_at
    );
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Make it json format
    echo json_encode($product_arr);
} else {
    // Set response code - 404 Not found
    http_response_code(404);
    
    // Tell the user product does not exist
    echo json_encode(array('message' => 'Product not found.'));
}
?>
