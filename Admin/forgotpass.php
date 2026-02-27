<?php
    session_start();
    include 'connection.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    $invalid = "";
    $otpSent = false;

    if(isset($_POST['Forpass']))
    {
        $email = trim($_POST['maill']);

        // Basic empty check
        if($email == "") {
            $invalid = "<div class='alert alert-danger text-center mt-3'>Please enter your email.</div>";
        } else {
            // Query user
            $email_safe = mysqli_real_escape_string($conn, $email);
            $str    = "SELECT * FROM users WHERE email='".$email_safe."'";
            $result = mysqli_query($conn,$str);
            $row    = mysqli_fetch_array($result);
            $count  = mysqli_num_rows($result);
            
            if($count > 0)
            {
                $otp = rand(100000, 999999);
                $_SESSION['otp']     = $otp;
                $_SESSION['otp_exp'] = time() + (1 * 60); // 1 minute
                $_SESSION['email']   = $email;
                $_SESSION['otp_first_load'] = true;
                
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'crickfolio31@gmail.com';
                    $mail->Password   = 'zkxs tqnm rgwb vysz'; // your app password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('yourgmail@gmail.com', 'OTP For Password Reset');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'OTP For Reset Your Password';
                    $mail->Body    = 'Your OTP for password reset is: <b>' . $otp . '</b>. It expires in 1 minutes.';
                    $mail->AltBody = 'Your OTP for password reset is: ' . $otp;

                    $mail->send();
                    $otpSent = true;

                } catch (Exception $e) {
                    $invalid = "<div class='alert alert-danger text-center mt-3'>
                                    Message could not be sent. Mailer Error: {$mail->ErrorInfo}
                                </div>";
                }
            }
            else
            {
                $invalid = "<div class='alert alert-danger text-center mt-3'>Invalid email!</div>";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Forgot Password | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "links.php"; ?>
</head>

<body>

    <div class="auth-bg d-flex min-vh-100">
        <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xxl-3 col-lg-5 col-md-6">

                <a href="index.php" class="auth-brand d-flex justify-content-center mb-2">
                    <img src="assets/images/logo-dark.png" alt="dark logo" height="26" class="logo-dark">
                    <img src="assets/images/logo.png" alt="logo light" height="26" class="logo-light">
                </a>

                <br>

                <div class="card overflow-hidden text-center p-xxl-4 p-3 mb-0">

                    <h4 class="fw-semibold mb-2 fs-22">Forgot Password</h4>
                    <p class="text-muted mb-3">
                        Enter your registered email and we’ll send you an OTP to reset your password.
                    </p>

                    <form id="PassForm" method="POST" class="text-start mb-3">

                        <div class="mb-3">
                            <label class="form-label" for="email">Email Address</label>
                            <input type="email" id="email" name="maill" class="form-control" placeholder="you@example.com">
                            <small class="text-danger" id="email_error"></small>
                        </div>

                        <?php 
                            if(!empty($invalid)) {
                                echo $invalid;
                            }
                        ?>

                        <div class="d-grid mt-3">
                            <button type="submit" name="Forpass" class="btn btn-primary fw-semibold">
                                Generate OTP
                            </button>
                        </div>
                    </form>

                    <p class="text-muted fs-14 mb-0">
                        Remember your password?
                        <a href="auth-login.php" class="fw-semibold text-danger ms-1">Back to Login</a>
                    </p>

                </div>                  
            </div>
        </div>
    </div>

    <?php include "scripts.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- LIVE VALIDATION (same style as login page) -->
    <script>
        const emailInput = document.getElementById('email');
        const emailError = document.getElementById('email_error');
        const form = document.getElementById('PassForm');

        function validateEmailField() {
            const email = emailInput.value.trim();
            const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email === "") {
                emailError.textContent = "Email is required.";
                return false;
            } else if (!pattern.test(email)) {
                emailError.textContent = "Enter a valid email address.";
                return false;
            } else {
                emailError.textContent = "";
                return true;
            }
        }

        emailInput.addEventListener('input', validateEmailField);
        emailInput.addEventListener('blur', validateEmailField);

        form.addEventListener('submit', function (e) {
            let ok = validateEmailField();

            if (!ok) {
                e.preventDefault();
            }
        });
    </script>

    <!-- SWEETALERT WHEN OTP IS SENT SUCCESSFULLY -->
    <?php if(isset($otpSent) && $otpSent == true): ?>
    <script>
        Swal.fire({
            title: "OTP Sent!",
            text: "An OTP has been sent to your email address.",
            icon: "success",
            confirmButtonText: "OK",
             timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
            willClose: () => {                    
                window.location.href = "verification.php";
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>
