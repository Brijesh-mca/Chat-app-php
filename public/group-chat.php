<?php
session_start();
if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit;
}
include_once "../php/config.php";

$group_id = isset($_GET['group_id']) ? mysqli_real_escape_string($conn, $_GET['group_id']) : null;
$user_id = $_SESSION['unique_id'];

// Default values
$is_admin = false;
$admin_only = false;

if ($group_id) {
    $group_query = mysqli_query($conn, "SELECT * FROM groups WHERE group_id = '{$group_id}'");
    if ($group_query && mysqli_num_rows($group_query) > 0) {
        $group = mysqli_fetch_assoc($group_query);
        $admin_only = $group['admin_only'] == 1;

        // Check if user is an admin
        $admin_check = mysqli_query($conn, "SELECT is_admin FROM group_members WHERE group_id = '$group_id' AND unique_id = '$user_id' AND is_admin = 1");
        $is_admin = mysqli_num_rows($admin_check) > 0;

    } else {
        $group = ['group_name' => 'Unknown Group'];
    }
} else {
    $group = ['group_name' => 'Invalid Group'];
}
?>
<?php include_once "../public/header.php"; ?>
<style>
  .typing-area {
    display: flex;
    align-items: center;
    padding: 10px;
    border-top: 1px solid #ccc;
    background: #fff;
  }

  .typing-area input.input-field {
    flex: 1;
    padding: 10px;
    margin: 0 10px;
    border: 1px solid #ccc;
    border-radius: 20px;
    font-size: 14px;
  }

  .typing-area .insert-button {
    margin-right: 10px;
    cursor: pointer;
    color: #555;
    font-size: 18px;
  }

  .typing-area button {
    background-color: #27ae60;
    border: none;
    color: white;
    padding: 10px 15px;
    border-radius: 50%;
    cursor: pointer;
  }

  .typing-area button:hover {
    background: #00796b;
  }

  .typing-area.disabled {
    justify-content: center;
    background: #f9f9f9;
    color: #888;
    font-style: italic;
    height: 60px;
  }

  .typing-area.disabled i {
    margin-right: 8px;
    color: #aaa;
  }

  .chat-area header {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #eeeeee;
    border-bottom: 1px solid #ccc;
  }

  .chat-area header img {
    width: 45px;
    height: 45px;
    object-fit: cover;
    border-radius: 50%;
    margin-right: 15px;
  }

  .chat-area .details span {
    font-weight: bold;
    font-size: 18px;
  }

  .chat-area .details p {
    margin: 0;
    font-size: 12px;
    color: #666;
  }

  .edit-group-btn {
    background: #26a69a;
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    margin-left: 10px;
    font-size: 13px;
    text-decoration: none;
  }

  .edit-group-btn:hover {
    background: #00796b;
  }

  .typing-area.warning-disabled {
    justify-content: center;
    font-style: italic;
    font-size: 14px;
    color: #999;
    background: #f5f5f5;
  }

  .typing-area.warning-disabled i {
    margin-right: 8px;
    color: #999;
  }
</style>
<body>
  <div class="wrapper">
    <section class="chat-area">
      <header>
        <a href="./users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
        <img src="<?php echo !empty($group['group_image']) ? '../php/images/' . $group['group_image'] : '../php/images/1749820324penguin.jpg'; ?>" alt="Group Image">
        <div class="details">
          <span><?php echo $group['group_name']; ?></span>
          <?php
          $creator_check = mysqli_query($conn, "SELECT * FROM groups WHERE group_id = '$group_id' AND created_by = '$user_id'");
          if (mysqli_num_rows($creator_check) > 0) {
              echo '<a href="edit-group.php?group_id=' . $group_id . '" class="edit-group-btn">Edit Group</a>';
          }
          ?>
          <p>Group Chat</p>
        </div>
      </header>

      <div class="chat-box">
        <!-- Group messages will be loaded here via JS -->
      </div>

      <?php if (!$admin_only || ($admin_only && $is_admin)) : ?>
  <!-- ✅ Message input is enabled -->
  <form action="#" class="typing-area" autocomplete="off" enctype="multipart/form-data">
    <input type="file" name="file" id="fileInput" class="file-input" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" hidden>
    <label for="fileInput" class="insert-button">
      <i class="fas fa-paperclip"></i>
    </label>
    <input type="text" name="group_id" value="<?php echo $group_id; ?>" hidden>
    <input type="text" name="sender_id" value="<?php echo $user_id; ?>" hidden>
    <input type="text" name="message" class="input-field" placeholder="Type a message here...">
    <button><i class="fab fa-telegram-plane"></i></button>
  </form>
<?php else: ?>
  <!-- ❌ Input box hidden; show warning instead -->
  <div class="typing-area warning-disabled">
    <i class="fas fa-lock"></i>
    Only admins can send messages in this group.
  </div>
<?php endif; ?>


    </section>
  </div>

  <script src="./js/group-chat.js?v=<?= time(); ?>"></script>

</body>
</html>
