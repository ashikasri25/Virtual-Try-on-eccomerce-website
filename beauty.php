<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

// Get all beauty products
$beauty_categories = getCategories('beauty');
$category_ids = array_column($beauty_categories, 'id');

// Check if a specific category is requested
$selected_category = null;
if (isset($_GET['category'])) {
    $selected_category = intval($_GET['category']);
    // Verify this is a beauty category
    if (!in_array($selected_category, $category_ids)) {
        $selected_category = null;
    }
}

// Get products from beauty categories
$products = [];
if (!empty($category_ids)) {
    if ($selected_category) {
        // Get products from specific category
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? AND p.active = 1
                ORDER BY p.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $selected_category);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            $stmt->close();
        }
    } else {
        // Get products from all beauty categories
        $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id IN ($placeholders) AND p.active = 1
                ORDER BY p.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $types = str_repeat('i', count($category_ids));
            $stmt->bind_param($types, ...$category_ids);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            $stmt->close();
        }
    }
}

$page_title = "Beauty Products";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - GlamWear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'partials/header.php'; ?>

<div class="container py-5">
    <h1 class="mb-4"><?= $page_title ?></h1>
    
    <div class="row mb-4">
        <?php foreach ($beauty_categories as $category): ?>
        <div class="col-md-3 mb-3">
            <a href="beauty.php?category=<?= $category['id'] ?>" class="btn btn-outline-primary w-100">
                <?= htmlspecialchars($category['name']) ?>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="row">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info">No beauty products found.</div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-light text-center py-5">No Image</div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($product['category_name']) ?></p>
                            <p class="card-text">$<?= number_format($product['price'], 2) ?></p>
                            <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>