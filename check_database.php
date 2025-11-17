<?php
// Direct database test without requiring config
$host = 'localhost';
$db = 'glamwear_db';
$user = 'root';
$pass = '';

echo "<h2>Database Connection Test</h2>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to database: $db<br><br>";
    
    // Check tables
    echo "<h3>Checking Tables:</h3>";
    $tables = ['users', 'carts', 'cart_items', 'products', 'categories', 'orders', 'order_items'];
    
    foreach ($tables as $table) {
        try {
            $result = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $result->fetchColumn();
            echo "✓ Table '$table' exists (rows: $count)<br>";
        } catch (Exception $e) {
            echo "✗ Table '$table' does NOT exist<br>";
        }
    }
    
    echo "<br><h3>Action Required:</h3>";
    echo "If any tables are missing, visit: <a href='setup_database.php' target='_blank'>setup_database.php</a><br>";
    
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "<br>";
    echo "Check your database credentials in inc/config.php<br>";
}
?>
