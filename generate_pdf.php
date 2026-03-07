<?php
// Include required files
include 'includes/db.php';
include 'util_functions.php';

// Check if mPDF is available, if not, we'll use a fallback
$mpdf_available = false;

// Try to include Composer autoloader
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    
    // Check if mPDF class exists
    if (class_exists('Mpdf\Mpdf')) {
        $mpdf_available = true;
    }
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
if (isset($_SESSION['result_personality']) && is_string($_SESSION['result_personality'])) {
    $result_personality = $_SESSION['result_personality'];
} else {
    $latestScoreId = isset($_SESSION['latest_score_id']) ? intval($_SESSION['latest_score_id']) : 0;
    $resultQuery = $latestScoreId > 0
        ? "SELECT result FROM personality_test_scores WHERE id = {$latestScoreId} LIMIT 1"
        : "SELECT result FROM personality_test_scores ORDER BY created_at DESC LIMIT 1";
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

// Get score percentages from the same score record if possible
$latestScoreId = isset($_SESSION['latest_score_id']) ? intval($_SESSION['latest_score_id']) : 0;
$scoreQuery = $latestScoreId > 0
    ? "SELECT realistic, investigative, artistic, social, enterprising, conventional FROM personality_test_scores WHERE id = {$latestScoreId} LIMIT 1"
    : "SELECT realistic, investigative, artistic, social, enterprising, conventional FROM personality_test_scores ORDER BY created_at DESC LIMIT 1";
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

// Prepare sorted score data
$personalityTypes = array(
    'R' => 'Realistic',
    'I' => 'Investigative',
    'A' => 'Artistic',
    'S' => 'Social',
    'E' => 'Enterprising',
    'C' => 'Conventional'
);

$sortedData = array();
foreach ($personalityTypes as $code => $name) {
    $sortedData[] = array(
        'code' => $code,
        'name' => $name,
        'percentage' => floatval($scorePercentageList[$code])
    );
}

usort($sortedData, function($a, $b) {
    return $b['percentage'] <=> $a['percentage'];
});

$top3 = array_slice($sortedData, 0, 3);
$top3Text = '';
foreach ($top3 as $item) {
    $top3Text .= $item['code'];
}

// Create HTML content for PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Tes RIASEC</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #13231a;
            background: #f3fbf3;
            line-height: 1.45;
            font-size: 12px;
        }
        .page {
            padding: 22px;
        }
        .card {
            background: #ffffff;
            border: 1px solid #d9eadc;
            border-radius: 14px;
            margin-bottom: 12px;
            padding: 14px;
        }
        .hero {
            background: #eef9f1;
            border-color: #c6e2cd;
        }
        .kicker {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #0a8f3d;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .title {
            font-size: 21px;
            font-weight: bold;
            color: #085c28;
            margin: 0 0 4px 0;
        }
        .subtitle {
            font-size: 12px;
            margin: 0;
            color: #2e4a38;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0a8f3d;
            margin: 0 0 8px 0;
        }
        .chip {
            display: inline-block;
            background: #dff3e5;
            border: 1px solid #b7ddc2;
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: bold;
            color: #0b5e29;
            margin-right: 5px;
        }
        .chip-primary {
            background: #0a8f3d;
            border-color: #0a8f3d;
            color: #ffffff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .score-table th {
            background: #0a8f3d;
            color: #ffffff;
            padding: 8px;
            font-size: 11px;
            text-align: left;
        }
        .score-table td {
            border: 1px solid #e1ece3;
            padding: 8px;
            vertical-align: middle;
            font-size: 11px;
        }
        .score-bar-wrap {
            background: #e7f0e8;
            border-radius: 999px;
            height: 10px;
            overflow: hidden;
        }
        .score-bar {
            height: 10px;
            background: #0a8f3d;
        }
        .score-pct {
            text-align: right;
            white-space: nowrap;
            font-weight: bold;
            color: #0b5e29;
            width: 56px;
        }
        .legend-list {
            margin: 0;
            padding-left: 18px;
        }
        .legend-list li {
            margin-bottom: 4px;
        }
        .explanation {
            margin: 10px 0;
            padding: 12px;
            border: 1px solid #d5e8d9;
            border-left: 4px solid #0a8f3d;
            border-radius: 8px;
            background: #f9fdf9;
        }
        .explanation h5 {
            margin: 0 0 8px 0;
            font-size: 13px;
            color: #0a8f3d;
        }
        .explanation h6 {
            margin: 8px 0 4px 0;
            font-size: 12px;
            color: #0a8f3d;
        }
        .explanation p {
            margin: 0;
            font-size: 11px;
        }
        .explanation ul {
            margin: 0;
            padding-left: 16px;
        }
        .footer {
            margin-top: 16px;
            padding-top: 10px;
            border-top: 1px solid #d9eadc;
            text-align: center;
            color: #5d7465;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="card hero">
            <div class="kicker">Laporan Profil Minat Karier</div>
            <h1 class="title">Hasil Asesmen RIASEC: ' . htmlspecialchars($result_personality) . '</h1>
            <p class="subtitle">Top 3 minat dominan: <strong>' . htmlspecialchars($top3Text) . '</strong> | Tanggal cetak: ' . date('d M Y H:i') . '</p>
            <div style="margin-top:8px;">
                <span class="chip chip-primary">Kode Utama: ' . htmlspecialchars($result_personality) . '</span>';

foreach ($top3 as $topItem) {
    $html .= '<span class="chip">' . htmlspecialchars($topItem['code'] . ' - ' . $topItem['name']) . '</span>';
}

$html .= '
            </div>
        </div>

        <div class="card">
            <h2 class="section-title">Distribusi Skor RIASEC</h2>
            <table class="score-table">
                <thead>
                    <tr>
                        <th style="width:170px;">Dimensi</th>
                        <th>Visual Skor</th>
                        <th style="width:60px; text-align:right;">%</th>
                    </tr>
                </thead>
                <tbody>';

foreach ($sortedData as $data) {
    $pct = max(0, min(100, floatval($data['percentage'])));
    $html .= '
                    <tr>
                        <td><strong>' . htmlspecialchars($data['code']) . '</strong> - ' . htmlspecialchars($data['name']) . '</td>
                        <td>
                            <div class="score-bar-wrap">
                                <div class="score-bar" style="width:' . $pct . '%;"></div>
                            </div>
                        </td>
                        <td class="score-pct">' . number_format($pct, 1) . '%</td>
                    </tr>';
}

$html .= '
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2 class="section-title">Keterangan Kode RIASEC</h2>
            <ul class="legend-list">
                <li><strong>R</strong> = Realistic</li>
                <li><strong>I</strong> = Investigative</li>
                <li><strong>A</strong> = Artistic</li>
                <li><strong>S</strong> = Social</li>
                <li><strong>E</strong> = Enterprising</li>
                <li><strong>C</strong> = Conventional</li>
            </ul>
        </div>';

// Add explanation paragraphs
if (!empty($paras)) {
    $html .= '<div class="card">
        <h2 class="section-title">Interpretasi Profil</h2>';
    
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
        <p>Laporan ini dibuat secara otomatis oleh sistem asesmen RIASEC</p>
        <p>© ' . date('Y') . ' Pusat Pasar Kerja</p>
    </div>
</div>
</body>
</html>';

if ($mpdf_available) {
    // Use mPDF to generate PDF
    try {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);
        
        $mpdf->WriteHTML($html);
        
        // Output PDF
        $filename = 'laporan_riasec_' . date('Y-m-d_H-i-s') . '.pdf';
        $mpdf->Output($filename, 'D');
        exit;
    } catch (Exception $e) {
        // Fallback to HTML output with PDF headers
        $filename = 'laporan_riasec_' . date('Y-m-d_H-i-s') . '.html';
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $html;
        exit;
    }
} else {
    // Debug: Check what's available
    if (file_exists('vendor/autoload.php')) {
        // Try to force load mPDF
        require_once 'vendor/autoload.php';
        if (class_exists('Mpdf\Mpdf')) {
            try {
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'margin_left' => 15,
                    'margin_right' => 15,
                    'margin_top' => 15,
                    'margin_bottom' => 15,
                ]);
                
                $mpdf->WriteHTML($html);
                
                // Output PDF
                $filename = 'laporan_riasec_' . date('Y-m-d_H-i-s') . '.pdf';
                $mpdf->Output($filename, 'D');
                exit;
            } catch (Exception $e) {
                // Continue to HTML fallback
            }
        }
    }
    
    // Fallback: Force download of HTML file that can be printed to PDF
    $filename = 'laporan_riasec_' . date('Y-m-d_H-i-s') . '.html';
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo $html;
    exit;
}
?>
