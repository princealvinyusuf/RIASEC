<?php
session_start();
echo "<h2>Chart Layout Test</h2>";

// Sample data for testing
$scorePercentageList = array(
    'R' => '25.5',
    'I' => '18.2', 
    'A' => '12.8',
    'S' => '22.1',
    'E' => '15.4',
    'C' => '6.0'
);

echo "<p>Testing chart layout with sample data:</p>";
echo "<ul>";
foreach ($scorePercentageList as $key => $value) {
    echo "<li>$key: $value%</li>";
}
echo "</ul>";

echo "<hr>";
echo "<h3>Chart Preview:</h3>";
?>

<style>
    .chart-container {
        height: 400px;
        width: 100%;
        margin: 20px 0;
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        position: relative;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .chart-title {
        font-size: 1.1rem;
        font-weight: bold;
        color: #333;
        text-align: center;
        margin-bottom: 20px;
        font-family: Arial, sans-serif;
    }
    .chart-bars {
        display: flex;
        justify-content: space-around;
        align-items: end;
        height: 250px;
        margin-top: 20px;
        padding: 0 10px;
        position: relative;
        flex-direction: row;
    }
    .chart-bar {
        width: 60px;
        background: linear-gradient(to top, #007bff, #0056b3);
        border-radius: 4px 4px 0 0;
        position: relative;
        margin: 0 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        min-height: 5px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .chart-bar-highest {
        background: linear-gradient(to top, #28a745, #20c997) !important;
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3) !important;
    }
    .chart-bar-highest .chart-bar-value {
        color: #28a745 !important;
        font-weight: bold;
    }
    .chart-bar-label {
        position: absolute;
        bottom: -30px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.75rem;
        font-weight: 600;
        color: #333;
        text-align: center;
        width: 100%;
        font-family: Arial, sans-serif;
        white-space: nowrap;
    }
    .chart-bar-value {
        position: absolute;
        top: -25px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.8rem;
        font-weight: bold;
        color: #007bff;
        background: rgba(255,255,255,0.9);
        padding: 2px 6px;
        border-radius: 3px;
        font-family: Arial, sans-serif;
    }
</style>

<div class="chart-container">
    <div class="chart-title">RIASEC test results in percentages</div>
    <div class="chart-bars">
        <?php
        $maxPercentage = max($scorePercentageList);
        $personalityTypes = array(
            'R' => 'Realistic',
            'I' => 'Investigative', 
            'A' => 'Artistic',
            'S' => 'Social',
            'E' => 'Enterprising',
            'C' => 'Conventional'
        );
        
        $maxBarHeight = 200;
        $minBarHeight = 10;
        
        foreach ($personalityTypes as $code => $name) {
            $percentage = floatval($scorePercentageList[$code]);
            
            if ($maxPercentage > 0) {
                $barHeight = max($minBarHeight, ($percentage / $maxPercentage) * $maxBarHeight);
            } else {
                $barHeight = $minBarHeight;
            }
            
            $isHighest = ($percentage == $maxPercentage && $percentage > 0);
            $barClass = $isHighest ? 'chart-bar chart-bar-highest' : 'chart-bar';
            
            echo '<div class="' . $barClass . '" style="height: ' . $barHeight . 'px;">';
            echo '<div class="chart-bar-value">' . number_format($percentage, 1) . '%</div>';
            echo '<div class="chart-bar-label">' . $name . '</div>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<hr>
<p><a href="test_form.php">Take Test</a> | <a href="result.php">View Results</a> | <a href="generate_pdf_advanced.php">Download PDF</a></p>
