<?php
require_once 'inc/config.php';

try {
    // First, let's check if the beauty category exists
    $stmt = $pdo->query("SELECT * FROM categories WHERE type = 'beauty' LIMIT 1");
    $beauty_category = $pdo->query("SELECT * FROM categories WHERE type = 'beauty' LIMIT 1");
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
    
    // Define beauty products to add
    $beauty_products = [
        [
            'name' => 'Rose Gold Lipstick',
            'description' => 'Beautiful rose gold lipstick with long-lasting color and moisturizing formula. Perfect for everyday wear and special occasions.',
            'price' => 24.99,
            'stock' => 50,
            'image_url' => 'virginia-berbece-y6440BymShk-unsplash.jpg',
            'color_hex' => '#FF69B4'
        ],
        [
            'name' => 'Classic Red Lipstick',
            'description' => 'Timeless classic red lipstick with bold, vibrant color. Long-lasting and perfect for making a statement.',
            'price' => 22.99,
            'stock' => 45,
            'image_url' => 'edz-norton-20h-C0vaNBA-unsplash.jpg',
            'color_hex' => '#DC143C'
        ],
        [
            'name' => 'Nude Lipstick',
            'description' => 'Elegant nude lipstick for a natural, everyday look. Moisturizing formula with subtle shine.',
            'price' => 19.99,
            'stock' => 60,
            'image_url' => 'OIP (1).jpeg',
            'color_hex' => '#DEB887'
        ],
        [
            'name' => 'Pink Blush',
            'description' => 'Soft pink blush for a natural, rosy glow. Buildable coverage for any occasion.',
            'price' => 18.99,
            'stock' => 40,
            'image_url' => 'default.jpg',
            'color_hex' => '#FFB6C1'
        ],
        [
            'name' => 'Black Eyeliner',
            'description' => 'Professional black eyeliner for precise application. Waterproof and long-lasting.',
            'price' => 16.99,
            'stock' => 55,
            'image_url' => 'default.jpg',
            'color_hex' => '#000000'
        ]
    ];
    
    $added_count = 0;
    
    foreach ($beauty_products as $product_data) {
        // Check if product already exists
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name = ?");
        $stmt->execute([$product_data['name']]);
        $existing_product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_product) {
            echo "Product '{$product_data['name']}' already exists with ID: {$existing_product['id']}\n";
        } else {
            // Add new product
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id, image_url, color_hex) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $product_data['name'],
                $product_data['description'],
                $product_data['price'],
                $product_data['stock'],
                $beauty_category_id,
                $product_data['image_url'],
                $product_data['color_hex']
            ]);
            
            $product_id = $pdo->lastInsertId();
            echo "✅ Added: {$product_data['name']} - ID: $product_id - Price: \${$product_data['price']}\n";
            $added_count++;
        }
    }
    
    echo "\n📊 Summary: Added $added_count new beauty products\n";
    
    // Show all beauty products
    echo "\n🎨 All beauty products in database:\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE c.type = 'beauty' ORDER BY p.name");
    $beauty_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($beauty_products) > 0) {
        foreach ($beauty_products as $product) {
            echo "ID: {$product['id']} | Name: {$product['name']} | Price: \${$product['price']} | Stock: {$product['stock']} | Image: {$product['image_url']}\n";
        }
    } else {
        echo "No beauty products found in database.\n";
    }
    
    echo "\n🚀 Your beauty products are now ready! Visit your home page to see them.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
