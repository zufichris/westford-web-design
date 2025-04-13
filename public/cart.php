<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            if ($quantity > 0) {
                $query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "iii", $quantity, $user_id, $product_id);
                mysqli_stmt_execute($stmt);
            } else {
                $query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
                mysqli_stmt_execute($stmt);
            }
        }
    } elseif (isset($_POST['checkout'])) {
        // Create order
        $query = "INSERT INTO orders (user_id, total_amount) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "id", $user_id, $_POST['total_amount']);
        mysqli_stmt_execute($stmt);
        $order_id = mysqli_insert_id($conn);

        // Add order items
        $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                 SELECT ?, c.product_id, c.quantity, p.price 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
        mysqli_stmt_execute($stmt);

        // Clear cart
        $query = "DELETE FROM cart WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        header('Location: order_confirmation.php?id=' . $order_id);
        exit();
    }
}

// Get cart items
$query = "SELECT c.*, p.name, p.price, p.image_url 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$cart_items = mysqli_stmt_get_result($stmt);

// Calculate total
$total = 0;
while ($item = mysqli_fetch_assoc($cart_items)) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - E-Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .cart-container {
        max-width: 1200px;
        margin: 100px auto;
        padding: 2rem;
    }

    .cart-items {
        margin-bottom: 2rem;
    }

    .cart-item {
        display: grid;
        grid-template-columns: 100px 1fr auto auto;
        gap: 1rem;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #ddd;
    }

    .cart-item img {
        width: 100px;
        height: 100px;
        object-fit: cover;
    }

    .quantity-input {
        width: 60px;
        padding: 0.5rem;
        text-align: center;
    }

    .remove-item {
        color: #dc3545;
        cursor: pointer;
    }

    .cart-summary {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 10px;
    }

    .cart-summary h3 {
        margin-bottom: 1rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .total {
        font-size: 1.2rem;
        font-weight: bold;
        border-top: 1px solid #ddd;
        padding-top: 1rem;
    }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="cart-container">
        <h1>Shopping Cart</h1>

        <form method="POST" action="">
            <div class="cart-items">
                <?php
                mysqli_data_seek($cart_items, 0);
                while ($item = mysqli_fetch_assoc($cart_items)):
                ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                        alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div>
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
                    </div>
                    <input type="number" name="quantity[<?php echo $item['product_id']; ?>]"
                        value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input">
                    <span class="remove-item" onclick="removeItem(<?php echo $item['product_id']; ?>)">
                        <i class="fas fa-trash"></i>
                    </span>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>

                <input type="hidden" name="total_amount" value="<?php echo $total; ?>">

                <div class="cart-actions">
                    <button type="submit" name="update_cart" class="btn">Update Cart</button>
                    <button type="submit" name="checkout" class="btn"
                        <?php echo $total == 0 ? 'disabled' : ''; ?>>Proceed to Checkout</button>
                </div>
            </div>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
    function removeItem(productId) {
        if (confirm('Are you sure you want to remove this item?')) {
            const input = document.querySelector(`input[name="quantity[${productId}]"]`);
            input.value = 0;
            input.form.submit();
        }
    }
    </script>
</body>

</html>