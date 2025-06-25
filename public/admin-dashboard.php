<?php
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/admin-functions.php';
checkAdminAuth();

// Fetch stats directly
$stats = [
    'total_users' => 0,
    'total_groups' => 0,
    'total_messages' => 0,
    'online_users' => 0,
    'open_tickets' => 0
];

$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) $stats['total_users'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM groups");
if ($result) $stats['total_groups'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT (SELECT COUNT(*) FROM messages) + (SELECT COUNT(*) FROM group_messages) as total");
if ($result) $stats['total_messages'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'Active now'");
if ($result) $stats['online_users'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM support_tickets WHERE status = 'open'");
if ($result) $stats['open_tickets'] = $result->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .quick-actions .btn {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Admin Dashboard</h2>
            <a href="../php/admin-logout.php" class="btn btn-danger">Logout</a>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Users</h6>
                                <h2 class="mb-0"><?= $stats['total_users'] ?></h2>
                            </div>
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Groups</h6>
                                <h2 class="mb-0"><?= $stats['total_groups'] ?></h2>
                            </div>
                            <i class="fas fa-users-cog fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Messages</h6>
                                <h2 class="mb-0"><?= $stats['total_messages'] ?></h2>
                            </div>
                            <i class="fas fa-comments fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card stat-card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Online Users</h6>
                                <h2 class="mb-0"><?= $stats['online_users'] ?></h2>
                            </div>
                            <i class="fas fa-user-check fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card stat-card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Open Tickets</h6>
                                <h2 class="mb-0"><?= $stats['open_tickets'] ?></h2>
                            </div>
                            <i class="fas fa-ticket-alt fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body quick-actions">
                        <a href="admin-users.php" class="btn btn-primary btn-block">
                            <i class="fas fa-users mr-2"></i> Manage Users
                        </a>
                        <a href="admin-groups.php" class="btn btn-primary btn-block">
                            <i class="fas fa-users-cog mr-2"></i> Manage Groups
                        </a>
                        <a href="admin-private-chats.php" class="btn btn-primary btn-block">
                            <i class="fas fa-comments mr-2"></i> View Private Chats
                        </a>
                        <a href="admin-view-tickets.php" class="btn btn-info btn-block">
                            <i class="fas fa-ticket-alt mr-2"></i> View Support Tickets
                        </a>
                        <a href="admin-create-user.php" class="btn btn-success btn-block">
                            <i class="fas fa-user-plus mr-2"></i> Create New User
                        </a>
                        <a href="admin-create-group.php" class="btn btn-success btn-block">
                            <i class="fas fa-plus-circle mr-2"></i> Create New Group
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>