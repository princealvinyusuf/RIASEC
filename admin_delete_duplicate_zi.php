<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
    header('Location: admin_login');
    exit;
}

include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/admin_auth.php';
include_once __DIR__ . '/includes/zi_core.php';

ensureAdminUsersTable($connection);
ensureZiTablesAndSeed($connection);

$sessionAdminId = isset($_SESSION['admin_user_id']) ? intval($_SESSION['admin_user_id']) : 0;
$sessionAdminLevel = isset($_SESSION['admin_level']) ? (string)$_SESSION['admin_level'] : '';
if (!in_array($sessionAdminLevel, array('super_admin', 'staff'), true)) {
    $sessionAdminLevel = getAdminLevelById($connection, $sessionAdminId);
    $_SESSION['admin_level'] = $sessionAdminLevel;
}

if ($sessionAdminLevel !== 'super_admin') {
    header('Location: admin_scores?' . http_build_query(array(
        'permission_error' => 'Akses ditolak. Hanya Super Admin yang dapat menghapus data duplikat.'
    )));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_scores');
    exit;
}

$duplicateIds = array();
$duplicateResult = mysqli_query(
    $connection,
    "SELECT older.id
     FROM zi_assessments older
     WHERE TRIM(COALESCE(older.respondent_email, '')) <> ''
       AND EXISTS (
           SELECT 1
           FROM zi_assessments newer
           WHERE LOWER(TRIM(newer.respondent_email)) = LOWER(TRIM(older.respondent_email))
             AND (
                 newer.submitted_at > older.submitted_at
                 OR (newer.submitted_at = older.submitted_at AND newer.id > older.id)
             )
       )"
);

if ($duplicateResult) {
    while ($duplicateRow = mysqli_fetch_assoc($duplicateResult)) {
        $duplicateId = intval($duplicateRow['id']);
        if ($duplicateId > 0) {
            $duplicateIds[] = $duplicateId;
        }
    }
}

$deletedCount = 0;
if (!empty($duplicateIds)) {
    mysqli_begin_transaction($connection);
    $deleteAnswersStmt = mysqli_prepare($connection, "DELETE FROM zi_answers WHERE assessment_id = ?");
    $deleteAssessmentStmt = mysqli_prepare($connection, "DELETE FROM zi_assessments WHERE id = ?");
    $deleteSucceeded = $deleteAnswersStmt && $deleteAssessmentStmt;

    if ($deleteSucceeded) {
        foreach ($duplicateIds as $duplicateId) {
            mysqli_stmt_bind_param($deleteAnswersStmt, 'i', $duplicateId);
            if (!mysqli_stmt_execute($deleteAnswersStmt)) {
                $deleteSucceeded = false;
                break;
            }

            mysqli_stmt_bind_param($deleteAssessmentStmt, 'i', $duplicateId);
            if (!mysqli_stmt_execute($deleteAssessmentStmt)) {
                $deleteSucceeded = false;
                break;
            }
            $deletedCount += mysqli_stmt_affected_rows($deleteAssessmentStmt) > 0 ? 1 : 0;
        }
    }

    if ($deleteAnswersStmt) {
        mysqli_stmt_close($deleteAnswersStmt);
    }
    if ($deleteAssessmentStmt) {
        mysqli_stmt_close($deleteAssessmentStmt);
    }

    if ($deleteSucceeded) {
        mysqli_commit($connection);
    } else {
        mysqli_rollback($connection);
        $deletedCount = 0;
    }
}

header('Location: admin_scores?' . http_build_query(array(
    'zi_duplicates_deleted' => $deletedCount
)));
exit;
?>
