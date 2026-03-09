<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/includes/db.php';
include 'util_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    getPersonalityTestResults();
} elseif (!isset($_SESSION['result_personality']) || !isset($_SESSION['score_percentage_list'])) {
    header('Location: test_form.php');
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

$jobZones = array(
    array('zone' => 1, 'label' => 'Persiapan minimal', 'desc' => 'Pelatihan singkat atau pengalaman kerja awal.'),
    array('zone' => 2, 'label' => 'Persiapan dasar', 'desc' => 'Butuh pelatihan beberapa bulan hingga 1 tahun.'),
    array('zone' => 3, 'label' => 'Persiapan menengah', 'desc' => 'Biasanya perlu pendidikan vokasi/D3 atau pengalaman teknis.'),
    array('zone' => 4, 'label' => 'Persiapan tinggi', 'desc' => 'Umumnya setara S1 dan pengalaman kerja lebih mendalam.'),
    array('zone' => 5, 'label' => 'Persiapan sangat tinggi', 'desc' => 'Sering membutuhkan pendidikan lanjut dan keahlian spesialis.')
);

$careerCatalog = array(
    array('title' => 'Analis Data', 'keyword' => 'analis data', 'related_keywords' => array('data analyst', 'business intelligence'), 'tags' => array('I', 'C'), 'zone' => 4, 'why' => 'Mengolah data, membuat insight, dan membantu pengambilan keputusan.'),
    array('title' => 'UI/UX Designer', 'keyword' => 'ui ux designer', 'related_keywords' => array('desain produk', 'desainer grafis'), 'tags' => array('A', 'I'), 'zone' => 4, 'why' => 'Merancang pengalaman digital yang estetis dan mudah digunakan.'),
    array('title' => 'Akuntan', 'keyword' => 'akuntan', 'related_keywords' => array('staff accounting', 'keuangan'), 'tags' => array('C', 'I'), 'zone' => 4, 'why' => 'Mengelola laporan keuangan secara teliti dan terstruktur.'),
    array('title' => 'Digital Marketing Specialist', 'keyword' => 'digital marketing', 'related_keywords' => array('marketing', 'social media specialist'), 'tags' => array('E', 'A'), 'zone' => 3, 'why' => 'Menggabungkan strategi promosi, kreativitas konten, dan analisis.'),
    array('title' => 'Psikolog/Konselor', 'keyword' => 'psikolog konselor', 'related_keywords' => array('konselor', 'psikolog'), 'tags' => array('S', 'I'), 'zone' => 5, 'why' => 'Mendampingi individu dalam pengembangan diri dan pemecahan masalah.'),
    array('title' => 'Guru/Pengajar', 'keyword' => 'guru pengajar', 'related_keywords' => array('guru', 'pengajar'), 'tags' => array('S', 'E'), 'zone' => 4, 'why' => 'Membantu proses belajar dan perkembangan peserta didik.'),
    array('title' => 'Wirausaha', 'keyword' => 'wirausaha', 'related_keywords' => array('business development', 'sales'), 'tags' => array('E', 'R'), 'zone' => 3, 'why' => 'Membangun produk/jasa, memimpin tim, dan mengambil peluang pasar.'),
    array('title' => 'Manajer Operasional', 'keyword' => 'manajer operasional', 'related_keywords' => array('operasional', 'supervisor'), 'tags' => array('E', 'C'), 'zone' => 4, 'why' => 'Mengelola proses, target kerja, dan koordinasi lintas tim.'),
    array('title' => 'Teknisi Jaringan', 'keyword' => 'teknisi jaringan', 'related_keywords' => array('network engineer', 'it support'), 'tags' => array('R', 'I'), 'zone' => 3, 'why' => 'Menangani perangkat dan sistem jaringan secara teknis.'),
    array('title' => 'Surveyor Lapangan', 'keyword' => 'surveyor', 'related_keywords' => array('teknik sipil', 'field officer'), 'tags' => array('R', 'C'), 'zone' => 3, 'why' => 'Bekerja langsung di lapangan dengan pengukuran yang presisi.'),
    array('title' => 'Perawat', 'keyword' => 'perawat', 'related_keywords' => array('tenaga kesehatan', 'nurse'), 'tags' => array('S', 'R'), 'zone' => 4, 'why' => 'Memberikan layanan kesehatan praktis dan empatik.'),
    array('title' => 'Arsitek', 'keyword' => 'arsitek', 'related_keywords' => array('drafter', 'desain bangunan'), 'tags' => array('A', 'R'), 'zone' => 5, 'why' => 'Menggabungkan kreativitas desain dengan perhitungan teknis bangunan.'),
    array('title' => 'Content Creator', 'keyword' => 'content creator', 'related_keywords' => array('copywriter', 'social media'), 'tags' => array('A', 'E'), 'zone' => 2, 'why' => 'Menciptakan konten yang menarik dan membangun audiens.'),
    array('title' => 'Administrator Proyek', 'keyword' => 'administrator proyek', 'related_keywords' => array('project admin', 'administrasi'), 'tags' => array('C', 'E'), 'zone' => 3, 'why' => 'Menata timeline, dokumen, dan koordinasi proyek secara rapi.'),
    array('title' => 'Peneliti', 'keyword' => 'peneliti', 'related_keywords' => array('research', 'analis riset'), 'tags' => array('I', 'A'), 'zone' => 5, 'why' => 'Menyusun hipotesis, eksperimen, dan publikasi berbasis data.'),
    array('title' => 'Mekanik Otomotif', 'keyword' => 'mekanik otomotif', 'related_keywords' => array('teknisi otomotif', 'mekanik'), 'tags' => array('R', 'C'), 'zone' => 2, 'why' => 'Memperbaiki kendaraan dengan pendekatan teknis dan prosedural.')
);

$trainingCatalog = array(
    array(
        'title' => 'Pembuatan Desain Grafis',
        'focus' => 'Belajar prinsip desain, membuat materi visual, dan mengoperasikan tools desain dasar.',
        'tags' => array('A', 'I'),
        'level' => 'Pemula',
        'delivery' => 'Gratis',
        'detail_url' => 'https://skillhub.kemnaker.go.id/pelatihan/pembuatan-desain-grafis-5a0931cc-ac69-47f9-96c0-3529066b460b',
        'related_url' => 'https://skillhub.kemnaker.go.id/pelatihan/?filters=vocational_id%3A3bacd8da-2eb7-4fbb-8c26-b0dd36feaca2%23teknologi+informasi+dan+komunikasi'
    ),
    array(
        'title' => 'Desain Grafis di Canva bagi UMKM',
        'focus' => 'Pelatihan desain cepat untuk konten promosi, branding visual, dan komunikasi digital.',
        'tags' => array('A', 'E'),
        'level' => 'Pemula',
        'delivery' => 'Gratis',
        'detail_url' => 'https://skillhub.kemnaker.go.id/pelatihan/desain-grafis-di-canva-bagi-umkm-b9c949c5-3d03-4161-858c-132fafb1d912?catalogue=da65ff26-9507-4ad7-be67-9e803af7e3ca',
        'related_url' => 'https://skillhub.kemnaker.go.id/pelatihan?filters=media%3Aonline%23Webinar'
    ),
    array(
        'title' => 'Administrative Assistant',
        'focus' => 'Cocok untuk minat administrasi, dokumen, ketelitian, dan dukungan operasional kantor.',
        'tags' => array('C', 'E'),
        'level' => 'Pemula - Menengah',
        'delivery' => 'Gratis',
        'detail_url' => 'https://skillhub.kemnaker.go.id/pelatihan/administrative-assistant-90ffbd81-7930-4a0a-9330-60dc8293af49',
        'related_url' => 'https://skillhub.kemnaker.go.id/pelatihan/?filters=vocational_id%3A4e18e53c-bd7d-4ef1-a7b0-eabbe1eba07f%23bisnis+dan+manajemen'
    ),
    array(
        'title' => 'Practical Office Advance',
        'focus' => 'Pendalaman aplikasi perkantoran untuk analisis data, administrasi, dan pekerjaan kantor modern.',
        'tags' => array('C', 'I'),
        'level' => 'Menengah',
        'delivery' => 'Gratis',
        'detail_url' => 'https://skillhub.kemnaker.go.id/pelatihan/practical-office-advance-95df3a51-317a-44c8-98e7-1266f9647c3b',
        'related_url' => 'https://skillhub.kemnaker.go.id/pelatihan/?filters=vocational_id%3A4e18e53c-bd7d-4ef1-a7b0-eabbe1eba07f%23bisnis+dan+manajemen'
    ),
    array(
        'title' => 'Digital Marketing Dasar',
        'focus' => 'Dasar strategi pemasaran digital, audiens, channel marketing, dan promosi online.',
        'tags' => array('E', 'A'),
        'level' => 'Pemula',
        'delivery' => 'Gratis',
        'detail_url' => 'https://skillhub.kemnaker.go.id/pelatihan/digital-marketing-dasar-b5a79bd7-b9b2-464e-bd8c-7ea4f6767d1b?catalogue=2650b6b7-cf54-4aff-aeae-05a99f96231a',
        'related_url' => 'https://skillhub.kemnaker.go.id/pelatihan/?filters=vocational_id%3A4e18e53c-bd7d-4ef1-a7b0-eabbe1eba07f%23bisnis+dan+manajemen'
    ),
    array(
        'title' => 'Pembuatan Konten Visual Untuk Sosial Media',
        'focus' => 'Pelatihan konten visual, social media, dan kreativitas digital yang relevan untuk promosi.',
        'tags' => array('A', 'E'),
        'level' => 'Menengah',
        'delivery' => 'Gratis',
        'detail_url' => 'https://skillhub.kemnaker.go.id/pelatihan/pembuatan-konten-visual-untuk-sosial-media-1c6479d7-c698-4cb4-a9e1-937b8fb544b2?catalogue=378884cd-2c44-4e9b-aeb9-b2171193e5ac',
        'related_url' => 'https://skillhub.kemnaker.go.id/pelatihan?filters=media%3Aonline%23Webinar'
    ),
    array(
        'title' => 'Teknik Engine Tune Up Sepeda Motor Injeksi',
        'focus' => 'Cocok untuk minat teknis, troubleshooting, praktik bengkel, dan logika mekanik.',
        'tags' => array('R', 'I'),
        'level' => 'Pemula',
        'delivery' => 'Gratis',
        'detail_url' => 'https://skillhub.kemnaker.go.id/pelatihan/teknik-engine-tune-up-sepeda-motor-injeksi-58d3cc4b-4b87-4fd3-afac-3c7cbf60b549',
        'related_url' => 'https://skillhub.kemnaker.go.id/pelatihan/'
    ),
    array(
        'title' => 'Servis Sepeda Motor Listrik Dasar',
        'focus' => 'Pelatihan otomotif modern untuk perawatan, pemeriksaan, dan sistem kendaraan listrik.',
        'tags' => array('R', 'I'),
        'level' => 'Pemula',
        'delivery' => 'Gratis',
        'detail_url' => 'https://skillhub.kemnaker.go.id/pelatihan/servis-sepeda-motor-listrik-dasar-55dd5041-35b6-48ad-b553-c65ec077d287',
        'related_url' => 'https://skillhub.kemnaker.go.id/pelatihan/'
    ),
    array(
        'title' => 'Juru Ukur/Surveyor (Kualifikasi 3)',
        'focus' => 'Sangat relevan untuk minat lapangan, pengukuran, presisi, dan konstruksi.',
        'tags' => array('R', 'C', 'I'),
        'level' => 'Menengah',
        'delivery' => 'Gratis',
        'detail_url' => 'https://skillhub.kemnaker.go.id/pelatihan/juru-ukursurveyor-kualifikasi-3-8115168f-170a-480c-ac82-1ef1e985441b?catalogue=ac42d723-11fa-4e1d-81bd-2c5c65962f46',
        'related_url' => 'https://skillhub.kemnaker.go.id/pelatihan/'
    ),
    array(
        'title' => 'Juru Gambar Bangunan Gedung (Kualifikasi 4)',
        'focus' => 'Pelatihan yang memadukan visualisasi desain, gambar teknik, dan detail bangunan.',
        'tags' => array('A', 'R', 'C'),
        'level' => 'Menengah',
        'delivery' => 'Gratis',
        'detail_url' => 'https://skillhub.kemnaker.go.id/pelatihan/juru-gambar-bangunan-gedung-984cd329-dd31-4fe0-9079-6f4f1f025307',
        'related_url' => 'https://skillhub.kemnaker.go.id/pelatihan/'
    ),
    array(
        'title' => 'Housekeeping',
        'focus' => 'Relevan untuk minat layanan, ketelitian, kerapihan, dan standar kerja hospitality.',
        'tags' => array('S', 'C', 'E'),
        'level' => 'Pemula',
        'delivery' => 'Gratis',
        'detail_url' => 'https://skillhub.kemnaker.go.id/pelatihan/housekeeping-4efdf3e4-b3d5-4541-bb55-7d85c3583e96?catalogue=5d543f17-319c-476c-941b-0583e368c566',
        'related_url' => 'https://skillhub.kemnaker.go.id/pelatihan/?filters=vocational_id%3Ad5744527-3d87-4fe4-b652-8832f9c5bef2%23Pariwisata'
    )
);

function buildKarirhubSearchUrl($keyword) {
    $keyword = trim((string)$keyword);
    if ($keyword === '') {
        return 'https://karirhub.kemnaker.go.id/lowongan-dalam-negeri/lowongan';
    }

    $filtersValue = 'keyword:' . $keyword . '#' . $keyword;
    return 'https://karirhub.kemnaker.go.id/lowongan-dalam-negeri/lowongan?filters=' . rawurlencode($filtersValue);
}

function getPrimaryCareerKeyword($career) {
    if (isset($career['keyword']) && trim((string)$career['keyword']) !== '') {
        return trim((string)$career['keyword']);
    }
    return trim((string)$career['title']);
}

function getRelatedCareerKeyword($career) {
    if (isset($career['related_keywords']) && is_array($career['related_keywords'])) {
        foreach ($career['related_keywords'] as $kw) {
            $kw = trim((string)$kw);
            if ($kw !== '') {
                return $kw;
            }
        }
    }
    return getPrimaryCareerKeyword($career);
}

$weights = array();
foreach ($topCodes as $idx => $code) {
    $weights[$code] = 3 - $idx;
}

foreach ($careerCatalog as $idx => $career) {
    $score = 0;
    foreach ($career['tags'] as $tag) {
        if (isset($weights[$tag])) {
            $score += $weights[$tag];
        }
    }
    $careerCatalog[$idx]['rank'] = $score;
}

usort($careerCatalog, function ($a, $b) {
    if ($a['rank'] === $b['rank']) {
        return $a['zone'] <=> $b['zone'];
    }
    return $b['rank'] <=> $a['rank'];
});
$careerRecommendations = array_slice($careerCatalog, 0, 12);

foreach ($trainingCatalog as $idx => $training) {
    $score = 0;
    $overlapCount = 0;
    foreach ($training['tags'] as $tag) {
        if (isset($weights[$tag])) {
            $score += $weights[$tag];
            $overlapCount++;
        }
    }
    $trainingCatalog[$idx]['rank'] = $score;
    $trainingCatalog[$idx]['overlap_count'] = $overlapCount;
    $trainingCatalog[$idx]['matches_primary'] = in_array($topCodes[0], $training['tags'], true);
}

usort($trainingCatalog, function ($a, $b) {
    if ($a['rank'] === $b['rank']) {
        return $b['overlap_count'] <=> $a['overlap_count'];
    }
    return $b['rank'] <=> $a['rank'];
});

$trainingRecommendations = array();
foreach ($trainingCatalog as $training) {
    $isRelevant = ($training['matches_primary'] && $training['rank'] >= 3) || $training['overlap_count'] >= 2;
    if ($isRelevant) {
        $trainingRecommendations[] = $training;
    }
    if (count($trainingRecommendations) >= 4) {
        break;
    }
}
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
    <div class="career-grid">
      <?php if (!empty($trainingRecommendations)) { ?>
        <?php foreach ($trainingRecommendations as $training) { ?>
          <article class="career-card">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <strong><?php echo htmlspecialchars($training['title']); ?></strong>
              <span class="badge-zone"><?php echo htmlspecialchars($training['delivery']); ?></span>
            </div>
            <div class="small mb-1"><strong>Level:</strong> <?php echo htmlspecialchars($training['level']); ?></div>
            <div class="small mb-1"><strong>Kecocokan:</strong> <?php echo htmlspecialchars(implode('-', array_intersect($training['tags'], $topCodes))); ?></div>
            <div class="muted small"><?php echo htmlspecialchars($training['focus']); ?></div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
              <a
                class="btn btn-sm btn-outline-success"
                href="<?php echo htmlspecialchars($training['detail_url']); ?>"
                target="_blank"
                rel="noopener noreferrer"
              >
                Lihat Detail Pelatihan
              </a>
              <a
                class="btn btn-sm btn-outline-secondary"
                href="<?php echo htmlspecialchars($training['related_url']); ?>"
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

  <div class="d-flex gap-2 flex-wrap">
    <a href="test_form.php" class="btn btn-outline-soft">Ulangi asesmen</a>
    <a href="generate_pdf.php" class="btn btn-primary-soft" target="_blank">Unduh laporan</a>
    <a href="index.php" class="btn btn-outline-secondary">Kembali ke beranda</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>