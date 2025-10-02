<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
  header('Location: admin_login.php');
  exit;
}
?>
<?php include 'includes/header.php' ?>
<?php
// Analytics Queries
$total_tests_query = "SELECT COUNT(*) as total FROM personality_test_scores";
$total_tests_result = mysqli_query($connection, $total_tests_query);
$total_tests = mysqli_fetch_assoc($total_tests_result)['total'];

$top_code_query = "SELECT result, COUNT(*) as count FROM personality_test_scores GROUP BY result ORDER BY count DESC LIMIT 1";
$top_code_result = mysqli_query($connection, $top_code_query);
$top_code = mysqli_fetch_assoc($top_code_result);

$avg_scores_query = "SELECT AVG(realistic) as avg_r, AVG(investigative) as avg_i, AVG(artistic) as avg_a, AVG(social) as avg_s, AVG(enterprising) as avg_e, AVG(conventional) as avg_c FROM personality_test_scores";
$avg_scores_result = mysqli_query($connection, $avg_scores_query);
$avg_scores = mysqli_fetch_assoc($avg_scores_result);

$schools_query = "SELECT COUNT(DISTINCT school_name) as total_schools FROM personal_info WHERE school_name IS NOT NULL AND school_name != ''";
$schools_result = mysqli_query($connection, $schools_query);
$total_schools = mysqli_fetch_assoc($schools_result)['total_schools'];
?>

<div class="container py-5">
  <div class="row mb-4">
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h5 class="card-title text-success">Total Tes</h5>
          <p class="card-text display-4 fw-bold"><?php echo $total_tests; ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h5 class="card-title text-success">Kode Paling Umum</h5>
          <p class="card-text display-4 fw-bold"><?php echo $top_code ? htmlspecialchars($top_code['result']) : '-'; ?></p>
          <small class="text-muted"><?php echo $top_code ? $top_code['count'] . ' kali' : 'Belum ada data'; ?></small>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body text-center">
          <h5 class="card-title text-success">Partisipasi Sekolah</h5>
          <p class="card-text display-4 fw-bold"><?php echo $total_schools; ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <h5 class="card-title text-success text-center">Rata-rata Skor (%)</h5>
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center border-0">Realistic (R) <span class="badge bg-primary rounded-pill"><?php echo round($avg_scores['avg_r'] ?? 0, 1); ?></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center border-0">Investigative (I) <span class="badge bg-secondary rounded-pill"><?php echo round($avg_scores['avg_i'] ?? 0, 1); ?></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center border-0">Artistic (A) <span class="badge bg-success rounded-pill"><?php echo round($avg_scores['avg_a'] ?? 0, 1); ?></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center border-0">Social (S) <span class="badge bg-danger rounded-pill"><?php echo round($avg_scores['avg_s'] ?? 0, 1); ?></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center border-0">Enterprising (E) <span class="badge bg-warning text-dark rounded-pill"><?php echo round($avg_scores['avg_e'] ?? 0, 1); ?></span></li>
            <li class="list-group-item d-flex justify-content-between align-items-center border-0">Conventional (C) <span class="badge bg-info text-dark rounded-pill"><?php echo round($avg_scores['avg_c'] ?? 0, 1); ?></span></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

<?php
// Ensure join columns exist but do not mutate schema here; util_functions handles backfill
$order = 'ORDER BY pts.created_at DESC';

$query = "SELECT pts.id AS score_id,
                 pts.result,
                 pts.realistic, pts.investigative, pts.artistic,
                 pts.social, pts.enterprising, pts.conventional,
                 pts.created_at,
                 pi.id AS person_id, pi.full_name, pi.birth_date, pi.phone, pi.email,
                 pi.class_level, pi.school_name, pi.extracurricular, pi.organization, pi.created_at AS person_created
          FROM personality_test_scores pts
          LEFT JOIN personal_info pi ON pi.id = pts.personal_info_id
          $order";

$scores = mysqli_query($connection, $query);
?>
<div class="container py-5">
  <div class="row justify-content-center mb-4">
    <div class="col-lg-12">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="card-title display-6 fw-bold text-success mb-0">Daftar Hasil Tes RIASEC</h1>
            <div>
              <a href="generate_excel.php" class="btn btn-outline-primary btn-sm">Export to Excel</a>
              <a href="admin_logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-success">
                <tr>
                  <th>#</th>
                  <th>Nama Lengkap</th>
                  <th>Email</th>
                  <th>Kelas</th>
                  <th>Sekolah</th>
                  <th>Hasil (Kode)</th>
                  <th>Realistic</th>
                  <th>Investigative</th>
                  <th>Artistic</th>
                  <th>Social</th>
                  <th>Enterprising</th>
                  <th>Conventional</th>
                  <th>Tanggal Tes</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($scores && mysqli_num_rows($scores) > 0) { $rowNum = 1; ?>
                  <?php while($row = mysqli_fetch_assoc($scores)) { ?>
                    <tr>
                      <td><?php echo $rowNum++; ?></td>
                      <td><?php echo htmlspecialchars($row['full_name'] ?? '-'); ?></td>
                      <td><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
                      <td><?php echo htmlspecialchars($row['class_level'] ?? '-'); ?></td>
                      <td><?php echo htmlspecialchars($row['school_name'] ?? '-'); ?></td>
                      <td><b><?php echo htmlspecialchars($row['result']); ?></b></td>
                      <td><?php echo floatval($row['realistic']); ?>%</td>
                      <td><?php echo floatval($row['investigative']); ?>%</td>
                      <td><?php echo floatval($row['artistic']); ?>%</td>
                      <td><?php echo floatval($row['social']); ?>%</td>
                      <td><?php echo floatval($row['enterprising']); ?>%</td>
                      <td><?php echo floatval($row['conventional']); ?>%</td>
                      <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                      <td>
                        <a href="admin_score_detail.php?score_id=<?php echo intval($row['score_id']); ?>" class="btn btn-sm btn-outline-success">Detail Tes</a>
                        <a href="admin_delete_score.php?score_id=<?php echo intval($row['score_id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus hasil tes ini?');">Delete</a>
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td colspan="12">
                        <div class="small text-muted">
                          <div><b>Tanggal Lahir:</b> <?php echo htmlspecialchars($row['birth_date'] ?? '-'); ?></div>
                          <div><b>No. HP:</b> <?php echo htmlspecialchars($row['phone'] ?? '-'); ?></div>
                          <div><b>Ekstrakurikuler:</b> <?php echo htmlspecialchars($row['extracurricular'] ?? '-'); ?></div>
                          <div><b>Organisasi:</b> <?php echo htmlspecialchars($row['organization'] ?? '-'); ?></div>
                        </div>
                      </td>
                    </tr>
                  <?php } ?>
                <?php } else { ?>
                  <tr>
                    <td colspan="13" class="text-center">Belum ada data.</td>
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


