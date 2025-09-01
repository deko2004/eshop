<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Include database
require_once 'includes/database.php';

// API information
$api_info = array(
    'name' => 'TechShop API',
    'version' => '1.0.0',
    'description' => 'RESTful API for TechShop e-commerce website',
    'endpoints' => array(
        'users' => array(
            'register' => '/backend/api/users/register.php',
            'login' => '/backend/api/users/login.php',
            'logout' => '/backend/api/users/logout.php',
            'profile' => '/backend/api/users/profile.php',
            'change_password' => '/backend/api/users/change_password.php'
        ),
        'products' => array(
            'read' => '/backend/api/products/read.php',
            'read_one' => '/backend/api/products/read_one.php',
            'create' => '/backend/api/products/create.php (Admin only)',
            'update' => '/backend/api/products/update.php (Admin only)',
            'delete' => '/backend/api/products/delete.php (Admin only)'
        ),
        'orders' => array(
            'checkout' => '/backend/api/orders/checkout.php',
            'history' => '/backend/api/orders/history.php',
            'details' => '/backend/api/orders/details.php'
        ),
        'cart' => array(
            'add' => '/backend/api/cart/add.php',
            'update' => '/backend/api/cart/update.php',
            'remove' => '/backend/api/cart/remove.php',
            'get' => '/backend/api/cart/get.php',
            'clear' => '/backend/api/cart/clear.php'
        )
    )
);

// Return API information
echo json_encode($api_info);
