<?php
session_start();
if (isset($_SESSION['unique_id'])) {
    include_once "./config.php";

    $created_by = $_SESSION['unique_id'];

    // ✅ Check if user is blocked
    $check_blocked = mysqli_query($conn, "SELECT is_blocked FROM users WHERE unique_id = '$created_by'");
    if ($check_blocked && mysqli_num_rows($check_blocked) > 0) {
        $user = mysqli_fetch_assoc($check_blocked);
        if ($user['is_blocked'] == 1) {
            // Redirect with error message
            header("Location: ../public/users.php?error=" . urlencode("You are blocked and cannot create groups."));
            exit();
        }
    }

    // ✅ Proceed with group creation
    $group_name = mysqli_real_escape_string($conn, $_POST['group_name']);
    $members = $_POST['members'];
    $image_name = "";

    if (!empty($group_name) && !empty($members)) {

        // ✅ Check for 250 members limit
        if (count($members) > 250) {
            die("Cannot create group with more than 250 members.");
        }

        // ✅ Handle uploaded image
        if (isset($_FILES['group_image']) && $_FILES['group_image']['error'] === UPLOAD_ERR_OK) {
            $img_name = $_FILES['group_image']['name'];
            $img_tmp = $_FILES['group_image']['tmp_name'];
            $img_ext = pathinfo($img_name, PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png'];

            if (in_array(strtolower($img_ext), $allowed)) {
                $new_img_name = time() . "_" . uniqid() . "." . $img_ext;
                $upload_path = "../php/images/" . $new_img_name;

                if (move_uploaded_file($img_tmp, $upload_path)) {
                    $image_name = $new_img_name;
                } else {
                    echo "Failed to upload image.";
                    exit();
                }
            } else {
                echo "Invalid image type. Only JPG, JPEG, PNG allowed.";
                exit();
            }
        }

        // ✅ Insert into groups table
        $insertGroup = mysqli_query($conn, "INSERT INTO groups (group_name, created_by, group_image) VALUES ('$group_name', '$created_by', '$image_name')");
        if ($insertGroup) {
            $group_id = mysqli_insert_id($conn);

            // ✅ Insert creator as admin
            mysqli_query($conn, "INSERT INTO group_members (group_id, unique_id, is_admin) VALUES ($group_id, '$created_by', true)");

            // ✅ Insert other members
            foreach ($members as $member_id) {
                mysqli_query($conn, "INSERT INTO group_members (group_id, unique_id) VALUES ($group_id, '$member_id')");
            }

            header("Location: ../public/users.php?group_id=$group_id");
            exit();
        } else {
            echo "Failed to create group: " . mysqli_error($conn);
        }
    } else {
        echo "Group name and members are required.";
    }
} else {
    header("location: ../login.php");
}
