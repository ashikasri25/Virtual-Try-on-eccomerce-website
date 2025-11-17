<?php
// Script to create the sellers table
require_once 'inc/config.php';

echo "<h2>Creating Sellers Table</h2>";
echo "<pre>";

try {
    // Check if table already exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'sellers'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✅ Sellers table already exists.\n";
    } else {
        // Create the sellers table
        $sql = "CREATE TABLE sellers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            shop_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            status ENUM('Active', 'Inactive', 'Suspended') DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "✅ Sellers table created successfully!\n";
        
        // Add a sample seller for testing
        $sql = "INSERT INTO sellers (name, shop_name, email, phone, status) 
                VALUES ('John Doe', 'Fashion Hub', 'john@example.com', '555-123-4567', 'Active')";
        $pdo->exec($sql);
        echo "✅ Added sample seller for testing\n";
    }
    
    echo "\n✅ OPERATION COMPLETED SUCCESSFULLY!";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<p><a href='admin/sellers.php'>Go to Sellers Admin Page</a></p>";
?>