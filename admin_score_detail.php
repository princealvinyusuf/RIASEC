<?php include 'includes/header.php' ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$scoreId = isset($_GET['score_id']) ? intval($_GET['score_id']) : 0;
if ($scoreId <= 0) {
  echo '<div class="container py-5"><div class="alert alert-danger">Parameter tidak valid.</div></div>';
  include 'includes/footer.php';
  exit;
}

// Fetch header info (person + aggregate result)
$headerSql = "SELECT pts.id AS score_id, pts.result,
                     pts.realistic, pts.investigative, pts.artistic,
                     pts.social, pts.enterprising, pts.conventional,
                     pts.created_at,
                     pi.full_name, pi.email, pi.class_level, pi.school_name,
                     pi.birth_date, pi.phone, pi.extracurricular, pi.organization
              FROM personality_test_scores pts
              LEFT JOIN personal_info pi ON pi.id = pts.personal_info_id
              WHERE pts.id = $scoreId";
$headerRes = mysqli_query($connection, $headerSql);
$header = $headerRes ? mysqli_fetch_assoc($headerRes) : null;

// Fetch detailed answers joined with statements
$detailSql = "SELECT ta.statement_id, ta.statement_category, ta.answer, s.statement_content
              FROM test_answers ta
              LEFT JOIN statements s 
                ON s.statement_id = ta.statement_id AND s.statement_category = ta.statement_category
              WHERE ta.score_id = $scoreId
              ORDER BY ta.statement_category, ta.statement_id";
$detailRes = mysqli_query($connection, $detailSql);
?>
<div class="container py-5">
  <div class="mb-3">
    <a href="admin_scores.php" class="text-decoration-none">&larr; Kembali ke Daftar</a>
  </div>
  <div class="row">
    <div class="col-lg-12">
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <h3 class="fw-bold text-success mb-3">Detail Tes</h3>
          <?php if ($header) { ?>
          <div class="row g-3">
            <div class="col-md-4"><b>Nama:</b> <?php echo htmlspecialchars($header['full_name'] ?? '-'); ?></div>
            <div class="col-md-4"><b>Email:</b> <?php echo htmlspecialchars($header['email'] ?? '-'); ?></div>
            <div class="col-md-4"><b>Tanggal Tes:</b> <?php echo htmlspecialchars($header['created_at']); ?></div>
            <div class="col-md-4"><b>Kelas:</b> <?php echo htmlspecialchars($header['class_level'] ?? '-'); ?></div>
            <div class="col-md-4"><b>Sekolah:</b> <?php echo htmlspecialchars($header['school_name'] ?? '-'); ?></div>
            <div class="col-md-4"><b>No. HP:</b> <?php echo htmlspecialchars($header['phone'] ?? '-'); ?></div>
            <div class="col-md-12"><b>Hasil:</b> <?php echo htmlspecialchars($header['result']); ?></div>
          </div>
          <hr />
          <div class="row g-3">
            <div class="col-md-2"><b>Realistic:</b> <?php echo floatval($header['realistic']); ?>%</div>
            <div class="col-md-2"><b>Investigative:</b> <?php echo floatval($header['investigative']); ?>%</div>
            <div class="col-md-2"><b>Artistic:</b> <?php echo floatval($header['artistic']); ?>%</div>
            <div class="col-md-2"><b>Social:</b> <?php echo floatval($header['social']); ?>%</div>
            <div class="col-md-2"><b>Enterprising:</b> <?php echo floatval($header['enterprising']); ?>%</div>
            <div class="col-md-2"><b>Conventional:</b> <?php echo floatval($header['conventional']); ?>%</div>
          </div>
          <?php } else { ?>
            <div class="alert alert-warning">Data tidak ditemukan.</div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="fw-bold text-success mb-3">Jawaban Detail</h5>
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-success">
                <tr>
                  <th>Kategori</th>
                  <th>ID Pernyataan</th>
                  <th>Pernyataan</th>
                  <th>Jawaban (1-5)</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($detailRes && mysqli_num_rows($detailRes) > 0) { ?>
                  <?php while($d = mysqli_fetch_assoc($detailRes)) { ?>
                    <tr>
                      <td><?php echo htmlspecialchars($d['statement_category']); ?></td>
                      <td><?php echo intval($d['statement_id']); ?></td>
                      <td><?php echo htmlspecialchars($d['statement_content'] ?? '-'); ?></td>
                      <td><?php echo intval($d['answer']); ?></td>
                    </tr>
                  <?php } ?>
                <?php } else { ?>
                  <tr>
                    <td colspan="4" class="text-center">Tidak ada jawaban yang tersimpan.</td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php' ?>


