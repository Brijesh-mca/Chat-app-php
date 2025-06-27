<?php
session_start();
if (!isset($_SESSION['unique_id'])) {
    header("Location: ../login.php");
    exit;
}

include_once "./config.php";

$outgoing_id = mysqli_real_escape_string($conn, $_POST['outgoing_id']);
$incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
$message     = mysqli_real_escape_string($conn, $_POST['message'] ?? '');
$file        = '';

// ✅ Step 1: Check if sender is blocked
$block_check = mysqli_query($conn, "SELECT is_blocked FROM users WHERE unique_id = '$outgoing_id'");
if ($block_check && mysqli_num_rows($block_check) > 0) {
    $is_blocked = mysqli_fetch_assoc($block_check)['is_blocked'];
    if ($is_blocked == 1) {
        echo "You are blocked and cannot send messages.";
        exit;
    }
}


// ✅ Step 2: Check message request status
$checkRequest = mysqli_query($conn, "SELECT * FROM message_requests 
    WHERE sender_id = {$outgoing_id} AND receiver_id = {$incoming_id} AND status = 'accepted'");

if (mysqli_num_rows($checkRequest) === 0) {
    // Check if a request already exists
    $existingRequest = mysqli_query($conn, "SELECT * FROM message_requests 
        WHERE sender_id = {$outgoing_id} AND receiver_id = {$incoming_id}");

    if (mysqli_num_rows($existingRequest) === 0) {
        mysqli_query($conn, "INSERT INTO message_requests (sender_id, receiver_id) 
            VALUES ({$outgoing_id}, {$incoming_id})");
    }

    echo "Message not allowed. Waiting for receiver approval.";
    exit;
}

// ✅ Step 3: Handle file upload if exists
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = [
        'image/jpeg', 'image/png', 'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $max_size = 5 * 1024 * 1024; // 5MB

    $file_type = $_FILES['file']['type'];
    $file_size = $_FILES['file']['size'];

    if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
        $upload_dir = '../php/uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $unique_name = uniqid('chatfile_', true) . '.' . $extension;
        $destination = $upload_dir . $unique_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
            $file = $destination;
        }
    }
}

// ✅ Step 4: Insert message if either text or file is present
if (!empty($message) || !empty($file)) {
    $stmt = $conn->prepare("INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg, file) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $incoming_id, $outgoing_id, $message, $file);
    if (!$stmt->execute()) {
        echo "Failed to send message: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Empty message and no file uploaded.";
}
?>


