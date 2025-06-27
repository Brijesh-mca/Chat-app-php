<?php
session_start();
if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit;
}
include_once "../php/config.php";

$unique_id = $_SESSION['unique_id'];
$sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = '{$unique_id}'");
if (!$sql || mysqli_num_rows($sql) == 0) {
    die("User not found.");
}
$user = mysqli_fetch_assoc($sql);

$page_title = "Edit Profile";
$active_page = "settings";

ob_start();
?>

<link rel="stylesheet" href="css/edit_profile.css">

<div class="edit-profile-container">
    <div class="profile-image-section">
        <div class="image-wrapper">
            <img src="../php/images/<?php echo $user['img']; ?>" alt="Profile" class="profile-img" id="profileImg">
            <button type="button" class="edit-img-btn edit-field" onclick="document.getElementById('profileImageInput').click()" style="display: none;">
                <i class="bi bi-pencil"></i>
            </button>
        </div>
        <div class="user-details">
            <span class="user-name"><?php echo $user['fname'] . ' ' . $user['lname']; ?></span>
            <span class="user-username">@<?php echo strtolower(str_replace(' ', '', $user['fname'] . $user['lname'])); ?></span>
        </div>
    </div>

    <div class="profile-details">
        <form class="edit-profile-form" id="editForm" action="../php/update-profile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="unique_id" value="<?php echo $user['unique_id']; ?>" />
            <input type="file" name="image" accept="image/*" id="profileImageInput" style="display: none;" onchange="previewImage(event)">

            <table>
                <tr>
                    <th>First Name</th>
                    <td>
                        <span class="display-field"><?php echo $user['fname']; ?></span>
                        <div class="form-group edit-field" style="display: none;">
                            <input type="text" name="fname" value="<?php echo $user['fname']; ?>" required>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>Last Name</th>
                    <td>
                        <span class="display-field"><?php echo $user['lname']; ?></span>
                        <div class="form-group edit-field" style="display: none;">
                            <input type="text" name="lname" value="<?php echo $user['lname']; ?>" required>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><span class="readonly-field"><?php echo $user['email']; ?></span></td>
                </tr>
                <tr>
                    <th>Designation</th>
                    <td><span class="readonly-field"><?php echo $user['designation']; ?></span></td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td>
                        <span class="display-field"><?php echo $user['location']; ?></span>
                        <div class="form-group edit-field" style="display: none;">
                            <input type="text" name="location" value="<?php echo $user['location']; ?>">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>Employee Code</th>
                    <td><span class="readonly-field"><?php echo $user['employee_code']; ?></span></td>
                </tr>
                <tr class="edit-field" style="display: none;">
                    <th>New Password</th>
                    <td>
                        <div class="form-group">
                            <input type="password" name="password" placeholder="Enter new password">
                        </div>
                    </td>
                </tr>
            </table>
            <div class="form-actions edit-field" style="display: none;">
                <button type="submit" class="save-btn">Save</button>
                <button type="button" class="cancel-btn" onclick="cancelEdit()">Cancel</button>
            </div>
        </form>
    </div>

    <button class="edit-btn" id="editButton" onclick="enableEditMode()">
        <i class="bi bi-pencil"></i> Edit Profile
    </button>
</div>

<script src="js/edit_profile.js"></script>

<?php
$content = ob_get_clean();
require_once 'layout.php';
?>
