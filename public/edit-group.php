<?php
session_start();
require_once '../php/config.php';

$group_id = intval($_GET['group_id']);
$user_id = $_SESSION['unique_id'] ?? 0;

// ✅ Blocked user check
$user_check = mysqli_query($conn, "SELECT is_blocked FROM users WHERE unique_id = $user_id");
if (!$user_check || mysqli_num_rows($user_check) === 0 || mysqli_fetch_assoc($user_check)['is_blocked'] == 1) {
    echo "<p style='color:red; text-align:center; padding:20px;'>You are blocked and cannot update any group.</p>";
    exit;
}

$success_message = '';
$error_message = '';

// ✅ Check if current user is admin of the group
$is_admin_result = mysqli_query($conn, "SELECT is_admin FROM group_members WHERE group_id = $group_id AND unique_id = $user_id");
if (!$is_admin_result || mysqli_num_rows($is_admin_result) === 0 || mysqli_fetch_assoc($is_admin_result)['is_admin'] != 1) {
    header("location: users.php");
    exit();
}

// ✅ Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = mysqli_real_escape_string($conn, $_POST['group_name']);
    $members = $_POST['members'] ?? [];
    $admins = $_POST['admins'] ?? [];
    $admin_only = isset($_POST['admin_only']) ? 1 : 0;

    if (count($members) > 250) {
        $error_message = "You can't add more than 250 members.";
    } else {
        mysqli_query($conn, "UPDATE groups SET group_name = '$group_name', admin_only = $admin_only WHERE group_id = $group_id");

        // ✅ Image upload
        if (isset($_FILES['group_image']) && $_FILES['group_image']['error'] === UPLOAD_ERR_OK) {
            $img_name = $_FILES['group_image']['name'];
            $img_tmp = $_FILES['group_image']['tmp_name'];
            $img_ext = pathinfo($img_name, PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png'];

            if (in_array(strtolower($img_ext), $allowed)) {
                $new_img_name = time() . "_" . uniqid() . "." . $img_ext;
                $upload_path = "../php/images/" . $new_img_name;

                if (move_uploaded_file($img_tmp, $upload_path)) {
                    // Remove old image if exists
                    $old_img = mysqli_fetch_assoc(mysqli_query($conn, "SELECT group_image FROM groups WHERE group_id = $group_id"));
                    if (!empty($old_img['group_image']) && file_exists("../php/images/" . $old_img['group_image'])) {
                        unlink("../php/images/" . $old_img['group_image']);
                    }

                    mysqli_query($conn, "UPDATE groups SET group_image = '$new_img_name' WHERE group_id = $group_id");
                }
            }
        }

        // ✅ Remove all members and reinsert
        mysqli_query($conn, "DELETE FROM group_members WHERE group_id = $group_id");

        foreach ($members as $member_id) {
            $member_id = mysqli_real_escape_string($conn, $member_id);
            $is_admin = in_array($member_id, $admins) ? 1 : 0;
            mysqli_query($conn, "INSERT INTO group_members (group_id, unique_id, is_admin) VALUES ($group_id, '$member_id', $is_admin)");
        }

        // ✅ Ensure updater stays admin
        mysqli_query($conn, "UPDATE group_members SET is_admin = 1 WHERE group_id = $group_id AND unique_id = $user_id");

        $success_message = "Group updated successfully.";
    }
}

// ✅ Fetch group and users
$group = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM groups WHERE group_id = $group_id"));
$users = mysqli_query($conn, "SELECT * FROM users");

$current_members = [];
$current_admins = [];
$members_result = mysqli_query($conn, "SELECT unique_id, is_admin FROM group_members WHERE group_id = $group_id");
while ($row = mysqli_fetch_assoc($members_result)) {
    $current_members[] = $row['unique_id'];
    if ($row['is_admin']) {
        $current_admins[] = $row['unique_id'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Group</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%);
            overflow: auto;
        }

        .wrapper {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out;
        }

        .form {
            padding: 20px;
            text-align: center;
        }

        .form h2 {
            font-size: 1.8rem;
            color: #00695c;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            animation: bounceIn 0.8s ease-out;
        }

        .field {
            margin-bottom: 15px;
            text-align: left;
        }

        .field label {
            display: block;
            color: #26a69a;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .field input[type="text"],
        .field input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 2px solid #e0f2f1;
            border-radius: 20px;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .field input[type="text"]:focus,
        .field input[type="file"]:focus {
            border-color: #00695c;
            box-shadow: 0 0 5px rgba(0, 105, 92, 0.2);
        }

        .field input[type="file"] {
            padding: 8px;
            cursor: pointer;
        }

        .members-list {
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #e0f2f1;
            border-radius: 10px;
        }

        .member-admin-pair {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .members-list label {
            display: flex;
            align-items: center;
            margin: 5px 0;
            color: #444;
        }

        .members-list input[type="checkbox"] {
            margin-right: 10px;
            cursor: pointer;
        }

        .field.button input {
            background: #26a69a;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
            padding: 10px;
            width: 100%;
            border-radius: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .field.button input:hover {
            background: #00695c;
            transform: translateY(-2px);
            animation: pulse 1.2s infinite;
        }

        .field.button input::after {
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.5s ease, height 0.5s ease;
        }

        .field.button input:hover::after {
            width: 150px;
            height: 150px;
        }

        .success-message, .error-message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .success-message { background: #4caf50; color: white; }
        .error-message { background: #f44336; color: white; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bounceIn {
            0% { transform: scale(0.9); opacity: 0; }
            50% { transform: scale(1.05); opacity: 0.5; }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 105, 92, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(0, 105, 92, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 105, 92, 0); }
        }

        @media (max-width: 400px) {
            .wrapper { max-width: 90%; padding: 15px; }
            .members-list { max-height: 150px; }
            .field input[type="text"], .field input[type="file"] { font-size: 0.8rem; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <section class="form">
        <h2>Edit Group: <?= htmlspecialchars($group['group_name']) ?></h2>

        <?php if (!empty($success_message)) : ?>
            <div class="success-message"><?= $success_message ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)) : ?>
            <div class="error-message"><?= $error_message ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="field input">
                <label>Group Name:</label>
                <input type="text" name="group_name" value="<?= htmlspecialchars($group['group_name']) ?>" required>
            </div>

            <div class="field input">
                <label>Group Image:</label>
                <img src="../php/images/<?= !empty($group['group_image']) ? $group['group_image'] : '1749820324penguin.jpg' ?>" class="group-image-preview" id="imagePreview" width="100" style="margin-bottom: 10px;">
                <input type="file" name="group_image" id="groupImage" accept="image/*">
            </div>

            <div class="field input">
                <label>Admin Only Messaging:</label>
                <input type="checkbox" name="admin_only" <?= $group['admin_only'] ? 'checked' : '' ?>> Only Admins Can Message
            </div>

            <div class="field input">
                <label>Members & Admins (Max 250):</label>
                <div class="members-list">
                    <?php
                    while ($user = mysqli_fetch_assoc($users)) {
                        $uid = $user['unique_id'];
                        $is_member = in_array($uid, $current_members);
                        $is_admin = in_array($uid, $current_admins);
                        $member_checked = $is_member ? 'checked' : '';
                        $admin_checked = $is_admin ? 'checked' : '';

                        echo "<div class='member-admin-pair'>
                                <label>
                                    <input type='checkbox' name='members[]' value='$uid' class='member-checkbox' data-user-id='$uid' $member_checked>
                                    {$user['fname']} {$user['lname']}
                                </label>
                                <label>
                                    <input type='checkbox' name='admins[]' value='$uid' class='admin-checkbox' data-user-id='$uid' $admin_checked>
                                    <small>(Admin)</small>
                                </label>
                              </div>";
                    }
                    ?>
                </div>
            </div>

            <div class="field button">
                <input type="submit" name="update_group" value="Update Group">
            </div>
        </form>
    </section>
</div>

<script>
    // Preview image
    document.getElementById('groupImage').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                document.getElementById('imagePreview').src = event.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    // Auto-uncheck admin if member is unchecked
    document.querySelectorAll('.member-checkbox').forEach(memberBox => {
        memberBox.addEventListener('change', function () {
            const userId = this.dataset.userId;
            const adminBox = document.querySelector(`.admin-checkbox[data-user-id='${userId}']`);
            if (!this.checked) {
                adminBox.checked = false;
            }
        });
    });
</script>
</body>
</html>
