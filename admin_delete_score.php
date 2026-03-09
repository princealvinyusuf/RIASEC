<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
  header('Location: admin_login.php');
  exit;
}

include 'includes/db.php';

$scoreIds = array();
$returnQuery = '';

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
            $deletedCount += mysqli_stmt_affected_rows($deleteScoresStmt) > 0 ? 1 : 0;
        }
    }

    if ($deleteAnswersStmt) {
        mysqli_stmt_close($deleteAnswersStmt);
    }
    if ($deleteScoresStmt) {
        mysqli_stmt_close($deleteScoresStmt);
    }
}

$redirectUrl = 'admin_scores.php';
$params = array();
if ($deletedCount > 0) {
    $params['deleted'] = $deletedCount;
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
