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

// Filter options
$schoolOptions = array();
$schoolOptionRes = mysqli_query($connection, "SELECT DISTINCT school_name FROM personal_info WHERE school_name IS NOT NULL AND school_name != '' ORDER BY school_name ASC");
if ($schoolOptionRes) {
    while ($schoolRow = mysqli_fetch_assoc($schoolOptionRes)) {
        $schoolOptions[] = $schoolRow['school_name'];
    }
}

// Current filter values
$filterSearch = isset($_GET['q']) ? trim($_GET['q']) : '';
$filterClass = isset($_GET['class_level']) ? trim($_GET['class_level']) : '';
$filterResult = isset($_GET['result_code']) ? strtoupper(trim($_GET['result_code'])) : '';
$filterSchool = isset($_GET['school_name']) ? trim($_GET['school_name']) : '';
$filterDateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$filterDateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

$whereClauses = array();

if ($filterSearch !== '') {
    $safeSearch = mysqli_real_escape_string($connection, $filterSearch);
    $whereClauses[] = "(pi.full_name LIKE '%{$safeSearch}%' OR pi.email LIKE '%{$safeSearch}%' OR pi.school_name LIKE '%{$safeSearch}%')";
}

if (in_array($filterClass, array('10', '11', '12'), true)) {
    $safeClass = mysqli_real_escape_string($connection, $filterClass);
    $whereClauses[] = "pi.class_level = '{$safeClass}'";
}

if (preg_match('/^[RIASEC]{1,3}$/', $filterResult)) {
    $safeResult = mysqli_real_escape_string($connection, $filterResult);
    $whereClauses[] = "pts.result LIKE '{$safeResult}%'";
}

if ($filterSchool !== '') {
    $safeSchool = mysqli_real_escape_string($connection, $filterSchool);
    $whereClauses[] = "pi.school_name = '{$safeSchool}'";
}

if ($filterDateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterDateFrom)) {
    $safeDateFrom = mysqli_real_escape_string($connection, $filterDateFrom);
    $whereClauses[] = "DATE(pts.created_at) >= '{$safeDateFrom}'";
}

if ($filterDateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterDateTo)) {
    $safeDateTo = mysqli_real_escape_string($connection, $filterDateTo);
    $whereClauses[] = "DATE(pts.created_at) <= '{$safeDateTo}'";
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

$query = "SELECT pts.id AS score_id,
                 pts.result,
                 pts.realistic, pts.investigative, pts.artistic,
                 pts.social, pts.enterprising, pts.conventional,
                 pts.created_at,
                 pi.id AS person_id, pi.full_name, pi.birth_date, pi.phone, pi.email,
                 pi.class_level, pi.school_name, pi.extracurricular, pi.organization, pi.created_at AS person_created
          FROM personality_test_scores pts
          LEFT JOIN personal_info pi ON pi.id = pts.personal_info_id
          {$whereSql}
          ORDER BY pts.created_at DESC";
$scores = mysqli_query($connection, $query);
$filteredTotal = $scores ? mysqli_num_rows($scores) : 0;
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
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
      <h2 class="h5 fw-bold text-success mb-0">Daftar hasil tes peserta</h2>
      <span class="badge text-bg-light border">Menampilkan <?php echo $filteredTotal; ?> data</span>
    </div>

    <form method="get" action="admin_scores.php" class="mb-3">
      <div class="row g-2">
        <div class="col-lg-3 col-md-6">
          <label class="form-label small mb-1">Cari (nama/email/sekolah)</label>
          <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($filterSearch); ?>" placeholder="Ketik kata kunci">
        </div>
        <div class="col-lg-2 col-md-6">
          <label class="form-label small mb-1">Kelas</label>
          <select class="form-select" name="class_level">
            <option value="">Semua</option>
            <option value="10" <?php echo $filterClass === '10' ? 'selected' : ''; ?>>10</option>
            <option value="11" <?php echo $filterClass === '11' ? 'selected' : ''; ?>>11</option>
            <option value="12" <?php echo $filterClass === '12' ? 'selected' : ''; ?>>12</option>
          </select>
        </div>
        <div class="col-lg-2 col-md-6">
          <label class="form-label small mb-1">Kode RIASEC</label>
          <select class="form-select" name="result_code">
            <option value="">Semua</option>
            <option value="R" <?php echo $filterResult === 'R' ? 'selected' : ''; ?>>R</option>
            <option value="I" <?php echo $filterResult === 'I' ? 'selected' : ''; ?>>I</option>
            <option value="A" <?php echo $filterResult === 'A' ? 'selected' : ''; ?>>A</option>
            <option value="S" <?php echo $filterResult === 'S' ? 'selected' : ''; ?>>S</option>
            <option value="E" <?php echo $filterResult === 'E' ? 'selected' : ''; ?>>E</option>
            <option value="C" <?php echo $filterResult === 'C' ? 'selected' : ''; ?>>C</option>
          </select>
        </div>
        <div class="col-lg-3 col-md-6">
          <label class="form-label small mb-1">Sekolah</label>
          <select class="form-select" name="school_name">
            <option value="">Semua sekolah</option>
            <?php foreach ($schoolOptions as $schoolName) { ?>
              <option value="<?php echo htmlspecialchars($schoolName); ?>" <?php echo $filterSchool === $schoolName ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($schoolName); ?>
              </option>
            <?php } ?>
          </select>
        </div>
        <div class="col-lg-2 col-md-6">
          <label class="form-label small mb-1">Dari tanggal</label>
          <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($filterDateFrom); ?>">
        </div>
        <div class="col-lg-2 col-md-6">
          <label class="form-label small mb-1">Sampai tanggal</label>
          <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($filterDateTo); ?>">
        </div>
      </div>
      <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary-soft">Terapkan filter</button>
        <a href="admin_scores.php" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>

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


