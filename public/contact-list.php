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
            <h2>Contacts</h2>
        </div>
        <header>
            <div class="content">
                <img src="../php/images/<?php echo htmlspecialchars($user_row['img']); ?>" alt="" class="profile-img">
                <div class="details">
                    <span class="contact-name"><?php echo htmlspecialchars($user_row['fname'] . ' ' . $user_row['lname']); ?></span>
                    <p class="contact-status <?php echo htmlspecialchars($user_row['status']) === 'Online' ? 'online' : 'offline'; ?>">
                        <?php echo htmlspecialchars($user_row['status']); ?>
                    </p>
                </div>
            </div>
        </header>
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

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f0f2f5;
    margin: 0;
    padding: 0;
}
.wrapper {
    display: flex;
    justify-content: center;
    min-height: 100vh;
    width: 100%;
}
.users {
    width: 100%;
    background: #fff;
    display: flex;
    flex-direction: column;
    height: 100vh;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    overflow: hidden;
}
.page-header {
    background: teal;
    color: #fff;
    padding: 15px 20px;
    text-align: left;
}
.page-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: bold;
}
.content {
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-bottom: 1px solid gray;
    max-width: 800px;
    margin: 0 auto;
}
.content img {
    border: 1px solid black;
}
.profile-img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}
.contact-name {
    font-weight: bold;
    font-size: 16px;
}
.contact-status {
    margin: 5px 0;
    font-size: 12px;
}
.contact-status.online {
    color: #28a745;
}
.contact-status.offline {
    color: #6c757d;
}
.contact-list {
    padding: 20px;
    background-color: #fff;
    border-radius: 15px;
    width: 70%;
    margin: 0 auto;
    flex-grow: 1;
    overflow-y: auto;
}
.contact-list a {
    padding: 0;
}
.contact-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 10px;
    text-decoration: none;
    color: #000;
}
.contact-item img {
    border: 1px solid black;
}
.contact-item:hover {
    background-color: #f1f1f1;
}
.contact-details {
    display: flex;
    flex-direction: column;
}
.contact-name {
    font-weight: bold;
    font-size: 16px;
}
.contact-status {
    font-size: 12px;
    color: #666;
}
.no-users {
    text-align: center;
    color: #666;
    padding: 20px;
}
.contact-list hr {
    border: 0;
    border-top: 1px solid #ccc;
    margin: 10px 0;
}

@media (max-width: 426px) {
    .page-header {
        padding: 10px 15px;
    }
    .page-header h2 {
        font-size: 18px;
    }
    .content {
        padding: 15px;
    }
    .contact-list {
        padding: 15px;
    }
    .contact-item {
        padding: 8px;
    }
    .profile-img {
        width: 40px;
        height: 40px;
    }
    .contact-name {
        font-size: 14px;
    }
    .contact-status {
        font-size: 11px;
    }
}
</style>
</body>
</html>