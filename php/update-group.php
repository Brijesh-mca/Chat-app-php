<?php
session_start();
require_once "config.php";

if (isset($_POST['update_group'])) {
    $group_id = intval($_POST['group_id']);
    $user_id = intval($_SESSION['unique_id']);

    // ✅ Blocked user check
    $block_check = mysqli_query($conn, "SELECT is_blocked FROM users WHERE unique_id = $user_id");
    if ($block_check && mysqli_num_rows($block_check) > 0) {
        $block_status = mysqli_fetch_assoc($block_check);
        if ($block_status['is_blocked'] == 1) {
            $_SESSION['error'] = "Blocked users are not allowed to update groups.";
            header("Location: ../public/edit-group.php?group_id=$group_id");
            exit();
        }
    }

    $group_name = mysqli_real_escape_string($conn, $_POST['group_name']);
    $members = $_POST['members'] ?? [];
    $admin_ids = $_POST['admins'] ?? [];
    $admin_only = isset($_POST['admin_only']) ? 1 : 0;

    if (count($members) > 250) {
        $_SESSION['error'] = "You cannot add more than 250 members.";
        header("Location: ../public/edit-group.php?group_id=$group_id");
        exit();
    }

    // ✅ Only admin can update
    $admin_check = mysqli_query($conn, "SELECT * FROM group_members WHERE group_id = $group_id AND unique_id = $user_id AND is_admin = 1");
    if (mysqli_num_rows($admin_check) === 0) {
        $_SESSION['error'] = "Only admins can update the group.";
        header("Location: ../public/edit-group.php?group_id=$group_id");
        exit();
    }

    // ✅ Image upload
    $image_name = '';
    if (isset($_FILES['group_image']) && $_FILES['group_image']['error'] === UPLOAD_ERR_OK) {
        $img_name = $_FILES['group_image']['name'];
        $img_tmp = $_FILES['group_image']['tmp_name'];
        $img_ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array(strtolower($img_ext), $allowed)) {
            $new_img_name = time() . "_" . uniqid() . "." . $img_ext;
            $upload_path = "images/" . $new_img_name;
            if (move_uploaded_file($img_tmp, $upload_path)) {
                $old_img = mysqli_fetch_assoc(mysqli_query($conn, "SELECT group_image FROM groups WHERE group_id = '$group_id'"));
                if (!empty($old_img['group_image']) && file_exists("images/" . $old_img['group_image'])) {
                    unlink("images/" . $old_img['group_image']);
                }
                $image_name = $new_img_name;
            }
        }
    }

    // ✅ Update group table
    $query = "UPDATE groups SET group_name = '$group_name', admin_only = $admin_only";
    if (!empty($image_name)) {
        $query .= ", group_image = '$image_name'";
    }
    $query .= " WHERE group_id = $group_id";
    mysqli_query($conn, $query);

    // ✅ Clear non-admin members
    mysqli_query($conn, "DELETE FROM group_members WHERE group_id = $group_id AND is_admin = 0");

    // ✅ Reinsert updated members
    foreach ($members as $member_id) {
        $member_id = intval($member_id);
        $is_admin = in_array($member_id, $admin_ids) ? 1 : 0;
        mysqli_query($conn, "INSERT INTO group_members (group_id, unique_id, is_admin) VALUES ($group_id, $member_id, $is_admin)");
    }

    // ✅ Ensure the updater is admin
    mysqli_query($conn, "INSERT INTO group_members (group_id, unique_id, is_admin) VALUES ($group_id, $user_id, 1)
        ON DUPLICATE KEY UPDATE is_admin = 1");

    $_SESSION['success'] = "Group updated successfully!";
    header("Location: ../public/edit-group.php?group_id=$group_id");
    exit();
}
?>
