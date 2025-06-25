<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            margin-top: 100px;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header text-center bg-dark text-white">
                    <h3>Admin Login</h3>
                </div>
                <div class="card-body">
                    <form id="adminLoginForm" action="../php/admin-login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                    <div id="errorMessage" class="mt-3 text-danger text-center"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#adminLoginForm').submit(function(e) {
                e.preventDefault();
                $('#errorMessage').text(''); // Clear previous errors
                
                const formData = $(this).serialize();
                $.post('../php/admin-login.php', formData)
                    .done(function(response) {
                        if (response === 'success') {
                            window.location.href = 'admin-dashboard.php';
                        } else {
                            $('#errorMessage').text(response);
                        }
                    })
                    .fail(function() {
                        $('#errorMessage').text('An error occurred. Please try again.');
                    });
            });
        });
    </script>
</body>
</html>