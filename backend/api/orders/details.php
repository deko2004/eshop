<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include database and functions
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->getConnection();

// Check if user is logged in
if (!is_logged_in()) {
    // Set response code - 401 Unauthorized
    http_response_code(401);
    
    // Tell the user
    echo json_encode(array('message' => 'Unauthorized access. Please login.'));
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate order ID
if ($order_id <= 0) {
    // Set response code - 400 Bad request
    http_response_code(400);
    
    // Tell the user
    echo json_encode(array('message' => 'Invalid order ID.'));
    exit;
}

// Check if order exists and belongs to user
$order_query = "SELECT id, total_amount, status, created_at, updated_at 
               FROM orders 
               WHERE id = ? AND user_id = ?";

$order_stmt = $db->prepare($order_query);
$order_stmt->execute([$order_id, $user_id]);

if ($order_stmt->rowCount() === 0) {
    // Set response code - 404 Not found
    http_response_code(404);
    
    // Tell the user
    echo json_encode(array('message' => 'Order not found.'));
    exit;
}

// Get order details
$order_row = $order_stmt->fetch(PDO::FETCH_ASSOC);

// Get order items
$items_query = "SELECT oi.id, oi.product_id, oi.quantity, oi.price, p.name, p.image, p.description 
               FROM order_items oi 
               JOIN products p ON oi.product_id = p.id 
               WHERE oi.order_id = ?";

$items_stmt = $db->prepare($items_query);
$items_stmt->execute([$order_id]);

$items = array();

while ($item_row = $items_stmt->fetch(PDO::FETCH_ASSOC)) {
    $item = array(
        'id' => $item_row['id'],
        'product_id' => $item_row['product_id'],
        'name' => $item_row['name'],
        'quantity' => $item_row['quantity'],
        'price' => $item_row['price'],
        'item_price' => $item_row['price'] * $item_row['quantity'],
        'image' => $item_row['image'],
        'description' => $item_row['description']
    );
    
    array_push($items, $item);
}

// Create order array
$order = array(
    'id' => $order_row['id'],
    'total_amount' => $order_row['total_amount'],
    'status' => $order_row['status'],
    'created_at' => $order_row['created_at'],
    'updated_at' => $order_row['updated_at'],
    'items' => $items,
    'item_count' => count($items)
);

// Set response code - 200 OK
http_response_code(200);

// Return order details
echo json_encode($order);
?>
