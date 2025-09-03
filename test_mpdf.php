<?php
// Simple test to check if mPDF is working

echo "<h2>mPDF Installation Test</h2>";

// Check if vendor/autoload.php exists
if (file_exists('vendor/autoload.php')) {
    echo "<p>✅ vendor/autoload.php exists</p>";
    require_once 'vendor/autoload.php';
} else {
    echo "<p>❌ vendor/autoload.php not found</p>";
}

// Check if mPDF class exists
if (class_exists('Mpdf\Mpdf')) {
    echo "<p>✅ mPDF class is available</p>";
    
    try {
        // Try to create a simple PDF
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML('<h1>Test PDF</h1><p>If you can see this, mPDF is working!</p>');
        
        // Set headers for download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="test_mpdf.pdf"');
        
        // Output the PDF
        $mpdf->Output('test_mpdf.pdf', 'D');
        exit;
        
    } catch (Exception $e) {
        echo "<p>❌ Error creating PDF: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ mPDF class is NOT available</p>";
    echo "<p>Please install mPDF using: <code>composer require mpdf/mpdf</code></p>";
}

echo "<hr>";
echo "<h3>Installation Instructions:</h3>";
echo "<ol>";
echo "<li>Install Composer from https://getcomposer.org/download/</li>";
echo "<li>Run: <code>composer require mpdf/mpdf</code></li>";
echo "<li>Refresh this page to test again</li>";
echo "</ol>";
?>
