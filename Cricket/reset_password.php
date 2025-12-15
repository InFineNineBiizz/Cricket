<?php 
    session_start();
    include 'connection.php';

    $logMsg = "";

    if(isset($_POST['newpass']))
    {
        $pass  = mysqli_real_escape_string($conn, $_POST["pass"]);
        $cpass = mysqli_real_escape_string($conn, $_POST["cpass"]);

        if(isset($_SESSION['reset_email'])) {
            $str = "UPDATE users 
                    SET password='$pass', cpassword='$cpass' 
                    WHERE email='".$_SESSION['reset_email']."'";

            if (mysqli_query($conn, $str)) {
                // Clear session variables
                unset($_SESSION['reset_email']);
                unset($_SESSION['otp']);
                unset($_SESSION['otp_exp']);
                
                $log = true;                
            } else {
                $logMsg = "<div class='alert alert-danger text-center py-2 mb-2'>
                                Error updating password. Please try again.
                           </div>";
            }
        } else {
            $logMsg = "<div class='alert alert-danger text-center py-2 mb-2'>
                            Session expired. Please try Forgot Password again.
                       </div>";
        }
    }
?>
<html>
<head>
    <title>Reset Password | CrickFolio</title>
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
                    <p>Create a strong new password to secure your account and continue managing your cricket auction experience. Choose a password that's easy to remember but hard to guess!</p>
                </div>
            </div>

            <div class="login-right">
                <button class="close-btn" id="closeBtn">&times;</button>
                
                <div class="login-header">
                    <h1>Reset Password</h1>
                    <p>Create a strong password for your account</p>
                </div>
                
                <?php 
                    if(isset($logMsg)) { echo $logMsg; }
                ?><br>

                <form id="resetForm" method="POST" class="login-form">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-addon input-group-text">
                                <i class="fa fa-lock"></i>
                            </span>
                            <input type="password" id="pass" name="pass" class="form-control" placeholder="Enter new password">
                            <span class="input-group-addon input-group-text" style="cursor:pointer;">
                                <i class="fa fa-eye" id="togglePass"></i>
                            </span>
                        </div>
                        <span class="error-message" id="passError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-addon input-group-text">
                                <i class="fa fa-lock"></i>
                            </span>
                            <input type="password" name="cpass" id="cpass" class="form-control" placeholder="Re-enter new password">
                            <span class="input-group-addon input-group-text" style="cursor:pointer;">
                                <i class="fa fa-eye" id="toggleCPass"></i>
                            </span>
                        </div>
                        <span class="error-message" id="cpassError"></span>
                    </div>
                    <br>

                    <div class="form-group">
                        <button type="submit" name="newpass" class="login-btn">Reset Password</button>
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

            // Password validation
            $('#pass').on('input blur', function() {
                var password = $(this).val();
                var passError = $('#passError');
                
                if (password === '') {
                    $(this).removeClass('valid').addClass('error');
                    passError.text('Password is required').show();
                    return false;
                }
                
                if (password.length < 8) {
                    $(this).removeClass('valid').addClass('error');
                    passError.text('Password must be at least 8 characters').show();
                    return false;
                }
                
                $(this).removeClass('error').addClass('valid');
                passError.hide();
                
                // Revalidate confirm password if it has a value
                if ($('#cpass').val() !== '') {
                    $('#cpass').trigger('blur');
                }
                
                return true;
            });

            // Confirm Password validation
            $('#cpass').on('input blur', function() {
                var password = $('#pass').val();
                var cpassword = $(this).val();
                var cpassError = $('#cpassError');
                
                if (cpassword === '') {
                    $(this).removeClass('valid').addClass('error');
                    cpassError.text('Please confirm your password').show();
                    return false;
                }
                
                if (password !== cpassword) {
                    $(this).removeClass('valid').addClass('error');
                    cpassError.text('Passwords do not match').show();
                    return false;
                }
                
                $(this).removeClass('error').addClass('valid');
                cpassError.hide();
                return true;
            });

            // Form submit validation
            $('#resetForm').on('submit', function(e) {
                // Trigger validations
                $('#pass').trigger('blur');
                $('#cpass').trigger('blur');
                
                var passValid = $('#pass').hasClass('valid');
                var cpassValid = $('#cpass').hasClass('valid');
                
                // If any invalid, stop submission
                if (!(passValid && cpassValid)) {
                    e.preventDefault();
                }
            });

            // Remove alerts on typing
            $('#pass, #cpass').on('keyup', function() {
                $('.alert').remove();
            });

            // Toggle password visibility
            $('#togglePass').on('click', function(){
                var passInput = $('#pass');
                var type = passInput.attr('type') === 'password' ? 'text' : 'password';
                passInput.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');
            });

            $('#toggleCPass').on('click', function(){
                var cpassInput = $('#cpass');
                var type = cpassInput.attr('type') === 'password' ? 'text' : 'password';
                cpassInput.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');
            });
        });
    </script>

    <?php if(isset($log) && $log == true): ?>
    <script>
    Swal.fire({
        title: "Password Changed!",
        text: "Your account password has been changed! You can now login.",
        icon: "success",
        timer: 3000,        
        timerProgressBar: true,
        showConfirmButton: false,
        willClose: () => {                    
            window.location.href = "login.php";
        }
    });        
    </script>
    <?php endif; ?>
</body>
</html>