<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
  header('Location: admin_login');
  exit;
}
include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/admin_auth.php';
ensureAdminUsersTable($connection);

$sessionAdminId = isset($_SESSION['admin_user_id']) ? intval($_SESSION['admin_user_id']) : 0;
$currentAdminLevel = isset($_SESSION['admin_level']) ? (string)$_SESSION['admin_level'] : 'staff';
if (!in_array($currentAdminLevel, array('super_admin', 'staff'), true) || $currentAdminLevel === '') {
    $currentAdminLevel = getAdminLevelById($connection, $sessionAdminId);
    $_SESSION['admin_level'] = $currentAdminLevel;
}
$isSuperAdmin = $currentAdminLevel === 'super_admin';
?>
<?php
$pageTitle = 'Dashboard Admin - RIASEC';

if (!$isSuperAdmin && isset($_POST['patch_region_submit'])) {
    $params = array(
        'permission_error' => 'Akses ditolak. Hanya Super Admin yang dapat melakukan patching Provinsi dan Kota.'
    );
    header('Location: admin_scores?' . http_build_query($params));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patch_region_submit'])) {
    $returnQueryRaw = isset($_POST['return_query']) ? trim((string)$_POST['return_query']) : '';
    $schoolKeyword = isset($_POST['school_keyword']) ? trim((string)$_POST['school_keyword']) : '';
    $patchProvince = isset($_POST['patch_province']) ? trim((string)$_POST['patch_province']) : '';
    $patchCity = isset($_POST['patch_city']) ? trim((string)$_POST['patch_city']) : '';

    $redirectParams = array();
    if ($returnQueryRaw !== '') {
        parse_str($returnQueryRaw, $parsedReturnQuery);
        if (is_array($parsedReturnQuery)) {
            $redirectParams = array_merge($redirectParams, $parsedReturnQuery);
        }
    }
    $redirectParams['show_patch'] = '1';

    if ($schoolKeyword === '') {
        $redirectParams['patch_error'] = 'Kata kunci pencarian Sekolah/Institusi/Universitas wajib diisi.';
        $redirectParams['patch_province'] = $patchProvince;
        $redirectParams['patch_city'] = $patchCity;
        header('Location: admin_scores?' . http_build_query($redirectParams));
        exit;
    }
    if ($patchProvince === '' || $patchCity === '') {
        $redirectParams['patch_error'] = 'Provinsi dan Kota wajib diisi untuk patching data.';
        $redirectParams['patch_keyword'] = $schoolKeyword;
        $redirectParams['patch_province'] = $patchProvince;
        $redirectParams['patch_city'] = $patchCity;
        header('Location: admin_scores?' . http_build_query($redirectParams));
        exit;
    }

    $likeKeyword = '%' . $schoolKeyword . '%';
    $matchedCount = 0;
    $matchedStmt = mysqli_prepare(
        $connection,
        "SELECT COUNT(*) AS total
         FROM personal_info
         WHERE school_name IS NOT NULL
           AND TRIM(school_name) NOT IN ('', '-')
           AND school_name LIKE ?"
    );
    if ($matchedStmt) {
        mysqli_stmt_bind_param($matchedStmt, 's', $likeKeyword);
        mysqli_stmt_execute($matchedStmt);
        mysqli_stmt_bind_result($matchedStmt, $totalMatched);
        if (mysqli_stmt_fetch($matchedStmt)) {
            $matchedCount = intval($totalMatched);
        }
        mysqli_stmt_close($matchedStmt);
    }

    $updatedCount = 0;
    if ($matchedCount > 0) {
        $updateStmt = mysqli_prepare(
            $connection,
            "UPDATE personal_info
             SET province = ?, city = ?
             WHERE school_name IS NOT NULL
               AND TRIM(school_name) NOT IN ('', '-')
               AND school_name LIKE ?"
        );
        if ($updateStmt) {
            mysqli_stmt_bind_param($updateStmt, 'sss', $patchProvince, $patchCity, $likeKeyword);
            mysqli_stmt_execute($updateStmt);
            $updatedCount = intval(mysqli_stmt_affected_rows($updateStmt));
            mysqli_stmt_close($updateStmt);
        } else {
            $redirectParams['patch_error'] = 'Gagal menyiapkan proses patching data.';
            $redirectParams['patch_keyword'] = $schoolKeyword;
            $redirectParams['patch_province'] = $patchProvince;
            $redirectParams['patch_city'] = $patchCity;
            header('Location: admin_scores?' . http_build_query($redirectParams));
            exit;
        }
    }

    $redirectParams['patched'] = $updatedCount;
    $redirectParams['matched'] = $matchedCount;
    $redirectParams['patch_keyword'] = $schoolKeyword;
    $redirectParams['patch_province'] = $patchProvince;
    $redirectParams['patch_city'] = $patchCity;
    header('Location: admin_scores?' . http_build_query($redirectParams));
    exit;
}

if (isset($_GET['patch_preview']) && $_GET['patch_preview'] === '1') {
    header('Content-Type: application/json; charset=utf-8');

    if (!$isSuperAdmin) {
        echo json_encode(array(
            'ok' => false,
            'message' => 'Akses ditolak. Hanya Super Admin yang dapat membuka preview patching.',
            'items' => array(),
            'total' => 0
        ));
        exit;
    }

    $keyword = isset($_GET['school_keyword']) ? trim((string)$_GET['school_keyword']) : '';
    if ($keyword === '') {
        echo json_encode(array(
            'ok' => false,
            'message' => 'Kata kunci pencarian wajib diisi.',
            'items' => array(),
            'total' => 0
        ));
        exit;
    }

    $likeKeyword = '%' . $keyword . '%';
    $items = array();
    $total = 0;

    $totalStmt = mysqli_prepare(
        $connection,
        "SELECT COUNT(*) AS total
         FROM personal_info
         WHERE school_name IS NOT NULL
           AND TRIM(school_name) NOT IN ('', '-')
           AND school_name LIKE ?"
    );
    if ($totalStmt) {
        mysqli_stmt_bind_param($totalStmt, 's', $likeKeyword);
        mysqli_stmt_execute($totalStmt);
        mysqli_stmt_bind_result($totalStmt, $totalRows);
        if (mysqli_stmt_fetch($totalStmt)) {
            $total = intval($totalRows);
        }
        mysqli_stmt_close($totalStmt);
    }

    $previewStmt = mysqli_prepare(
        $connection,
        "SELECT school_name, COUNT(*) AS total
         FROM personal_info
         WHERE school_name IS NOT NULL
           AND TRIM(school_name) NOT IN ('', '-')
           AND school_name LIKE ?
         GROUP BY school_name
         ORDER BY total DESC, school_name ASC
         LIMIT 30"
    );

    if ($previewStmt) {
        mysqli_stmt_bind_param($previewStmt, 's', $likeKeyword);
        mysqli_stmt_execute($previewStmt);
        mysqli_stmt_bind_result($previewStmt, $schoolName, $countBySchool);
        while (mysqli_stmt_fetch($previewStmt)) {
            $items[] = array(
                'school_name' => (string)$schoolName,
                'total' => intval($countBySchool)
            );
        }
        mysqli_stmt_close($previewStmt);
    }

    echo json_encode(array(
        'ok' => true,
        'message' => '',
        'items' => $items,
        'total' => $total
    ));
    exit;
}

function adminColumnExists($connection, $tableName, $columnName) {
    $safeTable = mysqli_real_escape_string($connection, $tableName);
    $safeColumn = mysqli_real_escape_string($connection, $columnName);
    $sql = "SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeColumn}'";
    $res = mysqli_query($connection, $sql);
    return $res && mysqli_num_rows($res) > 0;
}

if (!adminColumnExists($connection, 'personal_info', 'province')) {
    mysqli_query($connection, "ALTER TABLE personal_info ADD COLUMN province VARCHAR(100) NULL AFTER school_name");
}
if (!adminColumnExists($connection, 'personal_info', 'city')) {
    mysqli_query($connection, "ALTER TABLE personal_info ADD COLUMN city VARCHAR(120) NULL AFTER province");
}

// Analytics Queries
$total_tests_query = "SELECT COUNT(*) as total FROM personality_test_scores";
$total_tests_result = mysqli_query($connection, $total_tests_query);
$total_tests_row = $total_tests_result ? mysqli_fetch_assoc($total_tests_result) : array('total' => 0);
$total_tests = intval($total_tests_row['total']);

$top_code_query = "SELECT result, COUNT(*) as count
                   FROM personality_test_scores
                   WHERE result IS NOT NULL
                     AND TRIM(result) != ''
                     AND TRIM(result) != '-'
                     AND TRIM(result) REGEXP '^[RIASEC]{1,3}$'
                   GROUP BY result
                   ORDER BY count DESC, result ASC
                   LIMIT 1";
$top_code_result = mysqli_query($connection, $top_code_query);
$top_code = $top_code_result ? mysqli_fetch_assoc($top_code_result) : null;

$avg_scores_query = "SELECT AVG(realistic) as avg_r, AVG(investigative) as avg_i, AVG(artistic) as avg_a, AVG(social) as avg_s, AVG(enterprising) as avg_e, AVG(conventional) as avg_c FROM personality_test_scores";
$avg_scores_result = mysqli_query($connection, $avg_scores_query);
$avg_scores = $avg_scores_result ? mysqli_fetch_assoc($avg_scores_result) : array();

$schools_query = "SELECT COUNT(DISTINCT TRIM(pi.school_name)) as total_schools
                  FROM personality_test_scores pts
                  INNER JOIN personal_info pi ON pi.id = pts.personal_info_id
                  WHERE pi.school_name IS NOT NULL
                    AND TRIM(pi.school_name) NOT IN ('', '-')";
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

$provinceOptions = array();
$provinceOptionRes = mysqli_query($connection, "SELECT DISTINCT province FROM personal_info WHERE province IS NOT NULL AND TRIM(province) NOT IN ('', '-') ORDER BY province ASC");
if ($provinceOptionRes) {
    while ($provinceRow = mysqli_fetch_assoc($provinceOptionRes)) {
        $provinceOptions[] = $provinceRow['province'];
    }
}

$cityOptions = array();
$cityOptionRes = mysqli_query($connection, "SELECT DISTINCT city FROM personal_info WHERE city IS NOT NULL AND TRIM(city) NOT IN ('', '-') ORDER BY city ASC");
if ($cityOptionRes) {
    while ($cityRow = mysqli_fetch_assoc($cityOptionRes)) {
        $cityOptions[] = $cityRow['city'];
    }
}

// Current filter values
$filterSearch = isset($_GET['q']) ? trim($_GET['q']) : '';
$filterClass = isset($_GET['class_level']) ? trim($_GET['class_level']) : '';
$filterResult = isset($_GET['result_code']) ? strtoupper(trim($_GET['result_code'])) : '';
$filterSchool = isset($_GET['school_name']) ? trim($_GET['school_name']) : '';
$filterProvince = isset($_GET['province']) ? trim($_GET['province']) : '';
$filterCity = isset($_GET['city']) ? trim($_GET['city']) : '';
$filterEmptyCity = $isSuperAdmin && isset($_GET['empty_city']) && $_GET['empty_city'] === '1';
$filterDateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$filterDateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

$whereClauses = array();

if ($filterSearch !== '') {
    $safeSearch = mysqli_real_escape_string($connection, $filterSearch);
    $whereClauses[] = "(pi.full_name LIKE '%{$safeSearch}%' OR pi.email LIKE '%{$safeSearch}%' OR pi.school_name LIKE '%{$safeSearch}%')";
}

if (in_array($filterClass, array('10', '11', '12', 'Universitas'), true)) {
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
if ($filterProvince !== '') {
    $safeProvince = mysqli_real_escape_string($connection, $filterProvince);
    $whereClauses[] = "pi.province = '{$safeProvince}'";
}
if ($filterCity !== '') {
    $safeCity = mysqli_real_escape_string($connection, $filterCity);
    $whereClauses[] = "pi.city = '{$safeCity}'";
}
if ($filterEmptyCity) {
    $whereClauses[] = "TRIM(COALESCE(pi.city, '')) = ''";
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

$returnQuery = $_SERVER['QUERY_STRING'] ?? '';
$deletedCount = isset($_GET['deleted']) ? intval($_GET['deleted']) : 0;
$deletedUnknownCount = isset($_GET['deleted_unknown']) ? intval($_GET['deleted_unknown']) : 0;
$patchUpdatedCount = isset($_GET['patched']) ? intval($_GET['patched']) : -1;
$patchMatchedCount = isset($_GET['matched']) ? intval($_GET['matched']) : 0;
$patchKeyword = isset($_GET['patch_keyword']) ? trim($_GET['patch_keyword']) : '';
$patchProvinceValue = isset($_GET['patch_province']) ? trim($_GET['patch_province']) : '';
$patchCityValue = isset($_GET['patch_city']) ? trim($_GET['patch_city']) : '';
$patchError = isset($_GET['patch_error']) ? trim($_GET['patch_error']) : '';
$showPatchForm = $isSuperAdmin && isset($_GET['show_patch']) && $_GET['show_patch'] === '1';
$permissionError = isset($_GET['permission_error']) ? trim((string)$_GET['permission_error']) : '';
$tableColspan = $isSuperAdmin ? 17 : 16;

$unknownCountSql = "SELECT COUNT(*) AS total
                    FROM personality_test_scores pts
                    LEFT JOIN personal_info pi ON pi.id = pts.personal_info_id
                    WHERE
                      pi.id IS NULL
                      OR TRIM(COALESCE(pi.full_name, '')) IN ('', '-')
                      OR TRIM(COALESCE(pi.email, '')) IN ('', '-')
                      OR TRIM(COALESCE(pi.class_level, '')) IN ('', '-')
                      OR TRIM(COALESCE(pi.school_name, '')) IN ('', '-')";
$unknownCountRes = mysqli_query($connection, $unknownCountSql);
$unknownCountRow = $unknownCountRes ? mysqli_fetch_assoc($unknownCountRes) : array('total' => 0);
$unknownCount = intval($unknownCountRow['total']);

$emptyCityCountSql = "SELECT COUNT(*) AS total
                      FROM personality_test_scores pts
                      LEFT JOIN personal_info pi ON pi.id = pts.personal_info_id
                      WHERE TRIM(COALESCE(pi.city, '')) = ''";
$emptyCityCountRes = mysqli_query($connection, $emptyCityCountSql);
$emptyCityCountRow = $emptyCityCountRes ? mysqli_fetch_assoc($emptyCityCountRes) : array('total' => 0);
$emptyCityCount = intval($emptyCityCountRow['total']);

$showEmptyCityParams = $_GET;
$showEmptyCityParams['empty_city'] = '1';
$showEmptyCityUrl = 'admin_scores?' . http_build_query($showEmptyCityParams);

$showAllCityParams = $_GET;
unset($showAllCityParams['empty_city']);
$showAllCityUrl = 'admin_scores' . (!empty($showAllCityParams) ? ('?' . http_build_query($showAllCityParams)) : '');

$query = "SELECT pts.id AS score_id,
                 pts.result,
                 pts.realistic, pts.investigative, pts.artistic,
                 pts.social, pts.enterprising, pts.conventional,
                 pts.created_at,
                 pi.id AS person_id, pi.full_name, pi.birth_date, pi.phone, pi.email,
                 pi.class_level, pi.school_name, pi.province, pi.city,
                 pi.extracurricular, pi.organization, pi.created_at AS person_created
          FROM personality_test_scores pts
          LEFT JOIN personal_info pi ON pi.id = pts.personal_info_id
          {$whereSql}
          ORDER BY pts.created_at DESC";
$scores = mysqli_query($connection, $query);
$filteredTotal = $scores ? mysqli_num_rows($scores) : 0;
?>
<?php include 'includes/header.php'; ?>

<section class="page-wrap">
  <div class="glass-card hero-card mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <p class="kicker mb-1">Admin Dashboard</p>
        <h1 class="hero-title h2 mb-1">Analitik hasil asesmen RIASEC</h1>
        <p class="hero-subtitle mb-0">Pantau distribusi minat karier peserta dan akses detail jawaban per tes.</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="generate_excel" class="btn btn-outline-soft">Export CSV</a>
        <button type="button" class="btn btn-outline-soft" id="exportExcelBtn">Export to Excel</button>
        <?php if ($isSuperAdmin) { ?>
          <a href="admin_users" class="btn btn-outline-soft">Kelola Admin</a>
          <button type="button" class="btn btn-outline-soft" id="togglePatchRegionBtn">Patching Provinsi dan Kota</button>
        <?php } ?>
        <a href="admin_logout" class="btn btn-outline-danger">Logout</a>
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

    <?php if ($permissionError !== '') { ?>
      <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($permissionError); ?>
      </div>
    <?php } ?>
    <?php if ($deletedCount > 0) { ?>
      <div class="alert alert-success" role="alert">
        <?php echo $deletedCount; ?> data berhasil dihapus.
      </div>
    <?php } ?>
    <?php if ($deletedUnknownCount > 0) { ?>
      <div class="alert alert-success" role="alert">
        <?php echo $deletedUnknownCount; ?> data unknown (bernilai "-"/kosong) berhasil dihapus.
      </div>
    <?php } ?>
    <?php if ($isSuperAdmin && $patchError !== '') { ?>
      <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($patchError); ?>
      </div>
    <?php } ?>
    <?php if ($isSuperAdmin && $patchUpdatedCount >= 0 && $patchError === '') { ?>
      <div class="alert <?php echo $patchMatchedCount > 0 ? 'alert-success' : 'alert-warning'; ?>" role="alert">
        <?php if ($patchMatchedCount > 0) { ?>
          Patching selesai untuk pencarian "<?php echo htmlspecialchars($patchKeyword); ?>".
          Ditemukan <?php echo intval($patchMatchedCount); ?> data, berubah <?php echo intval($patchUpdatedCount); ?> data.
        <?php } else { ?>
          Tidak ada data Sekolah/Institusi/Universitas yang cocok dengan pencarian "<?php echo htmlspecialchars($patchKeyword); ?>".
        <?php } ?>
      </div>
    <?php } ?>
    <?php if ($filterEmptyCity) { ?>
      <div class="alert alert-warning" role="alert">
        Filter aktif: menampilkan data dengan Kota kosong.
      </div>
    <?php } ?>

    <?php if ($isSuperAdmin) { ?>
      <div class="glass-card app-form-card mb-3" id="patchRegionPanel" style="<?php echo $showPatchForm ? '' : 'display:none;'; ?>">
        <h3 class="h6 fw-bold text-success mb-3">Form Patching Provinsi dan Kota</h3>
        <form method="post" action="admin_scores" class="row g-2">
          <input type="hidden" name="return_query" value="<?php echo htmlspecialchars($returnQuery); ?>">
          <div class="col-lg-5 col-md-12">
            <label class="form-label small mb-1">Search "Sekolah/Institusi/Universitas"</label>
            <input
              type="text"
              class="form-control"
              name="school_keyword"
              id="patchSchoolKeyword"
              required
              placeholder="Contoh: SMK Negeri"
              value="<?php echo htmlspecialchars($patchKeyword); ?>"
            >
            <div class="d-flex justify-content-between align-items-center mt-2">
              <button type="button" class="btn btn-sm btn-outline-secondary" id="patchPreviewBtn">Cari Preview</button>
              <span class="small muted" id="patchPreviewHint">Preview menampilkan daftar sekolah yang cocok sebelum patching.</span>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <label class="form-label small mb-1">Provinsi</label>
            <input type="text" class="form-control" name="patch_province" required placeholder="Contoh: Jawa Barat" value="<?php echo htmlspecialchars($patchProvinceValue); ?>">
          </div>
          <div class="col-lg-3 col-md-6">
            <label class="form-label small mb-1">Kota</label>
            <input type="text" class="form-control" name="patch_city" required placeholder="Contoh: Bandung" value="<?php echo htmlspecialchars($patchCityValue); ?>">
          </div>
          <div class="col-lg-1 col-md-12 d-flex align-items-end">
            <button
              type="submit"
              name="patch_region_submit"
              value="1"
              class="btn btn-primary-soft w-100"
              onclick="return confirm('Yakin ingin patching Provinsi dan Kota untuk semua data yang cocok dengan pencarian ini?');"
            >
              Simpan
            </button>
          </div>
        </form>
        <p class="small muted mb-0 mt-2">Patching akan mengubah kolom Provinsi dan Kota pada semua data yang nama Sekolah/Institusi/Universitas mengandung kata kunci pencarian.</p>
        <div class="table-responsive mt-3" id="patchPreviewContainer" style="display:none;">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-success">
              <tr>
                <th>Sekolah/Institusi/Universitas</th>
                <th style="width:120px;">Jumlah Data</th>
              </tr>
            </thead>
            <tbody id="patchPreviewBody">
              <tr><td colspan="2" class="text-center muted">Belum ada preview.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    <?php } ?>

    <form method="get" action="admin_scores" class="mb-3">
      <div class="row g-2">
        <div class="col-lg-3 col-md-6">
          <label class="form-label small mb-1">Cari (nama/email/sekolah)</label>
          <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($filterSearch); ?>" placeholder="Ketik kata kunci">
        </div>
        <div class="col-lg-2 col-md-6">
          <label class="form-label small mb-1">Jenjang Pendidikan</label>
          <select class="form-select" name="class_level">
            <option value="">Semua</option>
            <option value="10" <?php echo $filterClass === '10' ? 'selected' : ''; ?>>10</option>
            <option value="11" <?php echo $filterClass === '11' ? 'selected' : ''; ?>>11</option>
            <option value="12" <?php echo $filterClass === '12' ? 'selected' : ''; ?>>12</option>
            <option value="Universitas" <?php echo $filterClass === 'Universitas' ? 'selected' : ''; ?>>Universitas</option>
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
          <label class="form-label small mb-1">Sekolah/Institusi/Universitas</label>
          <select class="form-select" name="school_name">
            <option value="">Semua sekolah</option>
            <?php foreach ($schoolOptions as $schoolName) { ?>
              <option value="<?php echo htmlspecialchars($schoolName); ?>" <?php echo $filterSchool === $schoolName ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($schoolName); ?>
              </option>
            <?php } ?>
          </select>
        </div>
        <div class="col-lg-3 col-md-6">
          <label class="form-label small mb-1">Provinsi</label>
          <select class="form-select" name="province">
            <option value="">Semua provinsi</option>
            <?php foreach ($provinceOptions as $provinceName) { ?>
              <option value="<?php echo htmlspecialchars($provinceName); ?>" <?php echo $filterProvince === $provinceName ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($provinceName); ?>
              </option>
            <?php } ?>
          </select>
        </div>
        <div class="col-lg-3 col-md-6">
          <label class="form-label small mb-1">Kota</label>
          <select class="form-select" name="city">
            <option value="">Semua kota</option>
            <?php foreach ($cityOptions as $cityName) { ?>
              <option value="<?php echo htmlspecialchars($cityName); ?>" <?php echo $filterCity === $cityName ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cityName); ?>
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
        <?php if ($isSuperAdmin) { ?>
          <a href="<?php echo htmlspecialchars($showEmptyCityUrl); ?>" class="btn btn-outline-warning">Show Empty Kota (<?php echo $emptyCityCount; ?>)</a>
          <?php if ($filterEmptyCity) { ?>
            <a href="<?php echo htmlspecialchars($showAllCityUrl); ?>" class="btn btn-outline-secondary">Show Semua Kota</a>
          <?php } ?>
        <?php } ?>
        <a href="admin_scores" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>

    <form method="post" action="admin_delete_score" id="bulkDeleteForm">
      <input type="hidden" name="return_query" value="<?php echo htmlspecialchars($returnQuery); ?>">
      <?php if ($isSuperAdmin) { ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="selectAllRows">
            <label class="form-check-label" for="selectAllRows">Pilih semua data di halaman ini</label>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <button
              type="submit"
              formaction="admin_delete_score"
              formmethod="post"
              name="delete_unknown"
              value="1"
              class="btn btn-warning"
              <?php echo $unknownCount <= 0 ? 'disabled' : ''; ?>
              onclick="return confirm('Apakah Anda yakin ingin menghapus semua data unknown (nilai \"-\" atau kosong)?');"
            >
              Remove Unknown data (<?php echo $unknownCount; ?>)
            </button>
            <button
              type="submit"
              class="btn btn-outline-danger"
              id="bulkDeleteBtn"
              disabled
              onclick="return confirm('Apakah Anda yakin ingin menghapus semua data yang dipilih?');"
            >
              Hapus data terpilih
            </button>
          </div>
        </div>
      <?php } ?>

      <div class="table-responsive">
        <table class="table table-hover align-middle" id="scoresTable" data-has-checkbox="<?php echo $isSuperAdmin ? '1' : '0'; ?>">
          <thead class="table-success">
            <tr>
              <?php if ($isSuperAdmin) { ?>
                <th><input class="form-check-input" type="checkbox" value="1" id="selectAllRowsHeader"></th>
              <?php } ?>
              <th>#</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Jenjang Pendidikan</th>
              <th>Sekolah/Institusi/Universitas</th>
              <th>Provinsi</th>
              <th>Kota</th>
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
                  <?php if ($isSuperAdmin) { ?>
                    <td>
                      <input
                        class="form-check-input row-checkbox"
                        type="checkbox"
                        name="score_ids[]"
                        value="<?php echo intval($row['score_id']); ?>"
                      >
                    </td>
                  <?php } ?>
                  <td><?php echo $rowNum++; ?></td>
                  <td><?php echo htmlspecialchars($row['full_name'] ?? '-'); ?></td>
                  <td><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
                  <td><?php echo htmlspecialchars($row['class_level'] ?? '-'); ?></td>
                  <td><?php echo htmlspecialchars($row['school_name'] ?? '-'); ?></td>
                  <td><?php echo htmlspecialchars($row['province'] ?? '-'); ?></td>
                  <td><?php echo htmlspecialchars($row['city'] ?? '-'); ?></td>
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
                      <a href="admin_score_detail?score_id=<?php echo intval($row['score_id']); ?>" class="btn btn-sm btn-outline-success">Detail</a>
                      <?php if ($isSuperAdmin) { ?>
                        <a href="admin_delete_score?score_id=<?php echo intval($row['score_id']); ?>&return_query=<?php echo urlencode($returnQuery); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus hasil tes ini?');">Hapus</a>
                      <?php } ?>
                    </div>
                  </td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr>
                <td colspan="<?php echo $tableColspan; ?>" class="text-center muted">Belum ada data hasil tes.</td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </form>
  </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const rowCheckboxes = Array.from(document.querySelectorAll('.row-checkbox'));
  const selectAllRows = document.getElementById('selectAllRows');
  const selectAllRowsHeader = document.getElementById('selectAllRowsHeader');
  const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
  const exportExcelBtn = document.getElementById('exportExcelBtn');
  const togglePatchRegionBtn = document.getElementById('togglePatchRegionBtn');
  const patchRegionPanel = document.getElementById('patchRegionPanel');
  const patchSchoolKeyword = document.getElementById('patchSchoolKeyword');
  const patchPreviewBtn = document.getElementById('patchPreviewBtn');
  const patchPreviewHint = document.getElementById('patchPreviewHint');
  const patchPreviewContainer = document.getElementById('patchPreviewContainer');
  const patchPreviewBody = document.getElementById('patchPreviewBody');
  const scoresTable = document.getElementById('scoresTable');

  if (togglePatchRegionBtn && patchRegionPanel) {
    togglePatchRegionBtn.addEventListener('click', function () {
      const isHidden = window.getComputedStyle(patchRegionPanel).display === 'none';
      patchRegionPanel.style.display = isHidden ? 'block' : 'none';
    });
  }

  function renderPatchPreviewRows(items) {
    if (!patchPreviewBody) {
      return;
    }
    patchPreviewBody.innerHTML = '';
    if (!items || !items.length) {
      patchPreviewBody.innerHTML = '<tr><td colspan="2" class="text-center muted">Tidak ada data yang cocok.</td></tr>';
      return;
    }

    items.forEach(function (item) {
      const tr = document.createElement('tr');
      const schoolTd = document.createElement('td');
      schoolTd.textContent = item.school_name || '-';
      const totalTd = document.createElement('td');
      totalTd.textContent = String(item.total || 0);
      tr.appendChild(schoolTd);
      tr.appendChild(totalTd);
      patchPreviewBody.appendChild(tr);
    });
  }

  function runPatchPreview() {
    if (!patchSchoolKeyword || !patchPreviewHint || !patchPreviewContainer) {
      return;
    }

    const keyword = patchSchoolKeyword.value.trim();
    if (!keyword) {
      patchPreviewContainer.style.display = 'none';
      patchPreviewHint.textContent = 'Masukkan kata kunci untuk melihat preview data.';
      return;
    }

    patchPreviewContainer.style.display = 'block';
    patchPreviewHint.textContent = 'Mencari data...';
    if (patchPreviewBody) {
      patchPreviewBody.innerHTML = '<tr><td colspan="2" class="text-center muted">Memuat preview...</td></tr>';
    }

    const url = 'admin_scores?patch_preview=1&school_keyword=' + encodeURIComponent(keyword);
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }
        return response.json();
      })
      .then(function (payload) {
        if (!payload || !payload.ok) {
          throw new Error((payload && payload.message) ? payload.message : 'Gagal mengambil preview.');
        }
        renderPatchPreviewRows(payload.items || []);
        patchPreviewHint.textContent = 'Ditemukan ' + String(payload.total || 0) + ' data cocok untuk kata kunci "' + keyword + '".';
      })
      .catch(function (error) {
        if (patchPreviewBody) {
          patchPreviewBody.innerHTML = '<tr><td colspan="2" class="text-center text-danger">Gagal memuat preview.</td></tr>';
        }
        patchPreviewHint.textContent = (error && error.message) ? error.message : 'Gagal memuat preview.';
      });
  }

  if (patchPreviewBtn) {
    patchPreviewBtn.addEventListener('click', runPatchPreview);
  }
  if (patchSchoolKeyword) {
    patchSchoolKeyword.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        runPatchPreview();
      }
    });
  }
  if (patchSchoolKeyword && patchSchoolKeyword.value.trim() !== '' && patchRegionPanel && window.getComputedStyle(patchRegionPanel).display !== 'none') {
    runPatchPreview();
  }

  if (rowCheckboxes.length && selectAllRows && selectAllRowsHeader && bulkDeleteBtn) {
    function syncBulkControls() {
      const checkedCount = rowCheckboxes.filter((checkbox) => checkbox.checked).length;
      const allChecked = checkedCount > 0 && checkedCount === rowCheckboxes.length;
      bulkDeleteBtn.disabled = checkedCount === 0;
      selectAllRows.checked = allChecked;
      selectAllRowsHeader.checked = allChecked;
      selectAllRows.indeterminate = checkedCount > 0 && !allChecked;
      selectAllRowsHeader.indeterminate = checkedCount > 0 && !allChecked;
    }

    function toggleAllRows(checked) {
      rowCheckboxes.forEach((checkbox) => {
        checkbox.checked = checked;
      });
      syncBulkControls();
    }

    selectAllRows.addEventListener('change', function () {
      toggleAllRows(this.checked);
    });

    selectAllRowsHeader.addEventListener('change', function () {
      toggleAllRows(this.checked);
    });

    rowCheckboxes.forEach((checkbox) => {
      checkbox.addEventListener('change', syncBulkControls);
    });

    syncBulkControls();
  }

  if (exportExcelBtn && scoresTable) {
    exportExcelBtn.addEventListener('click', function () {
      if (typeof XLSX === 'undefined') {
        alert('Library Excel belum tersedia. Silakan refresh halaman.');
        return;
      }

      const rows = Array.from(scoresTable.querySelectorAll('tr'));
      const hasCheckboxColumn = scoresTable.getAttribute('data-has-checkbox') === '1';
      const startColumnIndex = hasCheckboxColumn ? 1 : 0;
      const data = rows.map((row) => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells
          .slice(startColumnIndex, -1) // Skip optional checkbox and action columns.
          .map((cell) => cell.innerText.trim());
      }).filter((rowData) => rowData.length > 0);

      if (data.length <= 1) {
        alert('Tidak ada data untuk diekspor.');
        return;
      }

      const worksheet = XLSX.utils.aoa_to_sheet(data);
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, 'Hasil RIASEC');

      const now = new Date();
      const dateStamp = now.toISOString().slice(0, 10);
      XLSX.writeFile(workbook, 'hasil_riasec_' + dateStamp + '.xlsx');
    });
  }
});
</script>

<?php include 'includes/footer.php'; ?>


