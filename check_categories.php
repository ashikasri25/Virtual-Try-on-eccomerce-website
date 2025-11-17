<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

echo "<h2>Category Check</h2>";
echo "<pre>";

// Get all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id");
$categories = $stmt->fetchAll();

echo "Categories in Database:\n";
echo "=======================\n";
foreach ($categories as $cat) {
    echo "ID: {$cat['id']} | Name: {$cat['name']} | Type: {$cat['type']}\n";
}

echo "\n\nProducts with Categories:\n";
echo "========================\n";
$stmt = $pdo->query("SELECT p.id, p.name, p.category_id, c.name as category_name, c.type as category_type 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.id");
$products = $stmt->fetchAll();

foreach ($products as $prod) {
    echo "Product ID: {$prod['id']} | Name: {$prod['name']} | Category ID: {$prod['category_id']} | Category: {$prod['category_name']} ({$prod['category_type']})\n";
}

echo "</pre>";
?>
