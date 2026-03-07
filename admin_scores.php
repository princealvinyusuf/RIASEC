<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
  header('Location: admin_login.php');
  exit;
}
?>
<?php
$pageTitle = 'Dashboard Admin - RIASEC';
include 'includes/header.php';

// Analytics Queries
$total_tests_query = "SELECT COUNT(*) as total FROM personality_test_scores";
$total_tests_result = mysqli_query($connection, $total_tests_query);
$total_tests_row = $total_tests_result ? mysqli_fetch_assoc($total_tests_result) : array('total' => 0);
$total_tests = intval($total_tests_row['total']);

$top_code_query = "SELECT result, COUNT(*) as count FROM personality_test_scores GROUP BY result ORDER BY count DESC LIMIT 1";
$top_code_result = mysqli_query($connection, $top_code_query);
$top_code = $top_code_result ? mysqli_fetch_assoc($top_code_result) : null;

$avg_scores_query = "SELECT AVG(realistic) as avg_r, AVG(investigative) as avg_i, AVG(artistic) as avg_a, AVG(social) as avg_s, AVG(enterprising) as avg_e, AVG(conventional) as avg_c FROM personality_test_scores";
$avg_scores_result = mysqli_query($connection, $avg_scores_query);
$avg_scores = $avg_scores_result ? mysqli_fetch_assoc($avg_scores_result) : array();

$schools_query = "SELECT COUNT(DISTINCT school_name) as total_schools FROM personal_info WHERE school_name IS NOT NULL AND school_name != ''";
$schools_result = mysqli_query($connection, $schools_query);
$schools_row = $schools_result ? mysqli_fetch_assoc($schools_result) : array('total_schools' => 0);
$total_schools = intval($schools_row['total_schools']);

$query = "SELECT pts.id AS score_id,
                 pts.result,
                 pts.realistic, pts.investigative, pts.artistic,
                 pts.social, pts.enterprising, pts.conventional,
                 pts.created_at,
                 pi.id AS person_id, pi.full_name, pi.birth_date, pi.phone, pi.email,
                 pi.class_level, pi.school_name, pi.extracurricular, pi.organization, pi.created_at AS person_created
          FROM personality_test_scores pts
          LEFT JOIN personal_info pi ON pi.id = pts.personal_info_id
          ORDER BY pts.created_at DESC";
$scores = mysqli_query($connection, $query);
?>

<section class="page-wrap">
  <div class="glass-card hero-card mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <p class="kicker mb-1">Admin Dashboard</p>
        <h1 class="hero-title h2 mb-1">Analitik hasil asesmen RIASEC</h1>
        <p class="hero-subtitle mb-0">Pantau distribusi minat karier peserta dan akses detail jawaban per tes.</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="generate_excel.php" class="btn btn-outline-soft">Export CSV</a>
        <a href="admin_logout.php" class="btn btn-outline-danger">Logout</a>
      </div>
    </div>
  </div>

  <div class="results-grid mb-3">
    <div class="interest-pill">
      <div class="muted small">Total Tes</div>
      <div class="display-6 fw-bold text-success"><?php echo $total_tests; ?></div>
    </div>
    <div class="interest-pill">
      <div class="muted small">Kode Paling Umum</div>
      <div class="display-6 fw-bold text-success"><?php echo $top_code ? htmlspecialchars($top_code['result']) : '-'; ?></div>
      <div class="small muted"><?php echo $top_code ? intval($top_code['count']) . ' kali muncul' : 'Belum ada data'; ?></div>
    </div>
    <div class="interest-pill">
      <div class="muted small">Partisipasi Sekolah</div>
      <div class="display-6 fw-bold text-success"><?php echo $total_schools; ?></div>
    </div>
  </div>

  <div class="glass-card app-form-card mb-3">
    <h2 class="h5 fw-bold text-success mb-3">Rata-rata Skor RIASEC (%)</h2>
    <ul class="score-list">
      <li class="score-item">
        <div class="score-item-head"><span>Realistic (R)</span><span><?php echo round($avg_scores['avg_r'] ?? 0, 1); ?>%</span></div>
        <div class="score-track"><div class="score-fill" style="width: <?php echo round($avg_scores['avg_r'] ?? 0, 1); ?>%;"></div></div>
      </li>
      <li class="score-item">
        <div class="score-item-head"><span>Investigative (I)</span><span><?php echo round($avg_scores['avg_i'] ?? 0, 1); ?>%</span></div>
        <div class="score-track"><div class="score-fill" style="width: <?php echo round($avg_scores['avg_i'] ?? 0, 1); ?>%;"></div></div>
      </li>
      <li class="score-item">
        <div class="score-item-head"><span>Artistic (A)</span><span><?php echo round($avg_scores['avg_a'] ?? 0, 1); ?>%</span></div>
        <div class="score-track"><div class="score-fill" style="width: <?php echo round($avg_scores['avg_a'] ?? 0, 1); ?>%;"></div></div>
      </li>
      <li class="score-item">
        <div class="score-item-head"><span>Social (S)</span><span><?php echo round($avg_scores['avg_s'] ?? 0, 1); ?>%</span></div>
        <div class="score-track"><div class="score-fill" style="width: <?php echo round($avg_scores['avg_s'] ?? 0, 1); ?>%;"></div></div>
      </li>
      <li class="score-item">
        <div class="score-item-head"><span>Enterprising (E)</span><span><?php echo round($avg_scores['avg_e'] ?? 0, 1); ?>%</span></div>
        <div class="score-track"><div class="score-fill" style="width: <?php echo round($avg_scores['avg_e'] ?? 0, 1); ?>%;"></div></div>
      </li>
      <li class="score-item">
        <div class="score-item-head"><span>Conventional (C)</span><span><?php echo round($avg_scores['avg_c'] ?? 0, 1); ?>%</span></div>
        <div class="score-track"><div class="score-fill" style="width: <?php echo round($avg_scores['avg_c'] ?? 0, 1); ?>%;"></div></div>
      </li>
    </ul>
  </div>

  <div class="glass-card app-form-card">
    <h2 class="h5 fw-bold text-success mb-3">Daftar hasil tes peserta</h2>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-success">
          <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Kelas</th>
            <th>Sekolah</th>
            <th>Kode</th>
            <th>R</th>
            <th>I</th>
            <th>A</th>
            <th>S</th>
            <th>E</th>
            <th>C</th>
            <th>Tanggal Tes</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($scores && mysqli_num_rows($scores) > 0) { $rowNum = 1; ?>
            <?php while ($row = mysqli_fetch_assoc($scores)) { ?>
              <tr>
                <td><?php echo $rowNum++; ?></td>
                <td><?php echo htmlspecialchars($row['full_name'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['class_level'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['school_name'] ?? '-'); ?></td>
                <td><span class="badge text-bg-success"><?php echo htmlspecialchars($row['result']); ?></span></td>
                <td><?php echo floatval($row['realistic']); ?>%</td>
                <td><?php echo floatval($row['investigative']); ?>%</td>
                <td><?php echo floatval($row['artistic']); ?>%</td>
                <td><?php echo floatval($row['social']); ?>%</td>
                <td><?php echo floatval($row['enterprising']); ?>%</td>
                <td><?php echo floatval($row['conventional']); ?>%</td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                <td>
                  <div class="d-flex flex-column gap-1">
                    <a href="admin_score_detail.php?score_id=<?php echo intval($row['score_id']); ?>" class="btn btn-sm btn-outline-success">Detail</a>
                    <a href="admin_delete_score.php?score_id=<?php echo intval($row['score_id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus hasil tes ini?');">Hapus</a>
                  </div>
                </td>
              </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td colspan="14" class="text-center muted">Belum ada data hasil tes.</td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>


