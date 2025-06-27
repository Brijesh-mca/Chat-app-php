<?php
session_start();
error_log("Starting group chat message processing");

if (!isset($_SESSION['unique_id'])) {
    header("Location: ../login.php");
    exit;
}

include_once "./config.php";

$group_id  = intval($_POST['group_id']);
$sender_id = intval($_POST['sender_id']);
$message   = mysqli_real_escape_string($conn, $_POST['message'] ?? '');

$file_path = '';

// ✅ Step 0: Check if sender is blocked
$block_check = mysqli_query($conn, "SELECT is_blocked FROM users WHERE unique_id = '$sender_id'");
if ($block_check && mysqli_num_rows($block_check) > 0) {
    $is_blocked = mysqli_fetch_assoc($block_check)['is_blocked'];
    if ($is_blocked == 1) {
        error_log("Blocked user $sender_id attempted to send group message.");
        http_response_code(403);
        echo "You are blocked and cannot send messages.";
        exit;
    }
}

// ✅ Step 1: Check if group has admin-only enabled
$group_check = mysqli_query($conn, "SELECT admin_only FROM groups WHERE group_id = '$group_id'");
if (mysqli_num_rows($group_check) === 0) {
    error_log("Group not found or invalid group_id: $group_id");
    http_response_code(404);
    exit;
}

$group_data = mysqli_fetch_assoc($group_check);
$admin_only = $group_data['admin_only'];

// ✅ Step 2: If admin_only is enabled, check if sender is admin
if ($admin_only == 1) {
    $admin_check = mysqli_query($conn, "SELECT is_admin FROM group_members WHERE group_id = '$group_id' AND unique_id = '$sender_id'");
    $admin_data = mysqli_fetch_assoc($admin_check);

    if (!$admin_data || $admin_data['is_admin'] != 1) {
        error_log("User $sender_id tried to send in admin-only group $group_id");
        http_response_code(403);
        echo "Only group admins can send messages in this group.";
        exit;
    }
}

// ✅ Step 3: Handle file upload
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $max_size = 5 * 1024 * 1024;

    $file_type = $_FILES['file']['type'];
    $file_size = $_FILES['file']['size'];

    if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
        $upload_dir = '../php/uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $original_name = basename($_FILES['file']['name']);
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid('grpfile_', true) . '.' . $extension;
        $destination = $upload_dir . $new_file_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
            $file_path = $destination;
            error_log("File uploaded: $file_path");
        } else {
            error_log("File move failed.");
        }
    } else {
        error_log("Invalid file type or size too large.");
    }
}

// ✅ Step 4: Save message if content exists
if (!empty($message) || !empty($file_path)) {
    $stmt = $conn->prepare("INSERT INTO group_messages (group_id, sender_id, message, file_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $group_id, $sender_id, $message, $file_path);

    if ($stmt->execute()) {
        error_log("Group message inserted successfully.");
        echo "Message sent successfully.";
    } else {
        error_log("DB error: " . $stmt->error);
        http_response_code(500);
        echo "Failed to insert message.";
    }

    $stmt->close();
} else {
    error_log("No message or file provided.");
    http_response_code(400);
    echo "Please enter a message or upload a file.";
}
?>
