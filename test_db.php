<?php
require_once 'config.php';

echo "<h2>Testing Database Connection</h2>";

try {
    // Test connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "Database connection successful!<br><br>";

    // Check if users table exists and has records
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
    
    if ($result) {
        echo "<h3>Users in database:</h3>";
        echo "<pre>";
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . "\n";
            echo "Username: " . $row['username'] . "\n";
            echo "Email: " . $row['email'] . "\n";
            echo "Password: " . $row['password'] . "\n";
            echo "Is Admin: " . ($row['is_admin'] ? 'Yes' : 'No') . "\n";
            echo "Created At: " . $row['created_at'] . "\n";
            echo "------------------------\n";
        }
        echo "</pre>";
    } else {
        echo "Error: " . $conn->error;
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
