<?php
session_start();
include_once "config.php";

$unique_id = $_SESSION['unique_id'];
$searchTerm = isset($_POST['searchTerm']) ? mysqli_real_escape_string($conn, $_POST['searchTerm']) : '';

$sql = "SELECT * FROM users WHERE unique_id != '$unique_id'";
if ($searchTerm) {
    $sql .= " AND (fname LIKE '%$searchTerm%' OR lname LIKE '%$searchTerm%')";
}
$result = mysqli_query($conn, $sql);

$output = "";
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $output .= '<a href="?user_id=' . $row['unique_id'] . '" class="user-item">';
        $output .= '<div class="content">';
        $output .= '<img src="images/' . $row['img'] . '" alt="">';
        $output .= '<div class="details">';
        $output .= '<span>' . htmlspecialchars($row['fname'] . " " . $row['lname']) . '</span>';
        $output .= '<p>' . htmlspecialchars($row['status']) . '</p>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</a>';
    }
} else {
    $output = "<p>No users found.</p>";
}

echo $output;
?>