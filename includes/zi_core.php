<?php

function getDefaultZiStatementsSeed() {
    return array(
        'Layanan publik di instansi ini disampaikan secara adil tanpa diskriminasi.',
        'Proses pelayanan dilakukan secara transparan dan mudah dipahami.',
        'Petugas memberikan pelayanan dengan ramah, sopan, dan profesional.',
        'Waktu penyelesaian layanan sesuai dengan standar yang diinformasikan.',
        'Informasi biaya layanan tersedia jelas dan tidak ada pungutan liar.',
        'Saya merasa aman dari praktik korupsi, kolusi, dan nepotisme dalam layanan ini.',
        'Mekanisme pengaduan/masukan tersedia dan mudah diakses.',
        'Sarana dan prasarana pendukung layanan dalam kondisi baik.',
        'Petugas merespons pertanyaan atau keluhan dengan cepat.',
        'Secara keseluruhan, saya puas dengan penerapan Zona Integritas di layanan ini.'
    );
}

function ensureZiTablesAndSeed($connection) {
    if (!$connection) {
        return false;
    }

    $createStatementsSql = "CREATE TABLE IF NOT EXISTS zi_statements (
        statement_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        statement_content TEXT NOT NULL,
        sort_order INT UNSIGNED NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($connection, $createStatementsSql);

    $createAssessmentsSql = "CREATE TABLE IF NOT EXISTS zi_assessments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        respondent_name VARCHAR(160) NOT NULL,
        respondent_email VARCHAR(190) NULL,
        average_score DECIMAL(4,2) NOT NULL DEFAULT 0,
        positive_count INT UNSIGNED NOT NULL DEFAULT 0,
        neutral_count INT UNSIGNED NOT NULL DEFAULT 0,
        negative_count INT UNSIGNED NOT NULL DEFAULT 0,
        total_questions INT UNSIGNED NOT NULL DEFAULT 0,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_submitted_at (submitted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($connection, $createAssessmentsSql);

    $createAnswersSql = "CREATE TABLE IF NOT EXISTS zi_answers (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        assessment_id INT UNSIGNED NOT NULL,
        statement_id INT UNSIGNED NOT NULL,
        answer TINYINT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_assessment_id (assessment_id),
        INDEX idx_statement_id (statement_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($connection, $createAnswersSql);

    $countRes = mysqli_query($connection, "SELECT COUNT(*) AS total FROM zi_statements WHERE is_active = 1");
    $activeCount = 0;
    if ($countRes) {
        $countRow = mysqli_fetch_assoc($countRes);
        $activeCount = intval($countRow['total'] ?? 0);
    }

    if ($activeCount === 0) {
        $seed = getDefaultZiStatementsSeed();
        $insertStmt = mysqli_prepare(
            $connection,
            "INSERT INTO zi_statements (statement_content, sort_order, is_active) VALUES (?, ?, 1)"
        );
        if ($insertStmt) {
            foreach ($seed as $index => $content) {
                $sortOrder = $index + 1;
                mysqli_stmt_bind_param($insertStmt, 'si', $content, $sortOrder);
                mysqli_stmt_execute($insertStmt);
            }
            mysqli_stmt_close($insertStmt);
        }
    }

    return true;
}

function getZiStatements($connection) {
    $items = array();
    if (!$connection) {
        return $items;
    }

    $sql = "SELECT statement_id, statement_content
            FROM zi_statements
            WHERE is_active = 1
            ORDER BY sort_order ASC, statement_id ASC";
    $res = mysqli_query($connection, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $items[] = $row;
        }
    }

    return $items;
}

function extractZiAnswersFromSource($source) {
    $answers = array();
    if (!is_array($source)) {
        return $answers;
    }

    foreach ($source as $key => $value) {
        if (!preg_match('/^ZI_(\d+)$/', (string)$key, $matches)) {
            continue;
        }
        $statementId = intval($matches[1]);
        $answerValue = intval($value);
        $answers[] = array(
            'statement_id' => $statementId,
            'answer' => $answerValue
        );
    }

    return $answers;
}

function hasCompleteZiSubmission($statements, $source) {
    if (!is_array($source) || !is_array($statements) || empty($statements)) {
        return false;
    }

    if (!isset($source['can_save_data']) || $source['can_save_data'] !== 'true') {
        return false;
    }

    foreach ($statements as $statement) {
        $statementId = intval($statement['statement_id'] ?? 0);
        if ($statementId <= 0) {
            return false;
        }
        $fieldName = 'ZI_' . $statementId;
        if (!isset($source[$fieldName])) {
            return false;
        }
        $answerValue = intval($source[$fieldName]);
        if ($answerValue < 1 || $answerValue > 5) {
            return false;
        }
    }

    return true;
}

function calculateZiAssessmentSummary($answers) {
    $total = count($answers);
    if ($total <= 0) {
        return array(
            'average_score' => 0,
            'positive_count' => 0,
            'neutral_count' => 0,
            'negative_count' => 0,
            'total_questions' => 0
        );
    }

    $sum = 0;
    $positive = 0;
    $neutral = 0;
    $negative = 0;

    foreach ($answers as $answerItem) {
        $score = intval($answerItem['answer'] ?? 0);
        $sum += $score;
        if ($score >= 4) {
            $positive++;
        } elseif ($score === 3) {
            $neutral++;
        } else {
            $negative++;
        }
    }

    return array(
        'average_score' => round($sum / $total, 2),
        'positive_count' => $positive,
        'neutral_count' => $neutral,
        'negative_count' => $negative,
        'total_questions' => $total
    );
}

function insertZiAssessment($connection, $respondentName, $respondentEmail, $answers, $summary) {
    if (!$connection || $respondentName === '' || empty($answers)) {
        return 0;
    }

    mysqli_begin_transaction($connection);

    $assessmentId = 0;
    $insertAssessmentStmt = mysqli_prepare(
        $connection,
        "INSERT INTO zi_assessments (
            respondent_name,
            respondent_email,
            average_score,
            positive_count,
            neutral_count,
            negative_count,
            total_questions
        ) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$insertAssessmentStmt) {
        mysqli_rollback($connection);
        return 0;
    }

    $averageScore = floatval($summary['average_score'] ?? 0);
    $positiveCount = intval($summary['positive_count'] ?? 0);
    $neutralCount = intval($summary['neutral_count'] ?? 0);
    $negativeCount = intval($summary['negative_count'] ?? 0);
    $totalQuestions = intval($summary['total_questions'] ?? 0);
    $emailValue = $respondentEmail !== '' ? $respondentEmail : null;

    mysqli_stmt_bind_param(
        $insertAssessmentStmt,
        'ssdiiii',
        $respondentName,
        $emailValue,
        $averageScore,
        $positiveCount,
        $neutralCount,
        $negativeCount,
        $totalQuestions
    );

    if (!mysqli_stmt_execute($insertAssessmentStmt)) {
        mysqli_stmt_close($insertAssessmentStmt);
        mysqli_rollback($connection);
        return 0;
    }

    $assessmentId = intval(mysqli_insert_id($connection));
    mysqli_stmt_close($insertAssessmentStmt);
    if ($assessmentId <= 0) {
        mysqli_rollback($connection);
        return 0;
    }

    $insertAnswerStmt = mysqli_prepare(
        $connection,
        "INSERT INTO zi_answers (assessment_id, statement_id, answer) VALUES (?, ?, ?)"
    );
    if (!$insertAnswerStmt) {
        mysqli_rollback($connection);
        return 0;
    }

    foreach ($answers as $answerItem) {
        $statementId = intval($answerItem['statement_id'] ?? 0);
        $answerValue = intval($answerItem['answer'] ?? 0);
        if ($statementId <= 0 || $answerValue < 1 || $answerValue > 5) {
            mysqli_stmt_close($insertAnswerStmt);
            mysqli_rollback($connection);
            return 0;
        }

        mysqli_stmt_bind_param($insertAnswerStmt, 'iii', $assessmentId, $statementId, $answerValue);
        if (!mysqli_stmt_execute($insertAnswerStmt)) {
            mysqli_stmt_close($insertAnswerStmt);
            mysqli_rollback($connection);
            return 0;
        }
    }

    mysqli_stmt_close($insertAnswerStmt);
    mysqli_commit($connection);
    return $assessmentId;
}

?>
