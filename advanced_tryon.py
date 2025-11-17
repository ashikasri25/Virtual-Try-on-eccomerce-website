import cv2
import numpy as np
import sys
import json
import os
import base64
import time
from PIL import Image
import warnings
import logging

# Suppress warnings and logging
warnings.filterwarnings('ignore')
logging.getLogger('tensorflow').setLevel(logging.ERROR)
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'

def download_image_from_url(url, local_path):
    """Download image from URL if needed"""
    import urllib.request
    import urllib.parse

    try:
        # Check if it's a URL
        if url.startswith('http://') or url.startswith('https://'):
            urllib.request.urlretrieve(url, local_path)
            return local_path
        else:
            # It's already a local path
            return url
    except Exception as e:
        raise Exception(f"Failed to download image from {url}: {str(e)}")

def detect_body_pose(image):
    """
    Simple body pose detection using OpenCV
    Returns approximate body landmarks for better clothing placement
    """
    try:
        height, width = image.shape[:2]

        # Convert to grayscale for contour detection
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

        # Apply Gaussian blur to reduce noise
        blurred = cv2.GaussianBlur(gray, (5, 5), 0)

        # Use simple thresholding to find body silhouette
        _, thresh = cv2.threshold(blurred, 50, 255, cv2.THRESH_BINARY_INV + cv2.THRESH_OTSU)

        # Find contours
        contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

        if not contours:
            # Fallback: assume center of image
            return {
                'center_x': width // 2,
                'center_y': height // 2,
                'shoulder_width': width // 3,
                'torso_height': height // 2,
                'body_contour': None
            }

        # Find the largest contour (assumed to be the body)
        largest_contour = max(contours, key=cv2.contourArea)

        # Get bounding rectangle
        x, y, w, h = cv2.boundingRect(largest_contour)

        # Calculate approximate landmarks
        center_x = x + w // 2
        center_y = y + h // 2

        # Estimate shoulder width (wider than torso)
        shoulder_width = int(w * 1.2)
        torso_height = int(h * 0.7)

        return {
            'center_x': center_x,
            'center_y': center_y,
            'shoulder_width': shoulder_width,
            'torso_height': torso_height,
            'body_contour': largest_contour,
            'body_bbox': (x, y, w, h)
        }

    except Exception as e:
        # Fallback if pose detection fails
        return {
            'center_x': width // 2,
            'center_y': height // 2,
            'shoulder_width': width // 3,
            'torso_height': height // 2,
            'body_contour': None,
            'body_bbox': (0, 0, width, height)
        }

def create_clothing_mask(dress_img, pose_info, user_height, user_width):
    """
    Create a realistic mask for clothing placement based on pose detection
    """
    mask = np.zeros((user_height, user_width), dtype=np.uint8)

    # Get pose information
    center_x = pose_info['center_x']
    center_y = pose_info['center_y']
    shoulder_width = pose_info['shoulder_width']
    torso_height = pose_info['torso_height']

    # Create elliptical mask for torso area
    # Make it slightly wider and taller for more realistic fit
    mask_width = min(shoulder_width, user_width // 2)
    mask_height = min(torso_height, user_height // 3)

    # Draw main torso ellipse
    cv2.ellipse(mask, (center_x, center_y), (mask_width, mask_height), 0, 0, 360, 255, -1)

    # Add shoulder areas
    shoulder_height = mask_height // 3
    cv2.ellipse(mask, (center_x, center_y - mask_height // 2), (mask_width, shoulder_height), 0, 0, 360, 255, -1)

    # Create gradient mask for smoother blending
    # Apply Gaussian blur for soft edges
    mask = cv2.GaussianBlur(mask, (25, 25), 0)

    # Normalize to 0-1 range
    mask = mask.astype(np.float32) / 255.0

    return mask

def match_clothing_perspective(dress_img, user_img, pose_info):
    """
    Adjust clothing perspective to match user's pose and body shape
    """
    try:
        user_height, user_width = user_img.shape[:2]
        dress_height, dress_width = dress_img.shape[:2]

        # Get pose information
        center_x = pose_info['center_x']
        center_y = pose_info['center_y']
        shoulder_width = pose_info['shoulder_width']

        # Calculate scaling factor based on body size
        scale_factor = shoulder_width / dress_width

        # Don't scale too much - keep clothing recognizable
        scale_factor = min(scale_factor, 1.5)
        scale_factor = max(scale_factor, 0.3)

        # Resize dress to match body proportions
        new_width = int(dress_width * scale_factor)
        new_height = int(dress_height * scale_factor)

        resized_dress = cv2.resize(dress_img, (new_width, new_height))

        # Center the dress on the body
        dress_x = max(0, center_x - new_width // 2)
        dress_y = max(0, center_y - new_height // 2)

        # Make sure it fits within the image
        if dress_x + new_width > user_width:
            dress_x = user_width - new_width
        if dress_y + new_height > user_height:
            dress_y = user_height - new_height

        return resized_dress, (dress_x, dress_y, new_width, new_height)

    except Exception as e:
        # Fallback: simple resize
        user_height, user_width = user_img.shape[:2]
        resized_dress = cv2.resize(dress_img, (user_width, user_height))
        return resized_dress, (0, 0, user_width, user_height)

def apply_realistic_clothing_overlay(user_img, dress_img, pose_info, alpha=0.7):
    """
    Apply clothing with realistic lighting and shadow effects
    """
    try:
        user_height, user_width = user_img.shape[:2]

        # Get adjusted dress and position
        adjusted_dress, (dress_x, dress_y, dress_w, dress_h) = match_clothing_perspective(dress_img, user_img, pose_info)

        # Create clothing mask
        mask = create_clothing_mask(adjusted_dress, pose_info, user_height, user_width)

        # Create output image
        result = user_img.copy().astype(np.float32)

        # Apply the clothing overlay
        for c in range(3):
            result[:, :, c] = result[:, :, c] * (1 - mask * alpha) + adjusted_dress[:, :, c] * mask * alpha

        # Add subtle shadow effects for realism
        shadow_mask = cv2.GaussianBlur(mask, (15, 15), 0)
        shadow_mask = shadow_mask * 0.1  # Light shadow

        # Apply shadow (make areas around clothing slightly darker)
        for c in range(3):
            result[:, :, c] = result[:, :, c] * (1 - shadow_mask * 0.1)

        # Enhance contrast slightly for better visibility
        result = np.clip(result, 0, 255).astype(np.uint8)

        return result

    except Exception as e:
        # Fallback to simple overlay
        return simple_clothing_tryon_fallback(user_img, dress_img)

def simple_clothing_tryon_fallback(user_img, dress_img):
    """Fallback method if pose detection fails"""
    user_height, user_width = user_img.shape[:2]
    dress_img = cv2.resize(dress_img, (user_width, user_height))

    # Simple blend
    alpha = 0.6
    mask = np.zeros((user_height, user_width), dtype=np.float32)
    center_x, center_y = user_width // 2, user_height // 2

    cv2.ellipse(mask, (center_x, center_y), (user_width//3, user_height//2), 0, 0, 360, 1.0, -1)
    mask = cv2.GaussianBlur(mask, (31, 31), 0)

    result = user_img.astype(np.float32)
    for c in range(3):
        result[:, :, c] = result[:, :, c] * (1 - mask * alpha) + dress_img[:, :, c] * mask * alpha

    return np.clip(result, 0, 255).astype(np.uint8)

def simple_clothing_tryon(user_image_path, dress_image_path, output_path):
    """
    Advanced clothing try-on using pose detection and realistic overlay
    """
    try:
        # Handle dress image URL if needed
        if dress_image_path.startswith('http://') or dress_image_path.startswith('https://'):
            temp_dress_path = os.path.join(os.path.dirname(output_path), 'temp_dress.jpg')
            dress_image_path = download_image_from_url(dress_image_path, temp_dress_path)

        # Convert relative path to absolute if needed
        if not os.path.isabs(dress_image_path):
            script_dir = os.path.dirname(os.path.abspath(__file__))
            dress_image_path = dress_image_path.replace('/', os.sep).replace('\\', os.sep)
            dress_image_path = os.path.normpath(os.path.join(script_dir, dress_image_path))

        # Read images
        user_img = cv2.imread(user_image_path)
        dress_img = cv2.imread(dress_image_path)

        if user_img is None:
            raise ValueError(f"Could not read user image from {user_image_path}")
        if dress_img is None:
            raise ValueError(f"Could not read dress image from {dress_image_path}")

        # Detect body pose for better placement
        pose_info = detect_body_pose(user_img)

        # Apply realistic clothing overlay
        result = apply_realistic_clothing_overlay(user_img, dress_img, pose_info)

        # Save result
        cv2.imwrite(output_path, result)

        return output_path

    except Exception as e:
        raise Exception(f"Error in clothing try-on: {str(e)}")

def image_to_base64(image_path):
    """Convert image to base64 string"""
    try:
        with open(image_path, "rb") as image_file:
            return base64.b64encode(image_file.read()).decode('utf-8')
    except Exception as e:
        raise Exception(f"Error converting image to base64: {str(e)}")

def process_clothing_tryon(user_image_path, dress_image_path, dress_id):
    """
    Process clothing try-on and return the result
    """
    try:
        # Generate output path
        base_name = os.path.splitext(os.path.basename(user_image_path))[0]
        output_dir = os.path.join(os.path.dirname(user_image_path), 'output', 'clothing_tryon')
        os.makedirs(output_dir, exist_ok=True)

        output_filename = f"clothing_tryon_{dress_id}_{base_name}_{int(time.time())}.jpg"
        output_path = os.path.join(output_dir, output_filename)

        # Process clothing try-on
        result_path = simple_clothing_tryon(user_image_path, dress_image_path, output_path)

        # Convert result to base64 for web display
        result_base64 = image_to_base64(result_path)

        return {
            "success": True,
            "result_path": result_path,
            "result_base64": result_base64,
            "dress_id": dress_id
        }

    except Exception as e:
        return {
            "success": False,
            "error": str(e)
        }

if __name__ == "__main__":
    import time

    if len(sys.argv) < 4:
        print(json.dumps({"error": "Usage: python clothing_tryon.py <user_image_path> <dress_image_path> <dress_id>"}))
        sys.exit(1)

    user_image_path = sys.argv[1]
    dress_image_path = sys.argv[2]
    dress_id = sys.argv[3]

    result = process_clothing_tryon(user_image_path, dress_image_path, dress_id)
    print(json.dumps(result))
