<?php
require_once 'inc/config.php';

try {
    // First, let's check if the beauty category exists
    $stmt = $pdo->query("SELECT * FROM categories WHERE type = 'beauty' LIMIT 1");
    $beauty_category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$beauty_category) {
        // Create beauty category if it doesn't exist
        $stmt = $pdo->prepare("INSERT INTO categories (name, type, description) VALUES (?, ?, ?)");
        $stmt->execute(['Beauty Products', 'beauty', 'Makeup and beauty products']);
        $beauty_category_id = $pdo->lastInsertId();
        echo "Created beauty category with ID: $beauty_category_id\n";
    } else {
        $beauty_category_id = $beauty_category['id'];
        echo "Using existing beauty category with ID: $beauty_category_id\n";
    }
    
    // Check if rose lipstick already exists
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ?");
    $stmt->execute(['%rose%lipstick%']);
    $existing_product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_product) {
        echo "Rose lipstick already exists with ID: " . $existing_product['id'] . "\n";
        echo "Product details: " . $existing_product['name'] . " - $" . $existing_product['price'] . "\n";
    } else {
        // Add rose lipstick product
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id, image_url, color_hex) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'Rose Gold Lipstick',
            'Beautiful rose gold lipstick with long-lasting color and moisturizing formula. Perfect for everyday wear and special occasions.',
            24.99,
            50,
            $beauty_category_id,
            'virginia-berbece-y6440BymShk-unsplash.jpg',
            '#FF69B4'
        ]);
        
        $product_id = $pdo->lastInsertId();
        echo "Successfully added Rose Gold Lipstick with ID: $product_id\n";
        echo "Price: $24.99\n";
        echo "Stock: 50\n";
        echo "Image: virginia-berbece-y6440BymShk-unsplash.jpg\n";
    }
    
    // Show all beauty products
    echo "\nAll beauty products in database:\n";
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE c.type = 'beauty'");
    $beauty_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($beauty_products as $product) {
        echo "ID: {$product['id']} | Name: {$product['name']} | Price: \${$product['price']} | Stock: {$product['stock']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
