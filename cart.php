<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

// Migrate any session cart to database
migrateSessionCartToDB();

// Get cart items
$cart = getCartItems();

// Temporary debug: view cart session and resolved items via cart.php?debug=1
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    if (isset($_GET['seed']) && $_GET['seed'] == '1') {
        // Seed session with a test item to verify session persistence quickly
        $_SESSION['cart'][1] = ($_SESSION['cart'][1] ?? 0) + 1;
        $cart = getCartItems();
    }
    echo '<pre style="background:#111;color:#0f0;padding:10px;border-radius:6px;">';
    echo "session_id: " . session_id() . "\n";
    echo "SESSION cart (raw):\n";
    echo print_r($_SESSION['cart'] ?? [], true);
    echo "\nResolved cart (getCartItems):\n";
    echo print_r($cart, true);
    echo '</pre>';
}

// Handle remove single item
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    if (removeFromCartDB($product_id)) {
        $_SESSION['cart_message'] = "Item removed from cart.";
        $_SESSION['cart_message_type'] = "info";
    }
    header("Location: cart.php");
    exit();
}

// Handle clear cart
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    if (clearCartDB()) {
        $_SESSION['cart_message'] = "Cart cleared successfully.";
        $_SESSION['cart_message_type'] = "info";
    }
    header("Location: cart.php");
    exit();
}

// Update cart if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
            foreach ($_POST['quantity'] as $product_id => $quantity) {
                $product_id = intval($product_id);
                $quantity = intval($quantity);
                updateCartItemDB($product_id, $quantity);
            }
            $_SESSION['cart_message'] = "Cart updated successfully.";
            $_SESSION['cart_message_type'] = "success";
        }
        header("Location: cart.php");
        exit();
    } elseif (isset($_POST['checkout'])) {
        // Ensure user is logged in before checkout
        if (!isLoggedIn()) {
            // Store intended destination in session
            $_SESSION['redirect_after_login'] = 'checkout.php';
            header("Location: auth/login.php?message=Please login to proceed with checkout");
            exit();
        }
        header("Location: checkout.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - GlamWear</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <?php include 'partials/header.php'; ?>

    <!-- Cart -->
    <div class="container py-5">
        <h1 class="mb-4">Shopping Cart</h1>
        
        <?php if (isset($_SESSION['cart_message'])): ?>
        <div class="alert alert-<?= $_SESSION['cart_message_type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['cart_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
        unset($_SESSION['cart_message']);
        unset($_SESSION['cart_message_type']);
        endif; ?>
        
        <?php if (empty($cart['items'])): ?>
            <div class="alert alert-info">
                Your cart is empty. <a href="index.php">Continue shopping</a>
            </div>
        <?php else: ?>
            <form method="post">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <?php foreach ($cart['items'] as $item): ?>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-md-2">
                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($item['name']) ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <h5><a href="product.php?id=<?= $item['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($item['name']) ?></a></h5>
                                            <p class="text-muted mb-1"><?= htmlspecialchars($item['category_name']) ?></p>
                                            <p class="fw-bold">$<?= number_format($item['price'], 2) ?></p>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(<?= $item['id'] ?>, -1)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" class="form-control text-center" id="qty_<?= $item['id'] ?>" name="quantity[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" style="max-width: 80px;">
                                                <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(<?= $item['id'] ?>, 1)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <p class="fw-bold mb-0">$<?= number_format($item['subtotal'], 2) ?></p>
                                        </div>
                                        <div class="col-md-1">
                                            <a href="?remove=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this item from cart?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <?php if ($item !== end($cart['items'])): ?>
                                    <hr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <div class="mt-3">
                                    <button type="submit" name="update_cart" class="btn btn-primary">
                                        <i class="fas fa-sync me-2"></i>Update Cart
                                    </button>
                                    <a href="?clear=1" class="btn btn-outline-danger ms-2" onclick="return confirm('Clear all items from cart?')">
                                        <i class="fas fa-trash-alt me-2"></i>Clear Cart
                                    </a>
                                    <a href="index.php" class="btn btn-outline-secondary ms-2">
                                        <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Order Summary</h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>$<?= $cart['total'] ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Shipping:</span>
                                    <span>$0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Tax:</span>
                                    <span>$<?= number_format($cart['total'] * 0.1, 2) ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <strong>$<?= number_format($cart['total'] * 1.1, 2) ?></strong>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary" name="checkout">Proceed to Checkout</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include 'partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
    // Update quantity function
    function updateQuantity(productId, change) {
        const input = document.getElementById('qty_' + productId);
        let currentValue = parseInt(input.value) || 1;
        let newValue = currentValue + change;
        
        // Check bounds
        const min = parseInt(input.min) || 1;
        const max = parseInt(input.max) || 999;
        
        if (newValue < min) newValue = min;
        if (newValue > max) newValue = max;
        
        input.value = newValue;
    }
    
    // Auto-submit on quantity change
    document.querySelectorAll('input[name^="quantity"]').forEach(input => {
        input.addEventListener('change', function() {
            // Optional: auto-submit form when quantity changes
            // this.form.submit();
        });
    });
    
    // Removed localStorage sync to improve performance
    // Cart is now managed server-side only
    </script>
</body>
</html>