# Virtual Try-On - Complete Setup Guide

## Current Status

✅ Python is installed and working  
✅ All Python libraries are installed (OpenCV, NumPy, Pillow)  
✅ Database table created  
✅ All files are in place  
⚠️ **PHP cannot execute Python scripts** (needs configuration)

## The Problem

Your XAMPP installation has PHP execution functions disabled for security. This prevents the web server from running Python scripts.

## Quick Fix (5 minutes)

### Step 1: Enable PHP Execution

1. Open **XAMPP Control Panel**
2. Click **Config** button next to Apache
3. Select **PHP (php.ini)**
4. Find this line (around line 300-400):
   ```
   disable_functions = exec,passthru,shell_exec,system,...
   ```
5. Remove `exec`, `shell_exec`, `system`, and `passthru` from the list
6. Save the file
7. Click **Stop** then **Start** for Apache in XAMPP

### Step 2: Verify It Works

Visit: **http://localhost/glamwear/test_exec.php**

You should see green checkmarks for exec() and shell_exec()

### Step 3: Test Try-On

Visit: **http://localhost/glamwear/clothing_tryon.php**

1. Upload a photo
2. Select a clothing item
3. Click "Try On Clothing"
4. Wait for the result

## Alternative: If You Can't Edit php.ini

If you don't have permission to edit php.ini, use this workaround:

### Create a Batch File Wrapper

1. Create `run_tryon.bat` in the glamwear folder:
```batch
@echo off
python "%~dp0clothing_tryon.py" %*
```

2. Modify `process_clothing.php` to call the batch file instead of Python directly

## Troubleshooting

### Test Pages (Use These!)

1. **http://localhost/glamwear/check_status.php** - Overall status
2. **http://localhost/glamwear/test_exec.php** - Test PHP execution
3. **http://localhost/glamwear/fix_php_exec.php** - Fix instructions
4. **http://localhost/glamwear/test_tryon_integration.php** - Integration test

### Common Issues

**Issue: "Python not found"**
- Solution: Already fixed! Python is configured at: `python`

**Issue: "Module not found"**
- Solution: Already fixed! All libraries are installed

**Issue: "Cannot execute"**
- Solution: Enable exec() in php.ini (see Step 1 above)

**Issue: "Database error"**
- Solution: Already fixed! Table is created

### Check What's Working

Run this in Command Prompt:
```bash
cd C:\xampp\htdocs\glamwear
python simple_test.py
```

You should see: ✓ OpenCV, NumPy, PIL all working

## Files Overview

### Main Files
- `clothing_tryon.php` - Frontend page
- `process_clothing.php` - Backend processor
- `clothing_tryon.py` - Python AI script

### Configuration
- `python_config.php` - Python path (auto-generated)
- `requirements.txt` - Python dependencies

### Testing & Diagnostics
- `check_status.php` - ⭐ **START HERE**
- `test_exec.php` - Test PHP execution
- `fix_php_exec.php` - Fix instructions
- `test_tryon_integration.php` - Full integration test
- `simple_test.py` - Test Python environment

### Setup Scripts
- `find_python.php` - Find Python installation
- `setup_database.php` - Create database table
- `install_tryon.bat` - One-click installer

## What I Fixed

1. ✅ Added missing `import time` to Python script
2. ✅ Created Python path detection system
3. ✅ Installed all required Python libraries
4. ✅ Created database table
5. ✅ Improved error handling
6. ✅ Enhanced image processing algorithm
7. ✅ Created comprehensive diagnostic tools

## Next Steps

1. **Enable PHP execution** (see Step 1 above)
2. **Visit:** http://localhost/glamwear/check_status.php
3. **Test:** http://localhost/glamwear/clothing_tryon.php

## Need Help?

1. Visit: http://localhost/glamwear/fix_php_exec.php
2. Follow the instructions there
3. All diagnostic tools are ready to help you

---

**The try-on feature is 99% ready!** Just need to enable PHP execution in XAMPP.
