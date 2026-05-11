<?php
require 'db_connect.php';
session_start();

if (isset($_SESSION['isLoggedIn'])) {
    header("Location: dashboard.php");
    exit();
}

// comment to ha
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param("s", $username); // Ang "s" ay nangangahulugang "string"
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("SQL Error: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($result) > 0) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            $_SESSION['isLoggedIn'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Account not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
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
            transition: transform 0.2s;
        }

        .login-btn:hover {
            transform: translateY(-2px);
        }

        .password-container {
            position: relative;
            width: 100%;
        }

        .password-container input {
            width: 100%;
            padding: 12px 45px 12px 12px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 10px;
            outline: none;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
            font-size: 16px;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="logo-r">R</div>
        <h2>Sign In</h2>

        <form method="POST" action="">
            <?php if (!empty($error)): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 10px; margin-bottom: 15px; font-size: 14px; border: 1px solid #f5c6cb; text-align: center;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required>
            </div>

            <div class="input-group">
                <label>Password</label>

                <div class="password-container">
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
            </div>


            <button type="submit" class="login-btn">LOGIN</button>
        </form>
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