<?php
require_once 'inc/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Please log in to use this feature']);
    exit();
}

try {
    // Check if image was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No image uploaded or upload error');
    }

    // Validate makeup parameters
    $makeup_type = $_POST['makeup_type'] ?? 'lips';
    $color_hex = $_POST['color_hex'] ?? '#FF0000';
    $intensity = floatval($_POST['intensity'] ?? 0.5);

    // Validate makeup type
    $valid_types = ['lips', 'blush', 'eyeshadow', 'eyeliner', 'foundation'];
    if (!in_array($makeup_type, $valid_types)) {
        throw new Exception('Invalid makeup type');
    }

    // Validate intensity
    if ($intensity < 0.1 || $intensity > 1.0) {
        throw new Exception('Intensity must be between 0.1 and 1.0');
    }

    // Validate color hex
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color_hex)) {
        throw new Exception('Invalid color format');
    }

    // Create uploads directory if it doesn't exist
    $uploads_dir = 'uploads/makeup_tryon';
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }

    // Generate unique filename
    $timestamp = time();
    $original_filename = $_FILES['image']['name'];
    $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
    $upload_filename = "makeup_upload_{$timestamp}.{$file_extension}";
    $upload_path = $uploads_dir . '/' . $upload_filename;

    // Move uploaded file
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        throw new Exception('Failed to save uploaded image');
    }

    // Get Python script path
    $python_script = __DIR__ . '/makeup_tryon.py';
    if (!file_exists($python_script)) {
        throw new Exception('Makeup processing script not found');
    }

    // Prepare Python command with full path
    $python_path = 'C:\Users\Ashikasri K S\AppData\Local\Programs\Python\Python39\python.exe';
    $command = sprintf(
        '"%s" "%s" "%s" %s %s %.2f',
        $python_path,
        $python_script,
        $upload_path,
        escapeshellarg($makeup_type),
        escapeshellarg($color_hex),
        $intensity
    );

    // Execute Python script (redirect STDERR to null to suppress warnings)
    $output = shell_exec($command . ' 2>nul');
    
    if ($output === null || empty(trim($output))) {
        throw new Exception('Failed to execute makeup processing script or no output received');
    }

    // Parse JSON response from Python
    $result = json_decode(trim($output), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from makeup processing. Output: ' . substr($output, 0, 200));
    }

    if (!$result['success']) {
        throw new Exception($result['error']);
    }

    // Save session to database
    $session_id = uniqid('makeup_', true);
    $stmt = $pdo->prepare("
        INSERT INTO makeup_tryon_sessions 
        (user_id, session_id, original_image_path, processed_image_path, makeup_type, color_hex, intensity) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $session_id,
        $upload_path,
        $result['result_path'],
        $makeup_type,
        $color_hex,
        $intensity
    ]);

    // Return success response
    echo json_encode([
        'success' => true,
        'result_base64' => $result['result_base64'],
        'session_id' => $session_id,
        'makeup_type' => $makeup_type,
        'color_hex' => $color_hex,
        'intensity' => $intensity
    ]);

} catch (Exception $e) {
    // Clean up uploaded file if it exists
    if (isset($upload_path) && file_exists($upload_path)) {
        unlink($upload_path);
    }
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
