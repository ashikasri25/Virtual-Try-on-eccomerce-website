#!/usr/bin/env python3
"""
Create a test script that loads the actual pretrained models and inspects their architecture
"""

import os
import torch
import logging

# Set up logging
logging.basicConfig(filename="tryon.log", level=logging.INFO)

def analyze_pretrained_models():
    """Load the pretrained models and analyze their architecture"""
    try:
        print("🔍 Analyzing pretrained model architectures...")

        models = {
            'GMM': './latest_net_G.pth',
            'TOM': './latest_net_U.pth',
            'SEG': './latest_net_G1.pth',
            'ALIAS': './latest_net_G2.pth'
        }

        for model_name, model_path in models.items():
            if os.path.exists(model_path):
                print(f"\n📊 Analyzing {model_name} model...")
                try:
                    # Load the model
                    state_dict = torch.load(model_path, map_location='cpu')
                    print(f"   ✅ Loaded {model_name} model")
                    print(f"   📏 State dict size: {len(state_dict)} parameters")

                    # Find the first layer to determine input channels
                    first_layer_key = None
                    for key in sorted(state_dict.keys()):
                        if 'weight' in key and ('conv' in key.lower() or 'enc' in key.lower()):
                            first_layer_key = key
                            break

                    if first_layer_key:
                        weight_shape = state_dict[first_layer_key].shape
                        print(f"   🎯 First layer '{first_layer_key}': {weight_shape}")

                        if len(weight_shape) == 4:  # Conv2d weight
                            in_channels = weight_shape[1]
                            out_channels = weight_shape[0]
                            kernel_h, kernel_w = weight_shape[2], weight_shape[3]
                            print(f"   📥 Expected input channels: {in_channels}")
                            print(f"   📤 Output channels: {out_channels}")
                            print(f"   🔍 Kernel size: {kernel_h}x{kernel_w}")
                        else:
                            print(f"   ❓ Unexpected weight shape: {weight_shape}")
                    else:
                        print(f"   ⚠️  No convolutional layers found")

                    # Check total parameter count
                    total_params = sum(p.numel() for p in state_dict.values())
                    print(f"   📊 Total parameters: {total_params:,}")

                except Exception as e:
                    print(f"   ❌ Error analyzing {model_name}: {str(e)}")
            else:
                print(f"   ❌ Model file not found: {model_path}")

        print("\n✅ Model analysis completed")

    except Exception as e:
        print(f"❌ Model analysis failed: {str(e)}")
        return False

if __name__ == "__main__":
    analyze_pretrained_models()
