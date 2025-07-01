<?php
session_start();
include_once "config.php";

$unique_id = mysqli_real_escape_string($conn, $_SESSION['unique_id']);
$output = "";

$sql = "SELECT u.* 
        FROM users u 
        WHERE u.unique_id != '$unique_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Fetch the last message between the logged-in user and this user
        $last_msg_query = mysqli_query($conn, 
            "SELECT msg, created_at 
             FROM messages 
             WHERE (outgoing_msg_id = '{$row['unique_id']}' AND incoming_msg_id = '$unique_id') 
             OR (outgoing_msg_id = '$unique_id' AND incoming_msg_id = '{$row['unique_id']}') 
             ORDER BY created_at DESC LIMIT 1"
        );
        
        $last_msg = "No messages yet.";
        $msg_time = "";
        if (mysqli_num_rows($last_msg_query) > 0) {
            $msg_row = mysqli_fetch_assoc($last_msg_query);
            $last_msg = $msg_row['msg'] ?? "Attachment";
            $msg_time = date("h:i A", strtotime($msg_row['created_at'])); // Format as 12-hour time
            
            // Handle extensions (e.g., h, y, ch) to indicate message type
            $extension = pathinfo($last_msg, PATHINFO_EXTENSION) ?? '';
            if ($extension) {
                $last_msg = "Attachment (" . strtoupper($extension) . ")"; // e.g., "Attachment (H)"
            }
            // Truncate long messages
            $last_msg = strlen($last_msg) > 20 ? substr($last_msg, 0, 20) . "..." : $last_msg;
        }

        $output .= '<a href="?user_id=' . $row['unique_id'] . '" class="user-item">';
        $output .= '<div class="content">';
        $output .= '<img src="../php/images/' . ($row['img'] ? $row['img'] : 'default.jpg') . '" alt="">';
        $output .= '<div class="details">';
        $output .= '<span>' . htmlspecialchars($row['fname'] . " " . $row['lname']) . '</span>';
        $output .= '<p>' . htmlspecialchars($last_msg) . ' · ' . $msg_time . '</p>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</a>';
    }
} else {
    $output = "<p>No users found.</p>";
}

echo $output;
?>