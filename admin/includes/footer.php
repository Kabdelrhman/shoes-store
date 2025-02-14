    </div> <!-- End of admin-content -->
        
        <footer class="admin-footer">
            <div class="footer-content">
                <div class="footer-info">
                    <p>&copy; <?php echo date('Y'); ?> E-Commerce Store. All rights reserved. Devolop by 3bdo group</p>
                </div>
                <div class="footer-links">
                    <a href="../admin/dashboard.php">Dashboard</a>
                    <a href="../admin/manage_products.php">Products</a>
                    <a href="../admin/view_orders.php">Orders</a>
                    <a href="../index.php">Visit Store</a>
                </div>
            </div>
        </footer>

        <style>
            .admin-footer {
                margin-top: 40px;
                padding: 20px 0;
                background: #2c3e50;
                color: white;
            }

            .footer-content {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .footer-info p {
                margin: 0;
                font-size: 14px;
            }

            .footer-links {
                display: flex;
                gap: 20px;
            }

            .footer-links a {
                color: white;
                text-decoration: none;
                font-size: 14px;
                opacity: 0.8;
                transition: opacity 0.3s;
            }

            .footer-links a:hover {
                opacity: 1;
            }

            @media (max-width: 768px) {
                .footer-content {
                    flex-direction: column;
                    text-align: center;
                    gap: 15px;
                }
            }
        </style>
    </body>
</html>
