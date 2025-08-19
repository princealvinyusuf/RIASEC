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
<?php include 'includes/header.php' ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h3 class="fw-bold text-success mb-3 text-center">Admin Login</h3>
          <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
          <?php } ?>
          <form method="post" action="admin_login.php">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-success">Masuk</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php' ?>


