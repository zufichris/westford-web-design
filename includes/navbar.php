<nav class="navbar">
    <div class="container">
        <a href="index.php" class="logo">ABC Supermarket</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="cart.php">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count">0</span>
            </a>
            <?php if(isset($_SESSION['user_id'])): ?>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
            <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>