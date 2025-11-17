<?php
require_once 'inc/config.php';

echo "<h2>Create Test User</h2>";

try {
    // Check if test user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['test@glamwear.com']);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Test user already exists<br>";
        echo "Email: test@glamwear.com<br>";
        echo "Password: test123<br>";
    } else {
        // Create test user
        $email = 'test@glamwear.com';
        $password = password_hash('test123', PASSWORD_DEFAULT);
        $name = 'Test User';
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, 'user']);
        
        echo "✓ Test user created successfully<br>";
        echo "Email: test@glamwear.com<br>";
        echo "Password: test123<br>";
        echo "<br>";
        echo "<a href='auth/login.php'>Go to Login Page</a>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
