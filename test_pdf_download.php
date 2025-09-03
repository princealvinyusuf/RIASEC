<?php
// Test the PDF download feature

echo "<h2>Testing PDF Download Feature</h2>";

// Check if we have test results
session_start();

if (isset($_SESSION['result_personality'])) {
    echo "<p>✅ Test results found in session</p>";
    echo "<p><strong>Personality type:</strong> " . $_SESSION['result_personality'] . "</p>";
    
    echo "<hr>";
    echo "<h3>Test Options:</h3>";
    echo "<p><a href='generate_pdf.php' class='btn btn-primary'>Download PDF Report</a></p>";
    echo "<p><a href='test_mpdf.php' class='btn btn-secondary'>Test mPDF Only</a></p>";
    
} else {
    echo "<p>❌ No test results found in session</p>";
    echo "<p>Please complete a test first, then come back here.</p>";
    echo "<p><a href='test_form.php'>Take the Test</a></p>";
}

echo "<hr>";
echo "<h3>Debug Information:</h3>";

// Check mPDF availability
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

if (class_exists('Mpdf\Mpdf')) {
    echo "<p>✅ mPDF is available</p>";
} else {
    echo "<p>❌ mPDF is NOT available</p>";
}

// Check temp directory
$temp_dir = sys_get_temp_dir();
echo "<p><strong>System temp directory:</strong> " . $temp_dir . "</p>";
echo "<p><strong>Writable:</strong> " . (is_writable($temp_dir) ? 'Yes' : 'No') . "</p>";

// Check custom temp directory
$custom_temp = __DIR__ . '/tmp';
echo "<p><strong>Custom temp directory:</strong> " . $custom_temp . "</p>";
echo "<p><strong>Exists:</strong> " . (file_exists($custom_temp) ? 'Yes' : 'No') . "</p>";
echo "<p><strong>Writable:</strong> " . (is_writable($custom_temp) ? 'Yes' : 'No') . "</p>";

echo "<hr>";
echo "<h3>Instructions:</h3>";
echo "<ol>";
echo "<li>If you have test results, click 'Download PDF Report' to test the main feature</li>";
echo "<li>If mPDF is available, it should download a PDF file</li>";
echo "<li>If mPDF fails, it will fallback to HTML that can be printed to PDF</li>";
echo "</ol>";
?>

<style>
.btn {
    display: inline-block;
    padding: 10px 20px;
    margin: 5px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.btn-secondary {
    background-color: #6c757d;
}
</style>
