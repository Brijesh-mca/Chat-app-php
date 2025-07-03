<?php
session_start();
if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit;
}
include_once "../php/config.php";

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;

// Fetch logged-in user's details
$sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = '{$_SESSION['unique_id']}'");
if (!$sql || mysqli_num_rows($sql) == 0) {
    echo "User Query Error: " . mysqli_error($conn);
    exit;
}
$user_row = mysqli_fetch_assoc($sql);

// Fetch contacts
$sql_all = mysqli_query($conn, "SELECT * FROM users WHERE unique_id != '{$_SESSION['unique_id']}'");
if (!$sql_all) {
    echo "Contacts Query Error: " . mysqli_error($conn);
    exit;
}
$contacts = [];
while ($contact = mysqli_fetch_assoc($sql_all)) {
    $contacts[] = $contact;
}

if ($isAjax) {
    // Output only the contact list for AJAX requests
    if (count($contacts) > 0) {
        $first = true;
        foreach ($contacts as $contact) {
            if (!$first) {
                echo '<hr>';
            }
            $first = false;
            echo '<a href="users.php?user_id=' . htmlspecialchars($contact['unique_id']) . '" class="contact-item">';
            echo '<img src="../php/images/' . htmlspecialchars($contact['img']) . '" alt="" class="profile-img">';
            echo '<div class="contact-details">';
            echo '<span class="contact-name">' . htmlspecialchars($contact['fname'] . ' ' . $contact['lname']) . '</span>';
            echo '<p class="contact-status ' . (htmlspecialchars($contact['status']) === 'Online' ? 'online' : 'offline') . '">';
            echo htmlspecialchars($contact['status']);
            echo '</p>';
            echo '</div>';
            echo '</a>';
        }
    } else {
        echo '<p class="no-users">No contacts found</p>';
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<div class="wrapper">
    <section class="users">
        <div class="page-header">
            
        </div>
        <!-- <header>
            <div class="content">
                <img src="../php/images/<?php echo htmlspecialchars($user_row['img']); ?>" alt="" class="profile-img">
                <div class="details">
                    <span class="contact-name"><?php echo htmlspecialchars($user_row['fname'] . ' ' . $user_row['lname']); ?></span>
                    <p class="contact-status <?php echo htmlspecialchars($user_row['status']) === 'Online' ? 'online' : 'offline'; ?>">
                        <?php echo htmlspecialchars($user_row['status']); ?>
                    </p>
                </div>
            </div>
        </header> -->
        <div class="contact-list">
            <?php if (count($contacts) > 0): ?>
                <?php $first = true; ?>
                <?php foreach ($contacts as $contact): ?>
                    <?php if (!$first): ?>
                        <hr>
                    <?php endif; ?>
                    <?php $first = false; ?>
                    <a href="users.php?user_id=<?php echo htmlspecialchars($contact['unique_id']); ?>" class="contact-item">
                        <img src="../php/images/<?php echo htmlspecialchars($contact['img']); ?>" alt="" class="profile-img">
                        <div class="contact-details">
                            <span class="contact-name"><?php echo htmlspecialchars($contact['fname'] . ' ' . $contact['lname']); ?></span>
                            <p class="contact-status <?php echo htmlspecialchars($contact['status']) === 'Online' ? 'online' : 'offline'; ?>">
                                <?php echo htmlspecialchars($contact['status']); ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-users">No contacts found</p>
            <?php endif; ?>
        </div>
    </section>
</div>


</body>
</html>