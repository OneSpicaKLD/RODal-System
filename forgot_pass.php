<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'db_connect.php';

session_start();
date_default_timezone_set('Asia/Manila');

$message = "";
$status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate 6-digit OTP
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime('+10 minutes'));

        // Update DB: Save OTP sa reset_token column
        $update = $conn->prepare("UPDATE user SET reset_token = ?, token_expire = ? WHERE email = ?");
        $update->bind_param("sss", $otp, $expiry, $email);

        if ($update->execute()) {
            $_SESSION['reset_email'] = $email; // Itago ang email sa session para sa next page

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'haroldsilguera14@gmail.com';
                $mail->Password   = 'rmxjatqeirkaxtkm';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('haroldsilguera14@gmail.com', 'Inventory System');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Your Password Reset OTP';
                $mail->Body    = "<div style='font-family:sans-serif; text-align:center;'>
                                    <h2>Password Reset Code</h2>
                                    <p>Gamitin ang code sa ibaba para ma-reset ang iyong password:</p>
                                    <h1 style='color:#f1c40f; letter-spacing:5px;'>$otp</h1>
                                    <p>Valid ito sa loob ng 10 minuto.</p>
                                  </div>";
                $mail->send();

                header("Location: galawgaw.php"); // Lipat na sa OTP page
                exit();
            } catch (Exception $e) {
                $message = "Email error: {$mail->ErrorInfo}";
                $status = "error";
            }
        }
    } else {
        $message = "Email address not found.";
        $status = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cloudflare.com">
    <style>
        :root {
            --bg-yellow: #fffdf0;
            --primary-yellow: #f1c40f;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg-yellow);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
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

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-sizing: border-box;
            margin-bottom: 15px;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: var(--primary-yellow);
            border: none;
            border-radius: 10px;
            font-weight: 900;
            cursor: pointer;
        }

        .alert {
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
            border: 1px solid;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="logo-r">R</div>
        <h2>Forgot Password</h2>
        <?php if ($message): ?> <div class="alert <?php echo $status; ?>"><?php echo $message; ?></div> <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter Email" required>
            <button type="submit" class="login-btn">SEND OTP</button>
        </form>
    </div>
</body>

</html>