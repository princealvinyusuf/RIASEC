<?php
// Include required files
include 'includes/db.php';
include 'util_functions.php';

// Check if mPDF is available, if not, we'll use a fallback
$mpdf_available = false;
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

// Get test results
getPersonalityTestResults();

// Fetch paragraphs for the result personality type
$paras = array();
$res = mysqli_query($connection, "SELECT position, content FROM riasec_paragraphs WHERE code='" . mysqli_real_escape_string($connection, $result_personality) . "' ORDER BY position ASC");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $paras[] = $r['content'];
    }
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
            font-family: Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
            color: #333;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #28a745; 
            padding-bottom: 20px; 
        }
        .header h1 {
            color: #28a745;
            margin-bottom: 10px;
        }
        .result-box { 
            background-color: #f8fff8; 
            border-left: 4px solid #28a745; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 5px;
        }
        .section { 
            margin: 20px 0; 
        }
        .section-title { 
            font-weight: bold; 
            color: #28a745; 
            margin-bottom: 10px; 
            font-size: 16px;
        }
        .code-list { 
            margin: 15px 0; 
        }
        .code-list ul { 
            margin: 5px 0; 
            padding-left: 20px;
        }
        .code-list li { 
            margin: 3px 0; 
        }
        .explanation { 
            margin: 15px 0; 
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .footer { 
            text-align: center; 
            margin-top: 30px; 
            font-size: 12px; 
            color: #666; 
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .personality-type {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
            margin: 10px 0;
        }
        .date-info {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Hasil Tes RIASEC</h1>
        <p class="date-info">Tanggal: ' . date('d/m/Y H:i') . '</p>
    </div>
    
    <div class="result-box">
        <h2>Hasil Tes RIASEC</h2>
        <div class="personality-type">' . htmlspecialchars($result_personality) . '</div>
        <p><strong>Tipe Kepribadian:</strong> ' . htmlspecialchars($result_personality) . '</p>
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
        <div class="section-title">Penjelasan:</div>';
    
    foreach ($paras as $p) {
        $sections = formatContentForPDF($p);
        $html .= '<div class="explanation">';
        
        if (isset($sections['Title'])) {
            $html .= '<h4 style="color: #28a745; margin-bottom: 10px;">' . htmlspecialchars($sections['Title']) . '</h4>';
        }
        
        foreach ($sections as $sectionName => $sectionContent) {
            if ($sectionName === 'Title') continue;
            
            $html .= '<div style="margin: 10px 0;">';
            $html .= '<strong style="color: #28a745;">' . htmlspecialchars($sectionName) . ':</strong><br>';
            
            $lines = explode("\n", $sectionContent);
            if (count($lines) > 1) {
                $html .= '<ul style="margin: 5px 0; padding-left: 20px;">';
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $html .= '<li>' . htmlspecialchars($line) . '</li>';
                    }
                }
                $html .= '</ul>';
            } else {
                $html .= htmlspecialchars(trim($sectionContent));
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
    }
    
    $html .= '</div>';
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
        // Fallback to HTML output
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
} else {
    // Fallback: Output HTML that can be printed to PDF
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
}
?>
