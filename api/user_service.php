<?php
include_once __DIR__ . '/../includes/riasec_core.php';
include_once __DIR__ . '/../includes/riasec_recommendations.php';

function apiNormalizeInputText($value) {
    $value = trim((string)$value);
    $value = preg_replace('/\s+/', ' ', $value);
    return $value;
}

function apiIsMeaningfulText($value, $minLetters = 2) {
    $value = apiNormalizeInputText($value);
    if ($value === '') {
        return false;
    }
    if (!preg_match('/[\p{L}\p{N}]/u', $value)) {
        return false;
    }
    preg_match_all('/[\p{L}]/u', $value, $matches);
    return count($matches[0]) >= $minLetters;
}

function ensureMobileApiTables($connection) {
    $createApiSession = "CREATE TABLE IF NOT EXISTS api_participant_sessions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        personal_info_id INT UNSIGNED NOT NULL,
        access_token VARCHAR(80) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_personal_info_id (personal_info_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($connection, $createApiSession);
}

function validateParticipantPayload($payload) {
    $errors = array();
    $allowedEducationLevels = array('10', '11', '12', 'Universitas');

    $full_name = apiNormalizeInputText(apiGetInputValue($payload, 'full_name'));
    $birth_date = trim((string)apiGetInputValue($payload, 'birth_date'));
    $phone = apiNormalizeInputText(apiGetInputValue($payload, 'phone'));
    $email = apiNormalizeInputText(apiGetInputValue($payload, 'email'));
    $class_level = trim((string)apiGetInputValue($payload, 'class_level'));
    $school_name = apiNormalizeInputText(apiGetInputValue($payload, 'school_name'));
    $extracurricular = apiNormalizeInputText(apiGetInputValue($payload, 'extracurricular'));
    $organization = apiNormalizeInputText(apiGetInputValue($payload, 'organization'));

    if ($full_name === '') { $errors['full_name'] = 'Nama Lengkap wajib diisi.'; }
    if ($birth_date === '') { $errors['birth_date'] = 'Tanggal Lahir wajib diisi.'; }
    if ($phone === '') { $errors['phone'] = 'No. HP wajib diisi.'; }
    if ($email === '') { $errors['email'] = 'E-mail wajib diisi.'; }
    if ($class_level === '') { $errors['class_level'] = 'Jenjang Pendidikan wajib dipilih.'; }
    if ($school_name === '') { $errors['school_name'] = 'Nama Sekolah/Institusi/Universitas wajib diisi.'; }
    if ($extracurricular === '') { $errors['extracurricular'] = 'Ekstrakurikuler wajib diisi.'; }
    if ($organization === '') { $errors['organization'] = 'Organisasi wajib diisi.'; }

    if ($full_name !== '' && !apiIsMeaningfulText($full_name, 3)) {
        $errors['full_name'] = 'Nama Lengkap tidak valid.';
    }
    if ($school_name !== '' && !apiIsMeaningfulText($school_name, 3)) {
        $errors['school_name'] = 'Nama Sekolah/Institusi/Universitas tidak valid.';
    }
    if ($extracurricular !== '' && !apiIsMeaningfulText($extracurricular, 3)) {
        $errors['extracurricular'] = 'Ekstrakurikuler tidak valid.';
    }
    if ($organization !== '' && !apiIsMeaningfulText($organization, 3)) {
        $errors['organization'] = 'Organisasi tidak valid.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format E-mail tidak valid.';
    }

    if ($phone !== '') {
        $phoneDigits = preg_replace('/\D+/', '', $phone);
        if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
            $errors['phone'] = 'Nomor HP harus 10 sampai 15 digit.';
        } else {
            $phone = $phoneDigits;
        }
    }

    if ($class_level !== '' && !in_array($class_level, $allowedEducationLevels, true)) {
        $errors['class_level'] = 'Jenjang Pendidikan tidak valid.';
    }

    if ($birth_date !== '') {
        $birthTimestamp = strtotime($birth_date);
        $todayTimestamp = strtotime(date('Y-m-d'));
        if ($birthTimestamp === false) {
            $errors['birth_date'] = 'Tanggal Lahir tidak valid.';
        } elseif ($birthTimestamp > $todayTimestamp) {
            $errors['birth_date'] = 'Tanggal Lahir tidak boleh di masa depan.';
        } else {
            $age = (int)date('Y') - (int)date('Y', $birthTimestamp);
            if ($age < 10 || $age > 100) {
                $errors['birth_date'] = 'Tanggal Lahir tidak masuk akal untuk peserta asesmen.';
            }
        }
    }

    return array(
        'errors' => $errors,
        'sanitized' => array(
            'full_name' => $full_name,
            'birth_date' => $birth_date,
            'phone' => $phone,
            'email' => $email,
            'class_level' => $class_level,
            'school_name' => $school_name,
            'extracurricular' => $extracurricular,
            'organization' => $organization
        )
    );
}

function createParticipantWithToken($connection, $payload) {
    ensureMobileApiTables($connection);
    mysqli_query($connection, "ALTER TABLE personal_info MODIFY class_level ENUM('10','11','12','Universitas') NOT NULL");
    $validation = validateParticipantPayload($payload);
    if (!empty($validation['errors'])) {
        return array('errors' => $validation['errors']);
    }

    $data = $validation['sanitized'];
    $stmt = mysqli_prepare(
        $connection,
        "INSERT INTO personal_info (full_name, birth_date, phone, email, class_level, school_name, extracurricular, organization)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmt) {
        return array('errors' => array('server' => 'Gagal mempersiapkan penyimpanan peserta.'));
    }

    mysqli_stmt_bind_param(
        $stmt,
        'ssssssss',
        $data['full_name'],
        $data['birth_date'],
        $data['phone'],
        $data['email'],
        $data['class_level'],
        $data['school_name'],
        $data['extracurricular'],
        $data['organization']
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return array('errors' => array('server' => 'Gagal menyimpan peserta.'));
    }
    mysqli_stmt_close($stmt);

    $participantId = intval(mysqli_insert_id($connection));
    $token = bin2hex(random_bytes(24));
    $tokenStmt = mysqli_prepare(
        $connection,
        "INSERT INTO api_participant_sessions (personal_info_id, access_token) VALUES (?, ?)"
    );
    if (!$tokenStmt) {
        return array('errors' => array('server' => 'Gagal membuat sesi API.'));
    }
    mysqli_stmt_bind_param($tokenStmt, 'is', $participantId, $token);
    if (!mysqli_stmt_execute($tokenStmt)) {
        mysqli_stmt_close($tokenStmt);
        return array('errors' => array('server' => 'Gagal menyimpan sesi API.'));
    }
    mysqli_stmt_close($tokenStmt);

    return array(
        'data' => array(
            'participant_id' => $participantId,
            'access_token' => $token
        )
    );
}

function getParticipantFromToken($connection, $token) {
    if (!is_string($token) || trim($token) === '') {
        return null;
    }
    ensureMobileApiTables($connection);
    $stmt = mysqli_prepare(
        $connection,
        "SELECT personal_info_id FROM api_participant_sessions WHERE access_token = ? LIMIT 1"
    );
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $personalInfoId);
    $found = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$found) {
        return null;
    }
    return intval($personalInfoId);
}

function getStatements($connection) {
    $query = "SELECT statement_id, statement_content, statement_category FROM statements ORDER BY statement_id ASC";
    $statementSelectQuery = mysqli_query($connection, $query);
    $questions = array();
    if ($statementSelectQuery) {
        while ($row = mysqli_fetch_assoc($statementSelectQuery)) {
            $questions[] = array(
                'statement_id' => intval($row['statement_id']),
                'statement_content' => $row['statement_content'],
                'statement_category' => $row['statement_category']
            );
        }
    }
    return $questions;
}

function insertAssessmentResult($connection, $personalInfoId, $resultPersonality, $scorePercentages, $answers) {
    $insertScoreStmt = mysqli_prepare(
        $connection,
        "INSERT INTO personality_test_scores (personal_info_id, realistic, investigative, artistic, social, enterprising, conventional, result)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$insertScoreStmt) {
        return 0;
    }
    mysqli_stmt_bind_param(
        $insertScoreStmt,
        'idddddds',
        $personalInfoId,
        $scorePercentages['R'],
        $scorePercentages['I'],
        $scorePercentages['A'],
        $scorePercentages['S'],
        $scorePercentages['E'],
        $scorePercentages['C'],
        $resultPersonality
    );
    if (!mysqli_stmt_execute($insertScoreStmt)) {
        mysqli_stmt_close($insertScoreStmt);
        return 0;
    }
    mysqli_stmt_close($insertScoreStmt);

    $scoreId = intval(mysqli_insert_id($connection));

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

    $answerStmt = mysqli_prepare(
        $connection,
        "INSERT INTO test_answers (score_id, personal_info_id, statement_id, statement_category, answer)
         VALUES (?, ?, ?, ?, ?)"
    );
    if (!$answerStmt) {
        return $scoreId;
    }
    foreach ($answers as $item) {
        $statementIdValue = intval($item['statement_id']);
        $statementCategoryValue = $item['category'];
        $answerValue = intval($item['answer']);
        mysqli_stmt_bind_param(
            $answerStmt,
            'iiisi',
            $scoreId,
            $personalInfoId,
            $statementIdValue,
            $statementCategoryValue,
            $answerValue
        );
        mysqli_stmt_execute($answerStmt);
    }
    mysqli_stmt_close($answerStmt);
    return $scoreId;
}

function buildAssessmentFromPayload($connection, $payload) {
    $answersPayload = apiGetInputValue($payload, 'answers', array());
    $canSaveData = apiGetInputValue($payload, 'can_save_data', false);
    if (!is_array($answersPayload)) {
        return array('errors' => array('answers' => 'Field answers wajib berupa object.'));
    }

    $source = $answersPayload;
    $source['can_save_data'] = $canSaveData ? 'true' : 'false';
    if (!hasCompleteRiasecSubmission($connection, $source)) {
        return array('errors' => array('answers' => 'Jawaban tidak lengkap atau consent belum diaktifkan.'));
    }

    $answers = extractRiasecAnswersFromSource($answersPayload);
    $sortedScores = sortRiasecScoresStable(calculateRiasecScoresFromAnswers($answers));
    $scorePercentages = calculateRiasecPercentages($sortedScores);
    $resultPersonality = buildRiasecTopCode($sortedScores, 3);

    return array(
        'data' => array(
            'answers' => $answers,
            'scores' => $sortedScores,
            'percentages' => $scorePercentages,
            'result_personality' => $resultPersonality
        )
    );
}

function getAssessmentByScoreId($connection, $scoreId, $participantId) {
    $stmt = mysqli_prepare(
        $connection,
        "SELECT id, realistic, investigative, artistic, social, enterprising, conventional, result, created_at
         FROM personality_test_scores WHERE id = ? AND personal_info_id = ? LIMIT 1"
    );
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $scoreId, $participantId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    if (!$row) {
        return null;
    }

    return array(
        'score_id' => intval($row['id']),
        'result_personality' => $row['result'],
        'score_percentage_list' => array(
            'R' => floatval($row['realistic']),
            'I' => floatval($row['investigative']),
            'A' => floatval($row['artistic']),
            'S' => floatval($row['social']),
            'E' => floatval($row['enterprising']),
            'C' => floatval($row['conventional'])
        ),
        'created_at' => $row['created_at']
    );
}
?>
