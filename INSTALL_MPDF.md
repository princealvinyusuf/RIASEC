# Installing mPDF for PDF Generation

## Current Issue
The PDF download is currently generating HTML files instead of PDF files because mPDF is not installed.

## Quick Fix Options

### Option 1: Install mPDF via Composer (Recommended)

1. **Install Composer** (if not already installed):
   - Download from: https://getcomposer.org/download/
   - Run the installer

2. **Install mPDF**:
   ```bash
   # Navigate to your project directory
   cd /path/to/your/RIASEC/project
   
   # Install mPDF
   composer require mpdf/mpdf
   ```

3. **Verify Installation**:
   - Check that `vendor/` folder exists
   - Check that `vendor/mpdf/` folder exists

### Option 2: Manual Installation

1. **Download mPDF**:
   - Go to: https://github.com/mpdf/mpdf/releases
   - Download the latest release

2. **Extract to your project**:
   ```
   RIASEC/
   ├── vendor/
   │   └── mpdf/
   │       └── mpdf/
   │           ├── src/
   │           ├── composer.json
   │           └── ...
   ```

3. **Create autoloader**:
   Create a simple autoloader in your project root:
   ```php
   // autoload.php
   <?php
   require_once 'vendor/mpdf/mpdf/src/Mpdf.php';
   ```

### Option 3: Use Alternative PDF Library

If mPDF doesn't work, you can use TCPDF:

```bash
composer require tecnickcom/tcpdf
```

Then update `generate_pdf.php` to use TCPDF instead of mPDF.

## Testing

After installation:

1. **Test the download button** - Should now download `.pdf` files
2. **Check file size** - PDF files should be larger than HTML files
3. **Open the PDF** - Should open in a PDF viewer

## Troubleshooting

### If still getting HTML files:

1. **Check if mPDF is loaded**:
   ```php
   <?php
   if (class_exists('Mpdf\Mpdf')) {
       echo "mPDF is available";
   } else {
       echo "mPDF is NOT available";
   }
   ```

2. **Check file permissions**:
   - Ensure `vendor/` folder is readable
   - Ensure PHP has write permissions

3. **Check PHP version**:
   - mPDF requires PHP 7.4 or higher

### Alternative: Use Browser Print-to-PDF

If mPDF installation fails, the HTML version can be converted to PDF using:
1. Open the downloaded HTML file in browser
2. Press Ctrl+P (or Cmd+P on Mac)
3. Select "Save as PDF"
4. Save the file

## File Structure After Installation

```
RIASEC/
├── generate_pdf.php          # PDF generation script
├── composer.json             # Dependency management
├── vendor/                   # Created by Composer
│   ├── autoload.php         # Autoloader
│   └── mpdf/
│       └── mpdf/
│           ├── src/
│           └── ...
└── result.php               # Results page with download button
```

## Support

If you continue having issues:
1. Check PHP error logs
2. Verify Composer installation
3. Ensure all dependencies are installed
4. Test with a simple mPDF example first
