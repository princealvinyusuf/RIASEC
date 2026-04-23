<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
    header('Location: admin_login');
    exit;
}

include 'includes/db.php';
include_once 'includes/admin_auth.php';

ensureAdminUsersTable($connection);

$currentAdminId = isset($_SESSION['admin_user_id']) ? intval($_SESSION['admin_user_id']) : 0;
$currentAdminUsername = isset($_SESSION['admin_username']) ? (string)$_SESSION['admin_username'] : '';
$currentAdminLevel = isset($_SESSION['admin_level']) ? (string)$_SESSION['admin_level'] : 'staff';
if (!in_array($currentAdminLevel, array('super_admin', 'staff'), true)) {
    $currentAdminLevel = getAdminLevelById($connection, $currentAdminId);
    $_SESSION['admin_level'] = $currentAdminLevel;
}

if ($currentAdminLevel !== 'super_admin') {
    $params = array(
        'permission_error' => 'Akses ditolak. Hanya Super Admin yang dapat membuka halaman Kelola Admin.'
    );
    header('Location: admin_scores?' . http_build_query($params));
    exit;
}

function redirectAdminUsers($status, $message) {
    $params = array(
        'status' => $status,
        'message' => $message
    );
    header('Location: admin_users?' . http_build_query($params));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$connection) {
        redirectAdminUsers('error', 'Koneksi database tidak tersedia.');
    }

    $action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';

    if ($action === 'create_admin') {
        $username = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
        $adminLevel = isset($_POST['admin_level']) ? trim((string)$_POST['admin_level']) : 'staff';

        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
            redirectAdminUsers('error', 'Username wajib 3-50 karakter (huruf, angka, titik, underscore, atau strip).');
        }
        if (strlen($password) < 8) {
            redirectAdminUsers('error', 'Password minimal 8 karakter.');
        }
        if (!in_array($adminLevel, array('super_admin', 'staff'), true)) {
            redirectAdminUsers('error', 'Level admin tidak valid.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = mysqli_prepare($connection, "INSERT INTO admin_users (username, password_hash, admin_level, is_active) VALUES (?, ?, ?, 1)");
        if (!$insertStmt) {
            redirectAdminUsers('error', 'Gagal menyiapkan data admin baru.');
        }
        mysqli_stmt_bind_param($insertStmt, 'sss', $username, $passwordHash, $adminLevel);
        $insertOk = mysqli_stmt_execute($insertStmt);
        $insertErrno = mysqli_errno($connection);
        mysqli_stmt_close($insertStmt);

        if ($insertOk) {
            redirectAdminUsers('success', 'Admin baru berhasil ditambahkan.');
        }
        if ($insertErrno === 1062) {
            redirectAdminUsers('error', 'Username sudah digunakan. Pilih username lain.');
        }
        redirectAdminUsers('error', 'Gagal menambahkan admin baru.');
    }

    if ($action === 'change_password') {
        $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $newPassword = isset($_POST['new_password']) ? (string)$_POST['new_password'] : '';

        if ($userId <= 0) {
            redirectAdminUsers('error', 'ID admin tidak valid.');
        }
        if (strlen($newPassword) < 8) {
            redirectAdminUsers('error', 'Password baru minimal 8 karakter.');
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = mysqli_prepare($connection, "UPDATE admin_users SET password_hash = ? WHERE id = ?");
        if (!$updateStmt) {
            redirectAdminUsers('error', 'Gagal menyiapkan perubahan password.');
        }
        mysqli_stmt_bind_param($updateStmt, 'si', $passwordHash, $userId);
        mysqli_stmt_execute($updateStmt);
        $affected = mysqli_stmt_affected_rows($updateStmt);
        mysqli_stmt_close($updateStmt);

        if ($affected > 0) {
            redirectAdminUsers('success', 'Password admin berhasil diubah.');
        }
        redirectAdminUsers('error', 'Admin tidak ditemukan atau password tidak berubah.');
    }

    if ($action === 'change_level') {
        $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $newLevel = isset($_POST['new_level']) ? trim((string)$_POST['new_level']) : '';

        if ($userId <= 0 || !in_array($newLevel, array('super_admin', 'staff'), true)) {
            redirectAdminUsers('error', 'Permintaan ubah level tidak valid.');
        }

        $targetStmt = mysqli_prepare($connection, "SELECT username, admin_level FROM admin_users WHERE id = ? LIMIT 1");
        if (!$targetStmt) {
            redirectAdminUsers('error', 'Gagal membaca data admin.');
        }
        mysqli_stmt_bind_param($targetStmt, 'i', $userId);
        mysqli_stmt_execute($targetStmt);
        mysqli_stmt_bind_result($targetStmt, $targetUsername, $currentLevel);
        $foundTarget = mysqli_stmt_fetch($targetStmt);
        mysqli_stmt_close($targetStmt);

        if (!$foundTarget) {
            redirectAdminUsers('error', 'Admin tidak ditemukan.');
        }

        $currentLevel = in_array($currentLevel, array('super_admin', 'staff'), true) ? $currentLevel : 'staff';
        if ($currentLevel === $newLevel) {
            redirectAdminUsers('success', 'Level admin sudah sesuai.');
        }

        if ($currentLevel === 'super_admin' && $newLevel === 'staff') {
            $superAdminCount = countSuperAdminUsers($connection);
            if ($superAdminCount <= 1) {
                redirectAdminUsers('error', 'Tidak bisa mengubah level Super Admin terakhir menjadi Staff.');
            }
            if ($currentAdminId > 0 && $currentAdminId === $userId) {
                redirectAdminUsers('error', 'Anda tidak dapat mengubah level akun Anda sendiri menjadi Staff.');
            }
        }

        $levelStmt = mysqli_prepare($connection, "UPDATE admin_users SET admin_level = ? WHERE id = ?");
        if (!$levelStmt) {
            redirectAdminUsers('error', 'Gagal menyiapkan perubahan level admin.');
        }
        mysqli_stmt_bind_param($levelStmt, 'si', $newLevel, $userId);
        mysqli_stmt_execute($levelStmt);
        $changed = mysqli_stmt_affected_rows($levelStmt) > 0;
        mysqli_stmt_close($levelStmt);

        if ($changed) {
            $newLevelLabel = $newLevel === 'super_admin' ? 'Super Admin' : 'Staff';
            redirectAdminUsers('success', 'Level admin "' . $targetUsername . '" berhasil diubah ke ' . $newLevelLabel . '.');
        }
        redirectAdminUsers('error', 'Gagal mengubah level admin.');
    }

    if ($action === 'toggle_active') {
        $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $toggleTo = isset($_POST['toggle_to']) ? intval($_POST['toggle_to']) : -1;

        if ($userId <= 0 || ($toggleTo !== 0 && $toggleTo !== 1)) {
            redirectAdminUsers('error', 'Permintaan ubah status tidak valid.');
        }

        $targetStmt = mysqli_prepare($connection, "SELECT username, is_active, admin_level FROM admin_users WHERE id = ? LIMIT 1");
        if (!$targetStmt) {
            redirectAdminUsers('error', 'Gagal membaca data admin.');
        }
        mysqli_stmt_bind_param($targetStmt, 'i', $userId);
        mysqli_stmt_execute($targetStmt);
        mysqli_stmt_bind_result($targetStmt, $targetUsername, $currentStatus, $targetLevel);
        $foundTarget = mysqli_stmt_fetch($targetStmt);
        mysqli_stmt_close($targetStmt);

        if (!$foundTarget) {
            redirectAdminUsers('error', 'Admin tidak ditemukan.');
        }

        $currentStatus = intval($currentStatus);
        if ($currentStatus === $toggleTo) {
            redirectAdminUsers('success', 'Status admin sudah sesuai.');
        }

        if ($currentStatus === 1 && $toggleTo === 0) {
            $activeCount = countActiveAdminUsers($connection);
            if ($activeCount <= 1) {
                redirectAdminUsers('error', 'Tidak bisa menonaktifkan admin terakhir yang aktif.');
            }
            if ($currentAdminId > 0 && $currentAdminId === $userId) {
                redirectAdminUsers('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }
            if ($targetLevel === 'super_admin') {
                $activeSuperStmt = mysqli_prepare($connection, "SELECT COUNT(*) FROM admin_users WHERE admin_level = 'super_admin' AND is_active = 1");
                if ($activeSuperStmt) {
                    mysqli_stmt_execute($activeSuperStmt);
                    mysqli_stmt_bind_result($activeSuperStmt, $activeSuperCount);
                    mysqli_stmt_fetch($activeSuperStmt);
                    mysqli_stmt_close($activeSuperStmt);
                    if (intval($activeSuperCount) <= 1) {
                        redirectAdminUsers('error', 'Tidak bisa menonaktifkan Super Admin aktif terakhir.');
                    }
                }
            }
        }

        $toggleStmt = mysqli_prepare($connection, "UPDATE admin_users SET is_active = ? WHERE id = ?");
        if (!$toggleStmt) {
            redirectAdminUsers('error', 'Gagal menyiapkan perubahan status admin.');
        }
        mysqli_stmt_bind_param($toggleStmt, 'ii', $toggleTo, $userId);
        mysqli_stmt_execute($toggleStmt);
        $changed = mysqli_stmt_affected_rows($toggleStmt) > 0;
        mysqli_stmt_close($toggleStmt);

        if ($changed) {
            $statusText = $toggleTo === 1 ? 'diaktifkan' : 'dinonaktifkan';
            redirectAdminUsers('success', 'Admin "' . $targetUsername . '" berhasil ' . $statusText . '.');
        }
        redirectAdminUsers('error', 'Gagal mengubah status admin.');
    }

    redirectAdminUsers('error', 'Aksi tidak dikenali.');
}

$status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
$message = isset($_GET['message']) ? trim((string)$_GET['message']) : '';

$adminRows = array();
if ($connection) {
    $adminResult = mysqli_query($connection, "SELECT id, username, admin_level, is_active, created_at FROM admin_users ORDER BY id ASC");
    if ($adminResult) {
        while ($row = mysqli_fetch_assoc($adminResult)) {
            $adminRows[] = $row;
        }
    }
}

$pageTitle = 'Manajemen Admin - RIASEC';
?>
<?php include 'includes/header.php'; ?>

<section class="page-wrap">
  <div class="glass-card hero-card mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <p class="kicker mb-1">Panel Admin</p>
        <h1 class="hero-title h2 mb-1">Manajemen akun admin</h1>
        <p class="hero-subtitle mb-0">Tambah admin baru, reset password, dan atur status aktif akun.</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="admin_scores" class="btn btn-outline-secondary">Kembali ke dashboard</a>
        <a href="admin_logout" class="btn btn-outline-danger">Logout</a>
      </div>
    </div>
  </div>

  <?php if ($message !== '') { ?>
    <div class="alert <?php echo $status === 'success' ? 'alert-success' : 'alert-danger'; ?>" role="alert">
      <?php echo htmlspecialchars($message); ?>
    </div>
  <?php } ?>

  <div class="glass-card app-form-card mb-3">
    <h2 class="h5 fw-bold text-success mb-3">Tambah admin baru</h2>
    <form method="post" action="admin_users" class="row g-2">
      <input type="hidden" name="action" value="create_admin">
      <div class="col-md-4">
        <label class="form-label small mb-1">Username</label>
        <input type="text" class="form-control" name="username" required placeholder="Contoh: admin_ops">
      </div>
      <div class="col-md-4">
        <label class="form-label small mb-1">Password</label>
        <input type="password" class="form-control" name="password" required minlength="8" placeholder="Minimal 8 karakter">
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1">Admin Level</label>
        <select class="form-select" name="admin_level" required>
          <option value="staff">Staff</option>
          <option value="super_admin">Super Admin</option>
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary-soft w-100">Tambah</button>
      </div>
    </form>
  </div>

  <div class="glass-card app-form-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <h2 class="h5 fw-bold text-success mb-0">Daftar admin</h2>
      <span class="badge text-bg-light border">Login aktif: <?php echo htmlspecialchars($currentAdminUsername !== '' ? $currentAdminUsername : '-'); ?></span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-success">
          <tr>
            <th style="width:70px;">ID</th>
            <th>Username</th>
            <th style="width:170px;">Admin Level</th>
            <th style="width:130px;">Status</th>
            <th style="width:200px;">Dibuat</th>
            <th style="min-width:300px;">Ubah Password</th>
            <th style="width:160px;">Aksi Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($adminRows)) { ?>
            <?php foreach ($adminRows as $adminRow) { ?>
              <?php
                $adminId = intval($adminRow['id']);
                $isCurrent = $currentAdminId > 0 && $currentAdminId === $adminId;
                $isActive = intval($adminRow['is_active']) === 1;
                $adminLevel = $adminRow['admin_level'] === 'super_admin' ? 'super_admin' : 'staff';
              ?>
              <tr>
                <td><?php echo $adminId; ?></td>
                <td>
                  <?php echo htmlspecialchars($adminRow['username']); ?>
                  <?php if ($isCurrent) { ?>
                    <span class="badge text-bg-info ms-1">Anda</span>
                  <?php } ?>
                </td>
                <td>
                  <form method="post" action="admin_users" class="d-flex gap-2">
                    <input type="hidden" name="action" value="change_level">
                    <input type="hidden" name="user_id" value="<?php echo $adminId; ?>">
                    <select class="form-select form-select-sm" name="new_level">
                      <option value="super_admin" <?php echo $adminLevel === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                      <option value="staff" <?php echo $adminLevel === 'staff' ? 'selected' : ''; ?>>Staff</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Simpan</button>
                  </form>
                </td>
                <td>
                  <span class="badge <?php echo $isActive ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                    <?php echo $isActive ? 'Aktif' : 'Nonaktif'; ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars($adminRow['created_at']); ?></td>
                <td>
                  <form method="post" action="admin_users" class="d-flex gap-2">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="user_id" value="<?php echo $adminId; ?>">
                    <input type="password" class="form-control form-control-sm" name="new_password" required minlength="8" placeholder="Password baru">
                    <button type="submit" class="btn btn-sm btn-outline-success">Simpan</button>
                  </form>
                </td>
                <td>
                  <form method="post" action="admin_users">
                    <input type="hidden" name="action" value="toggle_active">
                    <input type="hidden" name="user_id" value="<?php echo $adminId; ?>">
                    <input type="hidden" name="toggle_to" value="<?php echo $isActive ? '0' : '1'; ?>">
                    <button
                      type="submit"
                      class="btn btn-sm <?php echo $isActive ? 'btn-outline-danger' : 'btn-outline-primary'; ?> w-100"
                      onclick="return confirm('Yakin ingin <?php echo $isActive ? 'menonaktifkan' : 'mengaktifkan'; ?> akun admin ini?');"
                    >
                      <?php echo $isActive ? 'Nonaktifkan' : 'Aktifkan'; ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td colspan="7" class="text-center muted">Belum ada data admin.</td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

