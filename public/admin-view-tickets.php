<?php
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/admin-functions.php';
checkAdminAuth();

// Handle ticket resolution
if (isset($_GET['resolve'])) {
    $ticket_id = intval($_GET['resolve']);
    $update_query = "UPDATE support_tickets SET status = 'resolved', resolved_at = NOW() WHERE id = $ticket_id";
    if ($conn->query($update_query)) {
        $success = "Ticket #$ticket_id has been resolved successfully.";
    } else {
        $error = "Error resolving ticket: " . $conn->error;
    }
}

// Fetch all tickets
$query = "SELECT t.*, u.fname, u.lname, u.email 
          FROM support_tickets t
          JOIN users u ON t.user_id = u.unique_id
          ORDER BY t.created_at DESC";
$result = $conn->query($query);
$tickets = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Support Tickets</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .ticket-card {
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        .ticket-open {
            border-left-color: #dc3545;
        }
        .ticket-resolved {
            border-left-color: #28a745;
        }
        .attachment-badge {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Support Tickets</h2>
            <a href="admin-dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <?php if (empty($tickets)): ?>
                    <div class="alert alert-info">No support tickets found.</div>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="card ticket-card <?= $ticket['status'] === 'open' ? 'ticket-open' : 'ticket-resolved' ?>">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Ticket #<?= $ticket['id'] ?>: <?= htmlspecialchars($ticket['subject']) ?></h5>
                                <span class="badge bg-<?= $ticket['status'] === 'open' ? 'danger' : 'success' ?>">
                                    <?= ucfirst($ticket['status']) ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p><strong>From:</strong> <?= htmlspecialchars($ticket['fname'] . ' ' . $ticket['lname']) ?> (<?= htmlspecialchars($ticket['email']) ?>)</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Submitted:</strong> <?= date('M j, Y g:i A', strtotime($ticket['created_at'])) ?></p>
                                        <?php if ($ticket['status'] === 'resolved'): ?>
                                            <p><strong>Resolved:</strong> <?= date('M j, Y g:i A', strtotime($ticket['resolved_at'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6>Message:</h6>
                                    <p><?= nl2br(htmlspecialchars($ticket['message'])) ?></p>
                                </div>
                                
                                <?php if (!empty($ticket['attachment'])): ?>
                                    <div class="mb-3">
                                        <h6>Attachment:</h6>
                                        <a href="<?= $ticket['attachment'] ?>" target="_blank" class="badge bg-info text-dark attachment-badge">
                                            <i class="fas fa-paperclip"></i> View Attachment
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($ticket['status'] === 'open'): ?>
                                    <div class="text-end">
                                        <a href="?resolve=<?= $ticket['id'] ?>" class="btn btn-success">
                                            <i class="fas fa-check"></i> Resolve Ticket
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>