<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

// Get products
$products = getProducts();

// Get makeup products
$makeup_products = getMakeupProducts();

// Get dresses
$dresses = getDresses();

// Get categories
$beauty_categories = getCategories('beauty');
$clothing_categories = getCategories('clothing');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlamWear - Beauty & Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">GlamWear</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Beauty
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($beauty_categories as $category): ?>
                                <li><a class="dropdown-item" href="category.php?id=<?= $category['id'] ?>"><?= $category['name'] ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Clothing
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($clothing_categories as $category): ?>
                                <li><a class="dropdown-item" href="category.php?id=<?= $category['id'] ?>"><?= $category['name'] ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tryon/">Virtual Try-On</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <?php 
                            $cart_count = getCartCountDB();
                            if ($cart_count > 0): 
                            ?>
                                <span class="badge bg-primary"><?= $cart_count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?= $_SESSION['user_name'] ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="admin/">Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['cart_message'])): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= $_SESSION['cart_message_type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['cart_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php 
    unset($_SESSION['cart_message']);
    unset($_SESSION['cart_message_type']);
    endif; ?>
    
    <!-- Hero Section -->
    <section class="hero-section text-white py-5">
        <div class="container text-center position-relative">
            <div class="hero-content">
                <h1 class="display-3 fw-bold mb-4">Discover Your Style</h1>
                <p class="lead mb-4 fs-5">Try before you buy with our cutting-edge virtual try-on technology</p>
                <div class="hero-buttons">
                    <a href="#products" class="btn btn-light btn-lg me-3 mb-2">
                        <i class="fas fa-shopping-bag me-2"></i>Shop Now
                    </a>
                    <a href="tryon/" class="btn btn-outline-light btn-lg mb-2">
                        <i class="fas fa-magic me-2"></i>Virtual Try-On
                    </a>
                </div>
            </div>
            <div class="hero-decoration">
                <div class="floating-shape shape-1"></div>
                <div class="floating-shape shape-2"></div>
                <div class="floating-shape shape-3"></div>
            </div>
        </div>
    </section>
                </tbody>
            </table>
        </div>

    </section>

    <!-- Products Section -->
    <section class="container py-5" id="products">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Featured Products</h2>
            <p class="section-subtitle">Discover our latest collection of beauty and fashion items</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="product-card-wrapper">
                    <div class="card product-card h-100">
                        <div class="product-image-container">
                            <?php 
                            // Use the actual product image from database
                            $displayImage = $product['image_url'];
                            ?>
                            <img src="<?= $displayImage ?>" class="card-img-top product-image" alt="<?= $product['name'] ?>">
                            <div class="product-overlay">
                                <div class="overlay-buttons">
                                    <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <a href="tryon/?product_id=<?= $product['id'] ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-magic me-1"></i>Try On
                                    </a>
                                    <button class="btn btn-outline-light btn-sm quick-view-btn" data-product-id="<?= $product['id'] ?>">
                                        <i class="fas fa-search me-1"></i>Quick View
                                    </button>
                                </div>
                            </div>
                            <div class="product-badge">
                                <span class="badge bg-<?= $product['stock'] > 0 ? 'success' : 'danger' ?>">
                                    <?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title product-title"><?= $product['name'] ?></h5>
                            <p class="card-text product-description"><?= substr($product['description'], 0, 100) ?>...</p>
                            <div class="product-price-section">
                                <span class="product-price">$<?= $product['price'] ?></span>
                                <div class="product-rating">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="far fa-star text-warning"></i>
                                    <span class="rating-text">(4.0)</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-grid gap-2">
                                <?php if ($product['stock'] > 0): ?>
                                <form method="post" action="add_to_cart.php" class="mb-2">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                    </button>
                                </form>
                                <?php else: ?>
                                <button class="btn btn-secondary w-100 mb-2" disabled>
                                    <i class="fas fa-times-circle me-2"></i>Out of Stock
                                </button>
                                <?php endif; ?>
                                <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary mb-2">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                                <a href="tryon/?product_id=<?= $product['id'] ?>" class="btn btn-success">
                                    <i class="fas fa-magic me-2"></i>Virtual Try-On
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Makeup Products Section -->
    <?php if (!empty($makeup_products)): ?>
    <section class="container py-5">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">💄 Virtual Makeup Collection</h2>
            <p class="section-subtitle">Try our AI-powered makeup products virtually</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($makeup_products as $makeup): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="product-card-wrapper">
                    <div class="card product-card h-100">
                        <div class="product-image-container">
                            <div class="makeup-color-preview" style="background: linear-gradient(45deg, <?= $makeup['color_hex'] ?>, <?= $makeup['color_hex'] ?>88); height: 200px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-<?= $makeup['makeup_type'] === 'lips' ? 'kiss' : ($makeup['makeup_type'] === 'blush' ? 'heart' : ($makeup['makeup_type'] === 'eyeshadow' ? 'eye' : 'eye-slash')) ?> fa-3x text-white"></i>
                            </div>
                            <div class="product-overlay">
                                <div class="overlay-buttons">
                                    <a href="makeup_tryon.php" class="btn btn-success btn-sm">
                                        <i class="fas fa-magic me-1"></i>Try On
                                    </a>
                                </div>
                            </div>
                            <div class="product-badge">
                                <span class="badge bg-info">Virtual Makeup</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title product-title"><?= htmlspecialchars($makeup['name']) ?></h5>
                            <p class="card-text text-muted"><?= ucfirst($makeup['makeup_type']) ?> • Intensity: <?= $makeup['intensity'] ?></p>
                            <div class="product-price-section">
                                <span class="product-price">$<?= number_format($makeup['price'], 2) ?></span>
                                <div class="product-rating">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="far fa-star text-warning"></i>
                                    <span class="rating-text">(4.0)</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-grid gap-2">
                                <a href="makeup_tryon.php" class="btn btn-success">
                                    <i class="fas fa-magic me-2"></i>Virtual Try-On
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Clothing Collection Section -->
    <?php if (!empty($dresses)): ?>
    <section class="container py-5">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">👗 Virtual Clothing Collection</h2>
            <p class="section-subtitle">Try on our clothing items virtually with AI technology</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($dresses as $dress): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="product-card-wrapper">
                    <div class="card product-card h-100">
                        <div class="product-image-container">
                            <img src="<?= $dress['image_url'] ?>" class="card-img-top product-image" alt="<?= htmlspecialchars($dress['name']) ?>" style="height: 200px; object-fit: cover;">
                            <div class="product-overlay">
                                <div class="overlay-buttons">
                                    <a href="clothing_tryon.php" class="btn btn-success btn-sm">
                                        <i class="fas fa-tshirt me-1"></i>Try On
                                    </a>
                                </div>
                            </div>
                            <div class="product-badge">
                                <span class="badge bg-info">Virtual Clothing</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title product-title"><?= htmlspecialchars($dress['name']) ?></h5>
                            <p class="card-text text-muted"><?= ucfirst($dress['category']) ?> • Size: <?= $dress['size'] ?> • Color: <?= $dress['color'] ?></p>
                            <div class="product-price-section">
                                <span class="product-price">$<?= number_format($dress['price'], 2) ?></span>
                                <div class="product-rating">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="far fa-star text-warning"></i>
                                    <span class="rating-text">(4.0)</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-grid gap-2">
                                <a href="clothing_tryon.php" class="btn btn-success">
                                    <i class="fas fa-tshirt me-2"></i>Virtual Try-On
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Features Section -->
    <section class="features-section py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Why Choose GlamWear?</h2>
                <p class="section-subtitle">Experience the future of fashion shopping</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <h4>Virtual Try-On</h4>
                        <p>Try clothing and makeup virtually before making a purchase with our AI-powered technology</p>
                        <div class="feature-link">
                            <a href="tryon/" class="btn btn-outline-primary btn-sm">
                                Try Now <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-truck-fast"></i>
                        </div>
                        <h4>Fast Delivery</h4>
                        <p>Free shipping on orders over $50 with express delivery options available</p>
                        <div class="feature-link">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                Learn More <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Secure Payment</h4>
                        <p>Your payment information is always secure with bank-level encryption</p>
                        <div class="feature-link">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                Security Info <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-section py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand">
                        <h5 class="footer-title">GlamWear</h5>
                        <p class="footer-description">Your destination for beauty and fashion with cutting-edge virtual try-on technology.</p>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#products">Products</a></li>
                        <li><a href="tryon/">Virtual Try-On</a></li>
                        <li><a href="auth/login.php">Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Categories</h5>
                    <ul class="footer-links">
                        <li><a href="#">Beauty Products</a></li>
                        <li><a href="#">Fashion Clothing</a></li>
                        <li><a href="#">Accessories</a></li>
                        <li><a href="#">New Arrivals</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Contact Info</h5>
                    <ul class="footer-contact">
                        <li><i class="fas fa-envelope"></i> info@glamwear.com</li>
                        <li><i class="fas fa-phone"></i> (123) 456-7890</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Fashion St, Style City</li>
                    </ul>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="copyright-text">&copy; 2024 GlamWear. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="footer-bottom-links">
                            <a href="#">Privacy Policy</a>
                            <a href="#">Terms of Service</a>
                            <a href="#">Cookie Policy</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>