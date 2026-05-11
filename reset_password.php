<?php
session_start();
require 'db_connect.php';

// Only admin can access this page
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$success = "";
$error = "";
$target_user = null;

// Fetch all users (admin + owner) for the dropdown
$users_result = $conn->query("SELECT user_id, username, role FROM user ORDER BY role ASC");
$all_users = [];
while ($row = $users_result->fetch_assoc()) {
    $all_users[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_id     = (int) $_POST['target_user_id'];
    $new_password  = $_POST['new_password'];
    $confirm_pass  = $_POST['confirm_password'];
    $admin_pass    = $_POST['admin_password'];

    // Verify admin's own password first
    $stmt = $conn->prepare("SELECT password FROM user WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (!password_verify($admin_pass, $admin['password'])) {
        $error = "Your admin password is incorrect.";
    } elseif ($target_id <= 0) {
        $error = "Please select a user account to reset.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_pass) {
        $error = "New passwords do not match.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
        $update->bind_param("si", $hashed, $target_id);

        if ($update->execute()) {
            // Get the username of who was reset for the success message
            $name_stmt = $conn->prepare("SELECT username FROM user WHERE user_id = ?");
            $name_stmt->bind_param("i", $target_id);
            $name_stmt->execute();
            $name_res = $name_stmt->get_result()->fetch_assoc();
            $success = "Password for <strong>" . htmlspecialchars($name_res['username']) . "</strong> has been reset successfully!";
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
    <title>Reset Password - Rodal Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --bg-yellow: #fffdf0;
            --primary-yellow: #f1c40f;
            --text-dark: #333;
        }

        .reset-pw-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--bg-yellow);
        }

        .reset-pw-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 2px solid var(--primary-yellow);
            width: 420px;
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

        .input-group select,
        .input-group input {
            width: 100%;
            padding: 11px 12px;
            border: 1.5px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
            transition: border 0.2s;
            background: #fff;
        }

        .input-group select:focus,
        .input-group input:focus {
            border-color: var(--primary-yellow);
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap input {
            padding-right: 42px;
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

        .section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #aaa;
            margin: 20px 0 12px;
        }

        .divider {
            border: none;
            border-top: 1.5px solid #f5f5f5;
            margin: 18px 0;
        }

        .btn-reset {
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

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(241,196,15,0.4);
        }

        .btn-back {
            display: block;
            text-align: center;
            margin-top: 14px;
            font-size: 13px;
            color: #888;
            text-decoration: none;
        }

        .btn-back:hover { color: #333; }

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

        .role-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2px 7px;
            border-radius: 20px;
            margin-left: 6px;
            vertical-align: middle;
        }

        .badge-owner {
            background: #fff3cd;
            color: #856404;
        }

        .badge-admin {
            background: #d1ecf1;
            color: #0c5460;
        }
    </style>
</head>
<body>
<div class="reset-pw-wrapper">
    <div class="reset-pw-card">

        <div class="card-header">
            <div class="card-icon"><i class="fas fa-shield-alt"></i></div>
            <div>
                <h2>Reset Password</h2>
                <p>Admin control — reset any account's password</p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <!-- Step 1: Select account to reset -->
            <p class="section-label">Step 1 — Select Account to Reset</p>

            <div class="input-group">
                <label>Choose Account</label>
                <select name="target_user_id" required>
                    <option value="">— Select a user —</option>
                    <?php foreach ($all_users as $u): ?>
                        <option value="<?php echo $u['user_id']; ?>"
                            <?php echo (isset($_POST['target_user_id']) && $_POST['target_user_id'] == $u['user_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['username']); ?> 
                            (<?php echo ucfirst($u['role']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <hr class="divider">

            <!-- Step 2: Set new password -->
            <p class="section-label">Step 2 — Set New Password</p>

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

            <hr class="divider">

            <!-- Step 3: Verify admin identity -->
            <p class="section-label">Step 3 — Confirm Your Admin Password</p>

            <div class="input-group">
                <label>Your Password <span style="color:#888;font-weight:400;">(to authorize this action)</span></label>
                <div class="password-wrap">
                    <input type="password" name="admin_password" id="adm_pw" placeholder="Enter your admin password" required>
                    <i class="fas fa-eye toggle-pw" onclick="toggleVisibility('adm_pw', this)"></i>
                </div>
            </div>

            <button type="submit" class="btn-reset">
                <i class="fas fa-sync-alt"></i> Reset Password
            </button>
        </form>

        <a href="admin_dashboard.php" class="btn-back">
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
        title: 'Password Reset!',
        html: '<?php echo addslashes($success); ?>',
        confirmButtonColor: '#f1c40f',
        confirmButtonText: 'OK'
    }).then(() => {
        window.location.href = 'admin_dashboard.php';
    });
    <?php endif; ?>
</script>
</body>
</html>
