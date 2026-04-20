<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
  header('Location: admin_login');
  exit;
}

include 'includes/db.php';

function exportColumnExists($connection, $tableName, $columnName) {
    $safeTable = mysqli_real_escape_string($connection, $tableName);
    $safeColumn = mysqli_real_escape_string($connection, $columnName);
    $sql = "SHOW COLUMNS FROM `{$safeTable}` LIKE '{$safeColumn}'";
    $res = mysqli_query($connection, $sql);
    return $res && mysqli_num_rows($res) > 0;
}

if (!exportColumnExists($connection, 'personal_info', 'province')) {
    mysqli_query($connection, "ALTER TABLE personal_info ADD COLUMN province VARCHAR(100) NULL AFTER school_name");
}
if (!exportColumnExists($connection, 'personal_info', 'city')) {
    mysqli_query($connection, "ALTER TABLE personal_info ADD COLUMN city VARCHAR(120) NULL AFTER province");
}

$query = "SELECT pts.id AS score_id,
                 pts.result,
                 pts.realistic, pts.investigative, pts.artistic,
                 pts.social, pts.enterprising, pts.conventional,
                 pts.created_at,
                 pi.id AS person_id, pi.full_name, pi.birth_date, pi.phone, pi.email,
                 pi.class_level, pi.school_name, pi.province, pi.city,
                 pi.extracurricular, pi.organization, pi.created_at AS person_created
          FROM personality_test_scores pts
          LEFT JOIN personal_info pi ON pi.id = pts.personal_info_id
          ORDER BY pts.created_at DESC";

$scores = mysqli_query($connection, $query);

if ($scores && mysqli_num_rows($scores) > 0) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="riasec_scores.csv"');

    $output = fopen('php://output', 'w');

    // Add column headers
    fputcsv($output, [
        'Nama Lengkap', 'Email', 'Jenjang Pendidikan', 'Sekolah/Institusi/Universitas', 'Provinsi', 'Kota', 'Hasil (Kode)',
        'Realistic', 'Investigative', 'Artistic', 'Social', 'Enterprising', 'Conventional',
        'Tanggal Tes', 'Tanggal Lahir', 'No. HP', 'Ekstrakurikuler', 'Organisasi'
    ]);

    // Add data rows
    while ($row = mysqli_fetch_assoc($scores)) {
        fputcsv($output, [
            $row['full_name'],
            $row['email'],
            $row['class_level'],
            $row['school_name'],
            $row['province'],
            $row['city'],
            $row['result'],
            $row['realistic'],
            $row['investigative'],
            $row['artistic'],
            $row['social'],
            $row['enterprising'],
            $row['conventional'],
            $row['created_at'],
            $row['birth_date'],
            $row['phone'],
            $row['extracurricular'],
            $row['organization']
        ]);
    }

    fclose($output);
    exit;
} else {
    // Optional: handle case with no data
    header('Location: admin_scores'); // Redirect or show a message
    exit;
}
?>
