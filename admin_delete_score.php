<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
  header('Location: admin_login');
  exit;
}

include 'includes/db.php';

$scoreIds = array();
$returnQuery = '';
$deleteUnknown = isset($_POST['delete_unknown']) && $_POST['delete_unknown'] === '1';

if (isset($_GET['return_query'])) {
    $returnQuery = trim((string)$_GET['return_query']);
} elseif (isset($_POST['return_query'])) {
    $returnQuery = trim((string)$_POST['return_query']);
}

if (isset($_GET['score_id'])) {
    $scoreId = intval($_GET['score_id']);
    if ($scoreId > 0) {
        $scoreIds[] = $scoreId;
    }
}

if (isset($_POST['score_ids']) && is_array($_POST['score_ids'])) {
    foreach ($_POST['score_ids'] as $postedId) {
        $scoreId = intval($postedId);
        if ($scoreId > 0) {
            $scoreIds[] = $scoreId;
        }
    }
}

$unknownDeletedCount = 0;
if ($deleteUnknown) {
    $unknownIds = array();
    $unknownSql = "SELECT pts.id AS score_id
                   FROM personality_test_scores pts
                   LEFT JOIN personal_info pi ON pi.id = pts.personal_info_id
                   WHERE
                     pi.id IS NULL
                     OR TRIM(COALESCE(pi.full_name, '')) IN ('', '-')
                     OR TRIM(COALESCE(pi.email, '')) IN ('', '-')
                     OR TRIM(COALESCE(pi.class_level, '')) IN ('', '-')
                     OR TRIM(COALESCE(pi.school_name, '')) IN ('', '-')";
    $unknownRes = mysqli_query($connection, $unknownSql);
    if ($unknownRes) {
        while ($unknownRow = mysqli_fetch_assoc($unknownRes)) {
            $unknownId = intval($unknownRow['score_id']);
            if ($unknownId > 0) {
                $unknownIds[] = $unknownId;
            }
        }
    }
    $scoreIds = array_merge($scoreIds, $unknownIds);
}

$scoreIds = array_values(array_unique($scoreIds));
$deletedCount = 0;

if (!empty($scoreIds)) {
    $deleteAnswersStmt = mysqli_prepare($connection, "DELETE FROM test_answers WHERE score_id = ?");
    $deleteScoresStmt = mysqli_prepare($connection, "DELETE FROM personality_test_scores WHERE id = ?");

    if ($deleteScoresStmt) {
        foreach ($scoreIds as $scoreId) {
            if ($deleteAnswersStmt) {
                mysqli_stmt_bind_param($deleteAnswersStmt, "i", $scoreId);
                mysqli_stmt_execute($deleteAnswersStmt);
            }

            mysqli_stmt_bind_param($deleteScoresStmt, "i", $scoreId);
            mysqli_stmt_execute($deleteScoresStmt);
            $isDeleted = mysqli_stmt_affected_rows($deleteScoresStmt) > 0;
            $deletedCount += $isDeleted ? 1 : 0;
            if ($deleteUnknown && $isDeleted) {
                $unknownDeletedCount++;
            }
        }
    }

    if ($deleteAnswersStmt) {
        mysqli_stmt_close($deleteAnswersStmt);
    }
    if ($deleteScoresStmt) {
        mysqli_stmt_close($deleteScoresStmt);
    }
}

$redirectUrl = 'admin_scores';
$params = array();
if ($deletedCount > 0) {
    $params['deleted'] = $deletedCount;
}
if ($unknownDeletedCount > 0) {
    $params['deleted_unknown'] = $unknownDeletedCount;
}
if ($returnQuery !== '') {
    parse_str($returnQuery, $parsedReturnQuery);
    if (is_array($parsedReturnQuery)) {
        $params = array_merge($parsedReturnQuery, $params);
    }
}
if (!empty($params)) {
    $redirectUrl .= '?' . http_build_query($params);
}

header('Location: ' . $redirectUrl);
exit;
?>
