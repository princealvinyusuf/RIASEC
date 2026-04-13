<?php

function apiJson($statusCode, $payload) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function apiOk($data, $message = 'OK') {
    apiJson(200, array('success' => true, 'message' => $message, 'data' => $data));
}

function apiCreated($data, $message = 'Created') {
    apiJson(201, array('success' => true, 'message' => $message, 'data' => $data));
}

function apiError($statusCode, $message, $errors = array()) {
    apiJson($statusCode, array(
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ));
}

function apiMethodNotAllowed($method, $allowed) {
    header('Allow: ' . implode(', ', $allowed));
    apiError(405, 'Method ' . $method . ' is not allowed.');
}

function apiReadJsonBody() {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return array();
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        apiError(400, 'Invalid JSON payload.');
    }
    return $decoded;
}

function apiGetBearerToken() {
    $headers = function_exists('getallheaders') ? getallheaders() : array();
    $authHeader = '';
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            $authHeader = trim((string)$value);
            break;
        }
    }

    if ($authHeader === '' || stripos($authHeader, 'Bearer ') !== 0) {
        return '';
    }

    return trim(substr($authHeader, 7));
}

function apiGetInputValue($payload, $key, $default = '') {
    if (!is_array($payload) || !array_key_exists($key, $payload)) {
        return $default;
    }
    return $payload[$key];
}
?>
