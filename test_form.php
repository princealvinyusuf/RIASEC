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
                        <input class="form-check-input" style="width:1.5em;height:1.5em;" type="radio" name="<?php echo $statement_category.$statement_id ?>" value="<?php echo $i ?>">
                      </div>
                    </td>
                    <?php } ?>
                  </tr>
<?php } ?>
                </tbody>
              </table>
            </div>
            <div class="mb-3 mt-4">
              <label class="form-label">Apakah Anda mengizinkan data ini digunakan untuk tujuan penelitian?</label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="can_save_data" value="true" id="saveDataYes">
                <label class="form-check-label" for="saveDataYes">Ya</label>
              </div>
            </div>
            <div class="text-end">
              <button type="submit" name="submit" class="btn btn-success btn-lg px-5">Lihat Hasil</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php' ?>