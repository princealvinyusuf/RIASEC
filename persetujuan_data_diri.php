<?php
$pageTitle = 'Persetujuan Data Diri - RIASEC';
$accessGranted = $_SERVER['REQUEST_METHOD'] === 'POST';
include 'includes/header.php';
?>

<section class="page-wrap">
  <div class="glass-card app-form-card">
    <div class="form-header mb-4">
      <h1 class="fw-bold text-success">Persetujuan akses data diri</h1>
      <p>Tinjau data diri berikut, lalu izinkan akses untuk melanjutkan asesmen minat karier.</p>
    </div>

    <?php if ($accessGranted) { ?>
      <div class="alert alert-success" role="alert">
        Akses data diri berhasil diizinkan.
      </div>
    <?php } ?>

    <form action="persetujuan_data_diri" method="post">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="fullName">Nama Lengkap</label>
          <input type="text" id="fullName" name="full_name" class="form-control bg-light" value="Budi Santoso" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="birthDate">Tanggal Lahir</label>
          <input type="date" id="birthDate" name="birth_date" class="form-control bg-light" value="2007-08-17" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="nik">NIK</label>
          <input type="text" id="nik" name="nik" class="form-control bg-light" value="3174011708070001" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="phone">No. HP</label>
          <input type="tel" id="phone" name="phone" class="form-control bg-light" value="081234567890" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="email">E-mail</label>
          <input type="email" id="email" name="email" class="form-control bg-light" value="budi.santoso@example.com" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="schoolName">Sekolah / Institusi / Penyelenggara Pendidikan</label>
          <input type="text" id="schoolName" name="school_name" class="form-control bg-light" value="SMA Negeri 1 Jakarta" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="province">Provinsi</label>
          <input type="text" id="province" name="province" class="form-control bg-light" value="DKI Jakarta" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="city">Kabupaten / Kota</label>
          <input type="text" id="city" name="city" class="form-control bg-light" value="Kota Jakarta Selatan" readonly>
        </div>
      </div>

      <div class="question-nav mt-4">
        <a href="index" class="btn btn-outline-secondary">Batalkan</a>
        <button type="submit" class="btn btn-primary-soft">Izinkan Akses</button>
      </div>
    </form>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
