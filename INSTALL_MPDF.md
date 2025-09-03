# Install mPDF for PDF Generation

## Quick Installation (Recommended)

### Option 1: Using Composer (Easiest)
```bash
# Navigate to your project directory
cd /path/to/your/RIASEC/project

# Install mPDF
composer require mpdf/mpdf

# If composer is not installed, download it first:
# Windows: Download composer-setup.exe from https://getcomposer.org/download/
# Linux/Mac: curl -sS https://getcomposer.org/installer | php
```

### Option 2: Manual Installation
1. Download mPDF from: https://github.com/mpdf/mpdf/releases
2. Extract to `vendor/mpdf/mpdf/` in your project
3. Create a simple autoloader or include the files manually

## Verify Installation
After installation, the PDF download should work properly and generate actual PDF files instead of HTML files.

## Current Behavior
- **With mPDF**: Downloads as `laporan_riasec_YYYY-MM-DD_HH-MM-SS.pdf`
- **Without mPDF**: Downloads as `laporan_riasec_YYYY-MM-DD_HH-MM-SS.html` (can be printed to PDF)

## Troubleshooting
If you still see HTML content in browser:
1. Check if mPDF is properly installed
2. Verify PHP has write permissions
3. Check server error logs
4. Try the manual installation method

## File Structure After Installation
```
RIASEC/
├── vendor/
│   └── mpdf/
│       └── mpdf/
├── generate_pdf.php
├── result.php
└── composer.json
```
