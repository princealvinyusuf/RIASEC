<?php
session_start();
echo "<h2>PDF Chart Test</h2>";

// Set sample session data for testing
$_SESSION['result_personality'] = 'RIA';
$_SESSION['scorePercentageList'] = array(
    'R' => '25.5',
    'I' => '18.2', 
    'A' => '12.8',
    'S' => '22.1',
    'E' => '15.4',
    'C' => '6.0'
);

echo "<p>✅ Session data set for testing:</p>";
echo "<ul>";
foreach ($_SESSION['scorePercentageList'] as $key => $value) {
    echo "<li>$key: $value%</li>";
}
echo "</ul>";

echo "<p>Result personality: " . $_SESSION['result_personality'] . "</p>";

echo "<hr>";
echo "<h3>Test PDF Generation:</h3>";
echo "<p><a href='generate_pdf_advanced.php' target='_blank'>Test PDF Download (Advanced)</a></p>";
echo "<p><a href='generate_pdf.php' target='_blank'>Test PDF Download (Regular)</a></p>";

echo "<hr>";
echo "<h3>Current Session Status:</h3>";
echo "<p><a href='test_session_data.php'>Check Session Data</a></p>";
echo "<p><a href='test_chart_layout.php'>View Chart Layout</a></p>";

echo "<hr>";
echo "<p><a href='test_form.php'>Take Test</a> | <a href='result.php'>View Results</a></p>";
?>
