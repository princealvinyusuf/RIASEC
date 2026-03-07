<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
  header('Location: admin_login.php');
  exit;
}
?>
<?php
$scoreId = isset($_GET['score_id']) ? intval($_GET['score_id']) : 0;
if ($scoreId <= 0) {
    $pageTitle = 'Detail Tes - Admin';
    include 'includes/header.php';
    echo '<section class="page-wrap"><div class="alert alert-danger">Parameter tidak valid.</div></section>';
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
<?php $pageTitle = 'Detail Tes - Admin'; ?>
<?php include 'includes/header.php'; ?>

<section class="page-wrap">
  <div class="glass-card hero-card mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <p class="kicker mb-1">Detail Hasil Tes</p>
        <h1 class="hero-title h2 mb-1">Informasi peserta & jawaban</h1>
        <p class="hero-subtitle mb-0">Lihat profil skor RIASEC dan jawaban per butir untuk sesi tes ini.</p>
      </div>
      <a href="admin_scores.php" class="btn btn-outline-soft">&larr; Kembali ke daftar</a>
    </div>
  </div>

  <div class="glass-card app-form-card mb-3">
    <?php if ($header) { ?>
      <div class="results-grid mb-3">
        <div class="interest-pill"><div class="muted small">Nama</div><strong><?php echo htmlspecialchars($header['full_name'] ?? '-'); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Email</div><strong><?php echo htmlspecialchars($header['email'] ?? '-'); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Kelas</div><strong><?php echo htmlspecialchars($header['class_level'] ?? '-'); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Sekolah</div><strong><?php echo htmlspecialchars($header['school_name'] ?? '-'); ?></strong></div>
        <div class="interest-pill"><div class="muted small">No. HP</div><strong><?php echo htmlspecialchars($header['phone'] ?? '-'); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Tanggal Tes</div><strong><?php echo htmlspecialchars($header['created_at'] ?? '-'); ?></strong></div>
      </div>

      <div class="mb-3">
        <span class="badge text-bg-success fs-6">Kode Hasil: <?php echo htmlspecialchars($header['result']); ?></span>
      </div>

      <ul class="score-list">
        <li class="score-item">
          <div class="score-item-head"><span>Realistic (R)</span><span><?php echo floatval($header['realistic']); ?>%</span></div>
          <div class="score-track"><div class="score-fill" style="width: <?php echo floatval($header['realistic']); ?>%;"></div></div>
        </li>
        <li class="score-item">
          <div class="score-item-head"><span>Investigative (I)</span><span><?php echo floatval($header['investigative']); ?>%</span></div>
          <div class="score-track"><div class="score-fill" style="width: <?php echo floatval($header['investigative']); ?>%;"></div></div>
        </li>
        <li class="score-item">
          <div class="score-item-head"><span>Artistic (A)</span><span><?php echo floatval($header['artistic']); ?>%</span></div>
          <div class="score-track"><div class="score-fill" style="width: <?php echo floatval($header['artistic']); ?>%;"></div></div>
        </li>
        <li class="score-item">
          <div class="score-item-head"><span>Social (S)</span><span><?php echo floatval($header['social']); ?>%</span></div>
          <div class="score-track"><div class="score-fill" style="width: <?php echo floatval($header['social']); ?>%;"></div></div>
        </li>
        <li class="score-item">
          <div class="score-item-head"><span>Enterprising (E)</span><span><?php echo floatval($header['enterprising']); ?>%</span></div>
          <div class="score-track"><div class="score-fill" style="width: <?php echo floatval($header['enterprising']); ?>%;"></div></div>
        </li>
        <li class="score-item">
          <div class="score-item-head"><span>Conventional (C)</span><span><?php echo floatval($header['conventional']); ?>%</span></div>
          <div class="score-track"><div class="score-fill" style="width: <?php echo floatval($header['conventional']); ?>%;"></div></div>
        </li>
      </ul>
    <?php } else { ?>
      <div class="alert alert-warning mb-0">Data tidak ditemukan.</div>
    <?php } ?>
  </div>

  <div class="glass-card app-form-card">
    <h2 class="h5 fw-bold text-success mb-3">Jawaban detail per butir</h2>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-success">
          <tr>
            <th>Kategori</th>
            <th>ID</th>
            <th>Pernyataan</th>
            <th>Skor (1-5)</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($detailRes && mysqli_num_rows($detailRes) > 0) { ?>
            <?php while($d = mysqli_fetch_assoc($detailRes)) { ?>
              <tr>
                <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($d['statement_category']); ?></span></td>
                <td><?php echo intval($d['statement_id']); ?></td>
                <td><?php echo htmlspecialchars($d['statement_content'] ?? '-'); ?></td>
                <td><?php echo intval($d['answer']); ?></td>
              </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td colspan="4" class="text-center muted">Tidak ada jawaban yang tersimpan.</td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>


