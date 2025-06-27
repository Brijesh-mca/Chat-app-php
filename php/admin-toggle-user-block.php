<?php
session_start();
require_once "config.php";
require_once "admin-functions.php";
checkAdminAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $user_id = intval($_POST['id']);

    $result = $conn->query("SELECT is_blocked FROM users WHERE unique_id = $user_id");
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $new_status = $user['is_blocked'] == 1 ? 0 : 1;

        $update = $conn->query("UPDATE users SET is_blocked = $new_status WHERE unique_id = $user_id");

        if ($update) {
            $msg = $new_status ? "User has been blocked." : "User has been unblocked.";
            header("Location: ../public/admin-users.php?success=" . urlencode($msg));
            exit;
        }
    }

    header("Location: ../public/admin-users.php?error=" . urlencode("Failed to update block status."));
    exit;
} else {
    header("Location: ../public/admin-users.php?error=" . urlencode("Invalid request."));
    exit;
}
