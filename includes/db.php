<?php

$db['db_host']="localhost";
$db['db_user']="root";
$db['db_pass']="";
$db['db_name']="riasec_test";
foreach($db as $key => $value){
    define(strtoupper($key),$value);
}

function shouldEnforceVisitorLimit() {
    if (PHP_SAPI === 'cli') {
        return false;
    }

    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? basename((string)$_SERVER['SCRIPT_NAME']) : '';
    if (strpos($scriptName, 'admin_') === 0) {
        return false;
    }

    if ($scriptName === 'generate_pdf.php' || $scriptName === 'generate_excel.php') {
        return false;
    }

    $requestPath = parse_url(isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH);
    $requestPath = '/' . ltrim((string)$requestPath, '/');
    if (preg_match('#/api(?:/|$)#', $requestPath)) {
        return false;
    }

    return true;
}

function buildVisitorKey() {
    $ip = '';
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwardedIps = explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($forwardedIps[0]);
    }

    if ($ip === '' && !empty($_SERVER['REMOTE_ADDR'])) {
        $ip = trim((string)$_SERVER['REMOTE_ADDR']);
    }

    if ($ip === '') {
        $ip = 'unknown-ip';
    }

    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? trim((string)$_SERVER['HTTP_USER_AGENT']) : 'unknown-agent';
    return hash('sha256', $ip . '|' . $userAgent);
}

function enforceVisitorLimit($connection) {
    if (!$connection || !shouldEnforceVisitorLimit() || headers_sent()) {
        return;
    }

    $maxConcurrentVisitors = 50;
    $activeWindowMinutes = 30;
    $redirectUrl = 'https://paskerid.freedev.app/';

    $createTableSql = "CREATE TABLE IF NOT EXISTS active_visitors (
        visitor_key CHAR(64) NOT NULL PRIMARY KEY,
        first_seen DATETIME NOT NULL,
        last_seen DATETIME NOT NULL,
        INDEX idx_last_seen (last_seen)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    if (!mysqli_query($connection, $createTableSql)) {
        return;
    }

    $now = date('Y-m-d H:i:s');
    $windowStart = date('Y-m-d H:i:s', time() - ($activeWindowMinutes * 60));
    $visitorKey = buildVisitorKey();

    $windowStartEscaped = mysqli_real_escape_string($connection, $windowStart);
    mysqli_query($connection, "DELETE FROM active_visitors WHERE last_seen < '{$windowStartEscaped}'");

    $isExistingActiveVisitor = false;
    $existingStmt = mysqli_prepare(
        $connection,
        "SELECT 1 FROM active_visitors WHERE visitor_key = ? AND last_seen >= ? LIMIT 1"
    );
    if ($existingStmt) {
        mysqli_stmt_bind_param($existingStmt, 'ss', $visitorKey, $windowStart);
        mysqli_stmt_execute($existingStmt);
        mysqli_stmt_store_result($existingStmt);
        $isExistingActiveVisitor = mysqli_stmt_num_rows($existingStmt) > 0;
        mysqli_stmt_close($existingStmt);
    }

    if (!$isExistingActiveVisitor) {
        $countQuery = mysqli_query(
            $connection,
            "SELECT COUNT(*) AS active_count FROM active_visitors WHERE last_seen >= '{$windowStartEscaped}'"
        );
        $activeCount = 0;
        if ($countQuery) {
            $row = mysqli_fetch_assoc($countQuery);
            $activeCount = isset($row['active_count']) ? intval($row['active_count']) : 0;
        }

        if ($activeCount >= $maxConcurrentVisitors) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    $upsertStmt = mysqli_prepare(
        $connection,
        "INSERT INTO active_visitors (visitor_key, first_seen, last_seen)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE last_seen = VALUES(last_seen)"
    );
    if ($upsertStmt) {
        mysqli_stmt_bind_param($upsertStmt, 'sss', $visitorKey, $now, $now);
        mysqli_stmt_execute($upsertStmt);
        mysqli_stmt_close($upsertStmt);
    }
}

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$connection) {
    error_log('Database connection failed: ' . mysqli_connect_error());
} else {
    enforceVisitorLimit($connection);
}
?>
