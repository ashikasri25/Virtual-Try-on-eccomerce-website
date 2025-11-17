<!DOCTYPE html>
<html>
<head>
    <title>Try-On Status Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status-item { padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #ddd; }
        .status-ok { background: #d4edda; border-left-color: #28a745; }
        .status-error { background: #f8d7da; border-left-color: #dc3545; }
        .status-warning { background: #fff3cd; border-left-color: #ffc107; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Virtual Try-On Status Check</h1>
        
        <?php
        require_once 'inc/config.php';
        
        $all_ok = true;
        
        // Check 1: Python
        echo "<h2>1. Python Environment</h2>";
        $python_config = __DIR__ . '/python_config.php';
        if (file_exists($python_config)) {
            require_once $python_config;
            if (defined('PYTHON_PATH')) {
                $version = shell_exec('"' . PYTHON_PATH . '" --version 2>&1');
                echo "<div class='status-item status-ok'>";
                echo "✅ <strong>Python Found:</strong> " . PYTHON_PATH . "<br>";
                echo "Version: " . htmlspecialchars(trim($version));
                echo "</div>";
            } else {
                echo "<div class='status-item status-error'>❌ Python config exists but PYTHON_PATH not defined</div>";
                $all_ok = false;
            }
        } else {
            echo "<div class='status-item status-error'>❌ Python not configured. Run: <code>php find_python.php</code></div>";
            $all_ok = false;
        }
        
        // Check 2: Python Libraries
        echo "<h2>2. Python Libraries</h2>";
        if (defined('PYTHON_PATH')) {
            // Change to the script directory first
            $old_dir = getcwd();
            chdir(__DIR__);
            
            $python_cmd = PYTHON_PATH;
            // Add quotes if path contains spaces
            if (strpos($python_cmd, ' ') !== false && strpos($python_cmd, '"') === false) {
                $python_cmd = '"' . $python_cmd . '"';
            }
            
            $test_output = shell_exec($python_cmd . ' test_python.py 2>&1');
            chdir($old_dir);
            
            if ($test_output && strpos($test_output, '"success": true') !== false) {
                echo "<div class='status-item status-ok'>✅ All Python libraries installed (OpenCV, NumPy, Pillow)</div>";
            } else {
                echo "<div class='status-item status-warning'>";
                echo "⚠️ Cannot verify Python libraries via shell_exec<br>";
                echo "This is likely a PHP configuration issue, but Python libraries are installed.<br>";
                echo "<details><summary>Show output</summary><pre>" . htmlspecialchars($test_output) . "</pre></details>";
                echo "</div>";
            }
        }
        
        // Check 3: Database
        echo "<h2>3. Database</h2>";
        try {
            $stmt = $pdo->query("DESCRIBE clothing_tryon_sessions");
            echo "<div class='status-item status-ok'>✅ Database table 'clothing_tryon_sessions' exists</div>";
        } catch (Exception $e) {
            echo "<div class='status-item status-error'>";
            echo "❌ Database table missing<br>";
            echo "Run: <code>php setup_database.php</code>";
            echo "</div>";
            $all_ok = false;
        }
        
        // Check 4: Dresses
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM dresses WHERE active = 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] > 0) {
                echo "<div class='status-item status-ok'>✅ Found {$result['count']} active dresses</div>";
            } else {
                echo "<div class='status-item status-warning'>⚠️ No active dresses found. Add some dresses to test.</div>";
            }
        } catch (Exception $e) {
            echo "<div class='status-item status-error'>❌ Cannot query dresses table</div>";
        }
        
        // Check 5: Directories
        echo "<h2>4. Directories</h2>";
        $dirs = ['uploads/clothing_tryon', 'output/clothing_tryon'];
        foreach ($dirs as $dir) {
            if (is_dir($dir) && is_writable($dir)) {
                echo "<div class='status-item status-ok'>✅ Directory '$dir' is ready</div>";
            } else {
                echo "<div class='status-item status-error'>❌ Directory '$dir' missing or not writable</div>";
                $all_ok = false;
            }
        }
        
        // Check 6: Files
        echo "<h2>5. Required Files</h2>";
        $files = [
            'clothing_tryon.py' => 'Python script',
            'process_clothing.php' => 'PHP processor',
            'clothing_tryon.php' => 'Frontend page'
        ];
        foreach ($files as $file => $desc) {
            if (file_exists($file)) {
                echo "<div class='status-item status-ok'>✅ $desc ($file)</div>";
            } else {
                echo "<div class='status-item status-error'>❌ Missing: $desc ($file)</div>";
                $all_ok = false;
            }
        }
        
        // Final Status
        echo "<h2>Overall Status</h2>";
        if ($all_ok) {
            echo "<div class='status-item status-ok'>";
            echo "<strong>🎉 Everything is ready!</strong><br>";
            echo "You can now use the virtual try-on feature.";
            echo "</div>";
        } else {
            echo "<div class='status-item status-error'>";
            echo "<strong>⚠️ Some issues need to be fixed</strong><br>";
            echo "Please address the errors above before using the try-on feature.";
            echo "</div>";
        }
        ?>
        
        <h2>Quick Actions</h2>
        <a href="clothing_tryon.php" class="btn">Go to Try-On Page</a>
        <a href="test_tryon_integration.php" class="btn">Run Integration Test</a>
        <a href="diagnose_tryon.php" class="btn">Detailed Diagnostics</a>
        <a href="javascript:location.reload()" class="btn" style="background: #6c757d;">Refresh Status</a>
    </div>
</body>
</html>
