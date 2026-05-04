<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/includes/db.php';
include 'util_functions.php';
include_once __DIR__ . '/includes/riasec_recommendations.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    getPersonalityTestResults();
} elseif (!isset($_SESSION['result_personality']) || !isset($_SESSION['score_percentage_list'])) {
    header('Location: test_form');
    exit;
} else {
    $result_personality = $_SESSION['result_personality'];
    $scorePercentageList = $_SESSION['score_percentage_list'];
}

$riasecInfo = array(
    'R' => array('name' => 'Realistic', 'desc' => 'Menyukai aktivitas praktis, alat, mesin, perbaikan, dan kerja lapangan.'),
    'I' => array('name' => 'Investigative', 'desc' => 'Suka menganalisis, riset, observasi, pemecahan masalah, dan logika.'),
    'A' => array('name' => 'Artistic', 'desc' => 'Suka mengekspresikan ide lewat desain, tulisan, seni, musik, atau kreasi.'),
    'S' => array('name' => 'Social', 'desc' => 'Suka membantu, mendampingi, mengajar, dan berinteraksi dengan orang lain.'),
    'E' => array('name' => 'Enterprising', 'desc' => 'Suka memimpin, memengaruhi, bernegosiasi, dan mengembangkan peluang.'),
    'C' => array('name' => 'Conventional', 'desc' => 'Suka ketertiban, data, administrasi, struktur, dan detail yang konsisten.')
);

$topCodes = str_split(substr($result_personality, 0, 3));
if (count($topCodes) < 3) {
    $sortedFallback = $scorePercentageList;
    arsort($sortedFallback);
    $topCodes = array_slice(array_keys($sortedFallback), 0, 3);
}

$sortedScores = $scorePercentageList;
arsort($sortedScores);

$recommendationPayload = getRiasecRecommendationPayload($result_personality, $scorePercentageList);
$topCodes = $recommendationPayload['top_codes'];
$careerRecommendations = $recommendationPayload['career_recommendations'];
$trainingRecommendations = $recommendationPayload['training_recommendations'];
$trainingTierSummary = $recommendationPayload['training_tier_summary'];
$jobZones = $recommendationPayload['job_zones'];
?>

<?php $pageTitle = 'Hasil Profil RIASEC'; ?>
<?php include 'includes/header.php'; ?>

<section class="page-wrap">
  <div class="glass-card hero-card mb-3">
    <p class="kicker mb-1">Hasil profil minatmu</p>
    <h1 class="hero-title mb-2">Kode RIASEC: <?php echo htmlspecialchars($result_personality); ?></h1>
    <p class="hero-subtitle mb-0">
      Tiga minat dominan kamu: <strong><?php echo htmlspecialchars(implode(', ', $topCodes)); ?></strong>.
      Gunakan hasil ini untuk mengeksplorasi jurusan, kegiatan pengembangan diri, dan opsi karier.
    </p>
  </div>

  <div class="results-grid mb-3">
    <?php foreach ($topCodes as $code) { ?>
      <div class="interest-pill">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <strong><?php echo htmlspecialchars($code . ' - ' . $riasecInfo[$code]['name']); ?></strong>
          <span class="badge text-bg-success"><?php echo floatval($scorePercentageList[$code]); ?>%</span>
        </div>
        <div class="muted"><?php echo htmlspecialchars($riasecInfo[$code]['desc']); ?></div>
      </div>
    <?php } ?>
  </div>

  <div class="glass-card app-form-card mb-3">
    <h2 class="h5 fw-bold text-success mb-3">Distribusi skor minat</h2>
    <ul class="score-list">
      <?php foreach ($sortedScores as $code => $score) { ?>
        <li class="score-item">
          <div class="score-item-head">
            <span><?php echo htmlspecialchars($code . ' - ' . $riasecInfo[$code]['name']); ?></span>
            <span><?php echo floatval($score); ?>%</span>
          </div>
          <div class="score-track">
            <div class="score-fill" style="width: <?php echo floatval($score); ?>%;"></div>
          </div>
        </li>
      <?php } ?>
    </ul>
  </div>

  <div class="glass-card app-form-card mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <h2 class="h5 fw-bold text-success mb-0">Rekomendasi karier eksplorasi</h2>
      <span class="muted">Berdasarkan kombinasi profil <?php echo htmlspecialchars($result_personality); ?></span>
    </div>
    <div class="career-grid">
      <?php foreach ($careerRecommendations as $career) { ?>
        <article class="career-card">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <strong><?php echo htmlspecialchars($career['title']); ?></strong>
            <span class="badge-zone">Job Zone <?php echo intval($career['zone']); ?></span>
          </div>
          <div class="small mb-1">Tag minat: <?php echo htmlspecialchars(implode('-', $career['tags'])); ?></div>
          <div class="muted small"><?php echo htmlspecialchars($career['why']); ?></div>
          <div class="mt-2 d-flex gap-2 flex-wrap">
            <a
              class="btn btn-sm btn-outline-success"
              href="<?php echo htmlspecialchars(buildKarirhubSearchUrl(getPrimaryCareerKeyword($career))); ?>"
              target="_blank"
              rel="noopener noreferrer"
            >
              Lihat Pekerjaan
            </a>
            <a
              class="btn btn-sm btn-outline-secondary"
              href="<?php echo htmlspecialchars(buildKarirhubSearchUrl(getRelatedCareerKeyword($career))); ?>"
              target="_blank"
              rel="noopener noreferrer"
            >
              Lihat Lowongan Serupa
            </a>
          </div>
        </article>
      <?php } ?>
    </div>
  </div>

  <div class="glass-card app-form-card mb-3">
    <h2 class="h5 fw-bold text-success mb-3">Panduan Job Zone</h2>
    <div class="career-grid">
      <?php foreach ($jobZones as $zone) { ?>
        <article class="career-card">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <strong>Zone <?php echo intval($zone['zone']); ?></strong>
            <span class="badge-zone"><?php echo htmlspecialchars($zone['label']); ?></span>
          </div>
          <div class="muted small"><?php echo htmlspecialchars($zone['desc']); ?></div>
        </article>
      <?php } ?>
    </div>
  </div>

  <div class="glass-card app-form-card mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <h2 class="h5 fw-bold text-success mb-0">Rekomendasi Pelatihan</h2>
      <span class="muted">Sumber data: SkillHub Kemnaker (berdasarkan profil <?php echo htmlspecialchars($result_personality); ?>)</span>
    </div>
    <div class="d-flex gap-2 flex-wrap mb-3">
      <span class="badge-tier badge-tier-top"><?php echo intval($trainingTierSummary['top']); ?> Sangat Direkomendasikan</span>
      <span class="badge-tier badge-tier-good"><?php echo intval($trainingTierSummary['good']); ?> Cocok</span>
      <span class="badge-tier badge-tier-alt"><?php echo intval($trainingTierSummary['alt']); ?> Eksplorasi Tambahan</span>
    </div>
    <div class="career-grid">
      <?php if (!empty($trainingRecommendations)) { ?>
        <?php foreach ($trainingRecommendations as $training) { ?>
          <article class="career-card <?php echo htmlspecialchars($training['tier']['card_class']); ?>">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-1 flex-wrap">
              <strong><?php echo htmlspecialchars($training['title']); ?></strong>
              <div class="d-flex gap-2 flex-wrap">
                <span class="badge-tier <?php echo htmlspecialchars($training['tier']['class']); ?>"><?php echo htmlspecialchars($training['tier']['label']); ?></span>
                <span class="badge-zone"><?php echo htmlspecialchars($training['delivery']); ?></span>
              </div>
            </div>
            <div class="small mb-1"><strong>Level:</strong> <?php echo htmlspecialchars($training['level']); ?></div>
            <div class="small mb-1"><strong>Kecocokan:</strong> <?php echo htmlspecialchars(!empty($training['matched_tags']) ? implode('-', $training['matched_tags']) : '-'); ?></div>
            <div class="muted small"><?php echo htmlspecialchars($training['focus']); ?></div>
            <div class="small mt-1" style="color:#0a6d31;"><strong>Alasan rekomendasi:</strong> <?php echo htmlspecialchars($training['reason']); ?></div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
              <span class="keyword-chip">Kata kunci utama: <?php echo htmlspecialchars(getPrimaryTrainingKeyword($training)); ?></span>
              <span class="keyword-chip">Alternatif: <?php echo htmlspecialchars(getRelatedTrainingKeyword($training)); ?></span>
            </div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
              <a
                class="btn btn-sm btn-outline-success"
                href="<?php echo htmlspecialchars(buildSkillhubSearchUrl(getPrimaryTrainingKeyword($training))); ?>"
                target="_blank"
                rel="noopener noreferrer"
              >
                Cari Pelatihan
              </a>
              <a
                class="btn btn-sm btn-outline-secondary"
                href="<?php echo htmlspecialchars(buildSkillhubSearchUrl(getRelatedTrainingKeyword($training))); ?>"
                target="_blank"
                rel="noopener noreferrer"
              >
                Pelatihan Serupa
              </a>
            </div>
          </article>
        <?php } ?>
      <?php } else { ?>
        <div class="muted">Belum ada pelatihan SkillHub yang sangat spesifik untuk kombinasi profil ini.</div>
      <?php } ?>
    </div>
  </div>

  <div class="glass-card app-form-card mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <h2 class="h5 fw-bold text-success mb-0">Program Terkait PaskerID</h2>
      <span class="muted">Integrasi fase growth</span>
    </div>
    <div class="career-grid">
      <a
        href="https://paskerid.kemnaker.go.id/career-boostday"
        target="_blank"
        rel="noopener noreferrer"
        class="text-decoration-none text-reset"
      >
        <article class="career-card">
          <div class="d-flex justify-content-between align-items-center mb-1 gap-2 flex-wrap">
            <strong>Career Boost Day</strong>
            <span class="badge-zone">Career</span>
          </div>
          <div class="muted small">Ikut sesi konsultasi karier dan penguatan profil kerja.</div>
        </article>
      </a>
    </div>
  </div>

  <div class="d-flex gap-2 flex-wrap">
    <a href="test_form" class="btn btn-outline-soft">Ulangi asesmen</a>
    <a href="generate_pdf" class="btn btn-primary-soft" target="_blank">Unduh laporan</a>
    <a href="index" class="btn btn-outline-secondary">Kembali ke beranda</a>
  </div>
</section>

<div class="modal fade" id="surveiEvaluasiModal" tabindex="-1" aria-labelledby="surveiEvaluasiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title h5 mb-0" id="surveiEvaluasiModalLabel">Survei Evaluasi</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        Mohon bantu kami dengan mengisi Survei Evaluasi untuk peningkatan layanan.
      </div>
      <div class="modal-footer">
        <a href="survei_evaluasi" class="btn btn-primary-soft">Ya, Isi Suvei Evaluasi</a>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tidak</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof bootstrap === 'undefined') {
    return;
  }

  var modalElement = document.getElementById('surveiEvaluasiModal');
  if (!modalElement) {
    return;
  }

  var surveiModal = new bootstrap.Modal(modalElement);
  setTimeout(function () {
    surveiModal.show();
  }, 5000);
});
</script>

<?php include 'includes/footer.php'; ?>