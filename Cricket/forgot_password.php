<?php
    session_start();
    include "connection.php";

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    if(isset($_POST['reset']))
    {
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        $str = "SELECT * FROM users WHERE email='$email'";            
        $result = mysqli_query($conn, $str);
        $count = mysqli_num_rows($result);            
        
        if($count > 0)
        {           
            $otp = rand(100000, 999999);
            $_SESSION['otp']     = $otp;
            $_SESSION['otp_exp'] = time() + (1 * 60);     
            $_SESSION['reset_email'] = $email;
            $_SESSION['otp_first_load'] = true;
            
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'crickfolio31@gmail.com';
                $mail->Password   = 'zkxs tqnm rgwb vysz';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('crickfolio31@gmail.com', 'CrickFolio');                
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'OTP For Reset Your Password';
                $mail->Body    = 'Your OTP for reset your password is: <b>' . $otp . '</b>. It expires in 1 minutes.';
                $mail->AltBody = 'Your OTP for reset your password is: ' . $otp;

                $mail->send();
                $otpSent = true;

            } catch (Exception $e) {
                $invalid = "<div class='alert alert-danger text-center mt-3'>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
            }
        }
        else
        {
            $invalid = "<div class='alert alert-danger text-center py-2 mb-2'>Email not found!</div>";
        }
    }
?>
<html>
<head>
    <title>Forgot Password | <?php echo $title_name;?> </title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/fontawesome-all.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/sweetalert2.css">
    <script src="assets/script/sweetalert2.js"></script>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            
            <div class="login-left">
                <div class="login-left-content">
                    <div class="logo">🏏</div>
                    <h2>Welcome to CrickFolio</h2>
                    <p>Reset your password to regain access to your account and manage your cricket auction experience. We're here to help!</p>
                </div>
            </div>

            <div class="login-right">
                <button class="close-btn" id="closeBtn">&times;</button>
                
                <div class="login-header">
                    <h1>Forgot Password</h1>
                    <p>Enter your registered email and we'll send an OTP to reset your password.</p>
                </div>
                
                <?php 
                    if(isset($invalid)) { echo $invalid; }
                ?><br>

                <form id="forgotForm" method="POST" class="login-form">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-addon input-group-text">
                                <i class="fa fa-envelope"></i>
                            </span>
                            <input type="text" id="email" name="email" class="form-control" placeholder="you@example.com">
                        </div>
                        <span class="error-message" id="emailError"></span>
                    </div>
                    <br>
                    <div class="form-group">
                        <button type="submit" name="reset" class="login-btn">Generate OTP</button>
                    </div>                    
                </form>

                <div class="signup-link">
                    Remember your password? <a href="login.php">Back to Login</a>
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

            // Email validation
            $('#email').on('input blur', function() {
                var email = $(this).val().trim();
                var emailError = $('#emailError');
                
                if (email === '') {
                    $(this).removeClass('valid').addClass('error');
                    emailError.text('Email is required').show();
                    $(this).focus();
                    return false;
                }
                
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email)) {
                    $(this).removeClass('valid').addClass('error');
                    emailError.text('Please enter a valid email address').show();
                    return false;
                }
                
                $(this).removeClass('error').addClass('valid');
                emailError.hide();
                return true;
            });

            // Form submit validation
            $('#forgotForm').on('submit', function(e) {
                // Trigger validation
                $('#email').trigger('blur');
                
                var emailValid = $('#email').hasClass('valid');
                
                // If invalid, stop submission
                if (!emailValid) {
                    e.preventDefault();
                }
            });    
                        
            $('#email').on('keyup', function() {
                $('.alert').remove();
            });
        });
    </script>

    <?php if(isset($otpSent) && $otpSent == true): ?>
    <script>
        Swal.fire({
            title: "OTP Sent!",
            text: "An OTP has been sent to your email address.",
            icon: "success",        
            timer: 3000,        
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