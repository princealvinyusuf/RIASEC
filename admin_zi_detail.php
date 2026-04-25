<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
    header('Location: admin_login');
    exit;
}

include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/zi_core.php';

ensureZiTablesAndSeed($connection);

$assessmentId = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;
if ($assessmentId <= 0) {
    $pageTitle = 'Detail Survei ZI - Admin';
    include 'includes/header.php';
    echo '<section class="page-wrap"><div class="alert alert-danger">Parameter assessment_id tidak valid.</div></section>';
    include 'includes/footer.php';
    exit;
}

$assessmentSql = "SELECT id, respondent_name, respondent_email, average_score, positive_count, neutral_count, negative_count, total_questions, submitted_at
                  FROM zi_assessments
                  WHERE id = " . $assessmentId;
$assessmentRes = mysqli_query($connection, $assessmentSql);
$assessment = $assessmentRes ? mysqli_fetch_assoc($assessmentRes) : null;

$answersSql = "SELECT za.statement_id, za.answer, zs.statement_content
               FROM zi_answers za
               LEFT JOIN zi_statements zs ON zs.statement_id = za.statement_id
               WHERE za.assessment_id = " . $assessmentId . "
               ORDER BY za.statement_id ASC";
$answersRes = mysqli_query($connection, $answersSql);
?>
<?php $pageTitle = 'Detail Survei ZI - Admin'; ?>
<?php include 'includes/header.php'; ?>

<section class="page-wrap">
  <div class="glass-card hero-card mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <p class="kicker mb-1">Detail Survei Evaluasi</p>
        <h1 class="hero-title h2 mb-1">Zona Integritas (ZI)</h1>
        <p class="hero-subtitle mb-0">Lihat ringkasan dan jawaban detail per pernyataan untuk satu entri survei.</p>
      </div>
      <a href="admin_scores" class="btn btn-outline-soft">&larr; Kembali ke dashboard admin</a>
    </div>
  </div>

  <div class="glass-card app-form-card mb-3">
    <?php if ($assessment) { ?>
      <div class="results-grid mb-3">
        <div class="interest-pill"><div class="muted small">Nama Responden</div><strong><?php echo htmlspecialchars($assessment['respondent_name']); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Email</div><strong><?php echo htmlspecialchars($assessment['respondent_email'] ?: '-'); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Rata-rata Nilai</div><strong><?php echo number_format(floatval($assessment['average_score']), 2); ?>/5</strong></div>
        <div class="interest-pill"><div class="muted small">Positif</div><strong><?php echo intval($assessment['positive_count']); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Netral</div><strong><?php echo intval($assessment['neutral_count']); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Negatif</div><strong><?php echo intval($assessment['negative_count']); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Total Pertanyaan</div><strong><?php echo intval($assessment['total_questions']); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Tanggal Submit</div><strong><?php echo htmlspecialchars($assessment['submitted_at']); ?></strong></div>
      </div>
    <?php } else { ?>
      <div class="alert alert-warning mb-0">Data survei tidak ditemukan.</div>
    <?php } ?>
  </div>

  <div class="glass-card app-form-card">
    <h2 class="h5 fw-bold text-success mb-3">Jawaban detail Survei ZI</h2>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-success">
          <tr>
            <th>ID Pernyataan</th>
            <th>Pernyataan</th>
            <th>Jawaban (1-5)</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($answersRes && mysqli_num_rows($answersRes) > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($answersRes)) { ?>
              <tr>
                <td><?php echo intval($row['statement_id']); ?></td>
                <td><?php echo htmlspecialchars($row['statement_content'] ?? '-'); ?></td>
                <td><span class="badge text-bg-success"><?php echo intval($row['answer']); ?></span></td>
              </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td colspan="3" class="text-center muted">Belum ada detail jawaban untuk data ini.</td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
