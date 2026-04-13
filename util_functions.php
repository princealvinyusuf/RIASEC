<?php
include_once __DIR__ . '/includes/riasec_core.php';
$scoreList = array('R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0);
$scorePercentageList = array('R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0);
$result_personality = '';

function getRiasecLabels() {
    return array(
        'R' => 'Realistic',
        'I' => 'Investigative',
        'A' => 'Artistic',
        'S' => 'Social',
        'E' => 'Enterprising',
        'C' => 'Conventional'
    );
}

function extractAnswersFromPost() {
    return extractRiasecAnswersFromSource($_POST);
}

/* for RIASEC test result */
function getPersonalityTestResults() {
    global $scoreList, $result_personality, $scorePercentageList;

    if (!isset($_POST['submit']) || !isSubmissionComplete()) {
        header("Location: test_form?message=REQ");
        exit;
    }

    $scoreList = getDefaultRiasecScoreList();
    $answers = extractAnswersFromPost();
    $scoreList = sortRiasecScoresStable(calculateRiasecScoresFromAnswers($answers));
    calculateScoreInPercentage($scoreList);
    $result_personality = buildRiasecTopCode($scoreList, 3);

    if (isset($_POST['can_save_data']) && $_POST['can_save_data'] === 'true') {
        insertTestResults($result_personality, $answers);
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['test_completed'] = true;
    $_SESSION['result_personality'] = $result_personality;
    $_SESSION['score_percentage_list'] = $scorePercentageList;
}

function isSubmissionComplete() {
    global $connection;
    return hasCompleteRiasecSubmission($connection, $_POST);
}

/* for calculating percentage scores of each personality */
function calculateScoreInPercentage($scores) {
    global $scorePercentageList;
    $scorePercentageList = calculateRiasecPercentages($scores);
}

// To insert data into database for research purposes
function insertTestResults($result, $answers) {
    global $scorePercentageList, $connection;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $personalInfoId = isset($_SESSION['personal_info_id']) ? intval($_SESSION['personal_info_id']) : null;

    // Ensure linking column exists
    $colRes = mysqli_query($connection, "SHOW COLUMNS FROM personality_test_scores LIKE 'personal_info_id'");
    if ($colRes && mysqli_num_rows($colRes) === 0) {
        mysqli_query($connection, "ALTER TABLE personality_test_scores ADD COLUMN personal_info_id INT UNSIGNED NULL");
    }

    // Optional timestamp column for ordering
    $tsRes = mysqli_query($connection, "SHOW COLUMNS FROM personality_test_scores LIKE 'created_at'");
    if ($tsRes && mysqli_num_rows($tsRes) === 0) {
        mysqli_query($connection, "ALTER TABLE personality_test_scores ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }

    if ($personalInfoId !== null) {
        $insertScoreStmt = mysqli_prepare(
            $connection,
            "INSERT INTO personality_test_scores (personal_info_id, realistic, investigative, artistic, social, enterprising, conventional, result)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if ($insertScoreStmt) {
            mysqli_stmt_bind_param(
                $insertScoreStmt,
                'idddddds',
                $personalInfoId,
                $scorePercentageList['R'],
                $scorePercentageList['I'],
                $scorePercentageList['A'],
                $scorePercentageList['S'],
                $scorePercentageList['E'],
                $scorePercentageList['C'],
                $result
            );
            mysqli_stmt_execute($insertScoreStmt);
            mysqli_stmt_close($insertScoreStmt);
        }
    } else {
        $insertScoreStmt = mysqli_prepare(
            $connection,
            "INSERT INTO personality_test_scores (realistic, investigative, artistic, social, enterprising, conventional, result)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        if ($insertScoreStmt) {
            mysqli_stmt_bind_param(
                $insertScoreStmt,
                'dddddds',
                $scorePercentageList['R'],
                $scorePercentageList['I'],
                $scorePercentageList['A'],
                $scorePercentageList['S'],
                $scorePercentageList['E'],
                $scorePercentageList['C'],
                $result
            );
            mysqli_stmt_execute($insertScoreStmt);
            mysqli_stmt_close($insertScoreStmt);
        }
    }

    $scoreId = mysqli_insert_id($connection);
    $_SESSION['latest_score_id'] = $scoreId;

    // Create table to store detailed answers if it does not exist
    $createAnswers = "CREATE TABLE IF NOT EXISTS test_answers (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        score_id INT UNSIGNED NOT NULL,
        personal_info_id INT UNSIGNED NULL,
        statement_id INT UNSIGNED NOT NULL,
        statement_category CHAR(1) NOT NULL,
        answer TINYINT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_score_id (score_id),
        INDEX idx_personal_info_id (personal_info_id),
        INDEX idx_statement (statement_id, statement_category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($connection, $createAnswers);

    if (!$scoreId) {
        return;
    }

    $answerStmt = mysqli_prepare(
        $connection,
        "INSERT INTO test_answers (score_id, personal_info_id, statement_id, statement_category, answer)
         VALUES (?, ?, ?, ?, ?)"
    );
    if (!$answerStmt) {
        return;
    }

    foreach ($answers as $item) {
        $scoreIdValue = intval($scoreId);
        $personalIdValue = $personalInfoId !== null ? intval($personalInfoId) : null;
        $statementIdValue = intval($item['statement_id']);
        $statementCategoryValue = $item['category'];
        $answerValue = intval($item['answer']);

        mysqli_stmt_bind_param(
            $answerStmt,
            'iiisi',
            $scoreIdValue,
            $personalIdValue,
            $statementIdValue,
            $statementCategoryValue,
            $answerValue
        );
        mysqli_stmt_execute($answerStmt);
    }
    mysqli_stmt_close($answerStmt);
}
?>
