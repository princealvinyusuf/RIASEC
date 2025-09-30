<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['is_admin'])) {
  header('Location: admin_login.php');
  exit;
}

include 'includes/db.php';

if (isset($_GET['score_id'])) {
    $score_id = intval($_GET['score_id']);

    if ($score_id > 0) {
        $query = "DELETE FROM personality_test_scores WHERE id = ?";
        
        $stmt = mysqli_prepare($connection, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $score_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

header('Location: admin_scores.php');
exit;
?>
