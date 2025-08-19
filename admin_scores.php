<?php include 'includes/header.php' ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

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
          <h1 class="card-title display-6 fw-bold text-success mb-3">Daftar Hasil Tes RIASEC</h1>
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


