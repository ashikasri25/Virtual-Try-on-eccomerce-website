<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

echo "<h2>Adding Dress Products to Database</h2>";
echo "<pre>";

try {
    // First, check if we have a dress/clothing category
    $stmt = $pdo->query("SELECT * FROM categories WHERE type IN ('dress', 'clothing') ORDER BY id");
    $categories = $stmt->fetchAll();
    
    echo "Available clothing categories:\n";
    foreach ($categories as $cat) {
        echo "ID: {$cat['id']} - {$cat['name']} ({$cat['type']})\n";
    }
    echo "\n";
    
    // Get or create a dress category
    $dress_category_id = null;
    foreach ($categories as $cat) {
        if (strtolower($cat['name']) == 'dresses' || strtolower($cat['name']) == 'dress') {
            $dress_category_id = $cat['id'];
            break;
        }
    }
    
    // If no dress category exists, create one
    if (!$dress_category_id) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, type) VALUES (?, ?)");
        $stmt->execute(['Dresses', 'dress']);
        $dress_category_id = $pdo->lastInsertId();
        echo "Created new category: Dresses (ID: $dress_category_id)\n\n";
    } else {
        echo "Using existing dress category (ID: $dress_category_id)\n\n";
    }
    
    // Add dress products
    $dress_products = [
        [
            'name' => 'Elegant Red Evening Dress',
            'description' => 'Beautiful red evening dress perfect for special occasions. Features a flowing design with elegant cut.',
            'price' => 89.99,
            'stock' => 25,
            'image_url' => 'red-dress.jpg',
            'color_hex' => null  // Dresses don't need color_hex
        ],
        [
            'name' => 'Summer Floral Dress',
            'description' => 'Light and comfortable floral dress ideal for summer days. Breathable fabric with beautiful floral pattern.',
            'price' => 49.99,
            'stock' => 40,
            'image_url' => 'floral-dress.jpg',
            'color_hex' => null
        ],
        [
            'name' => 'Classic Black Cocktail Dress',
            'description' => 'Timeless black cocktail dress suitable for any formal event. Elegant and sophisticated design.',
            'price' => 79.99,
            'stock' => 30,
            'image_url' => 'black-dress.jpg',
            'color_hex' => null
        ],
        [
            'name' => 'Blue Casual Sundress',
            'description' => 'Comfortable blue sundress perfect for casual outings. Light fabric with a relaxed fit.',
            'price' => 39.99,
            'stock' => 35,
            'image_url' => 'blue-sundress.jpg',
            'color_hex' => null
        ],
        [
            'name' => 'White Summer Dress',
            'description' => 'Pure white summer dress with delicate lace details. Perfect for beach vacations and summer parties.',
            'price' => 59.99,
            'stock' => 20,
            'image_url' => 'white-dress.jpg',
            'color_hex' => null
        ]
    ];
    
    echo "Adding dress products:\n";
    echo "======================\n";
    
    foreach ($dress_products as $product_data) {
        // Check if product already exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE name = ?");
        $stmt->execute([$product_data['name']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            echo "✓ Product '{$product_data['name']}' already exists (ID: {$existing['id']})\n";
        } else {
            // Add new product
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id, image_url, color_hex, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $product_data['name'],
                $product_data['description'],
                $product_data['price'],
                $product_data['stock'],
                $dress_category_id,
                $product_data['image_url'],
                $product_data['color_hex']
            ]);
            
            $product_id = $pdo->lastInsertId();
            echo "✓ Added: {$product_data['name']} (ID: $product_id)\n";
            echo "  Price: \${$product_data['price']} | Stock: {$product_data['stock']}\n";
            echo "  Image: {$product_data['image_url']}\n\n";
        }
    }
    
    // Show all dress products
    echo "\n\nAll Dress Products in Database:\n";
    echo "================================\n";
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.type as category_type 
                           FROM products p 
                           JOIN categories c ON p.category_id = c.id 
                           WHERE c.type IN ('dress', 'clothing')
                           ORDER BY p.id DESC");
    $stmt->execute();
    $all_dresses = $stmt->fetchAll();
    
    foreach ($all_dresses as $dress) {
        echo "ID: {$dress['id']} - {$dress['name']}\n";
        echo "  Category: {$dress['category_name']} ({$dress['category_type']})\n";
        echo "  Price: \${$dress['price']} | Stock: {$dress['stock']}\n";
        echo "  Image: {$dress['image_url']}\n\n";
    }
    
    echo "\n✅ DRESS PRODUCTS ADDED SUCCESSFULLY!\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>

<div style="margin: 20px;">
    <h3>Next Steps:</h3>
    <ol>
        <li>Upload dress images to <code>/assets/images/products/</code> folder</li>
        <li>Or use placeholder images for testing</li>
        <li>Go to the <a href="index.php">Home Page</a> to see the new products</li>
        <li>Go to <a href="tryon/">Try-On Page</a> to test the dress overlay</li>
    </ol>
    
    <div style="margin-top: 20px;">
        <a href="index.php" style="padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;">View Products</a>
        <a href="tryon/" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;">Test Try-On</a>
        <a href="admin/products.php" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;">Manage Products</a>
    </div>
</div>
