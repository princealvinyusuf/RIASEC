<?php

function getDefaultRiasecScoreList() {
    return array('R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0);
}

function getRiasecStableOrder() {
    return array('R' => 1, 'I' => 2, 'A' => 3, 'S' => 4, 'E' => 5, 'C' => 6);
}

function sortRiasecScoresStable($scores) {
    $order = getRiasecStableOrder();
    uksort($scores, function ($a, $b) use ($scores, $order) {
        if (!isset($scores[$a], $scores[$b])) {
            return 0;
        }
        if ($scores[$a] === $scores[$b]) {
            return $order[$a] <=> $order[$b];
        }
        return $scores[$b] <=> $scores[$a];
    });
    return $scores;
}

function calculateRiasecPercentages($scores) {
    $sum = array_sum($scores);
    if ($sum <= 0) {
        return getDefaultRiasecScoreList();
    }

    $scorePercentageList = array();
    foreach ($scores as $key => $value) {
        $scorePercentageList[$key] = round(($value / $sum) * 100, 2);
    }
    return $scorePercentageList;
}

function buildRiasecTopCode($sortedScores, $limit = 3) {
    $result = '';
    $counter = 0;
    foreach ($sortedScores as $key => $value) {
        $result .= $key;
        $counter++;
        if ($counter >= $limit) {
            break;
        }
    }
    return $result;
}

function extractRiasecAnswersFromSource($source) {
    $answers = array();
    foreach ($source as $key => $value) {
        if (!is_string($key)) {
            continue;
        }
        if (!preg_match('/^([RIASEC])(\d+)$/', $key, $matches)) {
            continue;
        }

        $category = $matches[1];
        $statementId = intval($matches[2]);
        $answer = intval($value);

        if ($statementId <= 0 || $answer < 1 || $answer > 5) {
            continue;
        }

        $answers[] = array(
            'key' => $key,
            'category' => $category,
            'statement_id' => $statementId,
            'answer' => $answer
        );
    }

    return $answers;
}

function calculateRiasecScoresFromAnswers($answers) {
    $scores = getDefaultRiasecScoreList();
    foreach ($answers as $item) {
        $category = isset($item['category']) ? $item['category'] : '';
        $answer = isset($item['answer']) ? intval($item['answer']) : 0;
        if (isset($scores[$category]) && $answer >= 1 && $answer <= 5) {
            $scores[$category] += $answer;
        }
    }
    return $scores;
}

function getRequiredRiasecAnswerKeys($connection) {
    $requiredKeys = array();
    $res = mysqli_query($connection, "SELECT statement_id, statement_category FROM statements");
    if (!$res) {
        return $requiredKeys;
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $requiredKeys[] = $row['statement_category'] . intval($row['statement_id']);
    }

    return $requiredKeys;
}

function hasCompleteRiasecSubmission($connection, $source) {
    if (!isset($source['can_save_data']) || $source['can_save_data'] !== 'true') {
        return false;
    }

    $requiredKeys = getRequiredRiasecAnswerKeys($connection);
    if (empty($requiredKeys)) {
        return false;
    }

    foreach ($requiredKeys as $name) {
        if (!isset($source[$name])) {
            return false;
        }
        $val = intval($source[$name]);
        if ($val < 1 || $val > 5) {
            return false;
        }
    }
    return true;
}
?>
