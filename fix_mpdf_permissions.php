<?php
// Script to fix mPDF permissions and directory issues

echo "<h2>Fixing mPDF Permissions</h2>";

// Define the paths
$mpdf_tmp_dir = __DIR__ . '/vendor/mpdf/mpdf/tmp';
$mpdf_cache_dir = __DIR__ . '/vendor/mpdf/mpdf/cache';

echo "<p><strong>Current directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>mPDF tmp directory:</strong> " . $mpdf_tmp_dir . "</p>";
echo "<p><strong>mPDF cache directory:</strong> " . $mpdf_cache_dir . "</p>";

// Create directories if they don't exist
$directories = [$mpdf_tmp_dir, $mpdf_cache_dir];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p>✅ Created directory: " . $dir . "</p>";
        } else {
            echo "<p>❌ Failed to create directory: " . $dir . "</p>";
        }
    } else {
        echo "<p>✅ Directory exists: " . $dir . "</p>";
    }
}

// Set permissions
foreach ($directories as $dir) {
    if (file_exists($dir)) {
        if (chmod($dir, 0755)) {
            echo "<p>✅ Set permissions on: " . $dir . "</p>";
        } else {
            echo "<p>❌ Failed to set permissions on: " . $dir . "</p>";
        }
    }
}

// Test if directories are writable
foreach ($directories as $dir) {
    if (is_writable($dir)) {
        echo "<p>✅ Directory is writable: " . $dir . "</p>";
    } else {
        echo "<p>❌ Directory is NOT writable: " . $dir . "</p>";
    }
}

// Create a test file to verify write permissions
$test_file = $mpdf_tmp_dir . '/test_write.txt';
if (file_put_contents($test_file, 'test')) {
    echo "<p>✅ Successfully wrote test file: " . $test_file . "</p>";
    unlink($test_file); // Clean up
} else {
    echo "<p>❌ Failed to write test file: " . $test_file . "</p>";
}

echo "<hr>";
echo "<h3>Manual Fix Instructions:</h3>";
echo "<p>If the automatic fix above doesn't work, try these manual steps:</p>";
echo "<ol>";
echo "<li><strong>Create directories manually:</strong><br>";
echo "<code>mkdir -p " . $mpdf_tmp_dir . "</code><br>";
echo "<code>mkdir -p " . $mpdf_cache_dir . "</code></li>";
echo "<li><strong>Set permissions:</strong><br>";
echo "<code>chmod 755 " . $mpdf_tmp_dir . "</code><br>";
echo "<code>chmod 755 " . $mpdf_cache_dir . "</code></li>";
echo "<li><strong>Set ownership (if needed):</strong><br>";
echo "<code>chown www-data:www-data " . $mpdf_tmp_dir . "</code><br>";
echo "<code>chown www-data:www-data " . $mpdf_cache_dir . "</code></li>";
echo "</ol>";

echo "<hr>";
echo "<h3>Alternative Solution:</h3>";
echo "<p>If you can't fix the permissions, we can configure mPDF to use a different temp directory:</p>";
echo "<p>Update <code>generate_pdf.php</code> to specify a custom temp directory:</p>";
echo "<pre>";
echo '$mpdf = new \\Mpdf\\Mpdf([
    \'mode\' => \'utf-8\',
    \'format\' => \'A4\',
    \'margin_left\' => 15,
    \'margin_right\' => 15,
    \'margin_top\' => 15,
    \'margin_bottom\' => 15,
    \'tempDir\' => __DIR__ . \'/tmp\', // Custom temp directory
]);';
echo "</pre>";

echo "<p><a href='test_mpdf.php'>Test mPDF again</a></p>";
?>
