<?php

if (!function_exists('ensureAdminUsersTable')) {
    function ensureAdminUsersTable($connection) {
        if (!$connection) {
            return;
        }

        $createTableSql = "CREATE TABLE IF NOT EXISTS admin_users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        mysqli_query($connection, $createTableSql);

        $defaultUsername = 'arifa_pasker';
        $checkStmt = mysqli_prepare($connection, "SELECT id FROM admin_users WHERE username = ? LIMIT 1");
        if (!$checkStmt) {
            return;
        }

        mysqli_stmt_bind_param($checkStmt, 's', $defaultUsername);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        $exists = mysqli_stmt_num_rows($checkStmt) > 0;
        mysqli_stmt_close($checkStmt);

        if (!$exists) {
            $defaultPasswordHash = password_hash('PusatpasarKerj4', PASSWORD_DEFAULT);
            $insertStmt = mysqli_prepare($connection, "INSERT INTO admin_users (username, password_hash, is_active) VALUES (?, ?, 1)");
            if ($insertStmt) {
                mysqli_stmt_bind_param($insertStmt, 'ss', $defaultUsername, $defaultPasswordHash);
                mysqli_stmt_execute($insertStmt);
                mysqli_stmt_close($insertStmt);
            }
        }
    }
}

if (!function_exists('countActiveAdminUsers')) {
    function countActiveAdminUsers($connection) {
        if (!$connection) {
            return 0;
        }

        $count = 0;
        $stmt = mysqli_prepare($connection, "SELECT COUNT(*) FROM admin_users WHERE is_active = 1");
        if ($stmt) {
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $activeCount);
            if (mysqli_stmt_fetch($stmt)) {
                $count = intval($activeCount);
            }
            mysqli_stmt_close($stmt);
        }

        return $count;
    }
}

?>
