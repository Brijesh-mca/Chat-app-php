<?php
$conn = mysqli_connect("localhost", "root", "", "php-chat");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
