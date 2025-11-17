#!/usr/bin/env python3
"""
Test to inspect the pretrained model architectures
"""

import os
import torch
import logging

# Set up logging
logging.basicConfig(filename="tryon.log", level=logging.INFO)

def inspect_model_architecture():
    """Inspect the architecture of the pretrained models"""
    try:
        print("🔍 Inspecting pretrained model architectures...")

        # Load each model and inspect its first layer
        models = {
            'GMM': './latest_net_G.pth',
            'TOM': './latest_net_U.pth',
            'SEG': './latest_net_G1.pth',
            'ALIAS': './latest_net_G2.pth'
        }

        for model_name, model_path in models.items():
            if os.path.exists(model_path):
                print(f"\n📊 Inspecting {model_name} model...")
                try:
                    # Load the model
                    state_dict = torch.load(model_path, map_location='cpu')

                    # Try to find the first convolutional layer
                    conv_keys = [k for k in state_dict.keys() if k.endswith('.weight') and ('conv' in k.lower() or 'enc' in k.lower())]
                    if conv_keys:
                        # Sort by layer number to get the first layer
                        conv_keys.sort()
                        first_layer_key = conv_keys[0]
                        weight_shape = state_dict[first_layer_key].shape
                        print(f"   First layer '{first_layer_key}': {weight_shape}")

                        if len(weight_shape) == 4:  # Conv2d weight
                            in_channels = weight_shape[1]
                            print(f"   Expected input channels: {in_channels}")
                            print(f"   Output channels: {weight_shape[0]}")
                            print(f"   Kernel size: {weight_shape[2]}x{weight_shape[3]}")
                        else:
                            print(f"   Unexpected weight shape: {weight_shape}")
                    else:
                        print(f"   No convolutional layers found in {model_name}")

                    # Also check for any other clues about input size
                    input_keys = [k for k in state_dict.keys() if 'input' in k.lower()]
                    if input_keys:
                        print(f"   Input-related keys: {input_keys[:3]}")

                except Exception as e:
                    print(f"   ❌ Error inspecting {model_name}: {str(e)}")
            else:
                print(f"   ❌ Model file not found: {model_path}")

        print("\n✅ Model inspection completed")

    except Exception as e:
        print(f"❌ Model inspection failed: {str(e)}")
        return False

if __name__ == "__main__":
    inspect_model_architecture()
