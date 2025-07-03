<?php
session_start();
ob_start(); // Start output buffering to catch stray output
header('Content-Type: application/json'); // Set JSON header for all responses

// Check if it's an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!isset($_SESSION['unique_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    ob_end_flush();
    exit;
}

include_once "./config.php";

$created_by = $_SESSION['unique_id'];

// Check if user is blocked
$check_blocked = mysqli_query($conn, "SELECT is_blocked FROM users WHERE unique_id = '$created_by'");
if (!$check_blocked || mysqli_num_rows($check_blocked) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found or database error: ' . mysqli_error($conn)]);
    ob_end_flush();
    exit;
}
$user = mysqli_fetch_assoc($check_blocked);
if ($user['is_blocked'] == 1) {
    echo json_encode(['status' => 'error', 'message' => 'You are blocked and cannot create groups']);
    ob_end_flush();
    exit;
}

// Proceed with group creation
$group_name = mysqli_real_escape_string($conn, $_POST['group_name'] ?? '');
$members = isset($_POST['members']) ? array_map('intval', $_POST['members']) : [];
$image_name = "default.jpg"; // Default image

if (empty($group_name) || empty($members)) {
    echo json_encode(['status' => 'error', 'message' => 'Group name and at least one member are required']);
    ob_end_flush();
    exit;
}

// Check for 250 members limit
if (count($members) > 250) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot create group with more than 250 members']);
    ob_end_flush();
    exit;
}

// Handle uploaded image
if (isset($_FILES['group_image']) && $_FILES['group_image']['error'] === UPLOAD_ERR_OK) {
    $img_name = $_FILES['group_image']['name'];
    $img_tmp = $_FILES['group_image']['tmp_name'];
    $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (in_array($img_ext, $allowed)) {
        $new_img_name = time() . "_" . uniqid() . "." . $img_ext;
        $upload_path = "images/" . $new_img_name; // Adjusted path to match your setup

        if (move_uploaded_file($img_tmp, $upload_path)) {
            $image_name = $new_img_name;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload image']);
            ob_end_flush();
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid image type. Only JPG, JPEG, PNG allowed']);
        ob_end_flush();
        exit;
    }
}

// Insert into groups table
$insertGroup = mysqli_query($conn, "INSERT INTO groups (group_name, created_by, group_image) VALUES ('$group_name', '$created_by', '$image_name')");
if (!$insertGroup) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create group: ' . mysqli_error($conn)]);
    ob_end_flush();
    exit;
}
$group_id = mysqli_insert_id($conn);

// Insert creator as admin
$insertCreator = mysqli_query($conn, "INSERT INTO group_members (group_id, unique_id, is_admin) VALUES ($group_id, '$created_by', true)");
if (!$insertCreator) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add creator as admin: ' . mysqli_error($conn)]);
    ob_end_flush();
    exit;
}

// Insert other members
foreach ($members as $member_id) {
    $insertMember = mysqli_query($conn, "INSERT INTO group_members (group_id, unique_id) VALUES ($group_id, '$member_id')");
    if (!$insertMember) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add member: ' . mysqli_error($conn)]);
        ob_end_flush();
        exit;
    }
}

// For non-AJAX requests, redirect; for AJAX, return JSON
if ($isAjax) {
    echo json_encode(['status' => 'success', 'message' => 'Group created successfully', 'group_id' => $group_id]);
} else {
    header("Location: ../public/users.php?group_id=$group_id");
}
ob_end_flush();
?>