<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === 'arifa_pasker' && $password === 'PusatpasarKerj4') {
        $_SESSION['is_admin'] = true;
        header('Location: admin_scores.php');
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

        <form method="post" action="admin_login.php">
          <div class="mb-3">
            <label class="form-label fw-semibold">Username</label>
            <input type="text" name="username" class="form-control form-control-lg" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control form-control-lg" required>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <a href="index.php" class="btn btn-outline-secondary">Kembali ke beranda</a>
            <button type="submit" class="btn btn-primary-soft">Masuk</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>


