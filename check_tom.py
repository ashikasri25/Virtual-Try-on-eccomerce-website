#!/usr/bin/env python3
"""
Check TOM model specifically
"""

import os
import torch

def check_tom_model():
    """Check the TOM model architecture"""
    try:
        tom_path = './latest_net_U.pth'
        if os.path.exists(tom_path):
            print("🔍 Checking TOM model...")
            state_dict = torch.load(tom_path, map_location='cpu')

            # Find the first layer
            conv_keys = [k for k in state_dict.keys() if k.endswith('.weight') and ('conv' in k.lower() or 'enc' in k.lower())]
            if conv_keys:
                conv_keys.sort()
                first_layer_key = conv_keys[0]
                weight_shape = state_dict[first_layer_key].shape
                print(f"   First layer '{first_layer_key}': {weight_shape}")

                if len(weight_shape) == 4:
                    in_channels = weight_shape[1]
                    print(f"   Expected input channels: {in_channels}")
                    print(f"   Output channels: {weight_shape[0]}")

            # Check if there are any clues about expected input size
            print(f"   Total layers: {len(state_dict)}")

        else:
            print("❌ TOM model not found")

    except Exception as e:
        print(f"❌ Error: {str(e)}")

if __name__ == "__main__":
    check_tom_model()
