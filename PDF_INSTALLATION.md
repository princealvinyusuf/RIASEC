# PDF Download Feature Installation

## Overview
The RIASEC test system now includes a PDF download feature that allows users to download their test results as a PDF report.

## Installation Steps

### 1. Install Composer (if not already installed)
```bash
# Windows
# Download composer-setup.exe from https://getcomposer.org/download/
# Run the installer

# Linux/Mac
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Install mPDF Library
Navigate to your project directory and run:
```bash
composer install
```

This will install the mPDF library and create a `vendor/` directory.

### 3. Alternative Manual Installation
If you can't use Composer, you can manually download mPDF:

1. Download mPDF from: https://github.com/mpdf/mpdf/releases
2. Extract the files to a `vendor/mpdf/mpdf/` directory in your project
3. Create an autoloader or include the files manually

### 4. Verify Installation
The PDF download button "Unduh Laporan Ini" will appear under the "Keterangan Kode RIASEC" section on the results page.

## Features

### PDF Report Includes:
- Test result header with date and time
- Personality type result prominently displayed
- RIASEC code explanations
- Detailed personality type explanations
- Professional formatting with green theme
- Footer with system information

### Fallback Support
If mPDF is not available, the system will:
- Display an HTML version that can be printed to PDF
- Maintain all formatting and content
- Work without additional dependencies

## Usage

1. Complete the RIASEC test
2. View your results on the results page
3. Click the "Unduh Laporan Ini" button
4. The PDF will download automatically with filename: `laporan_riasec_YYYY-MM-DD_HH-MM-SS.pdf`

## Troubleshooting

### Common Issues:

1. **PDF not downloading**: Check if mPDF is properly installed
2. **Blank PDF**: Ensure all required files are included
3. **Styling issues**: The PDF uses inline CSS for consistent formatting

### Manual PDF Generation:
If the automatic download doesn't work, you can:
1. Open `generate_pdf.php` in your browser
2. Use browser's "Print to PDF" function
3. Save the generated PDF

## File Structure
```
RIASEC/
├── generate_pdf.php          # PDF generation script
├── composer.json             # Dependency management
├── result.php               # Updated with download button
└── vendor/                  # Created by Composer (after installation)
    └── mpdf/
        └── mpdf/
```

## Support
For issues with PDF generation, check:
1. PHP version (requires 7.4+)
2. mPDF installation
3. File permissions
4. Server configuration
