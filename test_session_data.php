<?php
session_start();
echo "<h2>Session Data Test</h2>";

if (isset($_SESSION['result_personality'])) {
    echo "<p>✅ result_personality: " . $_SESSION['result_personality'] . "</p>";
} else {
    echo "<p>❌ result_personality not found</p>";
}

if (isset($_SESSION['scorePercentageList'])) {
    echo "<p>✅ scorePercentageList found:</p>";
    echo "<ul>";
    foreach ($_SESSION['scorePercentageList'] as $key => $value) {
        echo "<li>$key: $value%</li>";
    }
    echo "</ul>";
} else {
    echo "<p>❌ scorePercentageList not found</p>";
}

if (isset($_SESSION['test_completed'])) {
    echo "<p>✅ test_completed: " . ($_SESSION['test_completed'] ? 'true' : 'false') . "</p>";
} else {
    echo "<p>❌ test_completed not found</p>";
}

echo "<hr>";
echo "<p><a href='test_form.php'>Take Test</a> | <a href='result.php'>View Results</a> | <a href='generate_pdf_advanced.php'>Download PDF</a></p>";
?>
