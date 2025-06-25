<?php
session_start();
if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
}
?>
<?php include_once "header.php"; ?>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Sidebar (30%) -->
            <section class="sidebar">
                <header>
                    <?php
                    include_once "../php/config.php";
                    $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = '{$_SESSION['unique_id']}'");
                    if (mysqli_num_rows($sql) > 0) {
                        $row = mysqli_fetch_assoc($sql);
                    } else {
                        $row = ['img' => 'default.jpg', 'fname' => 'Unknown', 'lname' => 'User', 'status' => 'Offline']; // Fallback
                    }
                    ?>
                    <div class="content">
                        <img src="../php/images/<?php echo !empty($row['img']) ? htmlspecialchars($row['img']) : 'default.jpg'; ?>" alt="Profile Image" onerror="this.src='../php/images/default.jpg'">
                        <div class="details">
                            <span><?php echo htmlspecialchars($row['fname'] . " " . $row['lname']); ?></span>
                            <p><?php echo htmlspecialchars($row['status']); ?></p>
                        </div>
                    </div>
                    <a href="../public/settings.php" class="settings-btn">⚙️</a>
                </header>

                <!-- Icon Buttons -->
                <div class="action-buttons">
                    <a href="create-group.php" class="action-btn" title="Create Group"><i class="fas fa-users"></i></a>
                    <a href="chat-requests.php" class="action-btn" title="Chat Requests"><i class="fas fa-user-plus"></i></a>
                    <a href="contact-list.php" class="action-btn" title="Contacts"><i class="fas fa-address-book"></i></a>
                </div>

                <!-- Search Bar -->
                <div class="search">
                    <input type="text" placeholder="Enter name to search...">
                    <button><i class="fas fa-search"></i></button>
                </div>

                <!-- Combined Groups and Users List -->
                <div class="chat-list">
                    <!-- Group List -->
                    <div class="group-list">
                        <?php
                        $unique_id = $_SESSION['unique_id'];
                        $group_query = mysqli_query($conn, 
                            "SELECT g.group_id, g.group_name 
                             FROM groups g 
                             JOIN group_members gm ON g.group_id = gm.group_id 
                             WHERE gm.unique_id = '{$unique_id}'");
                        if (mysqli_num_rows($group_query) > 0) {
                            while ($group = mysqli_fetch_assoc($group_query)) {
                                echo '<div class="group-item">';
                                echo '<a href="?group_id=' . $group['group_id'] . '" class="chat-link"><i class="fas fa-users"></i> ' . htmlspecialchars($group['group_name']) . '</a>';
                                echo '</div>';
                            }
                        } else {
                            echo "<p>No groups yet.</p>";
                        }
                        ?>
                    </div>
                    <!-- User List -->
                    <div class="users-list"></div>
                </div>
            </section>

            <!-- Chat Area (70%) -->
            <section class="chat-area">
                <?php
                if (isset($_GET['user_id'])) {
                    $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
                    $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = '{$user_id}'");
                    if (mysqli_num_rows($sql) > 0) {
                        $row = mysqli_fetch_assoc($sql);
                    ?>
                    <header>
                        <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
                        <img src="../php/images/<?php echo $row['img'] ?>" alt="">
                        <div class="details">
                            <span><?php echo $row['fname'] . " " . $row['lname'] ?></span>
                            <p><?php echo $row['status'] ?></p>
                        </div>
                    </header>
                    <div class="chat-box"></div>
                    <form action="../php/insert-chat.php" method="POST" enctype="multipart/form-data" class="typing-area">
                        <input type="file" name="file" id="fileInput" class="file-input">
                        <label for="fileInput" class="insert-button"><i class="fas fa-paperclip"></i></label>
                        <input type="text" name="outgoing_id" value="<?php echo $_SESSION['unique_id']; ?>" hidden>
                        <input type="text" name="incoming_id" value="<?php echo $user_id; ?>" hidden>
                        <input type="text" name="message" class="input-field" placeholder="Type a message here...">
                        <button><i class="fab fa-telegram-plane"></i></button>
                    </form>
                    <script src="js/chat.js"></script>
                    <?php
                    }
                } elseif (isset($_GET['group_id'])) {
                    $group_id = mysqli_real_escape_string($conn, $_GET['group_id']);
                    $sql = mysqli_query($conn, "SELECT * FROM groups WHERE group_id = '{$group_id}'");
                    if ($sql && mysqli_num_rows($sql) > 0) {
                        $group = mysqli_fetch_assoc($sql);
                    } else {
                        $group = ['group_name' => 'Unknown Group'];
                    }
                    ?>
                    <header>
                        <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
                        <img src="<?php echo !empty($group['group_image']) ? '../php/images/' . $group['group_image'] : '../php/images/1749820324penguin.jpg'; ?>" alt="Group Image">
                        <div class="details">
                            <span><?php echo $group['group_name']; ?></span>
                            <?php
                            $creator_check = mysqli_query($conn, "SELECT * FROM groups WHERE group_id = '$group_id' AND created_by = '{$_SESSION['unique_id']}'");
                            if (mysqli_num_rows($creator_check) > 0) {
                                echo '<a href="edit-group.php?group_id=' . $group_id . '" class="edit-group-btn" style="margin-left:10px; font-size: 14px;">Edit Group</a>';
                            }
                            ?>
                            <p>Group Chat</p>
                        </div>
                    </header>
                    <div class="chat-box"></div>
                    <form action="../php/insert-group-chat.php" method="POST" class="typing-area" autocomplete="off" enctype="multipart/form-data">
                        <input type="file" name="file" id="fileInput" class="file-input" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" hidden>
                        <label for="fileInput" class="insert-button"><i class="fas fa-paperclip"></i></label>
                        <input type="text" name="group_id" value="<?php echo $group_id; ?>" hidden>
                        <input type="text" name="sender_id" value="<?php echo $_SESSION['unique_id']; ?>" hidden>
                        <input type="text" name="message" class="input-field" placeholder="Type a message here...">
                        <button><i class="fab fa-telegram-plane"></i></button>
                    </form>
                    <script src="js/group-chat.js"></script>
                    <?php
                } else {
                    ?>
                    <div class="welcome-message">
                        <p>Select a user or group to start chatting</p>
                    </div>
                    <?php
                }
                ?>
            </section>
        </div>
    </div>

    <script src="js/users.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const chatArea = document.querySelector('.chat-area');
            const chatList = document.querySelector('.chat-list');
            const backIcons = document.querySelectorAll('.back-icon');

            // Check if in mobile/tablet view
            const isMobileView = window.matchMedia('(max-width: 768px)').matches;

            // Function to update visibility based on URL
            function updateVisibility() {
                const isMobile = window.matchMedia('(max-width: 768px)').matches;
                if (isMobile) {
                    if (window.location.search.includes('user_id') || window.location.search.includes('group_id')) {
                        sidebar.style.display = 'none';
                        chatArea.style.display = 'flex';
                    } else {
                        sidebar.style.display = 'block';
                        chatArea.style.display = 'none';
                    }
                } else {
                    // Desktop view: show both
                    sidebar.style.display = 'flex';
                    chatArea.style.display = 'flex';
                }
            }

            // Initial visibility setup
            updateVisibility();

            if (isMobileView) {
                // Use event delegation on .chat-list to handle clicks on dynamically added links
                chatList.addEventListener('click', function(e) {
                    const link = e.target.closest('.chat-link, .users-list a');
                    if (link) {
                        e.preventDefault(); // Prevent default navigation
                        sidebar.style.display = 'none';
                        chatArea.style.display = 'flex';
                        // Navigate to the chat URL
                        window.location.href = link.href;
                    }
                });

                // Handle back button click
                backIcons.forEach(icon => {
                    icon.addEventListener('click', function(e) {
                        e.preventDefault();
                        chatArea.style.display = 'none';
                        sidebar.style.display = 'block';
                        window.location.href = 'users.php'; // Navigate back to user list
                    });
                });
            }

            // Update layout on window resize
            window.addEventListener('resize', updateVisibility);

            // Ensure profile images in users-list load correctly or fall back to default
            function fixUsersListProfileImages() {
                const userImages = document.querySelectorAll('.users-list img:not([data-fixed])');
                userImages.forEach(image => {
                    // Mark image as processed to avoid re-processing
                    image.setAttribute('data-fixed', 'true');
                    // Rewrite image src to correct path (from 'images/' to '../php/images/')
                    if (image.src.includes('/images/')) {
                        const filename = image.src.split('/images/')[1] || 'default.jpg';
                        image.src = '../php/images/' + filename;
                    }
                    // Add onerror handler to set default image if the source fails
                    image.onerror = function() {
                        this.src = '../php/images/default.jpg';
                    };
                    // Check if the image source is empty or invalid and set default immediately
                    if (!image.src || image.src.includes('undefined') || image.src === window.location.href || image.src.endsWith('/images/')) {
                        image.src = '../php/images/default.jpg';
                    }
                });
            }

            // Run the function after DOM is loaded
            fixUsersListProfileImages();

            // Use MutationObserver with debouncing to handle dynamic updates
            let debounceTimeout;
            const usersList = document.querySelector('.users-list');
            const observer = new MutationObserver(() => {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(fixUsersListProfileImages, 100);
            });
            observer.observe(usersList, { childList: true, subtree: true });
        });
    </script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .wrapper {
            display: flex;
            justify-content: end;
            min-height: 100vh;
            width: 100%;
        }
        .container {
            display: flex;
            width: 100%;
            height: 100vh;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .sidebar {
            width: 30%;
            background: teal;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
        }
        .chat-area {
            width: 70%;
            background: #e5ddd5;
            display: flex;
            flex-direction: column;
            height: 100vh;
            margin-left: 30%;
        }
        .sidebar header {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: teal;
        }
        .sidebar .content {
            display: flex;
            align-items: center;
        }
        .sidebar .content img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover; /* Ensure image fits properly */
            display: block; /* Prevent hiding */
        }
        .sidebar .details span {
            font-weight: bold;
            font-size: 16px;
            color: #fff;
        }
        .sidebar .details p {
            color: #ddd;
            font-size: 12px;
            margin: 0;
        }
        .sidebar .settings-btn {
            font-size: 18px;
            text-decoration: none;
            color: #fff;
            margin-left: 10px;
        }
        .action-buttons {
            display: flex;
            justify-content: space-around;
            padding: 10px 15px;
            background: teal;
            border-bottom: 1px solid #e0e0e0;
        }
        .action-btn {
            font-size: 20px;
            color: #fff;
            text-decoration: none;
            padding: 8px;
            border-radius: 50%;
            transition: background 0.3s;
        }
        .action-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .search {
            padding: 10px 15px;
            background: teal;
            display: flex;
        }
        .search input {
            width: calc(100% - 40px);
            padding: 8px;
            border-bottom: 1px solid red;
            outline: none;
            border-radius: 5px;
        }
        .search button {
            background: teal;
            color: #fff;
            border: none;
            padding: 8px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
            border-bottom: 1px solid red;
        }
        .chat-list {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            background: rgba(195, 233, 236, 0.7);
            max-height: calc(100vh - 150px);
        }
        .group-item {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 1px 2px rgb(255, 255, 255);
        }
        .group-item:hover {
            background: rgba(15, 134, 145, 0.8);
        }
        .group-item a, .user-item {
            display: flex;
            align-items: center;
            padding: 10px;
            text-decoration: none;
            color: #333;
            border-radius: 5px;
        }
        .user-item {
            box-shadow: 0 1px 2px rgb(255, 255, 255);
        }
        .user-item div div span {
            text-decoration: none;
            color: black !important;
            margin-bottom: 10px;
            font-weight: bold !important;
        }
        .user-item div div p {
            margin: 0;
            color: black !important;
        }
        .user-item:hover {
            background: rgb(15, 134, 145);
        }
        .user-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
            display: block;
        }
        .group-item a i {
            margin-right: 10px;
            color: #888;
        }
        .chat-area header {
            background: teal;
            padding: 15px;
            display: flex;
            align-items: center;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .chat-area .back-icon {
            color: #fff;
            font-size: 18px;
            margin-right: 15px;
            text-decoration: none;
        }
        .chat-area img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }
        .chat-area .details span {
            font-weight: bold;
            font-size: 16px;
        }
        .chat-area .details p {
            font-size: 12px;
            margin: 0;
        }
        .chat-box {
            flex-grow: 1;
            background: url('https://static.whatsapp.net/rsrc.php/v3/yP/r/r6Z9xDgPx.png');
            background-size: cover;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .chat {
            display: flex;
            max-width: 60%;
            margin-bottom: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            word-break: break-word;
            align-items: flex-start;
        }
        .chat.outgoing {
            align-self: flex-end;
            background: #dcf8c6;
            color: #000;
            flex-direction: row-reverse;
        }
        .chat.incoming {
            align-self: flex-start;
            background: rgb(108, 222, 212);
            color: #000;
            box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
        }
        .chat .details {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .chat .details p {
            font-size: 16px;
            margin: 0 0 4px 0;
            line-height: 1.4;
        }
        .chat .details img, .chat .details a {
            margin-top: 5px;
        }
        .chat .time {
            font-size: 10px;
            color: #888;
            align-self: flex-end;
            margin-top: 4px;
        }
        .chat .delete-btn {
            font-size: 12px;
            color: #888;
            background: none;
            border: none;
            cursor: pointer;
            margin-left: 5px;
            display: none;
        }
        .chat.outgoing:hover .delete-btn {
            display: inline;
        }
        .typing-area {
            display: flex;
            align-items: center;
            padding: 10px;
            background: teal;
            border-top: 1px solid #e0e0e0;
            position: sticky;
            bottom: 0;
        }
        .typing-area .file-input {
            display: none;
        }
        .typing-area .insert-button {
            font-size: 20px;
            color: #888;
            cursor: pointer;
            margin-right: 10px;
        }
        .typing-area .input-field {
            flex-grow: 1;
            padding: 10px;
            border: 20px;
            border-radius: 20px;
            outline: none;
        }
        .typing-area button {
            background: teal;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            margin-left: 10px;
        }
        .welcome-message {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            color: #888;
            background: url('https://static.whatsapp.net/rsrc.php/v3/yP/r/r6Z9xDgKm8l.png');
            background-size: cover;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: 100vh;
                position: static;
                display: block;
            }
            .chat-area {
                width: 100%;
                margin-left: 0;
                height: 100vh;
                display: none;
            }
            .chat-list {
                max-height: calc(100vh - 150px);
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            .sidebar .content img, .user-item img {
                width: 35px;
                height: 35px;
            }
            .sidebar .details span, .chat-area .details span {
                font-size: 14px;
            }
            .chat {
                max-width: 80%;
            }
            .chat .details p {
                font-size: 15px;
            }
            .chat .time {
                font-size: 9px;
            }
            .typing-area .input-field {
                padding: 8px;
            }
        }
        @media (max-width: 480px) {
            .sidebar .content img, .user-item img {
                width: 30px;
                height: 30px;
            }
            .sidebar .details span, .chat-area .details span {
                font-size: 13px;
            }
            .action-btn {
                font-size: 18px;
                padding: 6px;
            }
            .search input {
                padding: 6px;
            }
            .chat .details p {
                font-size: 14px;
            }
            .chat .time {
                font-size: 8px;
            }
        }
    </style>
</body>
</html>