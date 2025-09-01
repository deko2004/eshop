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

// Get query parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Calculate offset
$offset = ($page - 1) * $limit;

// Prepare query
$query = "SELECT * FROM products";

// Add search condition if search parameter is provided
if (!empty($search)) {
    $query .= " WHERE name LIKE :search OR description LIKE :search";
}

// Add pagination
$query .= " LIMIT :limit OFFSET :offset";

// Prepare statement
$stmt = $db->prepare($query);

// Bind parameters
if (!empty($search)) {
    $search_param = "%{$search}%";
    $stmt->bindParam(':search', $search_param);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

// Execute query
$stmt->execute();

// Get row count
$num = $stmt->rowCount();

// Check if any products found
if ($num > 0) {
    // Products array
    $products_arr = array();
    
    // Get total count for pagination
    $total_query = "SELECT COUNT(*) as total FROM products";
    if (!empty($search)) {
        $total_query .= " WHERE name LIKE :search OR description LIKE :search";
    }
    
    $total_stmt = $db->prepare($total_query);
    if (!empty($search)) {
        $total_stmt->bindParam(':search', $search_param);
    }
    $total_stmt->execute();
    $total_row = $total_stmt->fetch(PDO::FETCH_ASSOC);
    $total = $total_row['total'];
    
    // Calculate total pages
    $total_pages = ceil($total / $limit);
    
    // Add pagination info
    $products_arr['pagination'] = array(
        'total_results' => $total,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'limit' => $limit
    );
    
    // Products array
    $products_arr['products'] = array();
    
    // Fetch products
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Extract row
        extract($row);
        
        // Convert JSON strings to arrays
        $features = json_decode($features, true);
        $specifications = json_decode($specifications, true);
        
        $product_item = array(
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
        
        // Push to "products"
        array_push($products_arr['products'], $product_item);
    }
    
    // Set response code - 200 OK
    http_response_code(200);
    
    // Show products data
    echo json_encode($products_arr);
} else {
    // Set response code - 404 Not found
    http_response_code(404);
    
    // Tell the user no products found
    echo json_encode(
        array('message' => 'No products found.')
    );
}
?>
