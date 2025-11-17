<?php
require_once 'inc/config.php';

echo "Checking dresses in database:\n\n";

$stmt = $pdo->query("SELECT id, name, image_url, category FROM dresses WHERE active = 1");
$dresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($dresses) . " active dresses:\n\n";

foreach ($dresses as $dress) {
    echo "ID: {$dress['id']}\n";
    echo "Name: {$dress['name']}\n";
    echo "Image URL: {$dress['image_url']}\n";
    echo "Category: {$dress['category']}\n";
    
    // Check if image file exists
    if (file_exists($dress['image_url'])) {
        echo "✓ Image file exists\n";
    } else {
        echo "✗ Image file NOT found at: {$dress['image_url']}\n";
    }
    echo "\n";
}
?>
