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
    json_response(false, 'Unauthorized access. Please login.');
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

// Check if data is complete
if (!isset($data->product_id)) {
    json_response(false, 'Missing required fields');
}

// Get product details
$product_id = sanitize_input($data->product_id);

$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);

if ($stmt->rowCount() === 0) {
    json_response(false, 'Product not found');
}

$product = $stmt->fetch(PDO::FETCH_ASSOC);
$product_price = $product['price'];

try {
    // Start transaction
    $db->beginTransaction();

    // Create order
    $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$user_id, $product_price]);
    $order_id = $db->lastInsertId();

    // Add order item
    $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, 1, ?)");
    $stmt->execute([$order_id, $product_id, $product_price]);

    // Commit transaction
    $db->commit();

    // Get order details
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get order items
    $stmt = $db->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response
    $order_data = array(
        'id' => $order['id'],
        'total_amount' => $order['total_amount'],
        'status' => $order['status'],
        'created_at' => $order['created_at'],
        'items' => $items
    );

    json_response(true, 'Order created successfully', $order_data);
} catch (PDOException $e) {
    // Rollback transaction on error
    $db->rollBack();
    json_response(false, 'Database error: ' . $e->getMessage());
}
?>
