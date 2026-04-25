<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/zi_core.php';

ensureZiTablesAndSeed($connection);
$questions = getZiStatements($connection);

$respondentName = '';
$respondentEmail = '';
$formError = '';
$submitSuccess = isset($_GET['success']) && $_GET['success'] === '1';
$savedAssessmentId = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_zi'])) {
    $respondentName = trim((string)($_POST['respondent_name'] ?? ''));
    $respondentEmail = trim((string)($_POST['respondent_email'] ?? ''));

    if ($respondentName === '') {
        $formError = 'Nama responden wajib diisi.';
    } elseif ($respondentEmail !== '' && !filter_var($respondentEmail, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Format email tidak valid.';
    } elseif (!hasCompleteZiSubmission($questions, $_POST)) {
        $formError = 'Semua pertanyaan dan persetujuan penyimpanan data wajib diisi.';
    } else {
        $answers = extractZiAnswersFromSource($_POST);
        $summary = calculateZiAssessmentSummary($answers);
        $assessmentId = insertZiAssessment($connection, $respondentName, $respondentEmail, $answers, $summary);
        if ($assessmentId > 0) {
            header('Location: survei_evaluasi?success=1&assessment_id=' . intval($assessmentId));
            exit;
        }
        $formError = 'Terjadi kesalahan saat menyimpan jawaban. Silakan coba lagi.';
    }
}
?>
<?php $pageTitle = 'Survei Evaluasi Zona Integritas'; ?>
<?php include 'includes/header.php'; ?>

<section class="page-wrap">
  <div class="glass-card hero-card mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <p class="kicker mb-1">Survei Evaluasi</p>
        <h1 class="hero-title h2 mb-1">Zona Integritas (ZI)</h1>
        <p class="hero-subtitle mb-0">
          Bantu kami meningkatkan kualitas layanan dengan menilai pengalaman Anda terhadap implementasi Zona Integritas.
        </p>
      </div>
      <a href="index" class="btn btn-outline-soft">&larr; Kembali ke beranda</a>
    </div>
  </div>

  <?php if ($submitSuccess) { ?>
    <div class="alert alert-success" role="alert">
      Terima kasih. Jawaban Survei Evaluasi Zona Integritas berhasil disimpan.
      <?php if ($savedAssessmentId > 0) { ?>
        <span class="small d-block mt-1">ID survei: <?php echo $savedAssessmentId; ?></span>
      <?php } ?>
    </div>
  <?php } ?>

  <div class="glass-card app-form-card">
    <?php if ($formError !== '') { ?>
      <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($formError); ?>
      </div>
    <?php } ?>

    <?php if (empty($questions)) { ?>
      <div class="alert alert-warning mb-0">
        Pertanyaan Survei ZI belum tersedia. Silakan hubungi admin untuk mengisi bank pertanyaan.
      </div>
    <?php } else { ?>
      <form method="post" action="survei_evaluasi">
        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <label class="form-label">Nama Responden</label>
            <input
              type="text"
              class="form-control"
              name="respondent_name"
              value="<?php echo htmlspecialchars($respondentName); ?>"
              maxlength="160"
              required
              placeholder="Isi nama lengkap Anda"
            >
          </div>
          <div class="col-md-6">
            <label class="form-label">Email (opsional)</label>
            <input
              type="email"
              class="form-control"
              name="respondent_email"
              value="<?php echo htmlspecialchars($respondentEmail); ?>"
              maxlength="190"
              placeholder="nama@email.com"
            >
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-success">
              <tr>
                <th style="min-width:340px;">Pertanyaan Layanan Makeup</th>
                <th class="text-center">1</th>
                <th class="text-center">2</th>
                <th class="text-center">3</th>
                <th class="text-center">4</th>
                <th class="text-center">5</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($questions as $index => $question) { ?>
                <?php $fieldName = 'ZI_' . intval($question['statement_id']); ?>
                <tr>
                  <td>
                    <div class="fw-semibold"><?php echo ($index + 1) . '. ' . htmlspecialchars($question['statement_content']); ?></div>
                  </td>
                  <?php for ($score = 1; $score <= 5; $score++) { ?>
                    <td class="text-center">
                      <input
                        class="form-check-input"
                        type="radio"
                        name="<?php echo htmlspecialchars($fieldName); ?>"
                        value="<?php echo $score; ?>"
                        required
                        <?php echo (isset($_POST[$fieldName]) && intval($_POST[$fieldName]) === $score) ? 'checked' : ''; ?>
                      >
                    </td>
                  <?php } ?>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>

        <p class="small muted mb-2">
          Skala penilaian: 1 = sangat tidak setuju, 2 = tidak setuju, 3 = netral, 4 = setuju, 5 = sangat setuju.
        </p>

        <div class="form-check mb-3">
          <input
            class="form-check-input"
            type="checkbox"
            name="can_save_data"
            value="true"
            id="ziSaveData"
            required
            <?php echo (isset($_POST['can_save_data']) && $_POST['can_save_data'] === 'true') ? 'checked' : ''; ?>
          >
          <label class="form-check-label" for="ziSaveData">
            Saya menyetujui penyimpanan jawaban untuk evaluasi peningkatan kualitas layanan.
          </label>
        </div>

        <div class="d-flex gap-2 flex-wrap">
          <button type="submit" name="submit_zi" class="btn btn-primary-soft">Kirim Survei</button>
          <a href="index" class="btn btn-outline-secondary">Batal</a>
        </div>
      </form>
    <?php } ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
