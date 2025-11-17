<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

requireLogin();

// Get cart items
$cart = getCartItems();

// Check if cart is empty
if (empty($cart['items'])) {
    header("Location: cart.php");
    exit();
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = sanitize($_POST['shipping_address']);
    $total = $cart['total'] * 1.1; // Add 10% tax
    
    // Create order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, shipping_address) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $total, $shipping_address]);
    $order_id = $pdo->lastInsertId();
    
    // Add order items
    foreach ($cart['items'] as $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        
        // Update product stock
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['id']]);
    }
    
    // Clear cart
    unset($_SESSION['cart']);
    
    // Redirect to order confirmation
    header("Location: orders.php?order_id=" . $order_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GlamWear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <?php include 'partials/header.php'; ?>

    <!-- Checkout -->
    <div class="container py-5">
        <h1 class="mb-4">Checkout</h1>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">Shipping Address</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="4" required></textarea>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the terms and conditions
                                </label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Place Order</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cart['items'] as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <h6 class="mb-0"><?= $item['name'] ?></h6>
                                    <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                                </div>
                                <span>$<?= $item['subtotal'] ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-1">
                            <span>Subtotal:</span>
                            <span>$<?= $cart['total'] ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-1">
                            <span>Tax (10%):</span>
                            <span>$<?= number_format($cart['total'] * 0.1, 2) ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3 fw-bold">
                            <span>Total:</span>
                            <span>$<?= number_format($cart['total'] * 1.1, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>