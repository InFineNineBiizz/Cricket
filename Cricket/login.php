<?php
    session_start();
    include "connection.php";

    if(isset($_POST['login']))
    {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass  = mysqli_real_escape_string($conn, $_POST['password']);

        $str="SELECT * FROM users WHERE email='$email' AND password='$pass'";            
        $result=mysqli_query($conn,$str);
        $row=mysqli_fetch_array($result);
        $count=mysqli_num_rows($result);            
        
        if($count>0)
        {                
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_name'] = $row['name'];
            // $_SESSION['pass']  = $pass;

            header('location:index.php');                
            exit();
        }
        else
        {
            $invalid="<div class='alert alert-danger text-center py-2 mb-2'>Invalid email or password!</div>";
        }
    }
?>
<html>
<head>
    <title>Login | CrickFolio</title>
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
                    <p>Sign in to access your account and manage your cricket auction experience. Join thousands of cricket enthusiasts today!</p>
                </div>
            </div>

            <div class="login-right">
                <button class="close-btn" id="closeBtn">&times;</button>
                
                <div class="login-header">
                    <h1>Login</h1>
                    <p>Enter your credentials to access your account</p>
                </div>
                
                <?php 
                    if(isset($invalid)) { echo $invalid;}
                ?><br>

                <form id="loginForm" method="POST" class="login-form">
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

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-addon input-group-text">
                                <i class="fa fa-lock"></i>
                            </span>
                            <input type="password" name="password" id="password" class="form-control" placeholder="********">
                            <span class="input-group-addon input-group-text" style="cursor:pointer;">
                                <i class="fa fa-eye" id="togglePassword"></i>
                            </span>                        
                        </div>
                        <span class="error-message" id="passwordError"></span>
                    </div>

                    <div class="forgot-password">
                        <a href="forgot_password.php">Forgot Password?</a>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="login" class="login-btn">Login</button>
                    </div>

                    <div class="divider">
                        <span>OR</span>
                    </div>
                    
                </form>

                <div class="signup-link">
                    Don't have an account? <a href="signup.php">Sign Up</a>
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

            // Password validation
            $('#password').on('input blur', function() {
                var password = $(this).val();
                var passwordError = $('#passwordError');
                
                if (password === '') {
                    $(this).removeClass('valid').addClass('error');
                    passwordError.text('Password is required').show();
                    $(this).focus();
                    return false;
                }
                
                if (password.length < 8) {
                    $(this).removeClass('valid').addClass('error');
                    passwordError.text('Password must be at least 8 characters').show();                    
                    return false;
                }
                
                $(this).removeClass('error').addClass('valid');
                passwordError.hide();
                return true;
            });

            // Form submit validation
            $('#loginForm').on('submit', function(e) {
                // Trigger validations
                $('#email').trigger('blur');
                $('#password').trigger('blur');
                
                var emailValid    = $('#email').hasClass('valid');
                var passwordValid = $('#password').hasClass('valid');
                
                // If any invalid, stop submission
                if (!(emailValid && passwordValid)) {
                    e.preventDefault();
                }
                // If both valid → NO e.preventDefault() → form posts to PHP and redirects
            });    
                        
            $('#email').on('keyup', function() {
                $('.alert').remove();
            });

            $('#password').on('keyup', function() {
                $('.alert').remove();
            });

            // Toggle password visibility
            $('#togglePassword').on('click', function(){
                var passInput = $('#password');
                var type = passInput.attr('type') === 'password' ? 'text' : 'password';
                passInput.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');
            });         
        });
    </script>
    <?php if (isset($_SESSION['log'])) {?>
	<script>
		Swal.fire({
			icon: 'success',
			title: 'Register Successful',
			text: 'Your account has been registered successfully.',
			confirmButtonColor: '#4c6fff',
            showConfirmButton: false,
            timer:3000,
            timerProgressBar:true,
		});
	</script>
	<?php } unset($_SESSION['log']); ?>
</body>
</html>