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
        'title' => 'Desain Grafis & Visual',
        'focus' => 'Tema pelatihan untuk desain, visual communication, dan produksi materi kreatif.',
        'tags' => array('A', 'I'),
        'level' => 'Pemula',
        'delivery' => 'Gratis',
        'keyword' => 'desain grafis',
        'related_keywords' => array('graphic design', 'desainer grafis')
    ),
    array(
        'title' => 'Konten Visual & Canva',
        'focus' => 'Tema pelatihan untuk pembuatan konten promosi, Canva, dan desain cepat untuk media digital.',
        'tags' => array('A', 'E'),
        'level' => 'Pemula',
        'delivery' => 'Gratis',
        'keyword' => 'canva',
        'related_keywords' => array('konten visual', 'social media design')
    ),
    array(
        'title' => 'Administrasi Kantor',
        'focus' => 'Tema pelatihan untuk administrasi, dokumen, ketelitian, dan dukungan operasional perkantoran.',
        'tags' => array('C', 'E'),
        'level' => 'Pemula - Menengah',
        'delivery' => 'Gratis',
        'keyword' => 'administrative',
        'related_keywords' => array('administrative assistant', 'administrasi')
    ),
    array(
        'title' => 'Aplikasi Perkantoran',
        'focus' => 'Tema pelatihan untuk office tools, pengolahan data, dan administrasi digital.',
        'tags' => array('C', 'I'),
        'level' => 'Menengah',
        'delivery' => 'Gratis',
        'keyword' => 'office',
        'related_keywords' => array('practical office', 'aplikasi perkantoran')
    ),
    array(
        'title' => 'Digital Marketing',
        'focus' => 'Tema pelatihan untuk pemasaran digital, branding, promosi, dan channel online.',
        'tags' => array('E', 'A'),
        'level' => 'Pemula',
        'delivery' => 'Gratis',
        'keyword' => 'digital marketing',
        'related_keywords' => array('marketing', 'pemasaran digital')
    ),
    array(
        'title' => 'Konten Sosial Media',
        'focus' => 'Tema pelatihan untuk social media, content creation, dan kreativitas digital.',
        'tags' => array('A', 'E'),
        'level' => 'Menengah',
        'delivery' => 'Gratis',
        'keyword' => 'sosial media',
        'related_keywords' => array('konten visual', 'social media')
    ),
    array(
        'title' => 'Otomotif Injeksi',
        'focus' => 'Tema pelatihan untuk troubleshooting, servis motor, dan praktik otomotif teknis.',
        'tags' => array('R', 'I'),
        'level' => 'Pemula',
        'delivery' => 'Gratis',
        'keyword' => 'sepeda motor injeksi',
        'related_keywords' => array('engine tune up', 'otomotif')
    ),
    array(
        'title' => 'Otomotif Kendaraan Listrik',
        'focus' => 'Tema pelatihan untuk perawatan kendaraan listrik dan otomotif modern.',
        'tags' => array('R', 'I'),
        'level' => 'Pemula',
        'delivery' => 'Gratis',
        'keyword' => 'motor listrik',
        'related_keywords' => array('servis motor', 'otomotif')
    ),
    array(
        'title' => 'Surveyor & Pengukuran',
        'focus' => 'Tema pelatihan untuk pengukuran lapangan, presisi, dan pekerjaan teknis konstruksi.',
        'tags' => array('R', 'C', 'I'),
        'level' => 'Menengah',
        'delivery' => 'Gratis',
        'keyword' => 'surveyor',
        'related_keywords' => array('juru ukur', 'konstruksi')
    ),
    array(
        'title' => 'Gambar Bangunan',
        'focus' => 'Tema pelatihan untuk gambar teknik, visualisasi bangunan, dan detail arsitektural.',
        'tags' => array('A', 'R', 'C'),
        'level' => 'Menengah',
        'delivery' => 'Gratis',
        'keyword' => 'gambar bangunan',
        'related_keywords' => array('arsitektur', 'bangunan gedung')
    ),
    array(
        'title' => 'Hospitality & Housekeeping',
        'focus' => 'Tema pelatihan untuk layanan, kerapihan, etika kerja, dan standar hospitality.',
        'tags' => array('S', 'C', 'E'),
        'level' => 'Pemula',
        'delivery' => 'Gratis',
        'keyword' => 'housekeeping',
        'related_keywords' => array('hospitality', 'pariwisata')
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

function buildSkillhubSearchUrl($keyword) {
    $keyword = trim((string)$keyword);
    if ($keyword === '') {
        return 'https://skillhub.kemnaker.go.id/pelatihan/vokasi-nasional/jadwal';
    }

    $filtersValue = 'keyword:' . $keyword . '#' . $keyword;
    return 'https://skillhub.kemnaker.go.id/pelatihan/vokasi-nasional/jadwal?keyword=' . rawurlencode($keyword) . '&filters=' . rawurlencode($filtersValue);
}

function getPrimaryTrainingKeyword($training) {
    if (isset($training['keyword']) && trim((string)$training['keyword']) !== '') {
        return trim((string)$training['keyword']);
    }
    return trim((string)$training['title']);
}

function getRelatedTrainingKeyword($training) {
    if (isset($training['related_keywords']) && is_array($training['related_keywords'])) {
        foreach ($training['related_keywords'] as $kw) {
            $kw = trim((string)$kw);
            if ($kw !== '') {
                return $kw;
            }
        }
    }
    return getPrimaryTrainingKeyword($training);
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
    <p class="muted small mb-3">
      Rekomendasi ini menggunakan model hybrid dynamic: sistem memilih tema pelatihan yang cocok dengan hasil RIASEC,
      lalu membuka hasil pencarian live di SkillHub menggunakan search URL resmi.
    </p>
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

  <div class="d-flex gap-2 flex-wrap">
    <a href="test_form.php" class="btn btn-outline-soft">Ulangi asesmen</a>
    <a href="generate_pdf.php" class="btn btn-primary-soft" target="_blank">Unduh laporan</a>
    <a href="index.php" class="btn btn-outline-secondary">Kembali ke beranda</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>