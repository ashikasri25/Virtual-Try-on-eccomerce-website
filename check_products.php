<?php
// Check if products exist in database
require_once 'inc/config.php';
require_once 'inc/functions.php';

echo "<h2>Product Check</h2>";
echo "<pre>";

// Get all products
$products = getProducts();

echo "Total products in database: " . count($products) . "\n\n";

if (count($products) > 0) {
    echo "Products found:\n";
    foreach ($products as $product) {
        echo "ID: " . $product['id'] . " - " . $product['name'] . " (Stock: " . $product['stock'] . ", Price: $" . $product['price'] . ")\n";
    }
} else {
    echo "No products found in database.\n";
    echo "You may need to:\n";
    echo "1. Import the database schema\n";
    echo "2. Add products via the admin panel\n";
}

echo "\n\nDatabase connection test:\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database:\n";
    foreach ($tables as $table) {
        echo "  - " . $table . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";

echo "<hr>";
echo "<p><a href='index.php'>Go to Home</a> | <a href='cart.php'>Go to Cart</a> | <a href='test_cart.php'>Test Cart Functions</a></p>";
?>
