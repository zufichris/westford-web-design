<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout');
    exit();
}

// Get cart items
$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cart_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($cart_ids) - 1) . '?';
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($cart_ids)), ...$cart_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($product = $result->fetch_assoc()) {
        $quantity = $_SESSION['cart'][$product['id']];
        $product['quantity'] = $quantity;
        $product['subtotal'] = $product['price'] * $quantity;
        $cart_items[] = $product;
        $total += $product['subtotal'];
    }
} else {
    header('Location: cart.php');
    exit();
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $card_number = $_POST['card_number'];
    $card_expiry = $_POST['card_expiry'];
    $card_cvv = $_POST['card_cvv'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address, shipping_city, shipping_state, shipping_zip) VALUES (?, ?, 'pending', ?, ?, ?, ?)");
        $stmt->bind_param("idssss", $_SESSION['user_id'], $total, $address, $city, $state, $zip);
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // Add order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
            
            // Update product stock
            $update_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $update_stmt->bind_param("ii", $item['quantity'], $item['id']);
            $update_stmt->execute();
        }
        
        // Clear cart
        unset($_SESSION['cart']);
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to order confirmation
        header("Location: order_confirmation.php?order_id=$order_id");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error = "An error occurred while processing your order. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Modern E-Commerce</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .checkout-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    .checkout-form {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .form-group input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    .order-summary {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 2rem;
    }

    .order-items {
        margin: 1rem 0;
        border-bottom: 1px solid #eee;
        padding-bottom: 1rem;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .total {
        font-size: 1.25rem;
        font-weight: bold;
        margin-top: 1rem;
    }

    .btn-checkout {
        width: 100%;
        padding: 1rem;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .btn-checkout:hover {
        background-color: #0056b3;
    }

    .error {
        color: #dc3545;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .checkout-container {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="checkout-container">
        <div class="checkout-form">
            <h1>Checkout</h1>
            <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="checkout.php">
                <h2>Shipping Information</h2>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" required>
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" required>
                </div>

                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" required>
                </div>

                <div class="form-group">
                    <label for="zip">ZIP Code</label>
                    <input type="text" id="zip" name="zip" required>
                </div>

                <h2>Payment Information</h2>
                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" required>
                </div>

                <div class="form-group">
                    <label for="card_expiry">Expiry Date (MM/YY)</label>
                    <input type="text" id="card_expiry" name="card_expiry" required>
                </div>

                <div class="form-group">
                    <label for="card_cvv">CVV</label>
                    <input type="text" id="card_cvv" name="card_cvv" required>
                </div>

                <button type="submit" class="btn-checkout">Complete Purchase</button>
            </form>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="order-items">
                <?php foreach ($cart_items as $item): ?>
                <div class="order-item">
                    <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                    <span>$<?php echo number_format($item['subtotal'], 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="total">
                Total: $<?php echo number_format($total, 2); ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
    // Add input masking for card number and expiry date
    document.getElementById('card_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{4})/g, '$1 ').trim();
        e.target.value = value;
    });

    document.getElementById('card_expiry').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.slice(0, 2) + '/' + value.slice(2);
        }
        e.target.value = value;
    });

    document.getElementById('card_cvv').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });
    </script>
</body>

</html>