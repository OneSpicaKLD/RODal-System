<?php
session_start();
require 'db_connect.php';

// Only owner can access this page
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php");
    exit();
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password  = $_POST['current_password'];
    $new_password      = $_POST['new_password'];
    $confirm_password  = $_POST['confirm_password'];

    // Fetch current hashed password from DB
    $stmt = $conn->prepare("SELECT password FROM user WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (password_verify($new_password, $user['password'])) {
        $error = "New password must be different from the current password.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE user SET password = ? WHERE username = ?");
        $update->bind_param("ss", $hashed, $_SESSION['username']);
        if ($update->execute()) {
            $success = "Password changed successfully!";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Rodal Store</title>

    <link rel="icon" type="image/png" href="http://localhost:8000/RODALSystem/rodal-icon.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --bg-yellow: #fffdf0;
            --primary-yellow: #f1c40f;
            --text-dark: #333;
        }

        .change-pw-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--bg-yellow);
        }

        .change-pw-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--primary-yellow);
            width: 400px;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-yellow);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #333;
            flex-shrink: 0;
        }

        .card-header h2 {
            font-size: 1.3rem;
            color: var(--text-dark);
            margin: 0;
        }

        .card-header p {
            font-size: 0.82rem;
            color: #888;
            margin: 2px 0 0;
        }

        .input-group {
            margin-bottom: 16px;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #444;
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap input {
            width: 100%;
            padding: 11px 42px 11px 12px;
            border: 1.5px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
            transition: border 0.2s;
        }

        .password-wrap input:focus {
            border-color: var(--primary-yellow);
        }

        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 15px;
        }

        .btn-change {
            width: 100%;
            padding: 12px;
            background: var(--primary-yellow);
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
            color: #333;
        }

        .btn-change:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(241, 196, 15, 0.4);
        }

        .btn-back {
            display: block;
            text-align: center;
            margin-top: 14px;
            font-size: 13px;
            color: #888;
            text-decoration: none;
        }

        .btn-back:hover {
            color: #333;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-error {
            background: #fdecea;
            color: #c0392b;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #eafaf1;
            color: #1e8449;
            border: 1px solid #a9dfbf;
        }

        .divider {
            border: none;
            border-top: 1.5px solid #f5f5f5;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="change-pw-wrapper">
        <div class="change-pw-card">

            <div class="card-header">
                <div class="card-icon"><i class="fas fa-key"></i></div>
                <div>
                    <h2>Change Password</h2>
                    <p>Update your owner account password</p>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="input-group">
                    <label>Current Password</label>
                    <div class="password-wrap">
                        <input type="password" name="current_password" id="cur_pw" placeholder="Enter current password" required>
                        <i class="fas fa-eye toggle-pw" onclick="toggleVisibility('cur_pw', this)"></i>
                    </div>
                </div>

                <hr class="divider">

                <div class="input-group">
                    <label>New Password</label>
                    <div class="password-wrap">
                        <input type="password" name="new_password" id="new_pw" placeholder="Enter new password" required>
                        <i class="fas fa-eye toggle-pw" onclick="toggleVisibility('new_pw', this)"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label>Confirm New Password</label>
                    <div class="password-wrap">
                        <input type="password" name="confirm_password" id="con_pw" placeholder="Re-enter new password" required>
                        <i class="fas fa-eye toggle-pw" onclick="toggleVisibility('con_pw', this)"></i>
                    </div>
                </div>

                <button type="submit" class="btn-change">
                    <i class="fas fa-save"></i> Save New Password
                </button>
            </form>

            <a href="dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        function toggleVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        <?php if (!empty($success)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Password Changed!',
                text: 'Your password has been updated successfully.',
                confirmButtonColor: '#f1c40f',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'dashboard.php';
            });
        <?php endif; ?>
    </script>
</body>

</html>