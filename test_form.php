<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['personal_info_id'])) {
  header('Location: personal_info.php');
  exit;
}
?>
<?php include 'includes/header.php' ?>
<div class="container py-5">
  <div class="row justify-content-center mb-4">
    <div class="col-lg-10">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h1 class="card-title display-6 fw-bold text-success mb-3">Isi Formulir Berikut</h1>
          <?php if(isset($_GET['message']) && $_GET['message']=='T'){?>
          <div class="alert alert-warning" role="alert">
            Anda harus mengisi minimal 5-6 pernyataan untuk mendapatkan hasil.
          </div>
          <?php } elseif(isset($_GET['message']) && $_GET['message']=='REQ'){ ?>
          <div class="alert alert-danger" role="alert">
            Semua pernyataan dan pilihan persetujuan penelitian wajib diisi sebelum melihat hasil.
          </div>
          <?php } ?>
          <p><a href="index.php" class="text-decoration-none">&larr; Kembali ke Beranda</a></p>
          <form action="result.php" method="post">
            <div class="table-responsive">
              <table class="table table-bordered align-middle">
                <thead class="table-success">
                  <tr>
                    <th>Pernyataan</th>
                    <th>Tidak Suka</th>
                    <th>Kurang Suka</th>
                    <th>Netral</th>
                    <th>Agak Suka</th>
                    <th>Suka</th>
                  </tr>
                </thead>
                <tbody>
<?php 
$query = "SELECT * FROM statements ORDER BY RAND()";
$statement_select_query = mysqli_query($connection,$query);
while($row=mysqli_fetch_assoc($statement_select_query )){
    $statement_id = $row['statement_id'];
    $statement_content = $row['statement_content'];
    $statement_category = $row['statement_category'];
?>
                  <tr>
                    <td><?php echo $statement_content?></td>
                    <?php for($i=1;$i<=5;$i++){ ?>
                    <td>
                      <div class="form-check d-flex justify-content-center">
                        <input class="form-check-input" style="width:1.5em;height:1.5em;" type="radio" name="<?php echo $statement_category.$statement_id ?>" value="<?php echo $i ?>" <?php echo $i===1 ? 'required' : '' ?>>
                      </div>
                    </td>
                    <?php } ?>
                  </tr>
<?php } ?>
                </tbody>
              </table>
            </div>
            <div class="mb-3 mt-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="can_save_data" value="true" id="saveDataYes" required>
                <label class="form-check-label" for="saveDataYes">Ya, saya setuju jawaban saya disimpan secara anonim untuk tujuan penelitian.</label>
              </div>
            </div>
            <div class="text-end">
              <button type="submit" name="submit" class="btn btn-success btn-lg px-5" id="submitBtn" disabled>Lihat Hasil</button>
            </div>
          </form>
          <script type="text/javascript">
          document.addEventListener('DOMContentLoaded', function() {
            var form = document.querySelector('form[action="result.php"]');
            if (!form) { return; }
            var submitBtn = document.getElementById('submitBtn');
            function getStatementGroupNames() {
              var radios = form.querySelectorAll('input[type="radio"]');
              var names = {};
              radios.forEach(function(r){
                if (/^[RIASEC]\d+$/.test(r.name)) { names[r.name] = true; }
              });
              return Object.keys(names);
            }
            function isGroupChecked(name) {
              return !!form.querySelector('input[name="'+name+'"]:checked');
            }
            function checkCompleteness() {
              var names = getStatementGroupNames();
              var allStatementsAnswered = names.every(function(n){ return isGroupChecked(n); });
              var consentAnswered = !!form.querySelector('input[name="can_save_data"]:checked');
              submitBtn.disabled = !(allStatementsAnswered && consentAnswered);
            }
            form.addEventListener('change', function(e){ if (e.target && (e.target.matches('input[type="radio"]') || e.target.matches('input[type="checkbox"]'))) { checkCompleteness(); }});
            checkCompleteness();
          });
          </script>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php' ?>