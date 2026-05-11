<?php
require 'db_connect.php';
session_start();
date_default_timezone_set('Asia/Manila');

// Proteksyon: Kung walang session email, balik sa forgot password
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_pass.php");
    exit();
}

$message = "";
$status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_input = $_POST['otp'];
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_SESSION['reset_email'];

    // 1. Sync DB Time
    $conn->query("SET time_zone = '+08:00'");

    // 2. Verify OTP and Expiry
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? AND reset_token = ? AND token_expire > NOW()");
    $stmt->bind_param("ss", $email, $otp_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 3. VALID! Update Password & Clear OTP
        $update = $conn->prepare("UPDATE user SET password = ?, reset_token = NULL, token_expire = NULL WHERE email = ?");
        $update->bind_param("ss", $new_password, $email);

        if ($update->execute()) {
            $message = "Password updated! Redirecting to login...";
            $status = "success";
            unset($_SESSION['reset_email']); // Linisin ang session
            header("refresh:3;url=index.php");
        }
    } else {
        $message = "Invalid or Expired OTP code.";
        $status = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password - System</title>
    <link rel="stylesheet" href="https://cloudflare.com">
    <style>
        :root {
            --bg-yellow: #fffdf0;
            --primary-yellow: #f1c40f;
            --text-dark: #333;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--bg-yellow);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--primary-yellow);
            width: 350px;
            text-align: center;
        }

        .logo-r {
            width: 60px;
            height: 60px;
            background: var(--primary-yellow);
            color: black;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-weight: 900;
            font-size: 30px;
            margin: 0 auto 20px;
        }

        .input-group {
            text-align: left;
            margin-bottom: 15px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 13px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            outline: none;
            box-sizing: border-box;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: var(--primary-yellow);
            border: none;
            border-radius: 10px;
            font-weight: 900;
            cursor: pointer;
            margin-top: 10px;
        }

        .alert {
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
            border: 1px solid;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 35px;
            cursor: pointer;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="logo-r">R</div>
        <h2>Verify & Reset</h2>

        <?php if ($message): ?> <div class="alert <?php echo $status; ?>"><?php echo $message; ?></div> <?php endif; ?>

        <?php if ($status != 'success'): ?>
            <form method="POST">
                <div class="input-group">
                    <label>Enter 6-Digit OTP</label>
                    <input type="text" name="otp" placeholder="XXXXXX" required maxlength="6" style="text-align:center; font-size:20px; letter-spacing:5px;">
                </div>
                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" name="password" id="password" placeholder="Min. 6 characters" required minlength="6">
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
                <button type="submit" class="login-btn">UPDATE PASSWORD</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>