<?php
session_start();
require_once '../php/config.php';

$group_id = $_GET['group_id'];
$user_id = $_SESSION['unique_id'];

// Fetch group only if current user is the creator
$query = "SELECT * FROM groups WHERE group_id = $group_id AND created_by = $user_id";
$result = mysqli_query($conn, $query);
$group = mysqli_fetch_assoc($result);

if (!$group) {
    header("location: users.php");
    exit();
}

// Fetch all users
$users = mysqli_query($conn, "SELECT * FROM users WHERE unique_id != $user_id");

// Fetch current group members
$members = mysqli_query($conn, "SELECT unique_id FROM group_members WHERE group_id = $group_id");

$current_members = [];
while ($row = mysqli_fetch_assoc($members)) {
    $current_members[] = $row['unique_id'];
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Group</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
  <style>
    .group-image-preview {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 50%;
      margin-bottom: 10px;
    }
    .delete-group-btn {
      background-color: #ff4444;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 20px;
    }
    .delete-group-btn:hover {
      background-color: #cc0000;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <section class="form">
      <h2>Edit Group: <?= htmlspecialchars($group['group_name']) ?></h2>

      <form action="../php/update-group.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="group_id" value="<?= $group_id ?>">

        <div class="field input">
          <label>Group Name:</label>
          <input type="text" name="group_name" value="<?= htmlspecialchars($group['group_name']) ?>" required>
        </div>

        <div class="field input">
          <label>Group Image:</label>
          <?php if (!empty($group['group_image'])): ?>
            <img src="../php/images/<?= $group['group_image'] ?>" class="group-image-preview" id="imagePreview">
          <?php else: ?>
            <img src="../php/images/1749820324penguin.jpg" class="group-image-preview" id="imagePreview">
          <?php endif; ?>
          <input type="file" name="group_image" id="groupImage" accept="image/*">
        </div>

        <div class="field input">
          <label>Add/Remove Members:</label><br>
          <?php 
          mysqli_data_seek($users, 0); // Reset pointer to beginning
          while ($user = mysqli_fetch_assoc($users)) { ?>
            <input type="checkbox" name="members[]" value="<?= $user['unique_id'] ?>"
                <?= in_array($user['unique_id'], $current_members) ? 'checked' : '' ?>>
            <?= $user['fname'] . ' ' . $user['lname'] ?><br>
          <?php } ?>
        </div>

        <div class="field button">
          <input type="submit" name="update_group" value="Update Group">
        </div>
      </form>

      <form action="../php/delete-group.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this group? All messages will be lost.');">
        <input type="hidden" name="group_id" value="<?= $group_id ?>">
        <button type="submit" class="delete-group-btn">
          <i class="fas fa-trash"></i> Delete Group
        </button>
      </form>
    </section>
  </div>

  <script>
    // Preview image before upload
    document.getElementById('groupImage').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          document.getElementById('imagePreview').src = event.target.result;
        }
        reader.readAsDataURL(file);
      }
    });
  </script>
</body>
</html>