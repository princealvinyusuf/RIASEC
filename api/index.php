<?php
include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/_helpers.php';
include_once __DIR__ . '/user_service.php';

if (!$connection) {
    apiError(500, 'Database connection is not available.');
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$relativePath = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $requestPath);
$segments = array_values(array_filter(explode('/', trim($relativePath, '/'))));

if (count($segments) < 2 || $segments[0] !== 'v1') {
    apiError(404, 'API route not found.');
}

$resource = isset($segments[1]) ? $segments[1] : '';

if ($resource === 'participants') {
    if ($method !== 'POST') {
        apiMethodNotAllowed($method, array('POST'));
    }
    $payload = apiReadJsonBody();
    $created = createParticipantWithToken($connection, $payload);
    if (isset($created['errors'])) {
        apiError(422, 'Validation failed.', $created['errors']);
    }
    apiCreated($created['data'], 'Participant created.');
}

if ($resource === 'riasec' && isset($segments[2]) && $segments[2] === 'statements') {
    if ($method !== 'GET') {
        apiMethodNotAllowed($method, array('GET'));
    }
    apiOk(array('items' => getStatements($connection)));
}

if ($resource === 'riasec' && isset($segments[2]) && $segments[2] === 'assessments' && count($segments) === 3) {
    if ($method !== 'POST') {
        apiMethodNotAllowed($method, array('POST'));
    }
    $token = apiGetBearerToken();
    $participantId = getParticipantFromToken($connection, $token);
    if (!$participantId) {
        apiError(401, 'Unauthorized token.');
    }

    $payload = apiReadJsonBody();
    $assessment = buildAssessmentFromPayload($connection, $payload);
    if (isset($assessment['errors'])) {
        apiError(422, 'Validation failed.', $assessment['errors']);
    }

    $computed = $assessment['data'];
    $scoreId = insertAssessmentResult(
        $connection,
        $participantId,
        $computed['result_personality'],
        $computed['percentages'],
        $computed['answers']
    );
    if ($scoreId <= 0) {
        apiError(500, 'Failed to save assessment result.');
    }

    apiCreated(array(
        'score_id' => $scoreId,
        'result_personality' => $computed['result_personality'],
        'score_percentage_list' => $computed['percentages']
    ), 'Assessment submitted.');
}

if ($resource === 'riasec' && isset($segments[2]) && $segments[2] === 'assessments' && count($segments) === 4) {
    if ($method !== 'GET') {
        apiMethodNotAllowed($method, array('GET'));
    }
    $scoreId = intval($segments[3]);
    if ($scoreId <= 0) {
        apiError(422, 'Invalid score id.');
    }
    $token = apiGetBearerToken();
    $participantId = getParticipantFromToken($connection, $token);
    if (!$participantId) {
        apiError(401, 'Unauthorized token.');
    }
    $assessment = getAssessmentByScoreId($connection, $scoreId, $participantId);
    if (!$assessment) {
        apiError(404, 'Assessment not found.');
    }
    apiOk($assessment);
}

if ($resource === 'riasec' && isset($segments[2]) && $segments[2] === 'assessments' && count($segments) === 5 && $segments[4] === 'recommendations') {
    if ($method !== 'GET') {
        apiMethodNotAllowed($method, array('GET'));
    }
    $scoreId = intval($segments[3]);
    if ($scoreId <= 0) {
        apiError(422, 'Invalid score id.');
    }
    $token = apiGetBearerToken();
    $participantId = getParticipantFromToken($connection, $token);
    if (!$participantId) {
        apiError(401, 'Unauthorized token.');
    }
    $assessment = getAssessmentByScoreId($connection, $scoreId, $participantId);
    if (!$assessment) {
        apiError(404, 'Assessment not found.');
    }
    $recommendationPayload = getRiasecRecommendationPayload($assessment['result_personality'], $assessment['score_percentage_list']);
    apiOk(array(
        'score_id' => $assessment['score_id'],
        'result_personality' => $assessment['result_personality'],
        'top_codes' => $recommendationPayload['top_codes'],
        'career_recommendations' => $recommendationPayload['career_recommendations'],
        'training_recommendations' => $recommendationPayload['training_recommendations'],
        'training_tier_summary' => $recommendationPayload['training_tier_summary']
    ));
}

apiError(404, 'API route not found.');
?>
