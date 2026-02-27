<?php
session_start();
include 'connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$valid = "";
$msg   = "";

/* ================= TIMER SETUP ================= */
if (isset($_SESSION['otp_first_load']) && $_SESSION['otp_first_load'] === true) {
    $_SESSION['otp_exp'] = time() + 60;
    unset($_SESSION['otp_first_load']);
}

if (!isset($_SESSION['otp_exp'])) {
    $_SESSION['otp_exp'] = time() + 60;
}

$remaining = $_SESSION['otp_exp'] - time();
if ($remaining < 0) $remaining = 0;

/* ================= VERIFY OTP ================= */
if (isset($_POST['verotp'])) {
    $otp1 = $_POST["otp"] ?? "";

    if (time() > $_SESSION['otp_exp']) {
        $valid = "<div class='alert alert-danger text-center'>OTP expired! Please resend OTP.</div>";
    } else if ($otp1 === (string)($_SESSION['otp'] ?? '')) {
         $_SESSION['otp_verified'] = true;
    } else {
        $valid = "<div class='alert alert-danger text-center'>Invalid OTP!</div>";
    }
}

/* ================= RESEND OTP ================= */
if (isset($_POST['resend']) && isset($_SESSION['email'])) {

    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_exp'] = time() + 60;
    $remaining = 60;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'crickfolio31@gmail.com';
        $mail->Password = 'zkxs tqnm rgwb vysz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('crickfolio31@gmail.com', 'OTP Verification');
        $mail->addAddress($_SESSION['email']);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Your OTP is <b>$otp</b>. It expires in 1 minute.";

        $mail->send();
        $msg = "<div class='alert alert-info text-center'>New OTP sent successfully!</div>";
    } catch (Exception $e) {
        $msg = "<div class='alert alert-danger text-center'>Mail error!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Verify OTP | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php include "links.php"; ?>

<style>
.card {
    border-radius: 14px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

.otp-row {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.otp-box {
    width: 48px;
    height: 48px;
    font-size: 22px;
    font-weight: 600;
    text-align: center;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    outline: none;
    transition: 0.2s;
}

.otp-box:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
}

.btn {
    padding: 10px;
    font-size: 15px;
    border-radius: 10px;
}

#timer_text.expired {
    color: #dc2626;
    font-weight: 600;
}
</style>
</head>

<body>

<div class="auth-bg d-flex min-vh-100 align-items-center justify-content-center">
    <div class="col-lg-4 col-md-6">

        <a href="index.php" class="auth-brand d-flex justify-content-center mb-3">
            <img src="assets/images/logo-dark.png" height="28">
        </a>

        <div class="card p-4 text-center">

            <h4 class="fw-bold mb-2">Verify OTP</h4>
            <p class="text-muted">
                Enter the 6-digit OTP sent to<br>
                <strong><?php echo $_SESSION['email']; ?></strong>
            </p>

            <?php if($msg) echo $msg; ?>
            <?php if($valid) echo $valid; ?>

            <form method="POST" id="VerifyForm">

                <div class="otp-row mb-3">
                    <input type="text" maxlength="1" class="otp-box">
                    <input type="text" maxlength="1" class="otp-box">
                    <input type="text" maxlength="1" class="otp-box">
                    <input type="text" maxlength="1" class="otp-box">
                    <input type="text" maxlength="1" class="otp-box">
                    <input type="text" maxlength="1" class="otp-box">
                </div>

                <input type="hidden" name="otp" id="otp">
                <small id="otp_error" class="text-danger"></small>

                <div class="d-grid mt-3">
                    <button type="submit" name="verotp" class="btn btn-primary">Verify OTP</button>
                </div>

                <div class="d-grid mt-2">
                    <button type="submit" name="resend" class="btn btn-warning">Resend OTP</button>
                </div>

                <p class="text-muted mt-3" id="timer_text">
                    OTP expires in <strong><span id="timer"></span></strong>
                </p>

                <p class="text-muted mt-2 mb-0">
                    Entered wrong email?
                    <a href="forgotpass.php" class="text-danger fw-semibold">Go back</a>
                </p>
            </form>

        </div>
    </div>
</div>

<?php include "scripts.php"; ?>

<script>
const boxes = document.querySelectorAll(".otp-box");
const finalOtp = document.getElementById("otp");
const timerSpan = document.getElementById("timer");
const timerText = document.getElementById("timer_text");
const verifyBtn = document.querySelector("button[name='verotp']");

boxes.forEach((box, i) => {
    box.addEventListener("input", () => {
        box.value = box.value.replace(/[^0-9]/g, '');
        if (box.value && i < 5) boxes[i+1].focus();
        collectOtp();
    });

    box.addEventListener("keydown", e => {
        if (e.key === "Backspace" && !box.value && i > 0) boxes[i-1].focus();
    });
});

function collectOtp() {
    finalOtp.value = Array.from(boxes).map(b => b.value).join('');
}

let timeLeft = <?php echo (int)$remaining; ?>;

function formatTime(s) {
    return `00:${String(s).padStart(2,'0')}`;
}

if (timeLeft <= 0) {
    timerSpan.textContent = "00:00";
    expire();
} else {
    timerSpan.textContent = formatTime(timeLeft);
    setInterval(() => {
        timeLeft--;
        if (timeLeft <= 0) expire();
        else timerSpan.textContent = formatTime(timeLeft);
    }, 1000);
}

function expire() {
    verifyBtn.disabled = true;
    timerText.classList.add("expired");
    timerText.innerHTML = "OTP expired. Please resend OTP.";
}
</script>

<?php if(isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'OTP Verified!',
    text: 'Your OTP has been verified successfully.',
    timer: 3000,
    timerProgressBar: true,
    showConfirmButton: false,
    willClose: () => {
    window.location.href = "newpass.php";
    }       
});
</script>
<?php unset($_SESSION['otp_verified']); endif; ?>
</body>
</html>
