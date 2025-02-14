<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - E-Commerce Store</title>
    <link rel="stylesheet" href="../../style.css">
    <style>
        .admin-nav {
            background: #2c3e50;
            padding: 15px 0;
        }
        .admin-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .admin-nav a:hover {
            background: #34495e;
        }
        .admin-nav .active {
            background: #3498db;
        }
        .admin-header {
            background: #34495e;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .admin-content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
    </div>
    <nav class="admin-nav">
        <ul>
            <li><a href="../admin/dashboard.php">Dashboard</a></li>
            <li><a href="../admin/manage_products.php">Manage Products</a></li>
            <li><a href="../admin/view_orders.php">View Orders</a></li>
            <li><a href="../admin/edit_profile.php">Edit Profile</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="admin-content">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="message success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="message error"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
