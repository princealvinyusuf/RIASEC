<?php include 'includes/header.php' ?>
<?php include 'util_functions.php' ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <?php getPersonalityTestResults(); ?>
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

          // Fetch paragraphs for 'C'
          $paras = array();
          $res = mysqli_query($connection, "SELECT position, content FROM riasec_paragraphs WHERE code='C' ORDER BY position ASC");
          if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
              $paras[] = $r['content'];
            }
          }
          ?>
          <div class="mb-4">
            <h6 class="fw-bold mb-2">Penjelasan</h6>
            <div class="text-muted">
              <?php foreach ($paras as $p) { ?>
                <p class="mb-2"><?php echo htmlspecialchars($p); ?></p>
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