<?php
session_start();
if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat Application</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
      height: 100vh;
      overflow: hidden;
      background-color:#fdfdfd;
    }

    /* Left Section (Sidebar/Footer) */
    .left-section {
      width: 30vw;
      background-color: #fdfdfd;
      color: black;
      padding: 20px;
      overflow-y: auto;
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      transform: translateX(-25vw); /* Show 10vw by default */
      transition: transform 0.3s ease;
      z-index: 1000;
    }

    .left-section.expanded {
      transform: translateX(0); /* Show full 30vw */
    }

    .left-section .menu-toggle {
      font-size: 20px;
      color: black;
      text-align: right;
      text-decoration: none;
      margin-bottom: 10px;
      display: block;
      cursor: pointer;
    }

    .left-content {
      display: flex;
      flex-direction: column;
      height: 100%;
      justify-content: space-between;
    }

    .menu-items {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .menu-items li {
      margin: 10px 0;
    }

    .menu-item {
      color: black;
      text-decoration: none;
      font-size: 18px;
      display: flex;
      align-items: center;
      padding: 10px;
      border-radius: 5px;
    }

    .menu-item i {
      margin-right: 10px;
      font-size: 20px;
    }

    .menu-item span {
      display: none; /* Hide text by default */
    }

    .left-section.expanded .menu-item span {
      display: inline; /* Show text when expanded */
    }

    .menu-item:hover,
    .menu-item.active {
      background-color: #34495e;
    }

    /* Chat List (Sidebar) */
    .chat-list {
      width: 30vw;
      background: #fff;
      border-right: 1px solid #ddd;
      display: flex;
      flex-direction: column;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 5vw;
      z-index: 1;
    }

    .chat-list header {
      padding: 15px 20px;
      border-bottom: 1px solid #e0e0e0;
      background: #fdfdfd;
      color: black;
      display: flex;
      align-items: center;
      flex-shrink: 0;
    }

    .chat-list header h1 {
      margin: 0;
      font-size: 24px;
    }

    .chat-list .search {
      padding: 10px 15px;
      background: #fff;
      display: flex;
      flex-shrink: 0;
    }

    .chat-list .search input {
      width: calc(100% - 40px);
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 15px;
      outline: none;
    }

    .chat-list .search button {
      background: #3498db;
      color: black;
      border: none;
      padding: 8px;
      border-radius: 5px;
      cursor: pointer;
      margin-left: 10px;
    }

    .chat-list .toggle-buttons {
      display: flex;
      justify-content: space-around;
      padding: 10px 15px;
      background: #fff;
      border-bottom: 1px solid #e0e0e0;
      flex-shrink: 0;
    }

    .chat-list .toggle-btn {
      background: none;
      border: none;
      color: #333;
      font-size: 16px;
      cursor: pointer;
      padding: 10px;
      border-radius: 15px;
    }

    .chat-list .toggle-btn.active {
      background: #3498db;
      color: black;
    }

    .chat-list .users-list,
    .chat-list .group-list,
    .chat-list .requests-list,
    .chat-list .contacts-list,
    .chat-list .create-group {
      flex-grow: 1;
      overflow-y: auto;
      padding: 15px;
      background: #f4f4f4;
      display: none;
      position: relative;
      -webkit-overflow-scrolling: touch; /* Improve iOS scrolling */
      max-height: 100%;
    }

    .chat-list .users-list.active,
    .chat-list .group-list.active,
    .chat-list .requests-list.active,
    .chat-list .contacts-list.active,
    .chat-list .create-group.active {
      display: block;
    }

    .chat-list .user-item {
      margin-bottom: 10px;
      padding: 10px;
      border-radius: 5px;
      background: white;
      box-shadow: 0 1px 2px rgba(0,0,0,0.1);
      display: block;
      text-decoration: none;
      color: #333;
    }

    .chat-list .user-item:hover {
      background: #e0e0e0;
    }

    .chat-list .user-item .content {
      display: flex;
      align-items: center;
    }

    .chat-list .group-item {
      margin-bottom: 10px;
      padding: 10px;
      border-radius: 5px;
      background: white;
      box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .chat-list .group-item:hover {
      background: #e0e0e0;
    }

    .chat-list .group-item a {
      display: flex;
      align-items: center;
      text-decoration: none;
      color: #333;
    }

    .chat-list .group-item a i {
      margin-right: 10px;
      color: #888;
    }

    .chat-list .user-item img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 10px;
      object-fit: cover;
    }

    .chat-list .user-item .details span {
      font-weight: bold;
      font-size: 16px;
    }

    .chat-list .user-item .details p {
      font-size: 12px;
      color: #888;
      margin: 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .chat-list .user-item .details p .last-message {
      max-width: 100%;
      black-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .chat-list .user-item .details p .timestamp {
      font-size: 10px;
      color: #666;
    }

    /* Chat Requests Styles */
    .chat-list .requests-list .request-card {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #fff;
      border-radius: 8px;
      padding: 15px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: transform 0.2s, box-shadow 0.2s;
      margin-bottom: 10px;
    }

    .chat-list .requests-list .request-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .chat-list .requests-list .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .chat-list .requests-list .user-info img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
    }

    .chat-list .requests-list .user-details {
      display: flex;
      flex-direction: column;
    }

    .chat-list .requests-list .user-name {
      font-size: 16px;
      font-weight: bold;
      color: #333;
    }

    .chat-list .requests-list .request-text {
      font-size: 14px;
      color: #888;
      margin: 0;
    }

    .chat-list .requests-list .request-actions {
      display: flex;
      gap: 10px;
    }

    .chat-list .requests-list .btn-approve,
    .chat-list .requests-list .btn-reject {
      padding: 8px 16px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 5px;
      transition: background-color 0.2s, transform 0.1s;
    }

    .chat-list .requests-list .btn-approve {
      background-color: #28a745;
      color: black;
    }

    .chat-list .requests-list .btn-reject {
      background-color: #dc3545;
      color: black;
    }

    .chat-list .requests-list .btn-approve:hover {
      background-color: #218838;
      transform: scale(1.05);
    }

    .chat-list .requests-list .btn-reject:hover {
      background-color: #c82333;
      transform: scale(1.05);
    }

    .chat-list .requests-list .btn-approve:active,
    .chat-list .requests-list .btn-reject:active {
      transform: scale(0.95);
    }

    .chat-list .requests-list .no-requests {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      flex-grow: 1;
      color: #888;
      font-size: 16px;
      text-align: center;
      padding: 20px;
    }

    .chat-list .requests-list .no-requests i {
      font-size: 24px;
      margin-bottom: 10px;
    }

    /* Main Content */
    .main-content {
      width: 30vw;
      padding: 20px;
      overflow-y: auto;
      background-color: #f4f4f4;
      position: fixed;
      top: 0;
      left: 35vw;
      height: 100vh;
      z-index: 1;
    }

    .page {
      display: none;
    }

    .page.active {
      display: block;
    }

    .user-list {
      list-style: none;
      padding: 0;
    }

    .user-list li {
      padding: 10px;
      margin: 5px 0;
      background-color: #e0e0e0;
      border-radius: 5px;
      cursor: pointer;
    }

    .user-list li:hover {
      background-color: #d0d0d0;
    }

    .user-list li.active {
      background-color: #3498db;
      color: black;
    }

    /* Chat Area */
    .chat-area {
      width: calc(100vw - 35vw); /* ~65% */
      background: #ecf0f1;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      position: fixed;
      right: 0;
      top: 0;
      height: 100vh;
      z-index: 1;
    }

    .chat-area header {
      background: #fdfdfd;
      padding: 15px;
      border-bottom: 1px solid #ddd;
      display: flex;
      align-items: center;
      color: black;
      flex-shrink: 0;
    }

    .chat-area .back-icon {
      color: black;
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

    .chat-area .edit-group-btn {
      margin-left: 10px;
      position: fixed;
      right: 20px !important;
      font-size: 14px;
      color: black;
      text-decoration: none;
    }

    .chat-box {
      flex-grow: 1;
      background: url('https://static.whatsapp.net/rsrc.php/v3/yP/r/r6Z9xDgKm8l.png');
      background-size: cover;
      overflow-y: auto;
      padding: 20px;
      display: flex;
      flex-direction: column;
      -webkit-overflow-scrolling: touch; /* Improve iOS scrolling */
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
    }

    .chat.incoming {
      align-self: flex-start;
      background: #fff;
      color: #000;
      box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
    }

    .chat .details p {
      font-size: 16px;
      margin: 0 0 4px 0;
      line-height: 1.4;
    }

    .chat .time {
      font-size: 10px;
      color: #888;
      align-self: flex-end;
      margin-top: 4px;
    }

    .typing-area {
      display: flex;
      align-items: center;
      padding: 10px;
      background: #fdfdfd;
      border-top: 1px solid #ddd;
      flex-shrink: 0;
    }

    .typing-area .file-input {
      display: none;
    }

    .typing-area .insert-button {
      font-size: 20px;
      color: black;
      cursor: pointer;
      margin-right: 10px;
    }

    .typing-area .input-field {
      flex-grow: 1;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 20px;
      outline: none;
    }

    .typing-area button {
      background: #3498db;
      color: black;
      border: none;
      padding: 8px 12px;
      border-radius: 20px;
      cursor: pointer;
      margin-left: 5px;
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

    /* Contacts List */
    .contacts-list {
      display: none;
      flex-grow: 1;
      overflow-y: auto;
      padding: 15px;
      background: #f4f4f4;
      position: relative;
      -webkit-overflow-scrolling: touch; /* Improve iOS scrolling */
    }

    .contacts-list.active {
      display: block;
    }

    .contacts-list .contact-item {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 10px;
      text-decoration: none;
      color: #000;
      background: black;
      border-radius: 5px;
      margin-bottom: 10px;
    }

    .contacts-list .contact-item:hover {
      background: #e0e0e0;
    }

    .contacts-list .profile-img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 1px solid black;
    }

    .contacts-list .contact-details {
      display: flex;
      flex-direction: column;
    }

    .contacts-list .contact-name {
      font-weight: bold;
      font-size: 16px;
    }

    .contacts-list .contact-status {
      font-size: 12px;
      margin: 5px 0;
    }

    .contacts-list .contact-status.online {
      color: #28a745;
    }

    .contacts-list .contact-status.offline {
      color: #6c757d;
    }

    .contacts-list hr {
      border: 0;
      border-top: 1px solid #ccc;
      margin: 10px 0;
    }

    .contacts-list .no-users {
      text-align: center;
      color: #666;
      padding: 20px;
    }

    /* Create Group Form */
    .create-group {
      display: none;
      flex-grow: 1;
      overflow-y: auto;
      padding: 20px;
      background: #fff;
      position: relative;
      -webkit-overflow-scrolling: touch; /* Improve iOS scrolling */
    }

    .create-group.active {
      display: block;
    }

    .create-group .form {
      text-align: center;
    }

    .create-group .header-animation {
      font-size: 1.8rem;
      color: #00695c;
      margin-bottom: 15px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .create-group .field {
      margin-bottom: 15px;
      text-align: left;
    }

    .create-group .field label {
      display: block;
      color: #26a69a;
      font-weight: bold;
      margin-bottom: 5px;
      font-size: 0.9rem;
    }

    .create-group .field input[type="text"],
    .create-group .field input[type="file"] {
      width: 100%;
      padding: 8px;
      border: 2px solid #e0f2f1;
      border-radius: 20px;
      font-size: 0.9rem;
      outline: none;
    }

    .create-group .field input[type="text"]:focus,
    .create-group .field input[type="file"]:focus {
      border-color: #00695c;
      box-shadow: 0 0 5px rgba(0, 105, 92, 0.2);
    }

    .create-group .field input[type="file"] {
      padding: 8px;
      cursor: pointer;
    }

    .create-group .members-list {
      max-height: 200px;
      overflow-y: auto;
      padding: 10px;
      background: #f9f9f9;
      border: 1px solid #e0f2f1;
      border-radius: 10px;
      -webkit-overflow-scrolling: touch; /* Improve iOS scrolling */
    }

    .create-group .members-list label {
      display: flex;
      align-items: center;
      margin: 5px 0;
      color: #444;
    }

    .create-group .members-list input[type="checkbox"] {
      margin-right: 10px;
      cursor: pointer;
    }

    .create-group .field.button input {
      background: #26a69a;
      color: #fff;
      border: none;
      cursor: pointer;
      font-weight: bold;
      padding: 10px;
      width: 100%;
      border-radius: 20px;
    }

    .create-group .field.button input:hover {
      background: #00695c;
    }

    .create-group #memberCount {
      margin-top: 8px;
      font-size: 0.9rem;
      color: #333;
    }

    .create-group #memberCountWarning {
      color: red;
      font-size: 0.85rem;
    }

    /* Modal Styles */
    .modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal-content {
      background: black;
      padding: 20px;
      border-radius: 8px;
      width: 90%;
      max-width: 500px;
      max-height: 80vh;
      overflow-y: auto;
      position: relative;
      -webkit-overflow-scrolling: touch; /* Improve iOS scrolling */
    }

    .modal-content .close-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
    }

    /* Mobile Footer Styles */
    @media (max-width: 768px) {
      body {
        display: block; /* Remove flex to stack sections vertically */
      }

      .wrapper, .container {
        width: 100%;
        margin: 0;
        padding: 0;
      }

      .left-section {
        width: 100vw;
        max-width: 100vw;
        height: 60px;
        top: auto;
        bottom: 0;
        transform: none;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-around;
        padding:; /* Reduced padding for tighter fit */
        z-index: 1000;
      }

      .left-section .menu-toggle {
        display: none;
      }

      .left-section .menu-items {
        display: flex;
        justify-content: space-between; /* Changed to space-between for even spacing */
        width: 100%;
        flex-wrap: nowrap; /* Prevent wrapping */
      }

      .left-section .menu-items li {
        margin: 0;
        flex: 1; /* Distribute space equally */
        text-align: center;
      }

      .left-section .menu-item {
        padding: 5px;
        justify-content: center; /* Center icons */
      }

      .left-section .menu-item i {
        font-size: 18px; /* Slightly smaller icons for mobile */
        margin-right: 0; /* Remove margin for tighter fit */
      }

      .left-section .menu-item span {
        display: none;
      }

      .chat-list {
        margin-left: 0;
        width: 100vw;
        max-width: 100vw;
        position: static;
        height: calc(100vh - 60px);
        display: block;
      }

      .main-content {
        margin-left: 0;
        width: 100vw;
        max-width: 100vw;
        position: static;
        height: calc(100vh - 60px);
        display: block;
      }

      .chat-area {
        width: 100vw;
        max-width: 100vw;
        position: static;
        height: 100vh; /* Full height when active */
        display: none;
      }

      .chat-area.active {
        display: flex;
      }

      .chat-list .user-item img,
      .chat-list header img,
      .chat-list .requests-list .user-info img,
      .contacts-list .profile-img {
        width: 35px;
        height: 35px;
      }

      .chat-list .details span,
      .chat-area .details span,
      .chat-list .requests-list .user-name,
      .contacts-list .contact-name {
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

      .chat-list .requests-list .request-card {
        flex-direction: column;
        align-items: flex-start;
        padding: 12px;
      }

      .chat-list .requests-list .user-info {
        margin-bottom: 10px;
      }

      .chat-list .requests-list .user-name {
        font-size: 15px;
      }

      .chat-list .requests-list .request-text {
        font-size: 13px;
      }

      .chat-list .requests-list .request-actions {
        width: 100%;
        justify-content: flex-end;
      }

      .chat-list .requests-list .btn-approve,
      .chat-list .requests-list .btn-reject {
        padding: 6px 12px;
        font-size: 13px;
      }

      .contacts-list .contact-name {
        font-size: 14px;
      }

      .contacts-list .contact-status {
        font-size: 11px;
      }

      .create-group {
        padding: 15px;
      }

      .create-group .members-list {
        max-height: 150px;
      }

      .create-group .field input[type="text"],
      .create-group .field input[type="file"] {
        font-size: 0.8rem;
      }
    }

    @media (max-width: 480px) {
      .chat-list .user-item img,
      .chat-list header img,
      .chat-area img,
      .chat-list .requests-list .user-info img,
      .contacts-list .profile-img {
        width: 30px;
        height: 30px;
      }

      .chat-list .details span,
      .chat-area .details span,
      .chat-list .requests-list .user-name,
      .contacts-list .contact-name {
        font-size: 13px;
      }

      .chat .details p {
        font-size: 14px;
      }

      .chat .time {
        font-size: 8px;
      }

      .chat-list .requests-list .request-text {
        font-size: 12px;
      }

      .chat-list .requests-list .btn-approve,
      .chat-list .requests-list .btn-reject {
        padding: 5px 10px;
        font-size: 12px;
      }

      .contacts-list .contact-status {
        font-size: 10px;
      }

      .left-section .menu-item i {
        font-size: 16px; /* Even smaller icons for very small screens */
      }
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="container">
      <!-- Left Section (Sidebar/Footer) -->
      <section class="left-section">
        <div class="left-content">
          <a href="#" class="menu-toggle"><i class="fas fa-bars"></i></a>
          <ul class="menu-items">
            <li><a href="#" data-section="users" class="menu-item"><i class="fas fa-comment"></i><span>Chats</span></a></li>
            <li><a href="#" data-section="create-group" class="menu-item"><i class="fas fa-users"></i><span>Create Groups</span></a></li>
            <li><a href="#" data-section="requests" class="menu-item"><i class="fas fa-user-plus"></i><span>Chat Requests</span></a></li>
           
            <li><a href="#" data-section="contacts" class="menu-item"><i class="fas fa-address-book"></i><span>Contacts</span></a></li>
            <li><a href="settings.php" class="menu-item"><i class="fas fa-cog"></i><span>Settings</span></a></li>

          </ul>
          <ul class="menu-items">
          </ul>
        </div>
      </section>

      <!-- Chat List (Sidebar) -->
      <section class="chat-list">
        <header>
          <h1>Chats</h1>
        </header>
        <div class="search">
          <input type="text" placeholder="Enter name to search...">
          <button><i class="fas fa-search"></i></button>
        </div>
        <div class="toggle-buttons">
          <button id="show-users" class="toggle-btn active">Users</button>
          <button id="show-groups" class="toggle-btn">Groups</button>
        </div>
        <div class="users-list active" id="users-list">
          <?php
          include_once "../php/config.php";
          $unique_id = mysqli_real_escape_string($conn, $_SESSION['unique_id']);
          $sql = mysqli_query($conn, "SELECT u.* FROM users u WHERE u.unique_id != '$unique_id'");
          if (mysqli_num_rows($sql) > 0) {
            while ($row = mysqli_fetch_assoc($sql)) {
              $last_msg_query = mysqli_query($conn, 
                "SELECT msg, created_at 
                 FROM messages 
                 WHERE (outgoing_msg_id = '{$row['unique_id']}' AND incoming_msg_id = '$unique_id') 
                 OR (outgoing_msg_id = '$unique_id' AND incoming_msg_id = '{$row['unique_id']}') 
                 ORDER BY created_at DESC LIMIT 1"
              );
              $last_msg = "No messages yet.";
              $msg_time = "";
              if (mysqli_num_rows($last_msg_query) > 0) {
                $msg_row = mysqli_fetch_assoc($last_msg_query);
                $last_msg = $msg_row['msg'] ?? "Attachment";
                $msg_time = date("h:i A", strtotime($msg_row['created_at']));
                $extension = pathinfo($last_msg, PATHINFO_EXTENSION) ?? '';
                if ($extension) {
                  $last_msg = "Attachment (" . strtoupper($extension) . ")";
                }
                $last_msg = strlen($last_msg) > 20 ? substr($last_msg, 0, 20) . "..." : $last_msg;
              }
              echo '<a href="?user_id=' . $row['unique_id'] . '" class="user-item">';
              echo '<div class="content">';
              echo '<img src="../php/images/' . ($row['img'] ? $row['img'] : 'default.jpg') . '" alt="">';
              echo '<div class="details">';
              echo '<span>' . htmlspecialchars($row['fname'] . " " . $row['lname']) . '</span>';
              echo '<p><span class="last-message">' . htmlspecialchars($last_msg) . '</span> · <span class="timestamp">' . $msg_time . '</span></p>';
              echo '</div>';
              echo '</div>';
              echo '</a>';
            }
          } else {
            echo '<p>No users found.</p>';
          }
          ?>
        </div>
        <div class="group-list" id="group-list">
          <?php
          $group_query = mysqli_query($conn, 
            "SELECT g.group_id, g.group_name 
             FROM groups g 
             JOIN group_members gm ON g.group_id = gm.group_id 
             WHERE gm.unique_id = '$unique_id'");
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
        <div class="requests-list" id="requests-list"></div>
        <div id="contacts-list" class="contacts-list"></div>
        <div id="create-group" class="create-group"></div>
      </section>

      <!-- Main Content -->
      <section class="main-content">
        <!-- Content dynamically loaded here -->
      </section>

      <!-- Chat Area -->
      <section class="chat-area">
        <?php
        if (isset($_GET['user_id'])) {
          $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
          $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = '{$user_id}'");
          if (mysqli_num_rows($sql) > 0) {
            $row = mysqli_fetch_assoc($sql);
        ?>
        <header>
          <a href="javascript:void(0)" class="back-icon"><i class="fas fa-arrow-left"></i></a>
          <img src="../php/images/<?php echo $row['img'] ? $row['img'] : 'default.jpg'; ?>" alt="">
          <div class="details">
            <span><?php echo htmlspecialchars($row['fname'] . " " . $row['lname']); ?></span>
            <p><?php echo htmlspecialchars($row['status']); ?></p>
          </div>
        </header>
        <div class="chat-box"></div>
        <form action="../php/insert-chat.php" method="POST" enctype="multipart/form-data" class="typing-area">
          <input type="file" name="file" id="fileInput" class="file-input">
          <label for="fileInput" class="insert-button"><i class="fas fa-paperclip"></i></label>
          <input type="text" name="outgoing_id" value="<?php echo $_SESSION['unique_id']; ?>" hidden>
          <input type="text" name="incoming_id" value="<?php echo $user_id; ?>" hidden>
          <input type="text" name="message" class="input-field" placeholder="Type a message here...">
          <button>Send<i class="fab fa-telegram-plane"></i></button>
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
          <a href="javascript:void(0)" class="back-icon"><i class="fas fa-arrow-left"></i></a>
          <img src="<?php echo !empty($group['group_image']) ? '../php/images/' . $group['group_image'] : '../php/images/1749820324penguin.jpg'; ?>" alt="Group Image">
          <div class="details">
            <span><?php echo htmlspecialchars($group['group_name']); ?></span>
            <?php
            $creator_check = mysqli_query($conn, "SELECT * FROM groups WHERE group_id = '$group_id' AND created_by = '{$_SESSION['unique_id']}'");
            if (mysqli_num_rows($creator_check) > 0) {
              echo '<a href="edit-group.php?group_id=' . $group_id . '" class="edit-group-btn">Edit Group</a>';
            }
            ?>
          </div>
        </header>
        <div class="chat-box"></div>
        <form action="../php/insert-group-chat.php" method="POST" class="typing-area" autocomplete="off" enctype="multipart/form-data">
          <input type="file" name="file" id="fileInput" class="file-input" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" hidden>
          <label for="fileInput" class="insert-button"><i class="fas fa-paperclip"></i></label>
          <input type="text" name="group_id" value="<?php echo $group_id; ?>" hidden>
          <input type="text" name="sender_id" value="<?php echo $_SESSION['unique_id']; ?>" hidden>
          <input type="text" name="message" class="input-field" placeholder="Type a message here...">
          <button>Send<i class="fab fa-telegram-plane"></i></button>
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

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const leftSection = document.querySelector('.left-section');
      const menuToggle = document.querySelector('.menu-toggle');
      const mainContent = document.querySelector('.main-content');
      const chatArea = document.querySelector('.chat-area');
      const chatList = document.querySelector('.chat-list');
      const backIcons = document.querySelectorAll('.back-icon');
      const menuItems = document.querySelectorAll('.menu-item');
      const usersList = document.querySelector('.users-list');
      const groupList = document.querySelector('.group-list');
      const requestsList = document.querySelector('.requests-list');
      const contactsList = document.querySelector('.contacts-list');
      const createGroup = document.querySelector('.create-group');

      // Debug element existence
      console.log('Menu Toggle:', menuToggle);
      console.log('Left Section:', leftSection);
      if (!menuToggle) console.error('Menu toggle element not found');
      if (!leftSection) console.error('Left section element not found');

      // Update layout based on screen size and state
      function updateLayout() {
        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        console.log('Is Mobile:', isMobile, 'Left Section Expanded:', leftSection.classList.contains('expanded'));

        if (isMobile) {
          // Hide footer when chat is active
          if (chatArea.classList.contains('active') || window.location.search.includes('user_id') || window.location.search.includes('group_id')) {
            leftSection.style.display = 'none'; // Hide footer
            mainContent.style.display = 'none';
            chatList.style.display = 'none';
            chatArea.style.display = 'flex';
            contactsList.style.display = 'none';
            createGroup.style.display = 'none';
          } else if (contactsList.classList.contains('active')) {
            leftSection.style.display = 'flex'; // Show footer
            mainContent.style.display = 'none';
            chatList.style.display = 'block'; // Ensure chat-list is visible to show contacts
            chatArea.style.display = 'none';
            contactsList.style.display = 'block'; // Explicitly show contacts
            createGroup.style.display = 'none';
          } else {
            leftSection.style.display = 'flex'; // Show footer
            mainContent.style.display = 'block';
            chatList.style.display = 'block';
            chatArea.style.display = 'none';
            contactsList.style.display = 'none';
            createGroup.style.display = 'none';
          }
        } else {
          leftSection.style.display = 'block';
          leftSection.style.transform = leftSection.classList.contains('expanded') ? 'translateX(0)' : 'translateX(-25vw)';
          mainContent.style.display = 'block';
          chatList.style.display = 'flex';
          chatArea.style.display = 'flex';
          contactsList.style.display = contactsList.classList.contains('active') ? 'block' : 'none';
          createGroup.style.display = 'none';
        }

        console.log('Left Section Display:', leftSection.style.display, 'Transform:', leftSection.style.transform);
      }

      // Sidebar toggle (desktop only)
      if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
          e.preventDefault();
          if (!window.matchMedia('(max-width: 768px)').matches) {
            console.log('Menu toggle clicked');
            leftSection.classList.toggle('expanded');
            updateLayout();
          }
        });
      }

      // Handle user/group clicks in chat-list
      chatList.addEventListener('click', function(e) {
        const link = e.target.closest('.chat-link, .user-item');
        if (link) {
          e.preventDefault();
          const isMobile = window.matchMedia('(max-width: 768px)').matches;
          if (isMobile) {
            chatArea.classList.add('active');
            mainContent.style.display = 'none';
            chatList.style.display = 'none';
            chatArea.style.display = 'flex';
            contactsList.style.display = 'none';
            createGroup.style.display = 'none';
            leftSection.style.display = 'none'; // Hide footer when chat is opened
          }
          window.location.href = link.href;
        }
      });

      // Handle back button
      backIcons.forEach(icon => {
        icon.addEventListener('click', function(e) {
          e.preventDefault();
          const isMobile = window.matchMedia('(max-width: 768px)').matches;
          if (isMobile) {
            chatArea.classList.remove('active');
            chatArea.style.display = 'none';
            mainContent.style.display = 'block';
            chatList.style.display = 'block';
            contactsList.style.display = 'none';
            createGroup.style.display = 'none';
            leftSection.style.display = 'flex'; // Show footer when returning to chat list
          }
          window.location.href = 'users.php';
        });
      });

      // Toggle users/groups
      document.getElementById('show-users').addEventListener('click', function() {
        usersList.classList.add('active');
        groupList.classList.remove('active');
        requestsList.classList.remove('active');
        contactsList.classList.remove('active');
        createGroup.classList.remove('active');
        this.classList.add('active');
        document.getElementById('show-groups').classList.remove('active');
        document.querySelector('.chat-list header h1').textContent = 'Chats';
        updateLayout();
      });

      document.getElementById('show-groups').addEventListener('click', function() {
        usersList.classList.remove('active');
        groupList.classList.add('active');
        requestsList.classList.remove('active');
        contactsList.classList.remove('active');
        createGroup.classList.remove('active');
        this.classList.add('active');
        document.getElementById('show-users').classList.remove('active');
        document.querySelector('.chat-list header h1').textContent = 'Groups';
        updateLayout();
      });

      // Menu item handling
      menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
          e.preventDefault();
          const section = this.getAttribute('data-section');
          const page = this.getAttribute('data-page');
          const isMobile = window.matchMedia('(max-width: 768px)').matches;

          menuItems.forEach(i => i.classList.remove('active'));
          this.classList.add('active');

          if (section === 'users') {
            usersList.classList.add('active');
            groupList.classList.remove('active');
            requestsList.classList.remove('active');
            contactsList.classList.remove('active');
            createGroup.classList.remove('active');
            document.getElementById('show-users').classList.add('active');
            document.getElementById('show-groups').classList.remove('active');
            document.querySelector('.chat-list header h1').textContent = 'Chats';
            if (!isMobile) {
              leftSection.classList.add('expanded');
            }
            updateLayout();
          } else if (section === 'requests') {
            usersList.classList.remove('active');
            groupList.classList.remove('active');
            requestsList.classList.add('active');
            contactsList.classList.remove('active');
            createGroup.classList.remove('active');
            document.getElementById('show-users').classList.remove('active');
            document.getElementById('show-groups').classList.remove('active');
            document.querySelector('.chat-list header h1').textContent = 'Chat Requests';
            if (!isMobile) {
              leftSection.classList.add('expanded');
            }
            loadChatRequests();
            updateLayout();
          } else if (section === 'contacts') {
            usersList.classList.remove('active');
            groupList.classList.remove('active');
            requestsList.classList.remove('active');
            contactsList.classList.add('active');
            createGroup.classList.remove('active');
            document.getElementById('show-users').classList.remove('active');
            document.getElementById('show-groups').classList.remove('active');
            document.querySelector('.chat-list header h1').textContent = 'Contacts';
            if (!isMobile) {
              leftSection.classList.add('expanded');
            }
            loadContactsList();
            updateLayout();
          } else if (section === 'create-group') {
            usersList.classList.remove('active');
            groupList.classList.remove('active');
            requestsList.classList.remove('active');
            contactsList.classList.remove('active');
            createGroup.classList.remove('active');
            document.getElementById('show-users').classList.remove('active');
            document.getElementById('show-groups').classList.remove('active');
            document.querySelector('.chat-list header h1').textContent = 'Chats';
            showCreateGroupModal();
            updateLayout();
          } else if (page) {
            document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
            document.getElementById(page)?.classList.add('active');
            usersList.classList.remove('active');
            groupList.classList.remove('active');
            requestsList.classList.remove('active');
            contactsList.classList.remove('active');
            createGroup.classList.remove('active');
            if (!isMobile) {
              leftSection.classList.add('expanded');
            }
            updateLayout();
          } else {
            window.location.href = this.href;
          }
        });
      });

      // Create and show modal for create group
      function showCreateGroupModal() {
        // Remove any existing modal
        const existingModal = document.querySelector('.modal');
        if (existingModal) existingModal.remove();

        // Create modal elements
        const modal = document.createElement('div');
        modal.className = 'modal';
        const modalContent = document.createElement('div');
        modalContent.className = 'modal-content';
        const closeButton = document.createElement('button');
        closeButton.className = 'close-btn';
        closeButton.innerHTML = '×';

        closeButton.addEventListener('click', () => modal.remove());
        modalContent.appendChild(closeButton);
        modal.appendChild(modalContent);
        document.body.appendChild(modal);

        // Load create group content
        modalContent.innerHTML += '<p>Loading...</p>';

        fetch('create-group.php?ajax=1', {
          method: 'GET',
          headers: { 'Accept': 'text/html, application/json' }
        })
        .then(res => {
          if (!res.ok) {
            throw new Error(`HTTP error! Status: ${res.status}`);
          }
          return res.text();
        })
        .then(data => {
          try {
            const json = JSON.parse(data);
            if (json.status === 'error') {
              modalContent.innerHTML = `<p>Error: ${json.message}</p>`;
              modalContent.appendChild(closeButton);
              return;
            }
          } catch (e) {
            modalContent.innerHTML = data;
            const form = modalContent.querySelector('#groupForm');
            const checkboxes = form.querySelectorAll('input[name="members[]"]');
            const warning = form.querySelector('#memberCountWarning');
            const counter = form.querySelector('#memberCount');

            function updateCount() {
              const selected = [...checkboxes].filter(cb => cb.checked).length;
              counter.textContent = `Selected: ${selected} / 250`;
              if (selected > 250) {
                warning.style.display = 'block';
              } else {
                warning.style.display = 'none';
              }
            }

            checkboxes.forEach(cb => {
              cb.addEventListener('change', function () {
                const selected = [...checkboxes].filter(cb => cb.checked);
                if (selected.length > 250) {
                  this.checked = false;
                }
                updateCount();
              });
            });

            updateCount();

            form.addEventListener('submit', function(e) {
              e.preventDefault();
              const selected = [...checkboxes].filter(cb => cb.checked);
              if (selected.length > 250) {
                warning.style.display = 'block';
                return;
              }
              const formData = new FormData(this);
              fetch('../php/create-group.php', {
                method: 'POST',
                body: formData,
                headers: {
                  'X-Requested-With': 'XMLHttpRequest'
                }
              })
              .then(res => {
                if (!res.ok) {
                  return res.text().then(text => {
                    throw new Error(`HTTP error! Status: ${res.status}, Response: ${text}`);
                  });
                }
                return res.json();
              })
              .then(data => {
                if (data.status === 'success') {
                  alert('Group created successfully!');
                  modal.remove();
                  window.location.href = `users.php?group_id=${data.group_id}`;
                } else {
                  modalContent.innerHTML = `<p>Error: ${data.message}</p>`;
                  modalContent.appendChild(closeButton);
                }
              })
              .catch(error => {
                console.error('Error:', error);
                modalContent.innerHTML = `<p>Error creating group: ${error.message}</p>`;
                modalContent.appendChild(closeButton);
              });
            });
          }
        })
        .then(() => {
          // Ensure footer is visible when modal is opened
          const isMobile = window.matchMedia('(max-width: 768px)').matches;
          if (isMobile) {
            leftSection.style.display = 'flex';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          modalContent.innerHTML = `<p>Error loading create group form: ${error.message}</p>`;
          modalContent.appendChild(closeButton);
        });

        // Close modal on click outside
        modal.addEventListener('click', (e) => {
          if (e.target === modal) modal.remove();
        });

        // Close modal on Escape key
        modal.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') modal.remove();
        });
      }

      // Load chat requests via AJAX
      function loadChatRequests() {
        const requestsList = document.getElementById('requests-list');
        requestsList.innerHTML = '<p>Loading...</p>';

        fetch('chat-requests.php', {
          method: 'GET',
          headers: { 'Accept': 'text/html, application/json' }
        })
        .then(res => {
          if (!res.ok) {
            throw new Error(`HTTP error! Status: ${res.status}`);
          }
          return res.text();
        })
        .then(data => {
          try {
            const json = JSON.parse(data);
            if (json.status === 'error') {
              requestsList.innerHTML = `<p>Error: ${json.message}</p>`;
              return;
            }
          } catch (e) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, 'text/html');
            const chatBox = doc.querySelector('#requests-container');
            if (chatBox) {
              requestsList.innerHTML = '';
              requestsList.appendChild(chatBox);
              chatBox.addEventListener('click', function(e) {
                if (e.target.matches('.btn-approve') || e.target.matches('.btn-reject')) {
                  const card = e.target.closest('.request-card');
                  const senderId = card.dataset.senderId;
                  const action = e.target.dataset.action;

                  e.target.style.opacity = '0.7';
                  setTimeout(() => { e.target.style.opacity = '1'; }, 200);

                  fetch('../php/approve-request.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `sender_id=${encodeURIComponent(senderId)}&action=${encodeURIComponent(action)}`
                  })
                  .then(res => res.json())
                  .then(data => {
                    if (data.status === 'success') {
                      card.style.opacity = '0';
                      setTimeout(() => { 
                        card.remove(); 
                        if (!chatBox.querySelector('.request-card')) {
                          chatBox.innerHTML = `
                            <div class="no-requests">
                              <i class="fas fa-info-circle"></i>
                              <p>No chat requests at the moment.</p>
                            </div>`;
                        }
                      }, 300);
                    } else {
                      alert('Failed to process request: ' + data.status);
                    }
                  })
                  .catch(error => console.error('Error:', error));
                }
              });
            } else {
              requestsList.innerHTML = '<p>Error: Could not load requests.</p>';
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          requestsList.innerHTML = '<p>Error loading requests.</p>';
        });
      }

      // Load contacts list via AJAX
      function loadContactsList() {
        const contactsList = document.querySelector('#contacts-list');
        contactsList.innerHTML = '<p>Loading...</p>';

        fetch('contact-list.php?ajax=1', {
          method: 'GET',
          headers: { 'Accept': 'text/html, application/json' }
        })
        .then(res => {
          if (!res.ok) {
            throw new Error(`HTTP error! Status: ${res.status}`);
          }
          return res.text();
        })
        .then(data => {
          try {
            const json = JSON.parse(data);
            if (json.status === 'error') {
              contactsList.innerHTML = `<p>Error: ${json.message}</p>`;
              return;
            }
          } catch (e) {
            contactsList.innerHTML = data;
            fixUsersListProfileImages();
            const contactItems = contactsList.querySelectorAll('.contact-item');
            contactItems.forEach(item => {
              item.addEventListener('click', function(e) {
                e.preventDefault();
                const isMobile = window.matchMedia('(max-width: 768px)').matches;
                if (isMobile) {
                  chatArea.classList.add('active');
                  mainContent.style.display = 'none';
                  chatList.style.display = 'none';
                  chatArea.style.display = 'flex';
                  contactsList.style.display = 'none';
                  createGroup.style.display = 'none';
                  leftSection.style.display = 'none'; // Hide footer when chat is opened from contacts
                }
                window.location.href = this.href;
              });
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
          contactsList.innerHTML = '<p>Error loading contacts.</p>';
        });
      }

      // Fix profile images
      function fixUsersListProfileImages() {
        const userImages = document.querySelectorAll('.users-list img:not([data-fixed]), .requests-list img:not([data-fixed]), .contacts-list img:not([data-fixed])');
        userImages.forEach(image => {
          image.setAttribute('data-fixed', 'true');
          if (image.src.includes('/images/')) {
            const filename = image.src.split('/images/')[1] || 'default.jpg';
            image.src = '../php/images/' + filename;
          }
          image.onerror = function() {
            this.src = '../php/images/default.jpg';
          };
          if (!image.src || image.src.includes('undefined') || image.src === window.location.href || image.src.endsWith('/images/')) {
            image.src = '../php/images/default.jpg';
          }
        });
      }

      fixUsersListProfileImages();
      const observer = new MutationObserver(() => {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(fixUsersListProfileImages, 100);
      });
      let debounceTimeout;
      observer.observe(usersList, { childList: true, subtree: true });
      observer.observe(requestsList, { childList: true, subtree: true });
      observer.observe(contactsList, { childList: true, subtree: true });

      // Initial layout setup
      updateLayout();

      // Update layout on resize
      window.addEventListener('resize', updateLayout);
    });
  </script>
</body>
</html>