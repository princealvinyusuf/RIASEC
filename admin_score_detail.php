<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
  header('Location: admin_login');
  exit;
}
include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/riasec_recommendations.php';
?>
<?php
$scoreId = isset($_GET['score_id']) ? intval($_GET['score_id']) : 0;
if ($scoreId <= 0) {
    $pageTitle = 'Detail Tes - Admin';
    include 'includes/header.php';
    echo '<section class="page-wrap"><div class="alert alert-danger">Parameter tidak valid.</div></section>';
    include 'includes/footer.php';
    exit;
}

// Fetch header info (person + aggregate result)
$headerSql = "SELECT pts.id AS score_id, pts.result,
                     pts.realistic, pts.investigative, pts.artistic,
                     pts.social, pts.enterprising, pts.conventional,
                     pts.created_at,
                     pi.full_name, pi.email, pi.class_level, pi.school_name,
                     pi.birth_date, pi.phone, pi.extracurricular, pi.organization
              FROM personality_test_scores pts
              LEFT JOIN personal_info pi ON pi.id = pts.personal_info_id
              WHERE pts.id = $scoreId";
$headerRes = mysqli_query($connection, $headerSql);
$header = $headerRes ? mysqli_fetch_assoc($headerRes) : null;

// Fetch detailed answers joined with statements
$detailSql = "SELECT ta.statement_id, ta.statement_category, ta.answer, s.statement_content
              FROM test_answers ta
              LEFT JOIN statements s 
                ON s.statement_id = ta.statement_id AND s.statement_category = ta.statement_category
              WHERE ta.score_id = $scoreId
              ORDER BY ta.statement_category, ta.statement_id";
$detailRes = mysqli_query($connection, $detailSql);

$resultPersonality = $header['result'] ?? '';
$scorePercentageList = array(
    'R' => floatval($header['realistic'] ?? 0),
    'I' => floatval($header['investigative'] ?? 0),
    'A' => floatval($header['artistic'] ?? 0),
    'S' => floatval($header['social'] ?? 0),
    'E' => floatval($header['enterprising'] ?? 0),
    'C' => floatval($header['conventional'] ?? 0),
);
$recommendationPayload = getRiasecRecommendationPayload($resultPersonality, $scorePercentageList);
$topCodes = $recommendationPayload['top_codes'];
$careerRecommendations = $recommendationPayload['career_recommendations'];
$trainingRecommendations = $recommendationPayload['training_recommendations'];
$trainingTierSummary = $recommendationPayload['training_tier_summary'];
$jobZones = $recommendationPayload['job_zones'];
?>
<?php $pageTitle = 'Detail Tes - Admin'; ?>
<?php include 'includes/header.php'; ?>

<section class="page-wrap">
  <div class="glass-card hero-card mb-3">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <p class="kicker mb-1">Detail Hasil Tes</p>
        <h1 class="hero-title h2 mb-1">Informasi peserta & jawaban</h1>
        <p class="hero-subtitle mb-0">Lihat profil skor RIASEC dan jawaban per butir untuk sesi tes ini.</p>
        <div class="mt-2">
          <a href="generate_pdf?score_id=<?php echo intval($scoreId); ?>" class="btn btn-primary-soft btn-sm" target="_blank">Unduh Laporan</a>
        </div>
      </div>
      <a href="admin_scores" class="btn btn-outline-soft">&larr; Kembali ke daftar</a>
    </div>
  </div>

  <div class="glass-card app-form-card mb-3">
    <?php if ($header) { ?>
      <div class="results-grid mb-3">
        <div class="interest-pill"><div class="muted small">Nama</div><strong><?php echo htmlspecialchars($header['full_name'] ?? '-'); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Email</div><strong><?php echo htmlspecialchars($header['email'] ?? '-'); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Kelas</div><strong><?php echo htmlspecialchars($header['class_level'] ?? '-'); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Sekolah</div><strong><?php echo htmlspecialchars($header['school_name'] ?? '-'); ?></strong></div>
        <div class="interest-pill"><div class="muted small">No. HP</div><strong><?php echo htmlspecialchars($header['phone'] ?? '-'); ?></strong></div>
        <div class="interest-pill"><div class="muted small">Tanggal Tes</div><strong><?php echo htmlspecialchars($header['created_at'] ?? '-'); ?></strong></div>
      </div>

      <div class="mb-3">
        <span class="badge text-bg-success fs-6">Kode Hasil: <?php echo htmlspecialchars($header['result']); ?></span>
      </div>

      <ul class="score-list">
        <li class="score-item">
          <div class="score-item-head"><span>Realistic (R)</span><span><?php echo floatval($header['realistic']); ?>%</span></div>
          <div class="score-track"><div class="score-fill" style="width: <?php echo floatval($header['realistic']); ?>%;"></div></div>
        </li>
        <li class="score-item">
          <div class="score-item-head"><span>Investigative (I)</span><span><?php echo floatval($header['investigative']); ?>%</span></div>
          <div class="score-track"><div class="score-fill" style="width: <?php echo floatval($header['investigative']); ?>%;"></div></div>
        </li>
        <li class="score-item">
          <div class="score-item-head"><span>Artistic (A)</span><span><?php echo floatval($header['artistic']); ?>%</span></div>
          <div class="score-track"><div class="score-fill" style="width: <?php echo floatval($header['artistic']); ?>%;"></div></div>
        </li>
        <li class="score-item">
          <div class="score-item-head"><span>Social (S)</span><span><?php echo floatval($header['social']); ?>%</span></div>
          <div class="score-track"><div class="score-fill" style="width: <?php echo floatval($header['social']); ?>%;"></div></div>
        </li>
        <li class="score-item">
          <div class="score-item-head"><span>Enterprising (E)</span><span><?php echo floatval($header['enterprising']); ?>%</span></div>
          <div class="score-track"><div class="score-fill" style="width: <?php echo floatval($header['enterprising']); ?>%;"></div></div>
        </li>
        <li class="score-item">
          <div class="score-item-head"><span>Conventional (C)</span><span><?php echo floatval($header['conventional']); ?>%</span></div>
          <div class="score-track"><div class="score-fill" style="width: <?php echo floatval($header['conventional']); ?>%;"></div></div>
        </li>
      </ul>

      <div class="glass-card app-form-card mt-3 mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
          <h2 class="h5 fw-bold text-success mb-0">Rekomendasi karier eksplorasi</h2>
          <span class="muted">Berdasarkan kombinasi profil <?php echo htmlspecialchars($resultPersonality); ?></span>
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
          <span class="muted">Sumber data: SkillHub Kemnaker (profil <?php echo htmlspecialchars($resultPersonality); ?>)</span>
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

    <?php } else { ?>
      <div class="alert alert-warning mb-0">Data tidak ditemukan.</div>
    <?php } ?>
  </div>

  <div class="glass-card app-form-card">
    <h2 class="h5 fw-bold text-success mb-3">Jawaban detail per butir</h2>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-success">
          <tr>
            <th>Kategori</th>
            <th>ID</th>
            <th>Pernyataan</th>
            <th>Skor (1-5)</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($detailRes && mysqli_num_rows($detailRes) > 0) { ?>
            <?php while($d = mysqli_fetch_assoc($detailRes)) { ?>
              <tr>
                <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($d['statement_category']); ?></span></td>
                <td><?php echo intval($d['statement_id']); ?></td>
                <td><?php echo htmlspecialchars($d['statement_content'] ?? '-'); ?></td>
                <td><?php echo intval($d['answer']); ?></td>
              </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td colspan="4" class="text-center muted">Tidak ada jawaban yang tersimpan.</td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>


