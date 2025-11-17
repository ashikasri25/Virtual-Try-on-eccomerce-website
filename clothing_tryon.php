<?php
require_once 'inc/config.php';
require_once 'inc/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: auth/login.php");
    exit();
}

// Get dresses from database
$dresses = [];
$error_message = '';
try {
    $stmt = $pdo->query("SELECT * FROM dresses WHERE active = 1 ORDER BY name");
    $dresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($dresses)) {
        $error_message = "No dresses found in database. Please add some dresses first.";
    }
} catch (PDOException $e) {
    $error_message = "Error loading dresses: " . $e->getMessage();
    $dresses = [];
}

$page_title = "Virtual Clothing Try-On";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?> - GlamWear</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.2.4/fabric.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6fa;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 1200px;
      margin: 0 auto;
      text-align: center;
    }
    h1 {
      color: #333;
      margin-bottom: 30px;
    }
    .upload-section {
      margin-bottom: 20px;
    }
    .upload-section input, .upload-section button {
      margin: 5px;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      cursor: pointer;
    }
    .upload-section button:hover {
      background-color: #007bff;
      color: white;
    }
    #video {
      max-width: 400px;
      border-radius: 10px;
      margin-top: 10px;
      display: none;
    }
    #canvas-container {
      display: inline-block;
      border: 2px solid #ccc;
      border-radius: 10px;
      margin: 20px 0;
      background: #fff;
    }
    #canvas {
      border-radius: 8px;
    }
    .dress-selection {
      margin: 20px 0;
    }
    .dress-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      justify-content: center;
      margin-top: 15px;
    }
    .dress-item {
      width: 120px;
      cursor: pointer;
      border: 2px solid transparent;
      border-radius: 8px;
      overflow: hidden;
      transition: border-color 0.3s;
    }
    .dress-item:hover {
      border-color: #007bff;
    }
    .dress-item img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      display: block;
      border-radius: 8px 8px 0 0;
    }
    .dress-info {
      padding: 8px;
      background: #f8f9fa;
      font-size: 12px;
    }
    .controls {
      margin-top: 20px;
    }
    .controls button {
      margin: 5px;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
    }
    .btn-primary {
      background-color: #007bff;
      color: white;
    }
    .btn-secondary {
      background-color: #6c757d;
      color: white;
    }
    .btn-success {
      background-color: #28a745;
      color: white;
    }
    .error-message {
      color: red;
      margin: 10px 0;
      padding: 10px;
      background: #ffe6e6;
      border-radius: 5px;
    }
    .success-message {
      color: green;
      margin: 10px 0;
      padding: 10px;
      background: #e6ffe6;
      border-radius: 5px;
    }
    .clothing-effects {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      pointer-events: none;
      z-index: 10;
    }
    .lighting-effect {
      position: absolute;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
      transition: all 0.3s ease;
    }
    .fabric-texture {
      background-image:
        linear-gradient(45deg, rgba(0,0,0,0.02) 25%, transparent 25%),
        linear-gradient(-45deg, rgba(0,0,0,0.02) 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, rgba(0,0,0,0.02) 75%),
        linear-gradient(-45deg, transparent 75%, rgba(0,0,0,0.02) 75%);
      background-size: 20px 20px;
      background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
    }
  </style>
</head>
<body>
<div class="container">
  <h1>👕 Virtual Clothing Try-On</h1>

  <?php if ($error_message): ?>
    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
  <?php endif; ?>

  <!-- Upload Section -->
  <div class="upload-section">
    <input type="file" id="image-upload" accept="image/*">
    <button class="btn-primary" onclick="startWebcam()">📷 Use Webcam</button>
    <button class="btn-secondary" onclick="captureWebcam()" id="capture-btn" disabled>📸 Capture</button>
    <br>
    <video id="video" width="400" height="500" autoplay muted></video>
  </div>

  <!-- Canvas -->
  <div id="canvas-container" style="position: relative; display: inline-block; border: 2px solid #ccc; border-radius: 10px; margin: 20px 0; background: #fff; overflow: hidden;">
    <canvas id="canvas" width="400" height="500" style="display: block; max-width: 100%;"></canvas>
    <div class="clothing-effects" id="clothing-effects">
      <div class="lighting-effect" id="lighting-effect"></div>
    </div>
  </div>

  <!-- Dress Selection -->
  <div class="dress-selection">
    <h3>Select Clothing</h3>
    <?php if (empty($dresses)): ?>
      <div class="error-message">No dresses available. Please add dresses to the database first.</div>
    <?php else: ?>
      <div class="dress-grid">
        <?php foreach ($dresses as $dress): ?>
          <div class="dress-item" onclick="addClothEnhanced('<?php echo htmlspecialchars($dress['image_url']); ?>', '<?php echo htmlspecialchars($dress['name']); ?>', <?php echo (int)$dress['id']; ?>)" title="Click to try on <?php echo htmlspecialchars($dress['name']); ?>">
            <img src="<?php echo htmlspecialchars($dress['image_url']); ?>"
                 alt="<?php echo htmlspecialchars($dress['name']); ?>"
                 crossorigin="anonymous"
                 onload="this.style.opacity='1'; console.log('✅ Dress loaded: <?php echo htmlspecialchars($dress['name']); ?>')"
                 onerror="console.error('❌ Failed to load: <?php echo htmlspecialchars($dress['image_url']); ?>'); this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjBmMGYwIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='; this.style.opacity='0.5';"
                 style="opacity: 0; transition: opacity 0.3s; cursor: pointer;">
            <div class="dress-info">
              <div><strong><?php echo htmlspecialchars($dress['name']); ?></strong></div>
              <div><?php echo htmlspecialchars($dress['category']); ?> • $<?php echo number_format($dress['price'], 2); ?></div>
              <button class="btn btn-primary btn-sm mt-2" onclick="addToCart(<?php echo $dress['id']; ?>)">Add to Cart</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Controls -->
  <div class="controls">
    <button class="btn-success" onclick="saveResult()" title="Save result (S)">💾 Save Result</button>
    <button class="btn-secondary" onclick="resetCanvas()" title="Reset canvas (R)">🔄 Reset</button>
    <button class="btn-primary" onclick="processWithAI()" title="AI Try-On">🤖 AI Try-On</button>
    <button class="btn-secondary" onclick="debugImageLoading()" title="Debug images" style="font-size: 12px; padding: 8px 12px;">🔍 Debug</button>
  </div>

  <div class="info-message" style="margin-top: 20px; text-align: center;">
    <small>
      💡 <strong>Tips:</strong> Upload/capture a person image, click on clothing items to add them,
      drag to position, press Delete to remove, S to save, R to reset
    </small>
  </div>

  <div id="status-message"></div>
</div>

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
</script>

<script>
  const canvas = new fabric.Canvas('canvas', {
    preserveObjectStacking: true,
    backgroundColor: '#f0f0f0'
  });

  let videoStream = null;
  let backgroundImage = null;
  let uploadedFile = null; // File/Blob to send to backend
  let selectedDressId = null; // Selected clothing id

  // Initialize canvas with better rendering
  function initializeCanvas() {
    canvas.clear();
    canvas.setBackgroundColor('#f0f0f0', function() {
      canvas.renderAll();

      // Add helpful placeholder
      const ctx = canvas.getContext('2d');
      ctx.save();
      ctx.globalAlpha = 0.7;
      ctx.fillStyle = '#666';
      ctx.font = 'bold 24px Arial';
      ctx.textAlign = 'center';
      ctx.fillText('👕 Upload or capture', canvas.width/2, canvas.height/2 - 20);
      ctx.font = '16px Arial';
      ctx.fillText('an image to start trying on clothes', canvas.width/2, canvas.height/2 + 10);
      ctx.restore();
    });
  }

  initializeCanvas();

  // Upload user image with proper layering
  document.getElementById('image-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    uploadedFile = file;

    showStatus('Loading person image...', 'info');

    const reader = new FileReader();
    reader.onload = function(event) {
      fabric.Image.fromURL(event.target.result, function(img) {
        canvas.clear();
        canvas.setBackgroundColor('#f0f0f0', canvas.renderAll.bind(canvas));

        // Scale person image to fit canvas properly
        const scaleX = (canvas.width * 0.9) / img.width;  // 90% of canvas width
        const scaleY = (canvas.height * 0.9) / img.height; // 90% of canvas height
        const scale = Math.min(scaleX, scaleY);

        // Center the person image
        const left = (canvas.width - img.width * scale) / 2;
        const top = (canvas.height - img.height * scale) / 2;

        img.set({
          scaleX: scale,
          scaleY: scale,
          left: left,
          top: top,
          selectable: false,
          evented: false,
          name: 'person_image',
          opacity: 1.0
        });

        backgroundImage = img;
        canvas.add(backgroundImage);
        backgroundImage.sendToBack();  // Person stays in background
        canvas.renderAll();

        showStatus('Person image loaded! Click clothing items to overlay.', 'success');
      }, {
        crossOrigin: 'anonymous'
      });
    };
    reader.readAsDataURL(file);
  });

  // Start webcam with better constraints
  function startWebcam() {
    const video = document.getElementById('video');
    const captureBtn = document.getElementById('capture-btn');

    if (videoStream) {
      videoStream.getTracks().forEach(track => track.stop());
    }

    showStatus('Starting webcam...', 'info');

    navigator.mediaDevices.getUserMedia({
      video: {
        width: { ideal: 640, min: 320 },
        height: { ideal: 480, min: 240 },
        facingMode: 'user'  // Front camera for mobile
      }
    })
    .then(stream => {
      videoStream = stream;
      video.srcObject = stream;

      // Ensure video element is visible
      video.style.display = "block";
      video.style.width = "400px";
      video.style.height = "300px";
      video.style.borderRadius = "10px";

      captureBtn.disabled = false;

      // Wait for video to load before showing success
      video.onloadedmetadata = function() {
        showStatus('Webcam started! Position yourself and click capture.', 'success');
      };

    })
    .catch(err => {
      console.error('Webcam error:', err);
      showStatus('Webcam error: ' + err.message + '. Please check permissions.', 'error');
    });
  }

  // Capture webcam frame with proper layering
  function captureWebcam() {
    const video = document.getElementById('video');
    if (!videoStream) {
      showStatus('Start webcam first!', 'error');
      return;
    }

    showStatus('Capturing person image...', 'info');

    const tempCanvas = document.createElement('canvas');
    tempCanvas.width = video.videoWidth || 640;
    tempCanvas.height = video.videoHeight || 480;
    const ctx = tempCanvas.getContext('2d');

    // Draw current video frame
    ctx.drawImage(video, 0, 0, tempCanvas.width, tempCanvas.height);

    const dataURL = tempCanvas.toDataURL("image/png");
    // Store captured image as Blob to upload later
    uploadedFile = dataURLtoBlob(dataURL);

    fabric.Image.fromURL(dataURL, function(img) {
      canvas.clear();
      canvas.setBackgroundColor('#f0f0f0', canvas.renderAll.bind(canvas));

      // Scale person image to fit canvas properly (90% of canvas)
      const scaleX = (canvas.width * 0.9) / img.width;
      const scaleY = (canvas.height * 0.9) / img.height;
      const scale = Math.min(scaleX, scaleY);

      // Center the person image
      const left = (canvas.width - img.width * scale) / 2;
      const top = (canvas.height - img.height * scale) / 2;

      img.set({
        scaleX: scale,
        scaleY: scale,
        left: left,
        top: top,
        selectable: false,
        evented: false,
        name: 'person_image',
        opacity: 1.0
      });

      backgroundImage = img;
      canvas.add(backgroundImage);
      backgroundImage.sendToBack();  // Person stays in background
      canvas.renderAll();

      showStatus('Person image captured! Click clothing items to overlay.', 'success');

      // Debug info
      console.log('Webcam capture completed:');
      console.log(`- Video dimensions: ${video.videoWidth}x${video.videoHeight}`);
      console.log(`- Captured image: ${img.width}x${img.height}`);
      console.log(`- Scaled to: ${img.width * scale}x${img.height * scale}`);
      console.log(`- Position: (${left}, ${top})`);

    });

    // Stop webcam after capture
    videoStream.getTracks().forEach(track => track.stop());
    video.style.display = "none";
    document.getElementById('capture-btn').disabled = true;
  }

  // Add lighting effect that follows mouse
  canvas.on('mouse:move', function(options) {
    const pointer = canvas.getPointer(options.e);
    const lighting = document.getElementById('lighting-effect');

    if (lighting) {
      lighting.style.left = (pointer.x - 100) + 'px';
      lighting.style.top = (pointer.y - 100) + 'px';
    }
  });

  // Add fabric texture to clothing items
  function addFabricTexture(obj) {
    obj.filters.push(
      new fabric.Image.filters.Convolute({
        matrix: [
          0, -1, 0,
          -1, 5, -1,
          0, -1, 0
        ]
      })
    );
    obj.applyFilters();
  }

  // Enhanced clothing addition with body-aware positioning
  function addClothEnhanced(src, name, id) {
    if (!backgroundImage) {
      showStatus("Please upload or capture a person image first!", 'error');
      return;
    }

    // Track selected dress id for backend processing
    selectedDressId = id;

    showStatus(`Adding ${name}...`, 'info');

    fabric.Image.fromURL(src, function(img) {
      // Get person image dimensions and position for accurate overlay
      const personWidth = backgroundImage.width * backgroundImage.scaleX;
      const personHeight = backgroundImage.height * backgroundImage.scaleY;
      const personLeft = backgroundImage.left;
      const personTop = backgroundImage.top;

      console.log(`Person image bounds: ${personWidth}x${personHeight} at (${personLeft}, ${personTop})`);
      console.log(`Clothing original size: ${img.width}x${img.height}`);

      // Calculate clothing size relative to person body
      // Use person's actual displayed size for accurate fitting
      const maxClothingWidth = personWidth * 0.8;   // 80% of person width
      const maxClothingHeight = personHeight * 0.9;  // 90% of person height

      const scaleX = maxClothingWidth / img.width;
      const scaleY = maxClothingHeight / img.height;
      const scale = Math.min(scaleX, scaleY, 2.0);  // Allow up to 2x scaling

      // Position clothing to overlay on person body center
      const clothingWidth = img.width * scale;
      const clothingHeight = img.height * scale;

      // Center horizontally on person, position at chest/upper body level
      const clothingLeft = personLeft + (personWidth - clothingWidth) / 2;
      const clothingTop = personTop + (personHeight * 0.2);  // Upper body position

      // Ensure clothing stays within person bounds
      const finalLeft = Math.max(personLeft, Math.min(clothingLeft, personLeft + personWidth - clothingWidth));
      const finalTop = Math.max(personTop, Math.min(clothingTop, personTop + personHeight - clothingHeight));

      console.log(`Final clothing position: (${finalLeft}, ${finalTop})`);
      console.log(`Final clothing size: ${clothingWidth}x${clothingHeight}`);

      img.set({
        scaleX: scale,
        scaleY: scale,
        left: finalLeft,
        top: finalTop,
        selectable: true,
        evented: true,
        name: name,
        opacity: 0.95,  // Slightly transparent for better overlay
        shadow: new fabric.Shadow({
          color: 'rgba(0,0,0,0.4)',
          blur: 12,
          offsetX: 3,
          offsetY: 3
        }),
        stroke: 'rgba(255,255,255,0.3)',
        strokeWidth: 2
      });

      // Double-check bounds to ensure proper overlay
      const clothingRight = finalLeft + clothingWidth;
      const clothingBottom = finalTop + clothingHeight;

      if (clothingRight > personLeft + personWidth) {
        img.left = personLeft + personWidth - clothingWidth;
        console.log('Adjusted right bound');
      }
      if (clothingBottom > personTop + personHeight) {
        img.top = personTop + personHeight - clothingHeight;
        console.log('Adjusted bottom bound');
      }

      canvas.add(img);
      canvas.setActiveObject(img);
      canvas.bringToFront(img);

      // Animate for smooth appearance
      img.animate('opacity', 0.95, {
        duration: 400,
        onChange: canvas.renderAll.bind(canvas)
      });

      canvas.renderAll();
      showStatus(`${name} overlaid on person!`, 'success');
    }, {
      crossOrigin: 'anonymous'
    }).catch(function(error) {
      console.error('Error loading clothing image:', error);
      showStatus(`Failed to load ${name}. Check console for details.`, 'error');
    });
  }

  // Process with AI (Realistic Try-On)
  function processWithAI() {
    if (!backgroundImage) {
      showStatus("Please add a person image first!", 'error');
      return;
    }
    if (!selectedDressId) {
      showStatus("Please select a clothing item first!", 'error');
      return;
    }

    showStatus('🔄 Uploading image for AI processing...', 'info');

    const formData = new FormData();
    if (uploadedFile) {
      // Ensure we send a File with a filename
      let fileToSend = uploadedFile;
      if (!(uploadedFile instanceof File)) {
        fileToSend = new File([uploadedFile], 'person.png', { type: 'image/png' });
      }
      formData.append('image', fileToSend);
    } else {
      // Fallback: use canvas content
      const dataUrl = document.getElementById('canvas').toDataURL('image/png');
      formData.append('image', new File([dataURLtoBlob(dataUrl)], 'person.png', { type: 'image/png' }));
    }
    formData.append('dress_id', selectedDressId);

    fetch('process_clothing.php', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showStatus('✨ AI try-on completed!', 'success');
        // Display result image on canvas
        fabric.Image.fromURL(data.result_path, function(img) {
          canvas.clear();
          img.set({
            selectable: false,
            evented: false,
            scaleX: canvas.width / img.width,
            scaleY: canvas.height / img.height
          });
          canvas.add(img);
          canvas.renderAll();
        }, { crossOrigin: 'anonymous' });
      } else {
        showStatus('Error: ' + data.error, 'error');
      }
    })
    .catch(err => {
      showStatus('Error: ' + err.message, 'error');
    });
  }

  // Helper to convert data URL to Blob
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

  // Reset canvas
  function resetCanvas() {
    canvas.clear();
    initializeCanvas();
    backgroundImage = null;

    // Reset webcam
    if (videoStream) {
      videoStream.getTracks().forEach(track => track.stop());
      document.getElementById('video').style.display = "none";
      document.getElementById('capture-btn').disabled = true;
    }

    showStatus('Canvas reset', 'info');
  }

  // Show status messages
  function showStatus(message, type) {
    const statusDiv = document.getElementById('status-message');
    statusDiv.textContent = message;
    statusDiv.className = type === 'error' ? 'error-message' :
                        type === 'success' ? 'success-message' : 'info-message';
  }

  // Debug function to check if images are loading
  function debugImageLoading() {
    console.log('=== DEBUGGING IMAGE LOADING ===');
    console.log(`Canvas size: ${canvas.width}x${canvas.height}`);

    const dressImages = document.querySelectorAll('.dress-item img');
    console.log(`Found ${dressImages.length} dress images`);

    dressImages.forEach((img, index) => {
      console.log(`Dress ${index + 1}:`, {
        src: img.src.substring(0, 50) + '...',
        complete: img.complete,
        naturalWidth: img.naturalWidth,
        naturalHeight: img.naturalHeight,
        display: img.style.display,
        opacity: img.style.opacity
      });

      if (img.complete && img.naturalWidth === 0) {
        console.log(`❌ Dress ${index + 1} failed to load properly`);
      } else if (img.complete && img.naturalWidth > 0) {
        console.log(`✅ Dress ${index + 1} loaded successfully: ${img.naturalWidth}x${img.naturalHeight}`);
      }
    });

    // Check canvas objects
    const objects = canvas.getObjects();
    console.log(`Canvas has ${objects.length} objects`);
    objects.forEach((obj, index) => {
      console.log(`Object ${index + 1}:`, {
        type: obj.type,
        name: obj.name,
        visible: obj.visible,
        opacity: obj.opacity,
        width: obj.width,
        height: obj.height,
        scaleX: obj.scaleX,
        scaleY: obj.scaleY
      });
    });
  }

  // Enhanced canvas interactions for body-aware clothing movement
  canvas.on('object:moving', function(e) {
    const obj = e.target;
    if (obj && obj.name && obj.name !== 'person_image') {
      // Get person image bounds for constraint
      const personLeft = backgroundImage.left;
      const personTop = backgroundImage.top;
      const personWidth = backgroundImage.width * backgroundImage.scaleX;
      const personHeight = backgroundImage.height * backgroundImage.scaleY;

      const personRight = personLeft + personWidth;
      const personBottom = personTop + personHeight;

      // Get clothing bounds
      const objWidth = obj.width * obj.scaleX;
      const objHeight = obj.height * obj.scaleY;
      const objLeft = obj.left;
      const objTop = obj.top;
      const objRight = objLeft + objWidth;
      const objBottom = objTop + objHeight;

      // Constrain clothing to stay within person bounds with some padding
      const padding = 10;

      if (objLeft < personLeft - padding) obj.left = personLeft - padding;
      if (objTop < personTop - padding) obj.top = personTop - padding;
      if (objRight > personRight + padding) obj.left = personRight + padding - objWidth;
      if (objBottom > personBottom + padding) obj.top = personBottom + padding - objHeight;

      // Ensure minimum visibility (at least 30% of clothing should be visible)
      const visibleWidth = Math.min(objRight, personRight) - Math.max(objLeft, personLeft);
      const visibleHeight = Math.min(objBottom, personBottom) - Math.max(objTop, personTop);

      if (visibleWidth < objWidth * 0.3 || visibleHeight < objHeight * 0.3) {
        // Reset to center position if too much is hidden
        obj.left = personLeft + (personWidth - objWidth) / 2;
        obj.top = personTop + personHeight * 0.15;
      }
    }
  });

  // Add keyboard shortcuts for better UX
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Delete' && canvas.getActiveObject()) {
      canvas.remove(canvas.getActiveObject());
      showStatus('Clothing item removed', 'info');
    }
    if (e.key === 'r' || e.key === 'R') {
      resetCanvas();
    }
    if (e.key === 's' || e.key === 'S') {
      saveResult();
    }
  });
</script>
</body>
</html>
