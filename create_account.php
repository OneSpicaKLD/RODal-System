<?php
require 'db_connect.php';

$check_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM user");
$total_data = mysqli_fetch_assoc($check_total);

if ($total_data['total'] >= 2) {
    header("Location: index.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    // Check role
    $check_role = $conn->prepare("SELECT * FROM user WHERE role = ?");
    if (!$check_role) {
        die("Role query error: " . $conn->error);
    }
    $check_role->bind_param("s", $role);
    $check_role->execute();
    $role_taken = $check_role->get_result()->num_rows > 0;

    // Check email
    $check_email = $conn->prepare("SELECT * FROM user WHERE email = ?");
    if (!$check_email) {
        die("Email query error: " . $conn->error);
    }
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $email_taken = $check_email->get_result()->num_rows > 0;

    if ($role_taken) {
        $error = "The role <strong>$role</strong> is already taken.";
    } elseif ($email_taken) {
        $error = "The email <strong>$email</strong> is already registered.";
    } else {
        $stmt = $conn->prepare("INSERT INTO user (username, email, password, role) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            die("Insert query error: " . $conn->error);
        }
        $stmt->bind_param("ssss", $username, $email, $password, $role);
        if ($stmt->execute()) {
            header("Location: index.php?registered=1");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --bg-yellow: #fffdf0;
            --primary-yellow: #f1c40f;
            --text-dark: #333;
        }

        body {
            margin: 0;
            padding: 0;
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

        h2 {
            color: var(--text-dark);
            margin-bottom: 25px;
        }

        .input-group {
            text-align: left;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 14px;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            outline: none;
            box-sizing: border-box;
            font-family: inherit;
        }

        select {
            background: white;
            cursor: pointer;
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
            transition: transform 0.2s;
        }

        .login-btn:hover {
            transform: translateY(-2px);
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
            border: 1px solid #f5c6cb;
            text-align: center;
        }

        .footer-links {
            margin-top: 25px;
            font-size: 13px;
        }

        .footer-links a {
            color: #888;
            text-decoration: none;
        }

        /* Add this to your existing <style> */
        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .password-wrapper input {
            padding-right: 45px;
            /* Space for the icon so text doesn't overlap */
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
            font-size: 16px;
            z-index: 10;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="logo-r">R</div>
        <h2>Create Account</h2>


        <form method="POST" action="">
            <?php if (!empty($error)): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 10px; margin-bottom: 15px; font-size: 14px; border: 1px solid #f5c6cb; text-align: center;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Create your username" required>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@example.com" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Create a password" required>
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
            </div>


            <div class="input-group">
                <label>Assign Role</label>
                <select name="role" required>
                    <option value="" disabled selected>-- Select Role --</option>
                    <option value="owner">Owner</option>
                    <option value="admin">System Admin</option>
                </select>
            </div>

            <button type="submit" class="login-btn">REGISTER</button>

        </form>

        <div class="footer-links">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>

</body>
<script>
    const togglePassword = document.getElementById("togglePassword");
    const password = document.getElementById("password");

    togglePassword.addEventListener("click", function() {
        const isHidden = password.type === "password";
        password.type = isHidden ? "text" : "password";

        this.classList.toggle("fa-eye");
        this.classList.toggle("fa-eye-slash");
    });
</script>

</html>