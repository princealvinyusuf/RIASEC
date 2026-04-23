<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
    header('Location: admin_login');
    exit;
}

include 'includes/db.php';
include_once 'includes/admin_auth.php';

ensureAdminUsersTable($connection);

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

        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
            redirectAdminUsers('error', 'Username wajib 3-50 karakter (huruf, angka, titik, underscore, atau strip).');
        }
        if (strlen($password) < 8) {
            redirectAdminUsers('error', 'Password minimal 8 karakter.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = mysqli_prepare($connection, "INSERT INTO admin_users (username, password_hash, is_active) VALUES (?, ?, 1)");
        if (!$insertStmt) {
            redirectAdminUsers('error', 'Gagal menyiapkan data admin baru.');
        }
        mysqli_stmt_bind_param($insertStmt, 'ss', $username, $passwordHash);
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

    if ($action === 'toggle_active') {
        $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $toggleTo = isset($_POST['toggle_to']) ? intval($_POST['toggle_to']) : -1;
        $currentAdminId = isset($_SESSION['admin_user_id']) ? intval($_SESSION['admin_user_id']) : 0;

        if ($userId <= 0 || ($toggleTo !== 0 && $toggleTo !== 1)) {
            redirectAdminUsers('error', 'Permintaan ubah status tidak valid.');
        }

        $targetStmt = mysqli_prepare($connection, "SELECT username, is_active FROM admin_users WHERE id = ? LIMIT 1");
        if (!$targetStmt) {
            redirectAdminUsers('error', 'Gagal membaca data admin.');
        }
        mysqli_stmt_bind_param($targetStmt, 'i', $userId);
        mysqli_stmt_execute($targetStmt);
        mysqli_stmt_bind_result($targetStmt, $targetUsername, $currentStatus);
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
$currentAdminId = isset($_SESSION['admin_user_id']) ? intval($_SESSION['admin_user_id']) : 0;
$currentAdminUsername = isset($_SESSION['admin_username']) ? (string)$_SESSION['admin_username'] : '';

$adminRows = array();
if ($connection) {
    $adminResult = mysqli_query($connection, "SELECT id, username, is_active, created_at FROM admin_users ORDER BY id ASC");
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
      <div class="col-md-5">
        <label class="form-label small mb-1">Username</label>
        <input type="text" class="form-control" name="username" required placeholder="Contoh: admin_ops">
      </div>
      <div class="col-md-5">
        <label class="form-label small mb-1">Password</label>
        <input type="password" class="form-control" name="password" required minlength="8" placeholder="Minimal 8 karakter">
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
              <td colspan="6" class="text-center muted">Belum ada data admin.</td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

