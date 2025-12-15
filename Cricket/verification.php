<?php
    session_start();
    include 'connection.php';

    // PHPMailer uses
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    $valid = "";
    $msg   = "";

    if (isset($_SESSION['otp_first_load']) && $_SESSION['otp_first_load'] === true) {
        
        $_SESSION['otp_exp'] = time() + 60;        
        unset($_SESSION['otp_first_load']);
    }

    // If expiry is not set for some reason, set default 60 sec
    if (!isset($_SESSION['otp_exp'])) {
        $_SESSION['otp_exp'] = time() + 60;
    }

    // Remaining time for JS timer
    $remaining = $_SESSION['otp_exp'] - time();
    if ($remaining < 0) {
        $remaining = 0;
    }

    // VERIFY OTP
    if(isset($_POST['verotp']))
    {
        $otp1 = $_POST["otp"] ?? "";

        // Check if OTP expired
        if(isset($_SESSION['otp_exp']) && time() > $_SESSION['otp_exp']) {
            $valid = "<div class='alert alert-danger text-center py-2 mb-2'>OTP expired! Please resend OTP.</div>";
        }
        else if($otp1 === (string)($_SESSION['otp'] ?? ''))
        {
            header("Location: reset_password.php");
            exit;
        }
        else
        {                
            $valid = "<div class='alert alert-danger text-center py-2 mb-2'>Invalid OTP!</div>";
        }
    }

    // RESEND OTP
    if(isset($_POST['resend']))
    {
        if(isset($_SESSION['reset_email'])) {

            $email = $_SESSION['reset_email'];

            // Generate new OTP and expiry
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_exp'] = time() + (1 * 60); // 1 minute
            $remaining = 60;

            // Send email via PHPMailer
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'crickfolio31@gmail.com'; // your SMTP email
                $mail->Password   = 'zkxs tqnm rgwb vysz';    // your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('crickfolio31@gmail.com', 'CrickFolio');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'OTP For Reset Your Password (Resent)';
                $mail->Body    = 'Your new OTP for password reset is: <b>' . $otp . '</b>. It expires in 1 minute.';
                $mail->AltBody = 'Your new OTP for password reset is: ' . $otp;

                $mail->send();

                $msg = "<div class='alert alert-success text-center py-2 mb-2'>New OTP has been sent to your email!</div>";
            } catch (Exception $e) {
                $msg = "<div class='alert alert-danger text-center py-2 mb-2'>Resend failed. Error: {$mail->ErrorInfo}</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger text-center py-2 mb-2'>Session expired. Please try Forgot Password again.</div>";
        }
    }
?>
<html>
<head>
    <title>Verify OTP | CrickFolio</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/fontawesome-all.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/sweetalert2.css">
    <script src="assets/script/sweetalert2.js"></script>
    <style>
        .otp-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 20px;
        }
        .otp-box {
            width: 50px;
            height: 50px;
            font-size: 24px;
            text-align: center;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            outline: none;
            transition: all 0.3s ease;
        }
        .otp-box:focus {
            border-color: #4c6fff;
            box-shadow: 0 0 0 3px rgba(76, 111, 255, 0.1);
        }
        .resend-btn {
            background: transparent;
            border: 2px solid #4c6fff;
            color: #4c6fff;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 15px;
        }
        .resend-btn:hover {
            background: #4c6fff;
            color: white;
        }
        .timer-text {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 15px;
        }
        .timer-text strong {
            color: #4c6fff;
        }
        .resend-btn:disabled {
            background: #f3f4f6;
            border-color: #d1d5db;
            color: #9ca3af;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .resend-btn:hover:not(:disabled) {
            background: #4c6fff;
            color: white;
        }

        .login-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            
            <div class="login-left">
                <div class="login-left-content">
                    <div class="logo">🏏</div>
                    <h2>Welcome to CrickFolio</h2>
                    <p>We've sent a verification code to your email address. Enter the 6-digit OTP to reset your password and get back to managing your cricket auction experience!</p>
                </div>
            </div>

            <div class="login-right">
                <button class="close-btn" id="closeBtn">&times;</button>
                
                <div class="login-header">
                    <h1>Verify OTP</h1>
                    <p>Enter the 6-digit code sent to your email</p>
                </div>
                
                <?php 
                    if(isset($msg)) { echo $msg; }
                    if(isset($valid)) { echo $valid; }
                ?><br>

                <form id="verifyForm" method="POST" class="login-form">
                    <div class="form-group">
                        <label class="form-label">Enter OTP</label>
                        <div class="otp-container">
                            <input type="text" maxlength="1" class="otp-box form-control" id="otp1">
                            <input type="text" maxlength="1" class="otp-box form-control" id="otp2">
                            <input type="text" maxlength="1" class="otp-box form-control" id="otp3">
                            <input type="text" maxlength="1" class="otp-box form-control" id="otp4">
                            <input type="text" maxlength="1" class="otp-box form-control" id="otp5">
                            <input type="text" maxlength="1" class="otp-box form-control" id="otp6">
                        </div>
                        <input type="hidden" name="otp" id="otp">
                    </div>

                    <div class="form-group">
                        <button type="submit" name="verotp" class="login-btn">Verify OTP</button>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="resend" class="resend-btn">Resend OTP</button>
                    </div>

                    <p class="timer-text" id="timer_text">
                        OTP expire in : <strong><span id="timer"></span></strong>.
                    </p>
                    
                </form>

                <div class="signup-link">
                    Entered wrong email? <a href="forgot_password.php">Go Back</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php
        include "scripts.php";
    ?>

    <script>
        $(document).ready(function() {            
            // Close button
            $('#closeBtn').click(function() {
                window.location.href = 'index.php';
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // OTP input behavior
            const boxes = document.querySelectorAll(".otp-box");
            const finalOtp = document.getElementById("otp");

            boxes.forEach((box, index) => {
                box.addEventListener("input", () => {
                    // Only allow numbers
                    box.value = box.value.replace(/[^0-9]/g, '');
                    
                    if (box.value.length === 1 && index < 5) {
                        boxes[index + 1].focus();
                    }
                    collectOtp();
                });

                box.addEventListener("keydown", (e) => {
                    if (e.key === "Backspace" && box.value === "" && index > 0) {
                        boxes[index - 1].focus();
                    }
                });

                // Handle paste event
                box.addEventListener("paste", (e) => {
                    e.preventDefault();
                    const pasteData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
                    
                    if (pasteData.length === 6) {
                        boxes.forEach((b, i) => {
                            b.value = pasteData[i] || '';
                        });
                        collectOtp();
                        boxes[5].focus();
                    }
                });
            });

            function collectOtp() {
                let otp = "";
                boxes.forEach((box) => otp += box.value);
                finalOtp.value = otp;
            }

            // Auto-focus first input
            boxes[0].focus();

            // Remove alerts on typing
            boxes.forEach(box => {
                $(box).on('input', function() {
                    $('.alert').fadeOut('fast');
                });
            });
        });

        const timerSpan   = document.getElementById("timer");
        const timerText   = document.getElementById("timer_text");
        const verifyBtn   = document.querySelector("button[name='verotp']");
        const resendBtn   = document.querySelector("button[name='resend']");

        // Seconds from PHP session (remaining time)
        let timeLeft = <?php echo (int)$remaining; ?>;

        if (!timeLeft || timeLeft < 0) {
            timeLeft = 0;
        }

        function formatTime(seconds) {
            const m = Math.floor(seconds / 60).toString().padStart(2, "0");
            const s = (seconds % 60).toString().padStart(2, "0");
            return `${m}:${s}`;
        }

        function handleExpiry() {
            verifyBtn.disabled = true;
            resendBtn.disabled = false;
            timerText.innerHTML = `OTP Expired. Please click <strong>Resend OTP</strong> to get a new code.`;
        }

        function handleActive() {
            verifyBtn.disabled = false;
            resendBtn.disabled = true;
        }

        if (timeLeft <= 0) {
            timerSpan.textContent = "00:00";            
            handleExpiry();
        } else {
            timerSpan.textContent = formatTime(timeLeft);
            handleActive();
            
            const countdown = setInterval(() => {
                timeLeft--;
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    timerSpan.textContent = "00:00";
                    handleExpiry();
                } else {
                    timerSpan.textContent = formatTime(timeLeft);                
                }
            }, 1000);
        }
    </script>
</body>
</html>