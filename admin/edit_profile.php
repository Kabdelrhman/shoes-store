<?php
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate current password
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!password_verify($current_password, $user['password'])) {
        $_SESSION['error_message'] = "Current password is incorrect";
    } else {
        // Update username and email
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $email, $user_id);
        $stmt->execute();
        
        // Update password if provided
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $_SESSION['error_message'] = "New passwords do not match";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();
            }
        }
        
        $_SESSION['success_message'] = "Profile updated successfully";
        header("Location: edit_profile.php");
        exit;
    }
}

// Get current user data
$sql = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<div class="profile-form">
    <h2>Edit Profile</h2>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Current Password:</label>
            <input type="password" name="current_password" required>
        </div>
        
        <div class="form-group">
            <label>New Password:</label>
            <input type="password" name="new_password">
            <small>Leave blank to keep current password</small>
        </div>
        
        <div class="form-group">
            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password">
        </div>
        
        <div class="form-group">
            <input type="submit" value="Update Profile" class="btn">
        </div>
    </form>
</div>

<style>
.profile-form {
    max-width: 500px;
    margin: 0 auto;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-form h2 {
    margin-top: 0;
    color: #2c3e50;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #666;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-group small {
    color: #666;
    font-size: 12px;
}

.btn {
    background: #3498db;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn:hover {
    background: #2980b9;
}
</style>

<?php require_once 'includes/footer.php'; ?>
