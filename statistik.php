<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Statistik Asesmen RIASEC';
include 'includes/header.php';

$totalTests = 0;
$totalSchools = 0;
$totalParticipants = 0;
$totalParticipantsCompleted = 0;
$totalParticipantsIncomplete = 0;
$latestTestDate = '-';

$resTotal = mysqli_query($connection, "SELECT COUNT(*) AS total FROM personality_test_scores");
if ($resTotal) {
    $row = mysqli_fetch_assoc($resTotal);
    $totalTests = intval($row['total']);
}

$resParticipants = mysqli_query($connection, "
    SELECT COUNT(*) AS total
    FROM personal_info
    WHERE
      TRIM(COALESCE(full_name, '')) NOT IN ('', '-')
      AND TRIM(COALESCE(email, '')) NOT IN ('', '-')
      AND TRIM(COALESCE(class_level, '')) NOT IN ('', '-')
      AND TRIM(COALESCE(school_name, '')) NOT IN ('', '-')
");
if ($resParticipants) {
    $row = mysqli_fetch_assoc($resParticipants);
    $totalParticipants = intval($row['total']);
}

$resParticipantsCompleted = mysqli_query($connection, "
    SELECT COUNT(DISTINCT pi.id) AS total
    FROM personal_info pi
    INNER JOIN personality_test_scores pts ON pts.personal_info_id = pi.id
    WHERE
      TRIM(COALESCE(pi.full_name, '')) NOT IN ('', '-')
      AND TRIM(COALESCE(pi.email, '')) NOT IN ('', '-')
      AND TRIM(COALESCE(pi.class_level, '')) NOT IN ('', '-')
      AND TRIM(COALESCE(pi.school_name, '')) NOT IN ('', '-')
");
if ($resParticipantsCompleted) {
    $row = mysqli_fetch_assoc($resParticipantsCompleted);
    $totalParticipantsCompleted = intval($row['total']);
}

$resParticipantsIncomplete = mysqli_query($connection, "
    SELECT COUNT(*) AS total
    FROM personal_info pi
    LEFT JOIN personality_test_scores pts ON pts.personal_info_id = pi.id
    WHERE
      pts.id IS NULL
      AND TRIM(COALESCE(pi.full_name, '')) NOT IN ('', '-')
      AND TRIM(COALESCE(pi.email, '')) NOT IN ('', '-')
      AND TRIM(COALESCE(pi.class_level, '')) NOT IN ('', '-')
      AND TRIM(COALESCE(pi.school_name, '')) NOT IN ('', '-')
");
if ($resParticipantsIncomplete) {
    $row = mysqli_fetch_assoc($resParticipantsIncomplete);
    $totalParticipantsIncomplete = intval($row['total']);
}

$resSchools = mysqli_query($connection, "
    SELECT COUNT(DISTINCT TRIM(pi.school_name)) AS total
    FROM personality_test_scores pts
    INNER JOIN personal_info pi ON pi.id = pts.personal_info_id
    WHERE
      TRIM(COALESCE(pi.school_name, '')) NOT IN ('', '-')
      AND TRIM(COALESCE(pi.full_name, '')) NOT IN ('', '-')
      AND TRIM(COALESCE(pi.email, '')) NOT IN ('', '-')
      AND TRIM(COALESCE(pi.class_level, '')) NOT IN ('', '-')
");
if ($resSchools) {
    $row = mysqli_fetch_assoc($resSchools);
    $totalSchools = intval($row['total']);
}

$resLatest = mysqli_query($connection, "SELECT MAX(created_at) AS latest_at FROM personality_test_scores");
if ($resLatest) {
    $row = mysqli_fetch_assoc($resLatest);
    if (!empty($row['latest_at'])) {
        $latestTestDate = $row['latest_at'];
    }
}

$riasecDistribution = array();
$resCodes = mysqli_query($connection, "SELECT result, COUNT(*) AS total FROM personality_test_scores GROUP BY result ORDER BY total DESC");
if ($resCodes) {
    while ($row = mysqli_fetch_assoc($resCodes)) {
        $riasecDistribution[] = $row;
    }
}

$maxRiasecCount = 0;
foreach ($riasecDistribution as $row) {
    $count = intval($row['total']);
    if ($count > $maxRiasecCount) {
        $maxRiasecCount = $count;
    }
}

$classDistribution = array();
$resClass = mysqli_query($connection, "SELECT class_level, COUNT(*) AS total FROM personal_info GROUP BY class_level ORDER BY class_level ASC");
if ($resClass) {
    while ($row = mysqli_fetch_assoc($resClass)) {
        $classDistribution[] = $row;
    }
}

$maxClassCount = 0;
foreach ($classDistribution as $row) {
    $count = intval($row['total']);
    if ($count > $maxClassCount) {
        $maxClassCount = $count;
    }
}

$avgScores = array('avg_r' => 0, 'avg_i' => 0, 'avg_a' => 0, 'avg_s' => 0, 'avg_e' => 0, 'avg_c' => 0);
$resAvg = mysqli_query($connection, "SELECT AVG(realistic) AS avg_r, AVG(investigative) AS avg_i, AVG(artistic) AS avg_a, AVG(social) AS avg_s, AVG(enterprising) AS avg_e, AVG(conventional) AS avg_c FROM personality_test_scores");
if ($resAvg) {
    $row = mysqli_fetch_assoc($resAvg);
    if ($row) {
        $avgScores = $row;
    }
}

$topSchools = array();
$resTopSchools = mysqli_query($connection, "SELECT school_name, COUNT(*) AS total FROM personal_info WHERE school_name IS NOT NULL AND school_name != '' GROUP BY school_name ORDER BY total DESC, school_name ASC LIMIT 10");
if ($resTopSchools) {
    while ($row = mysqli_fetch_assoc($resTopSchools)) {
        $topSchools[] = $row;
    }
}
?>

<section class="page-wrap">
    <div class="glass-card hero-card mb-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <p class="kicker mb-1">Menu Statistik</p>
                <h1 class="hero-title h2 mb-1">Ringkasan data asesmen RIASEC</h1>
                <p class="hero-subtitle mb-0">Gambaran umum hasil tes berdasarkan data yang sudah terkumpul di sistem.</p>
            </div>
            <a href="index" class="btn btn-outline-soft">&larr; Kembali ke beranda</a>
        </div>
    </div>

    <div class="results-grid mb-3">
        <div class="interest-pill">
            <div class="muted small">Total Tes Selesai</div>
            <div class="display-6 fw-bold text-success"><?php echo $totalTests; ?></div>
        </div>
        <div class="interest-pill">
            <div class="muted small">Total Peserta Terdaftar</div>
            <div class="display-6 fw-bold text-success"><?php echo $totalParticipants; ?></div>
        </div>
        <div class="interest-pill">
            <div class="muted small">Peserta Sudah Selesai</div>
            <div class="display-6 fw-bold text-success"><?php echo $totalParticipantsCompleted; ?></div>
        </div>
        <div class="interest-pill">
            <div class="muted small">Peserta Belum Selesai</div>
            <div class="display-6 fw-bold text-success"><?php echo $totalParticipantsIncomplete; ?></div>
        </div>
        <div class="interest-pill">
            <div class="muted small">Partisipasi Sekolah</div>
            <div class="display-6 fw-bold text-success"><?php echo $totalSchools; ?></div>
        </div>
        <div class="interest-pill">
            <div class="muted small">Tes Terakhir</div>
            <div class="fw-semibold"><?php echo htmlspecialchars($latestTestDate); ?></div>
        </div>
    </div>

    <div class="glass-card app-form-card mb-3">
        <h2 class="h5 fw-bold text-success mb-3">Rata-rata Skor RIASEC (%)</h2>
        <ul class="score-list">
            <li class="score-item">
                <div class="score-item-head"><span>Realistic (R)</span><span><?php echo round($avgScores['avg_r'] ?? 0, 1); ?>%</span></div>
                <div class="score-track"><div class="score-fill" style="width: <?php echo round($avgScores['avg_r'] ?? 0, 1); ?>%;"></div></div>
            </li>
            <li class="score-item">
                <div class="score-item-head"><span>Investigative (I)</span><span><?php echo round($avgScores['avg_i'] ?? 0, 1); ?>%</span></div>
                <div class="score-track"><div class="score-fill" style="width: <?php echo round($avgScores['avg_i'] ?? 0, 1); ?>%;"></div></div>
            </li>
            <li class="score-item">
                <div class="score-item-head"><span>Artistic (A)</span><span><?php echo round($avgScores['avg_a'] ?? 0, 1); ?>%</span></div>
                <div class="score-track"><div class="score-fill" style="width: <?php echo round($avgScores['avg_a'] ?? 0, 1); ?>%;"></div></div>
            </li>
            <li class="score-item">
                <div class="score-item-head"><span>Social (S)</span><span><?php echo round($avgScores['avg_s'] ?? 0, 1); ?>%</span></div>
                <div class="score-track"><div class="score-fill" style="width: <?php echo round($avgScores['avg_s'] ?? 0, 1); ?>%;"></div></div>
            </li>
            <li class="score-item">
                <div class="score-item-head"><span>Enterprising (E)</span><span><?php echo round($avgScores['avg_e'] ?? 0, 1); ?>%</span></div>
                <div class="score-track"><div class="score-fill" style="width: <?php echo round($avgScores['avg_e'] ?? 0, 1); ?>%;"></div></div>
            </li>
            <li class="score-item">
                <div class="score-item-head"><span>Conventional (C)</span><span><?php echo round($avgScores['avg_c'] ?? 0, 1); ?>%</span></div>
                <div class="score-track"><div class="score-fill" style="width: <?php echo round($avgScores['avg_c'] ?? 0, 1); ?>%;"></div></div>
            </li>
        </ul>
    </div>

    <div class="results-grid">
        <div class="glass-card app-form-card">
            <h2 class="h5 fw-bold text-success mb-3">Distribusi Kode Hasil</h2>
            <div class="table-responsive stats-scroll">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-success" style="position: sticky; top: 0;">
                        <tr>
                            <th>Kode RIASEC</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($riasecDistribution)) { ?>
                            <?php foreach ($riasecDistribution as $row) { ?>
                                <tr>
                                    <td><span class="badge text-bg-success"><?php echo htmlspecialchars($row['result']); ?></span></td>
                                    <td><?php echo intval($row['total']); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="2" class="text-center muted">Belum ada data.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass-card app-form-card">
            <h2 class="h5 fw-bold text-success mb-3">Distribusi Kelas</h2>
            <div class="chart-stack mb-3">
                <?php if (!empty($classDistribution)) { ?>
                    <?php foreach ($classDistribution as $row) {
                        $count = intval($row['total']);
                        $width = $maxClassCount > 0 ? round(($count / $maxClassCount) * 100, 1) : 0;
                    ?>
                        <div class="chart-row">
                            <div class="chart-label">Kelas <?php echo htmlspecialchars($row['class_level'] ?: '-'); ?></div>
                            <div class="chart-track"><div class="chart-fill" style="width: <?php echo $width; ?>%;"></div></div>
                            <div class="chart-value"><?php echo $count; ?></div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="muted">Belum ada data.</div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="glass-card app-form-card mt-3">
        <h2 class="h5 fw-bold text-success mb-3">Top 10 Sekolah Partisipan</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Nama Sekolah</th>
                        <th>Jumlah Peserta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($topSchools)) { $i = 1; ?>
                        <?php foreach ($topSchools as $row) { ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($row['school_name']); ?></td>
                                <td><?php echo intval($row['total']); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr><td colspan="3" class="text-center muted">Belum ada data sekolah.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
