<?php

function getRiasecJobZones() {
    return array(
        array('zone' => 1, 'label' => 'Persiapan minimal', 'desc' => 'Pelatihan singkat atau pengalaman kerja awal.'),
        array('zone' => 2, 'label' => 'Persiapan dasar', 'desc' => 'Butuh pelatihan beberapa bulan hingga 1 tahun.'),
        array('zone' => 3, 'label' => 'Persiapan menengah', 'desc' => 'Biasanya perlu pendidikan vokasi/D3 atau pengalaman teknis.'),
        array('zone' => 4, 'label' => 'Persiapan tinggi', 'desc' => 'Umumnya setara S1 dan pengalaman kerja lebih mendalam.'),
        array('zone' => 5, 'label' => 'Persiapan sangat tinggi', 'desc' => 'Sering membutuhkan pendidikan lanjut dan keahlian spesialis.')
    );
}

function getCareerCatalog() {
    return array(
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
}

function getTrainingCatalog() {
    return array(
        array('title' => 'Desain Grafis & Visual', 'focus' => 'Tema pelatihan untuk desain, visual communication, dan produksi materi kreatif.', 'tags' => array('A', 'I'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'desain grafis', 'related_keywords' => array('graphic design', 'desainer grafis')),
        array('title' => 'Konten Visual & Canva', 'focus' => 'Tema pelatihan untuk pembuatan konten promosi, Canva, dan desain cepat untuk media digital.', 'tags' => array('A', 'E'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'canva', 'related_keywords' => array('konten visual', 'social media design')),
        array('title' => 'Administrasi Kantor', 'focus' => 'Tema pelatihan untuk administrasi, dokumen, ketelitian, dan dukungan operasional perkantoran.', 'tags' => array('C', 'E'), 'level' => 'Pemula - Menengah', 'delivery' => 'Gratis', 'keyword' => 'administrative', 'related_keywords' => array('administrative assistant', 'administrasi')),
        array('title' => 'Aplikasi Perkantoran', 'focus' => 'Tema pelatihan untuk office tools, pengolahan data, dan administrasi digital.', 'tags' => array('C', 'I'), 'level' => 'Menengah', 'delivery' => 'Gratis', 'keyword' => 'office', 'related_keywords' => array('practical office', 'aplikasi perkantoran')),
        array('title' => 'Digital Marketing', 'focus' => 'Tema pelatihan untuk pemasaran digital, branding, promosi, dan channel online.', 'tags' => array('E', 'A'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'digital marketing', 'related_keywords' => array('marketing', 'pemasaran digital')),
        array('title' => 'Konten Sosial Media', 'focus' => 'Tema pelatihan untuk social media, content creation, dan kreativitas digital.', 'tags' => array('A', 'E'), 'level' => 'Menengah', 'delivery' => 'Gratis', 'keyword' => 'sosial media', 'related_keywords' => array('konten visual', 'social media')),
        array('title' => 'Otomotif Injeksi', 'focus' => 'Tema pelatihan untuk troubleshooting, servis motor, dan praktik otomotif teknis.', 'tags' => array('R', 'I'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'sepeda motor injeksi', 'related_keywords' => array('engine tune up', 'otomotif')),
        array('title' => 'Otomotif Kendaraan Listrik', 'focus' => 'Tema pelatihan untuk perawatan kendaraan listrik dan otomotif modern.', 'tags' => array('R', 'I'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'motor listrik', 'related_keywords' => array('servis motor', 'otomotif')),
        array('title' => 'Surveyor & Pengukuran', 'focus' => 'Tema pelatihan untuk pengukuran lapangan, presisi, dan pekerjaan teknis konstruksi.', 'tags' => array('R', 'C', 'I'), 'level' => 'Menengah', 'delivery' => 'Gratis', 'keyword' => 'surveyor', 'related_keywords' => array('juru ukur', 'konstruksi')),
        array('title' => 'Gambar Bangunan', 'focus' => 'Tema pelatihan untuk gambar teknik, visualisasi bangunan, dan detail arsitektural.', 'tags' => array('A', 'R', 'C'), 'level' => 'Menengah', 'delivery' => 'Gratis', 'keyword' => 'gambar bangunan', 'related_keywords' => array('arsitektur', 'bangunan gedung')),
        array('title' => 'Hospitality & Housekeeping', 'focus' => 'Tema pelatihan untuk layanan, kerapihan, etika kerja, dan standar hospitality.', 'tags' => array('S', 'C', 'E'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'housekeeping', 'related_keywords' => array('hospitality', 'pariwisata')),
        array('title' => 'Operator Komputer', 'focus' => 'Tema pelatihan untuk pengoperasian komputer, aplikasi kerja, dan dukungan administrasi digital.', 'tags' => array('C', 'I'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'computer operator', 'related_keywords' => array('operator komputer', 'komputer')),
        array('title' => 'Data Science & Analitik', 'focus' => 'Tema pelatihan untuk analisis data, pengolahan dataset, logika, dan insight berbasis angka.', 'tags' => array('I', 'C'), 'level' => 'Menengah', 'delivery' => 'Gratis', 'keyword' => 'data scientist', 'related_keywords' => array('analisis data', 'data')),
        array('title' => 'Video Editing', 'focus' => 'Tema pelatihan untuk editing video, produksi konten audio visual, dan storytelling digital.', 'tags' => array('A', 'I'), 'level' => 'Pemula - Menengah', 'delivery' => 'Gratis', 'keyword' => 'video editor', 'related_keywords' => array('videography', 'konten video')),
        array('title' => 'Public Speaking & Presentasi', 'focus' => 'Tema pelatihan untuk komunikasi lisan, presentasi, negosiasi, dan membangun kepercayaan diri.', 'tags' => array('S', 'E'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'public speaking', 'related_keywords' => array('presentasi', 'komunikasi')),
        array('title' => 'Bahasa untuk Dunia Kerja', 'focus' => 'Tema pelatihan bahasa kerja untuk komunikasi profesional, layanan, dan kesiapan industri.', 'tags' => array('S', 'A', 'E'), 'level' => 'Pemula - Menengah', 'delivery' => 'Gratis', 'keyword' => 'bahasa inggris', 'related_keywords' => array('bahasa', 'english')),
        array('title' => 'Content Writing & Copywriting', 'focus' => 'Tema pelatihan menulis konten, copy promo, narasi brand, dan komunikasi pemasaran.', 'tags' => array('A', 'E'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'copywriter', 'related_keywords' => array('content creator', 'penulisan konten')),
        array('title' => 'Layanan Pelanggan', 'focus' => 'Tema pelatihan untuk customer service, penanganan kebutuhan klien, dan komunikasi layanan.', 'tags' => array('S', 'E', 'C'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'layanan pelanggan', 'related_keywords' => array('customer service', 'service')),
        array('title' => 'Kecantikan & Tata Rias', 'focus' => 'Tema pelatihan untuk tata kecantikan, layanan personal, ketelitian, dan sentuhan artistik.', 'tags' => array('A', 'S', 'E'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'tata kecantikan', 'related_keywords' => array('make up', 'beauty')),
        array('title' => 'Menjahit & Tata Busana', 'focus' => 'Tema pelatihan untuk jahit dasar, desain pakaian, produksi busana, dan ketelitian detail.', 'tags' => array('A', 'C', 'R'), 'level' => 'Pemula - Menengah', 'delivery' => 'Gratis', 'keyword' => 'menjahit', 'related_keywords' => array('tata busana', 'fashion')),
        array('title' => 'Listrik Bangunan', 'focus' => 'Tema pelatihan instalasi listrik, keselamatan kerja, dan keterampilan teknis bangunan.', 'tags' => array('R', 'I', 'C'), 'level' => 'Pemula - Menengah', 'delivery' => 'Gratis', 'keyword' => 'instalasi listrik', 'related_keywords' => array('listrik bangunan', 'teknik listrik')),
        array('title' => 'Pengolahan Roti & Kue', 'focus' => 'Tema pelatihan produksi pangan, resep, prosedur kerja, dan kreativitas produk olahan.', 'tags' => array('R', 'A', 'C'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'roti dan kue', 'related_keywords' => array('tata boga', 'bakery')),
        array('title' => 'Caregiver & Pendampingan', 'focus' => 'Tema pelatihan untuk pendampingan anak/lansia, empati, dan layanan berbasis kepedulian.', 'tags' => array('S', 'C'), 'level' => 'Pemula', 'delivery' => 'Gratis', 'keyword' => 'caregiver', 'related_keywords' => array('elderly caretaker', 'baby sitter'))
    );
}

function getTrainingProfileBoosts() {
    return array(
        'Desain Grafis & Visual' => array('primary' => array('A', 'I'), 'pairs' => array('AI', 'IA'), 'profiles' => array('AIE', 'AIS', 'RIA', 'IAR')),
        'Konten Visual & Canva' => array('primary' => array('A', 'E'), 'pairs' => array('AE', 'EA'), 'profiles' => array('AES', 'EAS', 'AEC')),
        'Administrasi Kantor' => array('primary' => array('C', 'E'), 'pairs' => array('CE', 'EC', 'CI'), 'profiles' => array('CEI', 'ECI', 'SEC', 'CIE')),
        'Aplikasi Perkantoran' => array('primary' => array('C', 'I'), 'pairs' => array('CI', 'IC'), 'profiles' => array('CIE', 'ICE', 'RIC', 'SEC')),
        'Digital Marketing' => array('primary' => array('E', 'A'), 'pairs' => array('EA', 'AE'), 'profiles' => array('EAS', 'AES', 'EAI')),
        'Konten Sosial Media' => array('primary' => array('A', 'E'), 'pairs' => array('AE', 'EA', 'AS'), 'profiles' => array('AES', 'ASE', 'EAS')),
        'Otomotif Injeksi' => array('primary' => array('R', 'I'), 'pairs' => array('RI', 'IR'), 'profiles' => array('RIA', 'RIC', 'IRC')),
        'Otomotif Kendaraan Listrik' => array('primary' => array('R', 'I'), 'pairs' => array('RI', 'IR'), 'profiles' => array('RIA', 'RIC', 'IRC')),
        'Surveyor & Pengukuran' => array('primary' => array('R', 'C'), 'pairs' => array('RC', 'CR', 'RI'), 'profiles' => array('RCI', 'RIC', 'CRI')),
        'Gambar Bangunan' => array('primary' => array('A', 'R'), 'pairs' => array('AR', 'RA', 'AC'), 'profiles' => array('ARC', 'ARI', 'RAC')),
        'Hospitality & Housekeeping' => array('primary' => array('S', 'C'), 'pairs' => array('SC', 'SE', 'CS'), 'profiles' => array('SEC', 'SCE', 'CSE')),
        'Operator Komputer' => array('primary' => array('C', 'I'), 'pairs' => array('CI', 'IC', 'CE'), 'profiles' => array('CIE', 'ICE', 'CEI')),
        'Data Science & Analitik' => array('primary' => array('I', 'C'), 'pairs' => array('IC', 'CI', 'IA'), 'profiles' => array('ICA', 'IAC', 'CIE')),
        'Video Editing' => array('primary' => array('A', 'I'), 'pairs' => array('AI', 'IA', 'AE'), 'profiles' => array('AIE', 'IAE', 'AES')),
        'Public Speaking & Presentasi' => array('primary' => array('S', 'E'), 'pairs' => array('SE', 'ES', 'SA'), 'profiles' => array('SEC', 'SEA', 'ESA')),
        'Bahasa untuk Dunia Kerja' => array('primary' => array('S', 'E'), 'pairs' => array('SE', 'SA', 'AE'), 'profiles' => array('SEA', 'AES', 'SEC')),
        'Content Writing & Copywriting' => array('primary' => array('A', 'E'), 'pairs' => array('AE', 'EA', 'AS'), 'profiles' => array('AES', 'AES', 'EAS')),
        'Layanan Pelanggan' => array('primary' => array('S', 'E'), 'pairs' => array('SE', 'ES', 'SC'), 'profiles' => array('SEC', 'SCE', 'ESC')),
        'Kecantikan & Tata Rias' => array('primary' => array('A', 'S'), 'pairs' => array('AS', 'SA', 'AE'), 'profiles' => array('ASE', 'AES', 'SAE')),
        'Menjahit & Tata Busana' => array('primary' => array('A', 'C'), 'pairs' => array('AC', 'AR', 'CA'), 'profiles' => array('ACR', 'ARC', 'CAR')),
        'Listrik Bangunan' => array('primary' => array('R', 'I'), 'pairs' => array('RI', 'RC', 'IR'), 'profiles' => array('RIC', 'RCI', 'IRC')),
        'Pengolahan Roti & Kue' => array('primary' => array('R', 'A'), 'pairs' => array('RA', 'AC', 'AR'), 'profiles' => array('RAC', 'ARC', 'AEC')),
        'Caregiver & Pendampingan' => array('primary' => array('S', 'C'), 'pairs' => array('SC', 'SR', 'SE'), 'profiles' => array('SEC', 'SRC', 'SCE'))
    );
}

function buildKarirhubSearchUrl($keyword) {
    $keyword = trim((string)$keyword);
    if ($keyword === '') {
        return 'https://karirhub.kemnaker.go.id/lowongan-dalam-negeri/lowongan';
    }
    $filtersValue = 'keyword:' . $keyword . '#' . $keyword;
    return 'https://karirhub.kemnaker.go.id/lowongan-dalam-negeri/lowongan?filters=' . rawurlencode($filtersValue);
}

function buildSkillhubSearchUrl($keyword) {
    $keyword = trim((string)$keyword);
    if ($keyword === '') {
        return 'https://skillhub.kemnaker.go.id/pelatihan/vokasi-nasional/jadwal';
    }
    $filtersValue = 'keyword:' . $keyword . '#' . $keyword;
    return 'https://skillhub.kemnaker.go.id/pelatihan/vokasi-nasional/jadwal?keyword=' . rawurlencode($keyword) . '&filters=' . rawurlencode($filtersValue);
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

function getTrainingBoostConfig($training, $trainingProfileBoosts) {
    $title = isset($training['title']) ? $training['title'] : '';
    if (isset($trainingProfileBoosts[$title])) {
        return $trainingProfileBoosts[$title];
    }
    return array('primary' => array(), 'pairs' => array(), 'profiles' => array());
}

function calculateTrainingRecommendation($training, $topCodes, $weights, $trainingProfileBoosts) {
    $score = 0;
    $overlapCount = 0;
    $matchedTags = array();

    foreach ($training['tags'] as $tag) {
        if (isset($weights[$tag])) {
            $score += $weights[$tag];
            $overlapCount++;
            $matchedTags[] = $tag;
        }
    }

    $boost = getTrainingBoostConfig($training, $trainingProfileBoosts);
    $pairCode = isset($topCodes[0], $topCodes[1]) ? ($topCodes[0] . $topCodes[1]) : '';
    $tripleCode = implode('', $topCodes);

    foreach ($boost['primary'] as $code) {
        if (isset($topCodes[0]) && $topCodes[0] === $code) {
            $score += 2.5;
        }
    }

    $matchedPair = false;
    if ($pairCode !== '' && in_array($pairCode, $boost['pairs'], true)) {
        $score += 3;
        $matchedPair = true;
    }

    $matchedProfile = false;
    if ($tripleCode !== '' && in_array($tripleCode, $boost['profiles'], true)) {
        $score += 4;
        $matchedProfile = true;
    }

    $reasonParts = array();
    if ($matchedProfile) {
        $reasonParts[] = 'sangat selaras dengan profil ' . $tripleCode;
    } elseif ($matchedPair) {
        $reasonParts[] = 'kuat di kombinasi ' . $pairCode;
    }

    if (!empty($matchedTags)) {
        $reasonParts[] = 'cocok dengan minat ' . implode('-', $matchedTags);
    }

    return array(
        'rank' => $score,
        'overlap_count' => $overlapCount,
        'matches_primary' => in_array($topCodes[0], $training['tags'], true),
        'matched_tags' => $matchedTags,
        'matched_pair' => $matchedPair,
        'matched_profile' => $matchedProfile,
        'reason' => !empty($reasonParts) ? implode('; ', $reasonParts) : 'relevan dengan pola minatmu'
    );
}

function getTrainingTier($training) {
    if (!empty($training['matched_profile']) || floatval($training['rank']) >= 8) {
        return array('label' => 'Sangat Direkomendasikan', 'class' => 'badge-tier-top', 'card_class' => 'recommendation-top');
    }
    if (!empty($training['matched_pair']) || floatval($training['rank']) >= 5) {
        return array('label' => 'Cocok', 'class' => 'badge-tier-good', 'card_class' => 'recommendation-good');
    }
    return array('label' => 'Eksplorasi Tambahan', 'class' => 'badge-tier-alt', 'card_class' => 'recommendation-alt');
}

function getRiasecRecommendationPayload($resultPersonality, $scorePercentageList) {
    $topCodes = str_split(substr((string)$resultPersonality, 0, 3));
    if (count($topCodes) < 3) {
        $sortedFallback = $scorePercentageList;
        arsort($sortedFallback);
        $topCodes = array_slice(array_keys($sortedFallback), 0, 3);
    }

    $weights = array();
    foreach ($topCodes as $idx => $code) {
        $weights[$code] = 3 - $idx;
    }

    $careerCatalog = getCareerCatalog();
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

    $trainingCatalog = getTrainingCatalog();
    $trainingProfileBoosts = getTrainingProfileBoosts();
    foreach ($trainingCatalog as $idx => $training) {
        $trainingRankData = calculateTrainingRecommendation($training, $topCodes, $weights, $trainingProfileBoosts);
        $trainingCatalog[$idx]['rank'] = $trainingRankData['rank'];
        $trainingCatalog[$idx]['overlap_count'] = $trainingRankData['overlap_count'];
        $trainingCatalog[$idx]['matches_primary'] = $trainingRankData['matches_primary'];
        $trainingCatalog[$idx]['matched_tags'] = $trainingRankData['matched_tags'];
        $trainingCatalog[$idx]['matched_pair'] = $trainingRankData['matched_pair'];
        $trainingCatalog[$idx]['matched_profile'] = $trainingRankData['matched_profile'];
        $trainingCatalog[$idx]['reason'] = $trainingRankData['reason'];
        $trainingCatalog[$idx]['tier'] = getTrainingTier($trainingCatalog[$idx]);
    }

    usort($trainingCatalog, function ($a, $b) {
        if ($a['rank'] === $b['rank']) {
            if ($a['overlap_count'] === $b['overlap_count']) {
                return strcmp($a['title'], $b['title']);
            }
            return $b['overlap_count'] <=> $a['overlap_count'];
        }
        return $b['rank'] <=> $a['rank'];
    });

    $trainingRecommendations = array();
    foreach ($trainingCatalog as $training) {
        $isRelevant = ($training['matches_primary'] && $training['rank'] >= 3)
            || $training['overlap_count'] >= 2
            || $training['matched_pair']
            || $training['matched_profile']
            || $training['rank'] >= 2.5;
        if ($isRelevant) {
            $trainingRecommendations[] = $training;
        }
        if (count($trainingRecommendations) >= 8) {
            break;
        }
    }

    $trainingTierSummary = array('top' => 0, 'good' => 0, 'alt' => 0);
    foreach ($trainingRecommendations as $training) {
        $tierClass = isset($training['tier']['class']) ? $training['tier']['class'] : '';
        if ($tierClass === 'badge-tier-top') {
            $trainingTierSummary['top']++;
        } elseif ($tierClass === 'badge-tier-good') {
            $trainingTierSummary['good']++;
        } else {
            $trainingTierSummary['alt']++;
        }
    }

    return array(
        'top_codes' => $topCodes,
        'career_recommendations' => $careerRecommendations,
        'training_recommendations' => $trainingRecommendations,
        'training_tier_summary' => $trainingTierSummary,
        'job_zones' => getRiasecJobZones()
    );
}
?>
