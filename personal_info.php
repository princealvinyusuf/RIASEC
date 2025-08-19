<?php
// Start session and prepare DB before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';

// Ensure required table exists
$createTableSql = "CREATE TABLE IF NOT EXISTS personal_info (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(150) NOT NULL,
    class_level ENUM('10','11','12') NOT NULL,
    school_name VARCHAR(150) NOT NULL,
    extracurricular TEXT NOT NULL,
    organization TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($connection, $createTableSql);

$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $birth_date = isset($_POST['birth_date']) ? trim($_POST['birth_date']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $class_level = isset($_POST['class_level']) ? trim($_POST['class_level']) : '';
    $school_name = isset($_POST['school_name']) ? trim($_POST['school_name']) : '';
    $extracurricular = isset($_POST['extracurricular']) ? trim($_POST['extracurricular']) : '';
    $organization = isset($_POST['organization']) ? trim($_POST['organization']) : '';

    if ($full_name === '') { $errors[] = 'Nama Lengkap wajib diisi.'; }
    if ($birth_date === '') { $errors[] = 'Tanggal Lahir wajib diisi.'; }
    if ($phone === '') { $errors[] = 'No. HP wajib diisi.'; }
    if ($email === '') { $errors[] = 'E-mail wajib diisi.'; }
    if ($class_level === '') { $errors[] = 'Kelas wajib dipilih.'; }
    if ($school_name === '') { $errors[] = 'Nama Sekolah wajib diisi.'; }
    if ($extracurricular === '') { $errors[] = 'Ekstrakurikuler yang diikuti wajib diisi.'; }
    if ($organization === '') { $errors[] = 'Organisasi yang diikuti wajib diisi.'; }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format E-mail tidak valid.';
    }
    if ($class_level !== '' && !in_array($class_level, array('10','11','12'), true)) {
        $errors[] = 'Kelas tidak valid.';
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $connection,
            "INSERT INTO personal_info (full_name, birth_date, phone, email, class_level, school_name, extracurricular, organization)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssssssss', $full_name, $birth_date, $phone, $email, $class_level, $school_name, $extracurricular, $organization);
            $ok = mysqli_stmt_execute($stmt);
            if ($ok) {
                $insertedId = mysqli_insert_id($connection);
                $_SESSION['personal_info_id'] = $insertedId;
                header('Location: test_form.php');
                exit;
            } else {
                $errors[] = 'Gagal menyimpan data. Silakan coba lagi.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = 'Terjadi kesalahan pada server. Silakan coba lagi.';
        }
    }
}
?>
<?php include 'includes/header.php' ?>
<div class="container py-5">
  <div class="row justify-content-center mb-4">
    <div class="col-lg-8">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h1 class="card-title display-6 fw-bold text-success mb-3">Formulir Data Pribadi</h1>
          <p class="mb-3"><a href="index.php" class="text-decoration-none">&larr; Kembali ke Beranda</a></p>
          <?php if (!empty($errors)) { ?>
          <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
              <?php foreach ($errors as $err) { ?>
                <li><?php echo htmlspecialchars($err); ?></li>
              <?php } ?>
            </ul>
          </div>
          <?php } ?>
          <form action="personal_info.php" method="post" novalidate>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="full_name" class="form-control" required value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : '' ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" name="birth_date" class="form-control" required value="<?php echo isset($birth_date) ? htmlspecialchars($birth_date) : '' ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">No. HP</label>
                <input type="text" name="phone" class="form-control" required value="<?php echo isset($phone) ? htmlspecialchars($phone) : '' ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required value="<?php echo isset($email) ? htmlspecialchars($email) : '' ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Kelas</label>
                <select name="class_level" class="form-select" required>
                  <option value="" disabled <?php echo !isset($class_level) || $class_level === '' ? 'selected' : '' ?>>Pilih Kelas</option>
                  <option value="10" <?php echo (isset($class_level) && $class_level==='10') ? 'selected' : '' ?>>10</option>
                  <option value="11" <?php echo (isset($class_level) && $class_level==='11') ? 'selected' : '' ?>>11</option>
                  <option value="12" <?php echo (isset($class_level) && $class_level==='12') ? 'selected' : '' ?>>12</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Nama Sekolah</label>
                <input type="text" name="school_name" class="form-control" required value="<?php echo isset($school_name) ? htmlspecialchars($school_name) : '' ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Ekstrakurikuler Yang Diikuti</label>
                <input type="text" name="extracurricular" class="form-control" required value="<?php echo isset($extracurricular) ? htmlspecialchars($extracurricular) : '' ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Organisasi Yang Diikuti</label>
                <input type="text" name="organization" class="form-control" required value="<?php echo isset($organization) ? htmlspecialchars($organization) : '' ?>">
              </div>
            </div>
            <div class="text-end mt-4">
              <button type="submit" class="btn btn-success btn-lg px-5">Lanjutkan ke Tes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php' ?>


