<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: auth/login.php");
    exit();
}

// Get makeup products
$makeup_products = [];
try {
    $stmt = $pdo->query("SELECT * FROM makeup_products WHERE active = 1 ORDER BY makeup_type, name");
    $makeup_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error loading makeup products: " . $e->getMessage();
}

$page_title = "Virtual Makeup Try-On";
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
    <style>
        .makeup-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .makeup-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        .image-container {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            background: #f8f9fa;
        }
        .image-container img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        .makeup-controls {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
        }
        .color-picker {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }
        .makeup-type-btn {
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            margin: 0.25rem;
            transition: all 0.3s ease;
        }
        .makeup-type-btn.active {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(255, 107, 107, 0.3);
        }
        .intensity-slider {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: #ddd;
            outline: none;
            -webkit-appearance: none;
        }
        .intensity-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #ff6b6b;
            cursor: pointer;
        }
        .loading-spinner {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
        .result-image {
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="makeup-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="makeup-card p-4">
                        <div class="text-center mb-4">
                            <h1 class="display-4 fw-bold text-primary">
                                <i class="fas fa-magic me-3"></i>Virtual Makeup Try-On
                            </h1>
                            <p class="lead text-muted">Try different makeup looks with AI-powered technology</p>
                        </div>

                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?= $error_message ?></div>
                        <?php endif; ?>

                        <div class="row">
                            <!-- Image Upload Section -->
                            <div class="col-lg-6 mb-4">
                                <div class="image-container">
                                    <div id="image-upload-area" class="d-flex align-items-center justify-content-center" style="height: 400px; border: 2px dashed #dee2e6;">
                                        <div class="text-center">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Upload Your Photo</h5>
                                            <p class="text-muted">Click to select or drag and drop</p>
                                            <input type="file" id="image-input" accept="image/*" class="d-none">
                                            <button class="btn btn-primary" onclick="document.getElementById('image-input').click()">
                                                <i class="fas fa-upload me-2"></i>Choose Image
                                            </button>
                                        </div>
                                    </div>
                                    <img id="uploaded-image" class="d-none" alt="Uploaded Image">
                                    <div class="loading-spinner">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Processing...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Makeup Controls -->
                            <div class="col-lg-6">
                                <div class="makeup-controls">
                                    <h4 class="mb-3">
                                        <i class="fas fa-palette me-2"></i>Makeup Controls
                                    </h4>

                                    <!-- Makeup Type Selection -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Makeup Type</label>
                                        <div class="d-flex flex-wrap">
                                            <button class="btn btn-outline-primary makeup-type-btn" data-type="lips">
                                                <i class="fas fa-kiss me-2"></i>Lips
                                            </button>
                                            <button class="btn btn-outline-primary makeup-type-btn" data-type="blush">
                                                <i class="fas fa-heart me-2"></i>Blush
                                            </button>
                                            <button class="btn btn-outline-primary makeup-type-btn" data-type="eyeshadow">
                                                <i class="fas fa-eye me-2"></i>Eyeshadow
                                            </button>
                                            <button class="btn btn-outline-primary makeup-type-btn" data-type="eyeliner">
                                                <i class="fas fa-eye-slash me-2"></i>Eyeliner
                                            </button>
                                            <button class="btn btn-outline-primary makeup-type-btn" data-type="foundation">
                                                <i class="fas fa-user me-2"></i>Foundation
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Color Selection -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Color</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div class="color-picker" style="background-color: #FF0000;" data-color="#FF0000"></div>
                                            <div class="color-picker" style="background-color: #FF69B4;" data-color="#FF69B4"></div>
                                            <div class="color-picker" style="background-color: #8A2BE2;" data-color="#8A2BE2"></div>
                                            <div class="color-picker" style="background-color: #4169E1;" data-color="#4169E1"></div>
                                            <div class="color-picker" style="background-color: #000000;" data-color="#000000"></div>
                                            <div class="color-picker" style="background-color: #8B4513;" data-color="#8B4513"></div>
                                            <div class="color-picker" style="background-color: #F5DEB3;" data-color="#F5DEB3"></div>
                                            <div class="color-picker" style="background-color: #FF7F50;" data-color="#FF7F50"></div>
                                            <div class="color-picker" style="background-color: #FDBCB4;" data-color="#FDBCB4" title="Light Foundation"></div>
                                            <div class="color-picker" style="background-color: #E8B896;" data-color="#E8B896" title="Medium Foundation"></div>
                                            <div class="color-picker" style="background-color: #C68642;" data-color="#C68642" title="Dark Foundation"></div>
                                        </div>
                                        <input type="color" id="custom-color" class="form-control mt-2" value="#FF0000">
                                    </div>

                                    <!-- Intensity Control -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Intensity</label>
                                        <input type="range" class="intensity-slider" id="intensity-slider" min="0.1" max="1.0" step="0.1" value="0.5">
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">Light</small>
                                            <small class="text-muted">Bold</small>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-grid gap-2">
                                        <button id="apply-makeup-btn" class="btn btn-primary btn-lg" disabled>
                                            <i class="fas fa-magic me-2"></i>Apply Makeup
                                        </button>
                                        <button id="save-result-btn" class="btn btn-success" disabled>
                                            <i class="fas fa-save me-2"></i>Save Result
                                        </button>
                                        <button id="reset-btn" class="btn btn-outline-secondary">
                                            <i class="fas fa-undo me-2"></i>Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Result Section -->
                        <div id="result-section" class="mt-4 d-none">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h5 class="mb-3">Original</h5>
                                    <img id="original-image" class="result-image w-100" alt="Original Image">
                                </div>
                                <div class="col-lg-6">
                                    <h5 class="mb-3">With Makeup</h5>
                                    <img id="result-image" class="result-image w-100" alt="Result Image">
                                </div>
                            </div>
                            
                            <!-- Shade Analysis Section (for foundation) -->
                            <div id="shade-analysis" class="mt-4 d-none">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-palette me-2"></i>Foundation Shade Analysis
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <strong>Match Quality:</strong>
                                                    <span id="match-quality" class="badge ms-2"></span>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Match Score:</strong>
                                                    <span id="match-score" class="ms-2"></span>%
                                                </div>
                                                <div class="mb-3">
                                                    <div id="match-message" class="alert"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <strong>Your Skin Tone:</strong>
                                                    <div id="skin-tone-preview" class="color-picker ms-2 d-inline-block"></div>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Recommended Shade:</strong>
                                                    <span id="recommended-shade" class="badge bg-info ms-2"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Makeup Products Section -->
                        <div class="mt-5">
                            <h3 class="mb-4">
                                <i class="fas fa-shopping-bag me-2"></i>Shop Makeup Products
                            </h3>
                            <div class="row">
                                <?php foreach ($makeup_products as $product): ?>
                                    <div class="col-md-3 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <div class="color-picker mx-auto mb-3" style="background-color: <?= $product['color_hex'] ?>;"></div>
                                                <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
                                                <p class="card-text text-muted small"><?= ucfirst($product['makeup_type']) ?></p>
                                                <p class="card-text fw-bold text-primary">$<?= number_format($product['price'], 2) ?></p>
                                                <button class="btn btn-sm btn-outline-primary me-2" onclick="selectMakeupProduct(<?= htmlspecialchars(json_encode($product)) ?>)">
                                                    Try This
                                                </button>
                                                <button class="btn btn-sm btn-primary" onclick="addToCart(<?= $product['id'] ?>)">
                                                    <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addToCart(productId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'add_to_cart.php';

            const productIdInput = document.createElement('input');
            productIdInput.type = 'hidden';
            productIdInput.name = 'product_id';
            productIdInput.value = productId;

            const quantityInput = document.createElement('input');
            quantityInput.type = 'hidden';
            quantityInput.name = 'quantity';
            quantityInput.value = '1';

            form.appendChild(productIdInput);
            form.appendChild(quantityInput);
            document.body.appendChild(form);
            form.submit();
        }

        let uploadedImageData = null;
        let currentMakeupType = 'lips';
        let currentColor = '#FF0000';
        let currentIntensity = 0.5;

        // Image upload handling
        document.getElementById('image-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    uploadedImageData = e.target.result;
                    document.getElementById('uploaded-image').src = e.target.result;
                    document.getElementById('uploaded-image').classList.remove('d-none');
                    document.getElementById('image-upload-area').classList.add('d-none');
                    document.getElementById('apply-makeup-btn').disabled = false;
                };
                reader.readAsDataURL(file);
            }
        });

        // Makeup type selection
        document.querySelectorAll('.makeup-type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.makeup-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentMakeupType = this.dataset.type;
            });
        });

        // Color selection
        document.querySelectorAll('.color-picker').forEach(picker => {
            picker.addEventListener('click', function() {
                currentColor = this.dataset.color;
                document.getElementById('custom-color').value = currentColor;
            });
        });

        document.getElementById('custom-color').addEventListener('change', function() {
            currentColor = this.value;
        });

        // Intensity slider
        document.getElementById('intensity-slider').addEventListener('input', function() {
            currentIntensity = parseFloat(this.value);
        });

        // Apply makeup
        document.getElementById('apply-makeup-btn').addEventListener('click', function() {
            if (!uploadedImageData) return;

            const spinner = document.querySelector('.loading-spinner');
            spinner.style.display = 'block';

            // Convert base64 to file and upload
            const formData = new FormData();
            const blob = dataURLtoBlob(uploadedImageData);
            formData.append('image', blob, 'uploaded_image.jpg');
            formData.append('makeup_type', currentMakeupType);
            formData.append('color_hex', currentColor);
            formData.append('intensity', currentIntensity);

            fetch('process_makeup.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                spinner.style.display = 'none';
                if (data.success) {
                    document.getElementById('original-image').src = uploadedImageData;
                    document.getElementById('result-image').src = 'data:image/jpeg;base64,' + data.result_base64;
                    document.getElementById('result-section').classList.remove('d-none');
                    document.getElementById('save-result-btn').disabled = false;
                    
                    // Handle foundation shade analysis
                    if (currentMakeupType === 'foundation' && data.shade_analysis) {
                        displayShadeAnalysis(data.shade_analysis);
                    } else {
                        document.getElementById('shade-analysis').classList.add('d-none');
                    }
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                spinner.style.display = 'none';
                alert('Error processing makeup: ' + error);
            });
        });

        // Save result
        document.getElementById('save-result-btn').addEventListener('click', function() {
            const resultImage = document.getElementById('result-image').src;
            const link = document.createElement('a');
            link.download = 'makeup_result.jpg';
            link.href = resultImage;
            link.click();
        });

        // Reset
        document.getElementById('reset-btn').addEventListener('click', function() {
            uploadedImageData = null;
            document.getElementById('uploaded-image').classList.add('d-none');
            document.getElementById('image-upload-area').classList.remove('d-none');
            document.getElementById('result-section').classList.add('d-none');
            document.getElementById('apply-makeup-btn').disabled = true;
            document.getElementById('save-result-btn').disabled = true;
            document.getElementById('image-input').value = '';
        });

        // Select makeup product
        function selectMakeupProduct(product) {
            currentMakeupType = product.makeup_type;
            currentColor = product.color_hex;
            currentIntensity = product.intensity;

            // Update UI
            document.querySelectorAll('.makeup-type-btn').forEach(b => b.classList.remove('active'));
            document.querySelector(`[data-type="${currentMakeupType}"]`).classList.add('active');
            document.getElementById('custom-color').value = currentColor;
            document.getElementById('intensity-slider').value = currentIntensity;
        }

        // Display shade analysis results
        function displayShadeAnalysis(analysis) {
            document.getElementById('shade-analysis').classList.remove('d-none');
            
            // Set match quality with appropriate badge color
            const matchQuality = document.getElementById('match-quality');
            matchQuality.textContent = analysis.match_quality;
            
            // Set badge color based on match quality
            matchQuality.className = 'badge ms-2 ';
            if (analysis.match_score > 80) {
                matchQuality.className += 'bg-success';
            } else if (analysis.match_score > 60) {
                matchQuality.className += 'bg-warning';
            } else {
                matchQuality.className += 'bg-danger';
            }
            
            // Set match score
            document.getElementById('match-score').textContent = analysis.match_score;
            
            // Set match message with appropriate alert color
            const matchMessage = document.getElementById('match-message');
            matchMessage.textContent = analysis.match_message;
            matchMessage.className = 'alert ';
            if (analysis.match_score > 80) {
                matchMessage.className += 'alert-success';
            } else if (analysis.match_score > 60) {
                matchMessage.className += 'alert-warning';
            } else {
                matchMessage.className += 'alert-danger';
            }
            
            // Set skin tone preview
            const skinTonePreview = document.getElementById('skin-tone-preview');
            const rgbColor = `rgb(${analysis.skin_tone_rgb.join(',')})`;
            skinTonePreview.style.backgroundColor = rgbColor;
            skinTonePreview.title = `Your skin tone: ${rgbColor}`;
            
            // Set recommended shade
            document.getElementById('recommended-shade').textContent = analysis.recommended_shade.replace('_', '-');
        }

        // Utility function to convert data URL to blob
        function dataURLtoBlob(dataurl) {
            const arr = dataurl.split(',');
            const mime = arr[0].match(/:(.*?);/)[1];
            const bstr = atob(arr[1]);
            let n = bstr.length;
            const u8arr = new Uint8Array(n);
            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }
            return new Blob([u8arr], { type: mime });
        }
    </script>
</body>
</html>
