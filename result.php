<?php include 'includes/header.php' ?>
<?php include 'util_functions.php' ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <?php getPersonalityTestResults(); ?>
          <div class="alert alert-success text-center mb-4" role="alert">
            <h4 class="alert-heading">Hasil Tes RIASEC Anda</h4>
            <p class="mb-0">Berdasarkan hasil tes, tipe kepribadian Anda adalah <b><?php echo $result_personality ?></b></p>
          </div>
          <div class="mb-4">
            <div id="chartContainer" style="height: 300px; width: 100%;"></div>
          </div>
          <div class="mb-4">
            <div class="card bg-light border-0">
              <div class="card-body p-3">
                <h6 class="fw-bold mb-2">Keterangan Kode RIASEC:</h6>
                <ul class="mb-0 ps-3">
                  <li><b>R</b> = Realistic</li>
                  <li><b>I</b> = Investigative</li>
                  <li><b>A</b> = Artistic</li>
                  <li><b>S</b> = Social</li>
                  <li><b>E</b> = Enterprising</li>
                  <li><b>C</b> = Conventional</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="text-center">
            <a href="test_form.php" class="btn btn-success btn-lg px-5">Coba Tes Lagi</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php' ?>