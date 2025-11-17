<?php
// Add to cart handler
require_once 'inc/config.php';
require_once 'inc/functions.php';

// Debug mode
$debug = isset($_GET['debug']) ? true : false;
if ($debug) {
    echo "<pre>";
    echo "Session ID: " . session_id() . "\n";
    echo "POST data: ";
    print_r($_POST);
    echo "GET data: ";
    print_r($_GET);
    echo "</pre>";
}

// Check if product_id and quantity are provided
if (isset($_POST['product_id']) || isset($_GET['product_id'])) {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : intval($_GET['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : (isset($_GET['quantity']) ? intval($_GET['quantity']) : 1);
    
    // Validate quantity
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    // Check if product exists
    $product = getProduct($product_id);
    if ($product && $product['stock'] > 0) {
        // Add to cart
        $result = addToCart($product_id, $quantity);
        
        if ($result) {
            // Set success message
            $_SESSION['cart_message'] = "Product added to cart successfully!";
            $_SESSION['cart_message_type'] = "success";
        } else {
            $_SESSION['cart_message'] = "Failed to add product to cart. Please try again.";
            $_SESSION['cart_message_type'] = "warning";
        }
    } else {
        $_SESSION['cart_message'] = "Product not available or out of stock.";
        $_SESSION['cart_message_type'] = "danger";
    }
    
    // Redirect back to the referring page or to cart
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'cart.php';
    header("Location: " . $redirect);
    exit();
} else {
    // No product specified, redirect to home
    header("Location: index.php");
    exit();
}
?>
