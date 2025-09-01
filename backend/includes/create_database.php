<?php
require_once 'config.php';

try {
    // Connect to MySQL without selecting a database
    $pdo = new PDO('mysql:host=' . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);

    echo "Database created successfully or already exists<br>";

    // Select the database
    $pdo->exec("USE " . DB_NAME);

    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        firstname VARCHAR(50) NOT NULL,
        lastname VARCHAR(50) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        age INT,
        wilaya VARCHAR(50),
        telephone VARCHAR(20),
        address TEXT,
        sex ENUM('male', 'female'),
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    echo "Users table created successfully<br>";

    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        image VARCHAR(255),
        description TEXT,
        features TEXT,
        specifications TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    echo "Products table created successfully<br>";

    // Create orders table
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    echo "Orders table created successfully<br>";

    // Create order_items table
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    echo "Order items table created successfully<br>";

    // Create cart table
    $sql = "CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    echo "Cart table created successfully<br>";


    // Insert default admin user
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);

    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $sql = "INSERT INTO users (firstname, lastname, username, email, password, role)
                VALUES ('Admin', 'User', 'admin', 'admin@techshop.com', :password, 'admin')";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':password', $password_hash);
        $stmt->execute();

        echo "Default admin user created successfully<br>";
    } else {
        echo "Admin user already exists<br>";
    }

    // Import products from products.js
    $products_file = file_get_contents(__DIR__ . '/../../products.js');

    // Extract products array using regex
    preg_match('/const products = (\[.*?\]);/s', $products_file, $matches);

    if (isset($matches[1])) {
        // Convert JS array to PHP array
        $products_js = $matches[1];
        // Replace JS object notation with PHP array notation
        $products_js = preg_replace('/(\w+):/', '"$1":', $products_js);
        // Replace single quotes with double quotes
        $products_js = str_replace("'", '"', $products_js);

        // Evaluate the string as PHP code
        $products = json_decode($products_js, true);

        if ($products) {
            // Check if products table is empty
            $stmt = $pdo->query("SELECT COUNT(*) FROM products");
            $product_count = $stmt->fetchColumn();

            if ($product_count == 0) {
                // Prepare statement for inserting products
                $stmt = $pdo->prepare("INSERT INTO products (id, name, price, image, description, features, specifications)
                                      VALUES (:id, :name, :price, :image, :description, :features, :specifications)");

                foreach ($products as $product) {
                    $features = json_encode($product['features']);
                    $specifications = json_encode($product['specifications']);

                    $stmt->bindParam(':id', $product['id']);
                    $stmt->bindParam(':name', $product['name']);
                    $stmt->bindParam(':price', $product['price']);
                    $stmt->bindParam(':image', $product['image']);
                    $stmt->bindParam(':description', $product['description']);
                    $stmt->bindParam(':features', $features);
                    $stmt->bindParam(':specifications', $specifications);

                    $stmt->execute();
                }

                echo "Products imported successfully<br>";
            } else {
                echo "Products table already has data<br>";
            }
        } else {
            echo "Failed to parse products data<br>";
        }
    } else {
        echo "Could not find products array in products.js<br>";
    }

    echo "Database setup completed successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
