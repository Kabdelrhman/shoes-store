<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$error = '';
$debug_info = ''; // For development purposes

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        try {
            // Check database connection
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT id, username, password, is_admin FROM users WHERE username = ? OR email = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("ss", $username, $username);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                // Debug info - remove in production
                $debug_info = "Attempting login with: Username: $username, Stored Password: {$user['password']}, Provided Password: $password";
                
                if ($password === $user['password']) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    
                    // Redirect based on user type
                    if ($user['is_admin']) {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                } else {
                    $error = "Invalid password";
                }
            } else {
                $error = "User not found";
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = "An error occurred. Please try again later.";
            error_log($e->getMessage());
            $debug_info = $e->getMessage(); // For development purposes
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - E-Commerce Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($debug_info): // Remove this in production ?>
            <div class="debug-info" style="background: #f8f9fa; padding: 10px; margin: 10px 0; font-family: monospace;">
                <?php echo htmlspecialchars($debug_info); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username or Email:</label>
                <input type="text" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <input type="submit" value="Login" class="btn">
            </div>
        </form>
        
        <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
    </div>
</body>
</html>
