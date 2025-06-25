<?php
session_start();
if (!isset($_SESSION['unique_id'])) {
    header("location: login.php");
    exit;
}
include_once "../php/config.php";

$receiver_id = $_SESSION['unique_id'];
$sql = "SELECT message_requests.*, users.fname, users.lname, users.img 
        FROM message_requests 
        LEFT JOIN users ON users.unique_id = message_requests.sender_id 
        WHERE message_requests.receiver_id = ? 
        AND message_requests.status = 'pending'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $receiver_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<?php include_once "../public/header.php"; ?>

<body>
<div class="wrapper">
  <section class="chat-area">
    <header>
      <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
      <div class="details">
        <span>Pending Chat Requests</span>
        <p>Approve or reject users who want to chat with you.</p>
      </div>
    </header>

    <div class="chat-box" id="requests-container">
      <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <div class="request-card" data-sender-id="<?php echo htmlspecialchars($row['sender_id']); ?>">
            <div class="user-info">
              <img src="../php/images/<?php echo htmlspecialchars($row['img']); ?>" alt="User Avatar">
              <div class="user-details">
                <span class="user-name"><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></span>
                <p class="request-text">Wants to chat with you</p>
              </div>
            </div>
            <div class="request-actions">
              <button class="btn-approve" data-action="accept"><i class="fas fa-check"></i> Accept</button>
              <button class="btn-reject" data-action="reject"><i class="fas fa-times"></i> Reject</button>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="no-requests">
          <i class="fas fa-info-circle"></i>
          <p>No chat requests at the moment.</p>
        </div>
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
    overflow: hidden;
}
.wrapper {
    display: flex;
    justify-content: center;
    min-height: 100vh;
    width: 100%;
}
.chat-area {
    width: 100%;
    max-width: 800px;
    background: #fff;
    display: flex;
    flex-direction: column;
    height: 100vh;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    border-radius: 10px;
    overflow: hidden;
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
.chat-area .details span {
    font-weight: bold;
    font-size: 16px;
}
.chat-area .details p {
    font-size: 12px;
    margin: 0;
    color: #ddd;
}
.chat-box {
    flex-grow: 1;
    background: #e5ddd5;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.request-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}
.request-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.user-info img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}
.user-details {
    display: flex;
    flex-direction: column;
}
.user-name {
    font-size: 16px;
    font-weight: bold;
    color: #333;
}
.request-text {
    font-size: 14px;
    color: #888;
    margin: 0;
}
.request-actions {
    display: flex;
    gap: 10px;
}
.btn-approve, .btn-reject {
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
.btn-approve {
    background-color: #28a745;
    color: white;
}
.btn-reject {
    background-color: #dc3545;
    color: white;
}
.btn-approve:hover {
    background-color: #218838;
    transform: scale(1.05);
}
.btn-reject:hover {
    background-color: #c82333;
    transform: scale(1.05);
}
.btn-approve:active, .btn-reject:active {
    transform: scale(0.95);
}
.no-requests {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-grow: 1;
    color: #888;
    font-size: 16px;
    text-align: center;
}
.no-requests i {
    font-size: 24px;
    margin-bottom: 10px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .chat-area {
        width: 100%;
        margin: 0;
        border-radius: 0;
    }
    .chat-area header {
        padding: 10px;
    }
    .chat-area .details span {
        font-size: 14px;
    }
    .chat-area .details p {
        font-size: 11px;
    }
    .request-card {
        flex-direction: column;
        align-items: flex-start;
        padding: 12px;
    }
    .user-info {
        margin-bottom: 10px;
    }
    .user-info img {
        width: 35px;
        height: 35px;
    }
    .user-name {
        font-size: 15px;
    }
    .request-text {
        font-size: 13px;
    }
    .request-actions {
        width: 100%;
        justify-content: flex-end;
    }
    .btn-approve, .btn-reject {
        padding: 6px 12px;
        font-size: 13px;
    }
}
@media (max-width: 480px) {
    .user-info img {
        width: 30px;
        height: 30px;
    }
    .user-name {
        font-size: 14px;
    }
    .request-text {
        font-size: 12px;
    }
    .btn-approve, .btn-reject {
        padding: 5px 10px;
        font-size: 12px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('requests-container');

  container.addEventListener('click', function (e) {
    if (e.target.matches('.btn-approve') || e.target.matches('.btn-reject')) {
      const card = e.target.closest('.request-card');
      const senderId = card.dataset.senderId;
      const action = e.target.dataset.action;

      // Add visual feedback
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
          setTimeout(() => { card.remove(); }, 300);
          // Check if no requests remain
          if (!container.querySelector('.request-card')) {
            container.innerHTML = `
              <div class="no-requests">
                <i class="fas fa-info-circle"></i>
                <p>No chat requests at the moment.</p>
              </div>`;
          }
        } else {
          alert('Failed to process request: ' + data.status);
        }
      })
      .catch(error => console.error('Error:', error));
    }
  });
});
</script>
</body>
</html>