<?php
// Include required files
include 'includes/db.php';
include 'util_functions.php';

// Check if mPDF is available, if not, we'll use a fallback
$mpdf_available = false;

// Try to include mPDF if it exists
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

try {
    if (class_exists('Mpdf\Mpdf')) {
        $mpdf_available = true;
    }
} catch (Exception $e) {
    $mpdf_available = false;
}

// Function to format content for PDF
function formatContentForPDF($content) {
    $sections = array();
    
    $sectionMarkers = array(
        'Penjelasan:' => 'Penjelasan',
        'Kekuatan:' => 'Kekuatan',
        'Lingkungan favorit:' => 'Lingkungan Favorit',
        'Contoh karir:' => 'Contoh Karir'
    );
    
    $currentSection = '';
    $currentContent = '';
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $foundSection = false;
        foreach ($sectionMarkers as $marker => $sectionName) {
            if (strpos($line, $marker) === 0) {
                if (!empty($currentSection) && !empty($currentContent)) {
                    $sections[$currentSection] = trim($currentContent);
                }
                $currentSection = $sectionName;
                $currentContent = '';
                $foundSection = true;
                break;
            }
        }
        
        if (!$foundSection && !empty($currentSection)) {
            $currentContent .= $line . "\n";
        } elseif (!$foundSection && empty($currentSection)) {
            $sections['Title'] = $line;
        }
    }
    
    if (!empty($currentSection) && !empty($currentContent)) {
        $sections[$currentSection] = trim($currentContent);
    }
    
    return $sections;
}

// Check if we have valid test results
session_start();

// Initialize result_personality
$result_personality = '';

// First try to get from session
if (isset($_SESSION['result_personality'])) {
    $result_personality = $_SESSION['result_personality'];
} else {
    // Try to get the latest test result from database
    $latestResult = null;
    
    // Get the most recent test result
    $resultQuery = "SELECT result FROM personality_test_scores ORDER BY created_at DESC LIMIT 1";
    $resultRes = mysqli_query($connection, $resultQuery);
    if ($resultRes && mysqli_num_rows($resultRes) > 0) {
        $latestScore = mysqli_fetch_assoc($resultRes);
        $result_personality = $latestScore['result'];
    }
}

// If still no result, try to get from POST data (if form was just submitted)
if (empty($result_personality) && isset($_POST['submit'])) {
    // Temporarily set a flag to prevent redirect
    $_POST['can_save_data'] = 'true';
    getPersonalityTestResults();
}

// If still no result, redirect to test form
if (empty($result_personality)) {
    header("Location: test_form.php?message=REQ");
    exit;
}

// Get score percentages for the chart
$scorePercentageList = array('R'=>'0','I'=>'0','A'=>'0','S'=>'0','E'=>'0','C'=>'0');

// First try to get from session if available
if (isset($_SESSION['scorePercentageList'])) {
    $scorePercentageList = $_SESSION['scorePercentageList'];
} else {
    // Get the latest test scores from database
    $scoreQuery = "SELECT realistic, investigative, artistic, social, enterprising, conventional FROM personality_test_scores ORDER BY created_at DESC LIMIT 1";
    $scoreRes = mysqli_query($connection, $scoreQuery);
    if ($scoreRes && mysqli_num_rows($scoreRes) > 0) {
        $scoreData = mysqli_fetch_assoc($scoreRes);
        $scorePercentageList['R'] = $scoreData['realistic'];
        $scorePercentageList['I'] = $scoreData['investigative'];
        $scorePercentageList['A'] = $scoreData['artistic'];
        $scorePercentageList['S'] = $scoreData['social'];
        $scorePercentageList['E'] = $scoreData['enterprising'];
        $scorePercentageList['C'] = $scoreData['conventional'];
    }
}

// Fetch paragraphs for the result personality type
$paras = array();

// First try to get paragraphs for the actual result personality type
$res = mysqli_query($connection, "SELECT position, content FROM riasec_paragraphs WHERE code='" . mysqli_real_escape_string($connection, $result_personality) . "' ORDER BY position ASC");
if ($res && mysqli_num_rows($res) > 0) {
    while ($r = mysqli_fetch_assoc($res)) {
        $paras[] = $r['content'];
    }
} else {
    // If no paragraphs found for the result type, get paragraphs for 'C' as fallback
    $res = mysqli_query($connection, "SELECT position, content FROM riasec_paragraphs WHERE code='C' ORDER BY position ASC");
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $paras[] = $r['content'];
        }
    }
}

// Create HTML content for PDF with advanced chart
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Tes RIASEC</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
            color: #333;
        }
        .alert-success { 
            background-color: #d1e7dd; 
            border: 1px solid #badbcc; 
            color: #0f5132; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 5px; 
            text-align: center;
        }
        .alert-heading {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
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

        .section { 
            margin: 20px 0; 
        }
        .section-title { 
            font-weight: bold; 
            color: #28a745; 
            margin-bottom: 10px; 
            font-size: 1rem;
        }
        .code-list { 
            margin: 15px 0; 
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .code-list ul { 
            margin: 5px 0; 
            padding-left: 20px;
        }
        .code-list li { 
            margin: 3px 0; 
            font-size: 0.9rem;
        }
        .explanation { 
            margin: 15px 0; 
            padding: 15px;
            background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
            border-left: 4px solid #28a745;
            border-radius: 5px;
        }
        .explanation h5 {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #28a745;
        }
        .explanation h6 {
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 8px;
            color: #28a745;
        }
        .explanation ul {
            margin: 0;
            padding-left: 20px;
        }
        .explanation li {
            margin-bottom: 3px;
            font-size: 0.9rem;
        }
        .explanation p {
            margin: 0;
            font-size: 0.9rem;
        }
        .footer { 
            text-align: center; 
            margin-top: 30px; 
            font-size: 12px; 
            color: #666; 
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="alert-success">
        <div class="alert-heading">Hasil Tes RIASEC Anda</div>
        <p style="margin: 0;">Berdasarkan hasil tes, tipe kepribadian Anda adalah <strong>' . htmlspecialchars($result_personality) . '</strong></p>
    </div>
    
         <div class="chart-container">
         <div class="chart-title">RIASEC test results in percentages</div>
         <div class="chart-bars">';
        
                 // Create chart bars similar to CanvasJS
         $maxPercentage = max($scorePercentageList);
         $personalityTypes = array(
             'R' => 'Realistic',
             'I' => 'Investigative', 
             'A' => 'Artistic',
             'S' => 'Social',
             'E' => 'Enterprising',
             'C' => 'Conventional'
         );
         
         // Calculate dynamic height based on max percentage
         $maxBarHeight = 200; // Maximum bar height in pixels
         $minBarHeight = 10;  // Minimum bar height for visibility
         
         foreach ($personalityTypes as $code => $name) {
             $percentage = floatval($scorePercentageList[$code]);
             
             // Calculate bar height with minimum height guarantee
             if ($maxPercentage > 0) {
                 $barHeight = max($minBarHeight, ($percentage / $maxPercentage) * $maxBarHeight);
             } else {
                 $barHeight = $minBarHeight;
             }
             
             // Add highlight for the highest score
             $isHighest = ($percentage == $maxPercentage && $percentage > 0);
             $barClass = $isHighest ? 'chart-bar chart-bar-highest' : 'chart-bar';
             
             $html .= '
             <div class="' . $barClass . '" style="height: ' . $barHeight . 'px;">
                 <div class="chart-bar-value">' . number_format($percentage, 1) . '%</div>
                 <div class="chart-bar-label">' . $name . '</div>
             </div>';
         }
        
        $html .= '
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Keterangan Kode RIASEC:</div>
        <div class="code-list">
            <ul>
                <li><strong>R</strong> = Realistic</li>
                <li><strong>I</strong> = Investigative</li>
                <li><strong>A</strong> = Artistic</li>
                <li><strong>S</strong> = Social</li>
                <li><strong>E</strong> = Enterprising</li>
                <li><strong>C</strong> = Conventional</li>
            </ul>
        </div>
    </div>';

// Add explanation paragraphs
if (!empty($paras)) {
    $html .= '<div class="section">
        <div class="section-title">Penjelasan</div>';
    
    foreach ($paras as $p) {
        $sections = formatContentForPDF($p);
        $html .= '<div class="explanation">';
        
        // Display title if exists
        if (isset($sections['Title'])) {
            $html .= '<h5>' . htmlspecialchars($sections['Title']) . '</h5>';
        }
        
        // Display each section
        foreach ($sections as $sectionName => $sectionContent) {
            if ($sectionName === 'Title') continue;
            
            $html .= '<div style="margin-bottom: 15px;">';
            $html .= '<h6>' . htmlspecialchars($sectionName) . ':</h6>';
            
            // Split content by lines and create bullet points
            $lines = explode("\n", $sectionContent);
            if (count($lines) > 1) {
                $html .= '<ul>';
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $html .= '<li>' . htmlspecialchars($line) . '</li>';
                    }
                }
                $html .= '</ul>';
            } else {
                // Single line content
                $html .= '<p>' . htmlspecialchars(trim($sectionContent)) . '</p>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
    }
}

$html .= '
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem tes RIASEC</p>
        <p>© ' . date('Y') . ' Sistem Tes RIASEC</p>
    </div>
</body>
</html>';

if ($mpdf_available) {
    // Use mPDF to generate PDF
    try {
        // Try to use system temp directory first, then fallback to custom
        $temp_dir = sys_get_temp_dir();
        if (!is_writable($temp_dir)) {
            $temp_dir = __DIR__ . '/tmp';
            if (!file_exists($temp_dir)) {
                mkdir($temp_dir, 0755, true);
            }
        }
        
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'tempDir' => $temp_dir,
        ]);
        
        $mpdf->WriteHTML($html);
        
        // Force download with proper headers
        $filename = 'laporan_riasec_advanced_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Set headers to force download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output the PDF
        $mpdf->Output($filename, 'D');
        exit;
    } catch (Exception $e) {
        // Log error for debugging
        error_log("mPDF Error: " . $e->getMessage());
        
        // Fallback to HTML output with download headers
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="laporan_riasec_advanced_' . date('Y-m-d_H-i-s') . '.html"');
        echo $html;
    }
} else {
    // mPDF not available - provide HTML that can be printed to PDF
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_riasec_advanced_' . date('Y-m-d_H-i-s') . '.html"');
    
    // Add print-friendly CSS for better PDF conversion
    $html = str_replace('</head>', '
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .chart-container { page-break-inside: avoid; }
            .explanation { page-break-inside: avoid; }
        }
    </style>
    </head>', $html);
    
    echo $html;
}
?>
