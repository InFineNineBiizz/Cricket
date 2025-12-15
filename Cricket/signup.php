<?php 
    session_start();
    include 'connection.php';	
    if(isset($_POST['register']))
    {
        $utype="User";
        $str = "INSERT INTO users(name,email,password,cpassword,role) VALUES('".$_POST['name']."','".$_POST['email']."','".$_POST['password']."','".$_POST['confirmPassword']."','".$utype."')";
        $res = mysqli_query($conn,$str);
        if($res)
        {
            $_SESSION['log'] = true;
            header('location:login.php');
            exit();
        }
    }		
?>
<html>
<head>
    <title>Sign Up | CrickFolio</title>
    <link rel="stylesheet" href="assets/css/signup.css">
    <link rel="stylesheet" href="assets/css/fontawesome-all.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/sweetalert2.css">
    <script src="assets/script/sweetalert2.js"></script>
</head>
<body>
    <div class="signup-wrapper">
        <div class="signup-container">
            
            <div class="signup-left">
                <div class="signup-left-content">
                    <div class="logo">🏏</div>
                    <h2>Welcome to CrickFolio</h2>
                    <p>Create your account and join thousands of cricket enthusiasts. Start your auction journey today!</p>
                </div>
            </div>

            <div class="signup-right">
                <button class="close-btn" id="closeBtn">&times;</button>
                
                <div class="signup-header">
                    <h1>Create Account</h1>
                    <p>Fill in your details to get started</p>
                </div>

                <form id="signupForm" method="POST" class="signup-form">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <div class="input-group">
                        <span class="input-group-addon input-group-text">
                            <i class="fa fa-user"></i>
                        </span>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter your name">
                    </div>
                        <span class="error-message" id="fullNameError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-addon input-group-text">
                                <i class="fa fa-envelope"></i>
                            </span>
                            <input type="text" id="email" name="email" class="form-control" placeholder="Enter your email">
                        </div>
                        <span class="error-message" id="emailError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-addon input-group-text">
                                <i class="fa fa-lock"></i>
                            </span>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password">
                            <span class="input-group-addon input-group-text" style="cursor:pointer;">
                                <i class="fa fa-eye" id="togglePass"></i>
                            </span>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                        <div class="password-strength-text" id="passwordStrengthText"></div>
                        <span class="error-message" id="passwordError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                        <span class="input-group-addon input-group-text">
                            <i class="fa fa-check"></i>
                        </span>
                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="Re-enter your password">
                        <span class="input-group-addon input-group-text" style="cursor:pointer;">
                            <i class="fa fa-eye" id="toggleCPass"></i>
                        </span>
                    </div>
                        <span class="error-message" id="confirmPasswordError"></span>
                    </div>
                    <br>
                    <div class="form-group">
                        <button type="submit" name="register" class="signup-btn">Create Account</button>
                    </div>

                    <div class="divider">
                        <span>OR</span>
                    </div>                    
                </form>

                <div class="login-link">
                    Already have an account? <a href="login.php">Login</a>                    
                </div>
                <br>
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

            // Name validation
            $('#name').on('input blur', function() {
                var fullName = $(this).val().trim();
                var error = $('#fullNameError');
                
                if (fullName === '') {
                    $(this).removeClass('valid').addClass('error');
                    error.text('Name is required').show();
                    return false;
                }
                
                if (fullName.length < 3) {
                    $(this).removeClass('valid').addClass('error');
                    error.text('Name must be at least 3 characters').show();
                    return false;
                }
                
                if (!/^[a-zA-Z\s]+$/.test(fullName)) {
                    $(this).removeClass('valid').addClass('error');
                    error.text('Name can only contain letters').show();
                    return false;
                }
                
                $(this).removeClass('error').addClass('valid');
                error.hide();
                return true;
            });

            // Email validation
            $('#email').on('input blur', function() {
                var email = $(this).val().trim();
                var error = $('#emailError');
                
                if (email === '') {
                    $(this).removeClass('valid').addClass('error');
                    error.text('Email is required').show();
                    return false;
                }
                
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email)) {
                    $(this).removeClass('valid').addClass('error');
                    error.text('Please enter a valid email address').show();
                    return false;
                }
                
                $(this).removeClass('error').addClass('valid');
                error.hide();
                return true;
            });

            // Password validation with strength checker
            $('#password').on('input blur', function() {
                var password = $(this).val();
                var error = $('#passwordError');
                
                // Check password strength
                checkPasswordStrength(password);
                
                if (password === '') {
                    $(this).removeClass('valid').addClass('error');
                    error.text('Password is required').show();
                    $('#passwordStrength').removeClass('active');
                    $('#passwordStrengthText').removeClass('active');
                    return false;
                }
                
                if (password.length < 8) {
                    $(this).removeClass('valid').addClass('error');
                    error.text('Password must be at least 8 characters').show();
                    return false;
                }
                
                $(this).removeClass('error').addClass('valid');
                error.hide();
                
                // Trigger confirm password validation if it has value
                if ($('#confirmPassword').val() !== '') {
                    $('#confirmPassword').trigger('blur');
                }
                
                return true;
            });

            // Confirm Password validation
            $('#confirmPassword').on('input blur', function() {
                var password = $('#password').val();
                var confirmPassword = $(this).val();
                var error = $('#confirmPasswordError');
                
                if (confirmPassword === '') {
                    $(this).removeClass('valid').addClass('error');
                    error.text('Please confirm your password').show();
                    return false;
                }
                
                if (password !== confirmPassword) {
                    $(this).removeClass('valid').addClass('error');
                    error.text('Passwords do not match').show();
                    return false;
                }
                
                $(this).removeClass('error').addClass('valid');
                error.hide();
                return true;
            });

            // Password strength checker
            function checkPasswordStrength(password) {
                var strengthBar = $('#passwordStrengthBar');
                var strengthText = $('#passwordStrengthText');
                var strengthContainer = $('#passwordStrength');
                
                if (password.length === 0) {
                    strengthContainer.removeClass('active');
                    strengthText.removeClass('active');
                    return;
                }
                
                strengthContainer.addClass('active');
                strengthText.addClass('active');
                
                var strength = 0;
                
                // Length check
                if (password.length >= 6) strength++;
                if (password.length >= 10) strength++;
                
                // Character variety checks
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                if (/\d/.test(password)) strength++;
                if (/[^a-zA-Z0-9]/.test(password)) strength++;
                
                // Update bar and text
                strengthBar.removeClass('weak medium strong');
                strengthText.removeClass('weak medium strong');
                
                if (strength <= 2) {
                    strengthBar.addClass('weak');
                    strengthText.addClass('weak');
                    strengthText.text('Weak password');
                } else if (strength <= 4) {
                    strengthBar.addClass('medium');
                    strengthText.addClass('medium');
                    strengthText.text('Medium strength');
                } else {
                    strengthBar.addClass('strong');
                    strengthText.addClass('strong');
                    strengthText.text('Strong password');
                }
            }

            // Form submit validation
            $('#signupForm').on('submit', function(e) {
                // Trigger validations
                $('#name').trigger('blur');
                $('#email').trigger('blur');
                $('#password').trigger('blur');
                $('#confirmPassword').trigger('blur');
                
                // Check if all fields have 'valid' class
                var fullNameValid = $('#name').hasClass('valid');
                var emailValid = $('#email').hasClass('valid');
                var passwordValid = $('#password').hasClass('valid');
                var confirmPasswordValid = $('#confirmPassword').hasClass('valid');

                // If any invalid, prevent form submit
                if (!(fullNameValid && emailValid && passwordValid && confirmPasswordValid)) {
                    e.preventDefault();
                }
                // If all valid → DO NOT call e.preventDefault(), form will submit normally to PHP
            });

            // Toggle Password visibility (FIXED IDs)
            $('#togglePass').on('click', function(){
                var input = $('#password');
                var type = input.attr('type') === 'password' ? 'text' : 'password';
                input.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');
            });

            $('#toggleCPass').on('click', function(){
                var input = $('#confirmPassword');
                var type = input.attr('type') === 'password' ? 'text' : 'password';
                input.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');
            });
        });
    </script>    
</body>
</html>