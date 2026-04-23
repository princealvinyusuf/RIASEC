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
            admin_level VARCHAR(20) NOT NULL DEFAULT 'staff',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        mysqli_query($connection, $createTableSql);

        $adminLevelColumnSql = "SHOW COLUMNS FROM admin_users LIKE 'admin_level'";
        $adminLevelColumnRes = mysqli_query($connection, $adminLevelColumnSql);
        $hasAdminLevelColumn = $adminLevelColumnRes && mysqli_num_rows($adminLevelColumnRes) > 0;
        if (!$hasAdminLevelColumn) {
            mysqli_query($connection, "ALTER TABLE admin_users ADD COLUMN admin_level VARCHAR(20) NOT NULL DEFAULT 'staff' AFTER password_hash");
        }

        mysqli_query($connection, "UPDATE admin_users SET admin_level = 'staff' WHERE admin_level IS NULL OR admin_level NOT IN ('super_admin', 'staff')");

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
            $insertStmt = mysqli_prepare($connection, "INSERT INTO admin_users (username, password_hash, admin_level, is_active) VALUES (?, ?, 'super_admin', 1)");
            if ($insertStmt) {
                mysqli_stmt_bind_param($insertStmt, 'ss', $defaultUsername, $defaultPasswordHash);
                mysqli_stmt_execute($insertStmt);
                mysqli_stmt_close($insertStmt);
            }
        }

        $upgradeDefaultStmt = mysqli_prepare($connection, "UPDATE admin_users SET admin_level = 'super_admin' WHERE username = ?");
        if ($upgradeDefaultStmt) {
            mysqli_stmt_bind_param($upgradeDefaultStmt, 's', $defaultUsername);
            mysqli_stmt_execute($upgradeDefaultStmt);
            mysqli_stmt_close($upgradeDefaultStmt);
        }

        $superCountRes = mysqli_query($connection, "SELECT COUNT(*) AS total FROM admin_users WHERE admin_level = 'super_admin'");
        $superCountRow = $superCountRes ? mysqli_fetch_assoc($superCountRes) : array('total' => 0);
        $superAdminCount = intval($superCountRow['total']);
        if ($superAdminCount <= 0) {
            mysqli_query($connection, "UPDATE admin_users SET admin_level = 'super_admin' ORDER BY id ASC LIMIT 1");
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

if (!function_exists('countSuperAdminUsers')) {
    function countSuperAdminUsers($connection) {
        if (!$connection) {
            return 0;
        }

        $count = 0;
        $stmt = mysqli_prepare($connection, "SELECT COUNT(*) FROM admin_users WHERE admin_level = 'super_admin'");
        if ($stmt) {
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $superCount);
            if (mysqli_stmt_fetch($stmt)) {
                $count = intval($superCount);
            }
            mysqli_stmt_close($stmt);
        }

        return $count;
    }
}

if (!function_exists('getAdminLevelById')) {
    function getAdminLevelById($connection, $adminUserId) {
        if (!$connection || $adminUserId <= 0) {
            return 'staff';
        }

        $level = 'staff';
        $stmt = mysqli_prepare($connection, "SELECT admin_level FROM admin_users WHERE id = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $adminUserId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $foundLevel);
            if (mysqli_stmt_fetch($stmt) && $foundLevel === 'super_admin') {
                $level = 'super_admin';
            }
            mysqli_stmt_close($stmt);
        }

        return $level;
    }
}

?>
