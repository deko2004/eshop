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

// Get query parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Calculate offset
$offset = ($page - 1) * $limit;

// Get orders
$orders_query = "SELECT id, total_amount, status, created_at 
                FROM orders 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";

$orders_stmt = $db->prepare($orders_query);
$orders_stmt->bindParam(1, $user_id);
$orders_stmt->bindParam(2, $limit, PDO::PARAM_INT);
$orders_stmt->bindParam(3, $offset, PDO::PARAM_INT);
$orders_stmt->execute();

// Get total count for pagination
$total_query = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
$total_stmt = $db->prepare($total_query);
$total_stmt->execute([$user_id]);
$total_row = $total_stmt->fetch(PDO::FETCH_ASSOC);
$total = $total_row['total'];

// Calculate total pages
$total_pages = ceil($total / $limit);

// Check if any orders found
if ($orders_stmt->rowCount() > 0) {
    // Orders array
    $orders_arr = array();
    
    // Add pagination info
    $orders_arr['pagination'] = array(
        'total_results' => $total,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'limit' => $limit
    );
    
    // Orders array
    $orders_arr['orders'] = array();
    
    // Fetch orders
    while ($order_row = $orders_stmt->fetch(PDO::FETCH_ASSOC)) {
        // Get order items
        $items_query = "SELECT oi.product_id, oi.quantity, oi.price, p.name, p.image 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?";
        
        $items_stmt = $db->prepare($items_query);
        $items_stmt->execute([$order_row['id']]);
        
        $items = array();
        
        while ($item_row = $items_stmt->fetch(PDO::FETCH_ASSOC)) {
            $item = array(
                'product_id' => $item_row['product_id'],
                'name' => $item_row['name'],
                'quantity' => $item_row['quantity'],
                'price' => $item_row['price'],
                'item_price' => $item_row['price'] * $item_row['quantity'],
                'image' => $item_row['image']
            );
            
            array_push($items, $item);
        }
        
        $order = array(
            'id' => $order_row['id'],
            'total_amount' => $order_row['total_amount'],
            'status' => $order_row['status'],
            'created_at' => $order_row['created_at'],
            'items' => $items,
            'item_count' => count($items)
        );
        
        // Push to "orders"
        array_push($orders_arr['orders'], $order);
    }
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Show orders data
    echo json_encode($orders_arr);
} else {
    // Set response code - 200 OK (no orders is not an error)
    http_response_code(200);
    
    // Tell the user no orders found
    echo json_encode(array(
        'pagination' => array(
            'total_results' => 0,
            'total_pages' => 0,
            'current_page' => $page,
            'limit' => $limit
        ),
        'orders' => array(),
        'message' => 'No orders found.'
    ));
}
?>
