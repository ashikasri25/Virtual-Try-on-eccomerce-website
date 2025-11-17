<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

// Check if category ID is provided
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$category_id = intval($_GET['id']);

// Get category details
$category = null;
$sql = "SELECT * FROM categories WHERE id = ?";
$stmt = $pdo->prepare($sql);
if ($stmt) {
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
}

// If category doesn't exist, redirect to home
if (!$category) {
    header('Location: index.php');
    exit;
}

// Get products from this category
$products = [];
$sql = "SELECT p.*, c.name as category_name FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.category_id = ?
        ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
if ($stmt) {
    $stmt->execute([$category_id]);
    $products = $stmt->fetchAll();
}

$page_title = $category['name'];
$category_type = $category['type']; // 'beauty' or 'clothing'
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
    
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">
                <a href="<?= $category_type ?>.php">
                    <?= ucfirst($category_type) ?>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page"><?= $page_title ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info">No products found in this category.</div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($product['image_url'])): ?>
                            <img src="assets/images/products/<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-light text-center py-5">No Image</div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($product['category_name']) ?></p>
                            <p class="card-text"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                            <div class="mt-auto">
                                <p class="card-text fw-bold">$<?= number_format($product['price'], 2) ?></p>
                                <div class="d-flex justify-content-between">
                                    <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-primary">View Details</a>
                                    <a href="add_to_cart.php?product_id=<?= $product['id'] ?>" class="btn btn-outline-success">
                                        <i class="fas fa-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
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