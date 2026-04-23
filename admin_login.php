<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

function ensureAdminUsersTable($connection) {
    if (!$connection) {
        return;
    }

    $createTableSql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($connection, $createTableSql);

    $defaultUsername = 'arifa_pasker';
    $checkStmt = mysqli_prepare($connection, "SELECT id FROM admin_users WHERE username = ? LIMIT 1");
    if (!$checkStmt) {
        return;
    }

    mysqli_stmt_bind_param($checkStmt, 's', $defaultUsername);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);
    $exists = mysqli_stmt_num_rows($checkStmt) > 0;
    mysqli_stmt_close($checkStmt);

    if (!$exists) {
        $defaultPasswordHash = password_hash('PusatpasarKerj4', PASSWORD_DEFAULT);
        $insertStmt = mysqli_prepare($connection, "INSERT INTO admin_users (username, password_hash, is_active) VALUES (?, ?, 1)");
        if ($insertStmt) {
            mysqli_stmt_bind_param($insertStmt, 'ss', $defaultUsername, $defaultPasswordHash);
            mysqli_stmt_execute($insertStmt);
            mysqli_stmt_close($insertStmt);
        }
    }
}

ensureAdminUsersTable($connection);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $isValidAdmin = false;
    if ($connection) {
        $stmt = mysqli_prepare(
            $connection,
            "SELECT password_hash FROM admin_users WHERE username = ? AND is_active = 1 LIMIT 1"
        );
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $passwordHash);
            if (mysqli_stmt_fetch($stmt) && password_verify($password, $passwordHash)) {
                $isValidAdmin = true;
            }
            mysqli_stmt_close($stmt);
        }
    }

    if ($isValidAdmin) {
        $_SESSION['is_admin'] = true;
        header('Location: admin_scores');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<?php $pageTitle = 'Admin Login - RIASEC'; ?>
<?php include 'includes/header.php'; ?>

<section class="page-wrap">
  <div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
      <div class="glass-card hero-card">
        <p class="kicker mb-1">Panel Admin</p>
        <h1 class="hero-title h2 mb-2">Masuk ke dashboard asesmen</h1>
        <p class="hero-subtitle mb-3">Kelola data peserta, hasil profil, dan detail jawaban tes RIASEC.</p>

        <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="post" action="admin_login">
          <div class="mb-3">
            <label class="form-label fw-semibold">Username</label>
            <input type="text" name="username" class="form-control form-control-lg" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control form-control-lg" required>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <a href="index" class="btn btn-outline-secondary">Kembali ke beranda</a>
            <button type="submit" class="btn btn-primary-soft">Masuk</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>


