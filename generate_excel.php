<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
  header('Location: admin_login.php');
  exit;
}

include 'includes/db.php';

$query = "SELECT pts.id AS score_id,
                 pts.result,
                 pts.realistic, pts.investigative, pts.artistic,
                 pts.social, pts.enterprising, pts.conventional,
                 pts.created_at,
                 pi.id AS person_id, pi.full_name, pi.birth_date, pi.phone, pi.email,
                 pi.class_level, pi.school_name, pi.extracurricular, pi.organization, pi.created_at AS person_created
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
        'Nama Lengkap', 'Email', 'Kelas', 'Sekolah', 'Hasil (Kode)',
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
    header('Location: admin_scores.php'); // Redirect or show a message
    exit;
}
?>
