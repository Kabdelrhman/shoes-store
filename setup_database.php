<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';

try {
    // Create connection
    $conn = new mysqli($db_host, $db_user, $db_pass);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS ecommerce_db";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select the database
    $conn->select_db("ecommerce_db");

    // Create products table
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        description TEXT,
        stock INT DEFAULT 0
    )";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating products table: " . $conn->error);
    }

    // Create orders table
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total_amount DECIMAL(10,2) NOT NULL
    )";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating orders table: " . $conn->error);
    }

    // Create order_items table
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        quantity INT,
        price DECIMAL(10,2),
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating order_items table: " . $conn->error);
    }

    // Insert sample products
    $sql = "INSERT INTO products (name, price, description, stock) VALUES 
        ('Laptop', 999.99, 'High-performance laptop', 10),
        ('Smartphone', 499.99, 'Latest model smartphone', 15),
        ('Headphones', 79.99, 'Wireless headphones', 20)";
    if (!$conn->query($sql)) {
        // Ignore duplicate entry errors
        if ($conn->errno != 1062) {
            throw new Exception("Error inserting sample products: " . $conn->error);
        }
    }

    echo "Database and tables created successfully!";
    
} catch (Exception $e) {
    die("Setup failed: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
