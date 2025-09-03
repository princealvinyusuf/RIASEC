<?php include 'includes/header.php' ?>
<?php include 'util_functions.php' ?>

<?php
// Function to format content with bullet points
function formatContentWithBullets($content) {
    // Split content by sections
    $sections = array();
    
    // Define section markers
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
                // Save previous section if exists
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
            // This is the title/header line
            $sections['Title'] = $line;
        }
    }
    
    // Save the last section
    if (!empty($currentSection) && !empty($currentContent)) {
        $sections[$currentSection] = trim($currentContent);
    }
    
    return $sections;
}

// Function to render formatted content
function renderFormattedContent($content) {
    $sections = formatContentWithBullets($content);
    
    $output = '';
    
    // Start the main container with green background
    $output .= '<div class="card bg-light border-0 mb-4" style="background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%) !important; border-left: 4px solid #28a745 !important;">';
    $output .= '<div class="card-body p-4">';
    
    // Display title if exists
    if (isset($sections['Title'])) {
        $output .= '<h5 class="fw-bold mb-3 text-success">' . htmlspecialchars($sections['Title']) . '</h5>';
    }
    
    // Display each section
    foreach ($sections as $sectionName => $sectionContent) {
        if ($sectionName === 'Title') continue;
        
        $output .= '<div class="mb-3">';
        $output .= '<h6 class="fw-bold mb-2 text-success">' . htmlspecialchars($sectionName) . ':</h6>';
        
        // Split content by lines and create bullet points
        $lines = explode("\n", $sectionContent);
        if (count($lines) > 1) {
            $output .= '<ul class="mb-0 ps-3">';
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $output .= '<li>' . htmlspecialchars($line) . '</li>';
                }
            }
            $output .= '</ul>';
        } else {
            // Single line content
            $output .= '<p class="mb-0">' . htmlspecialchars(trim($sectionContent)) . '</p>';
        }
        
        $output .= '</div>';
    }
    
    $output .= '</div>'; // Close card-body
    $output .= '</div>'; // Close card
    
    return $output;
}
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <?php 
          getPersonalityTestResults(); 
          
                     // Set session flag to indicate test completion
           if (session_status() === PHP_SESSION_NONE) { session_start(); }
           $_SESSION['test_completed'] = true;
           $_SESSION['result_personality'] = $result_personality;
           $_SESSION['scorePercentageList'] = $scorePercentageList;
          ?>
          <div class="alert alert-success text-center mb-4" role="alert">
            <h4 class="alert-heading">Hasil Tes RIASEC Anda</h4>
            <p class="mb-0">Berdasarkan hasil tes, tipe kepribadian Anda adalah <b><?php echo $result_personality ?></b></p>
          </div>
          <div class="mb-4">
            <div id="chartContainer" style="height: 300px; width: 100%;"></div>
          </div>
          <div class="mb-4">
            <div class="card bg-light border-0">
              <div class="card-body p-3">
                <h6 class="fw-bold mb-2">Keterangan Kode RIASEC:</h6>
                <ul class="mb-0 ps-3">
                  <li><b>R</b> = Realistic</li>
                  <li><b>I</b> = Investigative</li>
                  <li><b>A</b> = Artistic</li>
                  <li><b>S</b> = Social</li>
                  <li><b>E</b> = Enterprising</li>
                  <li><b>C</b> = Conventional</li>
                </ul>
              </div>
            </div>
            <div class="text-center mt-3">
              <a href="generate_pdf_advanced.php" class="btn btn-primary btn-lg px-4">
                <i class="fas fa-download me-2"></i>Unduh Laporan Ini
              </a>
            </div>
          </div>
          <?php
          // Ensure table for dynamic RIASEC paragraphs exists
          $createParaTable = "CREATE TABLE IF NOT EXISTS riasec_paragraphs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            code CHAR(1) NOT NULL,
            position TINYINT UNSIGNED NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_code_position (code, position)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
          mysqli_query($connection, $createParaTable);

          // Seed default 4 paragraphs for code 'C' if empty
          $countRes = mysqli_query($connection, "SELECT COUNT(*) AS c FROM riasec_paragraphs WHERE code='C'");
          $rowC = $countRes ? mysqli_fetch_assoc($countRes) : null;
          if (!$rowC || intval($rowC['c']) === 0) {
            $defaults = array(
              1 => 'Tipe Conventional (C) cenderung menyukai aktivitas yang terstruktur, detail, dan mengikuti aturan yang jelas. Mereka bekerja efektif dengan data, administrasi, dan prosedur yang rapi.',
              2 => 'Orang dengan tipe C biasanya teliti, disiplin, dan dapat diandalkan. Mereka nyaman bekerja di lingkungan yang stabil dengan tanggung jawab yang jelas.',
              3 => 'Contoh bidang pekerjaan yang sesuai: administrasi, akuntansi, arsip, sekretaris, data entry, dan pekerjaan perkantoran lainnya.',
              4 => 'Kekuatan utama tipe C adalah ketelitian, ketertiban, dan konsistensi. Mereka unggul dalam menjaga sistem tetap berjalan dengan rapi dan efisien.'
            );
            foreach ($defaults as $pos => $content) {
              $stmt = mysqli_prepare($connection, "INSERT INTO riasec_paragraphs (code, position, content) VALUES ('C', ?, ?)");
              if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'is', $pos, $content);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
              }
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
          ?>
          <div class="mb-4">
            <h6 class="fw-bold mb-2">Penjelasan</h6>
            <div class="text-muted">
              <?php foreach ($paras as $p) { ?>
                <div class="mb-3">
                  <?php echo renderFormattedContent($p); ?>
                </div>
              <?php } ?>
            </div>
          </div>
          <div class="text-center">
            <a href="test_form.php" class="btn btn-success btn-lg px-5">Coba Tes Lagi</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php' ?>