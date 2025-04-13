<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get order ID from URL
if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$order_id = $_GET['order_id'];

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit();
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name as product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order_items = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Modern E-Commerce</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .confirmation-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .confirmation-card {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .confirmation-icon {
        font-size: 4rem;
        color: #28a745;
        margin-bottom: 1rem;
    }

    .confirmation-title {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .confirmation-message {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 2rem;
    }

    .order-details {
        text-align: left;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #eee;
    }

    .order-details h2 {
        margin-bottom: 1rem;
    }

    .order-info {
        margin-bottom: 1.5rem;
    }

    .order-info p {
        margin: 0.5rem 0;
    }

    .order-items {
        margin: 1.5rem 0;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #eee;
    }

    .order-total {
        font-size: 1.25rem;
        font-weight: bold;
        margin-top: 1rem;
        text-align: right;
    }

    .btn-continue {
        display: inline-block;
        padding: 1rem 2rem;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        margin-top: 2rem;
        transition: background-color 0.3s;
    }

    .btn-continue:hover {
        background-color: #0056b3;
    }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="confirmation-container">
        <div class="confirmation-card">
            <i class="fas fa-check-circle confirmation-icon"></i>
            <h1 class="confirmation-title">Thank You for Your Order!</h1>
            <p class="confirmation-message">Your order has been successfully placed. We'll send you an email
                confirmation shortly.</p>

            <div class="order-details">
                <h2>Order Details</h2>

                <div class="order-info">
                    <p><strong>Order Number:</strong> #<?php echo $order['id']; ?></p>
                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                    <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                </div>

                <h2>Shipping Information</h2>
                <div class="order-info">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['user_email']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                    <p><strong>City:</strong> <?php echo htmlspecialchars($order['shipping_city']); ?></p>
                    <p><strong>State:</strong> <?php echo htmlspecialchars($order['shipping_state']); ?></p>
                    <p><strong>ZIP Code:</strong> <?php echo htmlspecialchars($order['shipping_zip']); ?></p>
                </div>

                <h2>Order Items</h2>
                <div class="order-items">
                    <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <span><?php echo htmlspecialchars($item['product_name']); ?> x
                            <?php echo $item['quantity']; ?></span>
                        <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-total">
                    Total: $<?php echo number_format($order['total_amount'], 2); ?>
                </div>
            </div>

            <a href="products.php" class="btn-continue">Continue Shopping</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>