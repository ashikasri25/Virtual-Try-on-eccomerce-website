<?php
// Create placeholder dress images for testing

$dress_images = [
    'red-dress.jpg' => ['color' => '#DC143C', 'name' => 'Red Dress'],
    'floral-dress.jpg' => ['color' => '#FFB6C1', 'name' => 'Floral'],
    'black-dress.jpg' => ['color' => '#000000', 'name' => 'Black'],
    'blue-sundress.jpg' => ['color' => '#4169E1', 'name' => 'Blue'],
    'white-dress.jpg' => ['color' => '#FFFFFF', 'name' => 'White']
];

$upload_dir = __DIR__ . '/assets/images/products/';

echo "<h2>Creating Placeholder Dress Images</h2>";
echo "<pre>";

foreach ($dress_images as $filename => $data) {
    $filepath = $upload_dir . $filename;
    
    // Check if file already exists
    if (file_exists($filepath)) {
        echo "✓ Image already exists: $filename\n";
        continue;
    }
    
    // Create a simple dress image
    $width = 400;
    $height = 500;
    $image = imagecreatetruecolor($width, $height);
    
    // White background
    $bg_color = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $bg_color);
    
    // Parse hex color
    $hex = str_replace('#', '', $data['color']);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Dress color
    $dress_color = imagecolorallocate($image, $r, $g, $b);
    
    // Draw a simple dress shape
    // Top part (bodice)
    imagefilledpolygon($image, [
        150, 100,  // left shoulder
        250, 100,  // right shoulder
        270, 200,  // right waist
        130, 200   // left waist
    ], 4, $dress_color);
    
    // Bottom part (skirt)
    imagefilledpolygon($image, [
        130, 200,  // left waist
        270, 200,  // right waist
        320, 450,  // right hem
        80, 450    // left hem
    ], 4, $dress_color);
    
    // Add sleeves for some variety
    if ($filename != 'white-dress.jpg') {
        // Left sleeve
        imagefilledpolygon($image, [
            150, 100,
            130, 120,
            110, 180,
            130, 200
        ], 4, $dress_color);
        
        // Right sleeve
        imagefilledpolygon($image, [
            250, 100,
            270, 120,
            290, 180,
            270, 200
        ], 4, $dress_color);
    }
    
    // Add text label
    $text_color = imagecolorallocate($image, 100, 100, 100);
    $font_size = 5;
    $text = $data['name'];
    $text_width = imagefontwidth($font_size) * strlen($text);
    $x = ($width - $text_width) / 2;
    imagestring($image, $font_size, $x, 460, $text, $text_color);
    
    // Save image
    if (imagejpeg($image, $filepath, 90)) {
        echo "✓ Created placeholder image: $filename\n";
    } else {
        echo "✗ Failed to create: $filename\n";
    }
    
    imagedestroy($image);
}

echo "\n✅ Placeholder dress images created!\n";
echo "</pre>";

// Display the created images
echo "<h3>Created Dress Images:</h3>";
echo "<div style='display: flex; flex-wrap: wrap; gap: 20px; margin: 20px;'>";

foreach ($dress_images as $filename => $data) {
    $filepath = 'assets/images/products/' . $filename;
    if (file_exists(__DIR__ . '/' . $filepath)) {
        echo "<div style='text-align: center;'>";
        echo "<img src='$filepath' style='width: 150px; height: 200px; object-fit: cover; border: 1px solid #ddd;'>";
        echo "<p>$filename</p>";
        echo "</div>";
    }
}

echo "</div>";
?>

<div style="margin: 20px;">
    <a href="add_dress_product.php" style="padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;">Add Dress Products to DB</a>
    <a href="index.php" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;">View Homepage</a>
    <a href="tryon/" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;">Test Try-On</a>
</div>
