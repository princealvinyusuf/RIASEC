<?php
// Start session and prepare DB before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';

$educationLevels = array('10', '11', '12', 'Universitas');
$regionApiBaseUrl = 'https://www.emsifa.com/api-wilayah-indonesia/api';

function personalInfoColumnExists($connection, $tableName, $columnName) {
    $safeTable = mysqli_real_escape_string($connection, $tableName);
    $safeColumn = mysqli_real_escape_string($connection, $columnName);
    $sql = "SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeColumn}'";
    $res = mysqli_query($connection, $sql);
    return $res && mysqli_num_rows($res) > 0;
}

function normalizeInputText($value) {
    $value = trim((string)$value);
    $value = preg_replace('/\s+/', ' ', $value);
    return $value;
}

function isMeaningfulText($value, $minLetters = 2) {
    $value = normalizeInputText($value);
    if ($value === '') {
        return false;
    }

    // Reject inputs made only of punctuation/symbols such as "-", ".", "--", etc.
    if (!preg_match('/[\p{L}\p{N}]/u', $value)) {
        return false;
    }

    preg_match_all('/[\p{L}]/u', $value, $matches);
    return count($matches[0]) >= $minLetters;
}

// Ensure required table exists
$createTableSql = "CREATE TABLE IF NOT EXISTS personal_info (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(150) NOT NULL,
    class_level ENUM('10','11','12','Universitas') NOT NULL,
    school_name VARCHAR(150) NOT NULL,
    province VARCHAR(100) NULL,
    city VARCHAR(120) NULL,
    extracurricular TEXT NOT NULL,
    organization TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($connection, $createTableSql);
mysqli_query($connection, "ALTER TABLE personal_info MODIFY class_level ENUM('10','11','12','Universitas') NOT NULL");
if (!personalInfoColumnExists($connection, 'personal_info', 'province')) {
    mysqli_query($connection, "ALTER TABLE personal_info ADD COLUMN province VARCHAR(100) NULL AFTER school_name");
}
if (!personalInfoColumnExists($connection, 'personal_info', 'city')) {
    mysqli_query($connection, "ALTER TABLE personal_info ADD COLUMN city VARCHAR(120) NULL AFTER province");
}

$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = isset($_POST['full_name']) ? normalizeInputText($_POST['full_name']) : '';
    $birth_date = isset($_POST['birth_date']) ? trim($_POST['birth_date']) : '';
    $phone = isset($_POST['phone']) ? normalizeInputText($_POST['phone']) : '';
    $email = isset($_POST['email']) ? normalizeInputText($_POST['email']) : '';
    $class_level = isset($_POST['class_level']) ? trim($_POST['class_level']) : '';
    $school_name = isset($_POST['school_name']) ? normalizeInputText($_POST['school_name']) : '';
    $province = isset($_POST['province']) ? normalizeInputText($_POST['province']) : '';
    $city = isset($_POST['city']) ? normalizeInputText($_POST['city']) : '';
    $extracurricular = isset($_POST['extracurricular']) ? normalizeInputText($_POST['extracurricular']) : '';
    $organization = isset($_POST['organization']) ? normalizeInputText($_POST['organization']) : '';

    if ($full_name === '') { $errors[] = 'Nama Lengkap wajib diisi.'; }
    if ($birth_date === '') { $errors[] = 'Tanggal Lahir wajib diisi.'; }
    if ($phone === '') { $errors[] = 'No. HP wajib diisi.'; }
    if ($email === '') { $errors[] = 'E-mail wajib diisi.'; }
    if ($class_level === '') { $errors[] = 'Jenjang Pendidikan wajib dipilih.'; }
    if ($school_name === '') { $errors[] = 'Nama Sekolah/Institusi/Universitas wajib diisi.'; }
    if ($province === '') { $errors[] = 'Provinsi wajib dipilih.'; }
    if ($city === '') { $errors[] = 'Kota wajib dipilih.'; }
    if ($extracurricular === '') { $errors[] = 'Ekstrakurikuler yang diikuti wajib diisi.'; }
    if ($organization === '') { $errors[] = 'Organisasi yang diikuti wajib diisi.'; }

    if ($full_name !== '' && !isMeaningfulText($full_name, 3)) {
        $errors[] = 'Nama Lengkap tidak valid. Gunakan nama yang benar, bukan simbol seperti "-" atau input acak.';
    }

    if ($school_name !== '' && !isMeaningfulText($school_name, 3)) {
        $errors[] = 'Nama Sekolah/Institusi/Universitas tidak valid.';
    }
    if ($province !== '' && !isMeaningfulText($province, 3)) {
        $errors[] = 'Provinsi tidak valid.';
    }
    if ($city !== '' && !isMeaningfulText($city, 3)) {
        $errors[] = 'Kota tidak valid.';
    }

    if ($extracurricular !== '' && !isMeaningfulText($extracurricular, 3)) {
        $errors[] = 'Ekstrakurikuler yang diikuti tidak valid.';
    }

    if ($organization !== '' && !isMeaningfulText($organization, 3)) {
        $errors[] = 'Organisasi yang diikuti tidak valid.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format E-mail tidak valid.';
    }

    if ($phone !== '') {
        $phoneDigits = preg_replace('/\D+/', '', $phone);
        if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
            $errors[] = 'Nomor HP tidak valid. Gunakan 10 sampai 15 digit angka.';
        } else {
            $phone = $phoneDigits;
        }
    }

    if ($class_level !== '' && !in_array($class_level, $educationLevels, true)) {
        $errors[] = 'Jenjang Pendidikan tidak valid.';
    }

    if ($birth_date !== '') {
        $birthTimestamp = strtotime($birth_date);
        $todayTimestamp = strtotime(date('Y-m-d'));
        if ($birthTimestamp === false) {
            $errors[] = 'Tanggal Lahir tidak valid.';
        } elseif ($birthTimestamp > $todayTimestamp) {
            $errors[] = 'Tanggal Lahir tidak boleh di masa depan.';
        } else {
            $age = (int)date('Y') - (int)date('Y', $birthTimestamp);
            if ($age < 10 || $age > 100) {
                $errors[] = 'Tanggal Lahir tidak masuk akal untuk peserta asesmen.';
            }
        }
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $connection,
            "INSERT INTO personal_info (full_name, birth_date, phone, email, class_level, school_name, province, city, extracurricular, organization)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssssssssss', $full_name, $birth_date, $phone, $email, $class_level, $school_name, $province, $city, $extracurricular, $organization);
            $ok = mysqli_stmt_execute($stmt);
            if ($ok) {
                $insertedId = mysqli_insert_id($connection);
                $_SESSION['personal_info_id'] = $insertedId;
                header('Location: test_form');
                exit;
            } else {
                $errors[] = 'Gagal menyimpan data. Silakan coba lagi.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = 'Terjadi kesalahan pada server. Silakan coba lagi.';
        }
    }
}
?>
<?php $pageTitle = 'Data Peserta - RIASEC'; ?>
<?php include 'includes/header.php'; ?>

<section class="page-wrap">
  <div class="glass-card app-form-card">
    <div class="form-header mb-4">
      <p class="mb-2"><a href="index" class="text-decoration-none">&larr; Kembali ke beranda</a></p>
      <h1 class="fw-bold text-success">Data awal peserta</h1>
      <p>Lengkapi data berikut untuk memulai asesmen minat karier.</p>
    </div>

    <?php if (!empty($errors)) { ?>
      <div class="alert alert-danger" role="alert">
        <ul class="mb-0 ps-3">
          <?php foreach ($errors as $err) { ?>
            <li><?php echo htmlspecialchars($err); ?></li>
          <?php } ?>
        </ul>
      </div>
    <?php } ?>

    <form action="personal_info" method="post" novalidate>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nama lengkap</label>
          <input type="text" name="full_name" class="form-control" required minlength="3" maxlength="100" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Tanggal lahir</label>
          <input type="date" name="birth_date" class="form-control" required max="<?php echo date('Y-m-d'); ?>" value="<?php echo isset($birth_date) ? htmlspecialchars($birth_date) : ''; ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Nomor HP</label>
          <input type="tel" name="phone" class="form-control" required inputmode="numeric" minlength="10" maxlength="15" pattern="[0-9]{10,15}" placeholder="Contoh: 081234567890" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required maxlength="150" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Jenjang Pendidikan</label>
          <select name="class_level" class="form-select" required>
            <option value="" disabled <?php echo !isset($class_level) || $class_level === '' ? 'selected' : ''; ?>>Pilih jenjang pendidikan</option>
            <option value="10" <?php echo (isset($class_level) && $class_level === '10') ? 'selected' : ''; ?>>10</option>
            <option value="11" <?php echo (isset($class_level) && $class_level === '11') ? 'selected' : ''; ?>>11</option>
            <option value="12" <?php echo (isset($class_level) && $class_level === '12') ? 'selected' : ''; ?>>12</option>
            <option value="Universitas" <?php echo (isset($class_level) && $class_level === 'Universitas') ? 'selected' : ''; ?>>Universitas</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Nama Sekolah/Institusi/Universitas</label>
          <input type="text" name="school_name" class="form-control" required minlength="3" maxlength="150" value="<?php echo isset($school_name) ? htmlspecialchars($school_name) : ''; ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Provinsi</label>
          <select
            name="province"
            id="provinceSelect"
            class="form-select"
            required
            data-selected="<?php echo isset($province) ? htmlspecialchars($province) : ''; ?>"
            data-api-base="<?php echo htmlspecialchars($regionApiBaseUrl); ?>"
          >
            <option value="" selected disabled>Pilih provinsi</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Kota</label>
          <select
            name="city"
            id="citySelect"
            class="form-select"
            required
            data-selected="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>"
          >
            <option value="" selected disabled>Pilih kota</option>
          </select>
          <div class="form-text" id="regionHelp">Pilih provinsi terlebih dahulu untuk menampilkan daftar kota.</div>
        </div>
        <div class="col-12">
          <label class="form-label">Ekstrakurikuler yang diikuti</label>
          <input type="text" name="extracurricular" class="form-control" required minlength="3" maxlength="255" value="<?php echo isset($extracurricular) ? htmlspecialchars($extracurricular) : ''; ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Organisasi yang diikuti</label>
          <input type="text" name="organization" class="form-control" required minlength="3" maxlength="255" value="<?php echo isset($organization) ? htmlspecialchars($organization) : ''; ?>">
        </div>
      </div>

      <div class="question-nav mt-4">
        <a href="index" class="btn btn-outline-secondary">Kembali</a>
        <button type="submit" class="btn btn-primary-soft">Lanjut ke pertanyaan</button>
      </div>
    </form>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var provinceSelect = document.getElementById('provinceSelect');
  var citySelect = document.getElementById('citySelect');
  var regionHelp = document.getElementById('regionHelp');

  if (!provinceSelect || !citySelect) {
    return;
  }

  var apiBase = provinceSelect.getAttribute('data-api-base') || '';
  var selectedProvinceName = provinceSelect.getAttribute('data-selected') || '';
  var selectedCityName = citySelect.getAttribute('data-selected') || '';
  var provincesEndpoint = apiBase.replace(/\/$/, '') + '/provinces.json';

  function setRegionHelp(message) {
    if (regionHelp) {
      regionHelp.textContent = message;
    }
  }

  function resetCitySelect(placeholder) {
    citySelect.innerHTML = '';
    var option = document.createElement('option');
    option.value = '';
    option.textContent = placeholder;
    option.disabled = true;
    option.selected = true;
    citySelect.appendChild(option);
  }

  function fillProvinceOptions(provinces) {
    resetCitySelect('Pilih kota');
    provinceSelect.innerHTML = '';
    var placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Pilih provinsi';
    placeholder.disabled = true;
    placeholder.selected = true;
    provinceSelect.appendChild(placeholder);

    provinces.forEach(function (province) {
      var option = document.createElement('option');
      option.value = province.name;
      option.textContent = province.name;
      option.setAttribute('data-id', province.id);
      if (selectedProvinceName && selectedProvinceName === province.name) {
        option.selected = true;
      }
      provinceSelect.appendChild(option);
    });
  }

  function fillCityOptions(cities) {
    citySelect.innerHTML = '';
    var placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Pilih kota';
    placeholder.disabled = true;
    placeholder.selected = true;
    citySelect.appendChild(placeholder);

    cities.forEach(function (city) {
      var option = document.createElement('option');
      option.value = city.name;
      option.textContent = city.name;
      if (selectedCityName && selectedCityName === city.name) {
        option.selected = true;
      }
      citySelect.appendChild(option);
    });
  }

  function loadCitiesByProvinceId(provinceId) {
    if (!provinceId) {
      resetCitySelect('Pilih kota');
      setRegionHelp('Pilih provinsi terlebih dahulu untuk menampilkan daftar kota.');
      return;
    }

    resetCitySelect('Memuat kota...');
    setRegionHelp('Mengambil data kota...');

    fetch(apiBase.replace(/\/$/, '') + '/regencies/' + encodeURIComponent(provinceId) + '.json')
      .then(function (response) {
        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }
        return response.json();
      })
      .then(function (cities) {
        fillCityOptions(Array.isArray(cities) ? cities : []);
        setRegionHelp('Data kota berhasil dimuat.');
      })
      .catch(function () {
        resetCitySelect('Gagal memuat kota');
        setRegionHelp('Gagal memuat data kota. Silakan periksa koneksi internet lalu coba lagi.');
      });
  }

  provinceSelect.addEventListener('change', function () {
    selectedCityName = '';
    var selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
    var provinceId = selectedOption ? selectedOption.getAttribute('data-id') : '';
    loadCitiesByProvinceId(provinceId);
  });

  fetch(provincesEndpoint)
    .then(function (response) {
      if (!response.ok) {
        throw new Error('HTTP ' + response.status);
      }
      return response.json();
    })
    .then(function (provinces) {
      fillProvinceOptions(Array.isArray(provinces) ? provinces : []);
      var selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
      var provinceId = selectedOption ? selectedOption.getAttribute('data-id') : '';
      if (provinceId) {
        loadCitiesByProvinceId(provinceId);
      } else {
        setRegionHelp('Pilih provinsi terlebih dahulu untuk menampilkan daftar kota.');
      }
    })
    .catch(function () {
      provinceSelect.innerHTML = '<option value="" selected disabled>Gagal memuat provinsi</option>';
      resetCitySelect('Gagal memuat kota');
      setRegionHelp('Gagal memuat data wilayah. Silakan refresh halaman atau periksa koneksi internet.');
    });
});
</script>

<?php include 'includes/footer.php'; ?>


