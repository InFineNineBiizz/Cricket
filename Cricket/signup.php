<?php 
    session_start();
    include 'connection.php';	
    if(isset($_POST['register']))
    {
        $utype="User";
        $str = "INSERT INTO users(uname,email,password,cpassword,role) VALUES('".$_POST['name']."','".$_POST['email']."','".$_POST['password']."','".$_POST['confirmPassword']."','".$utype."')";
        $res = mysqli_query($conn,$str);
        if($res)
        {
            $_SESSION['log'] = true;
            header('location:login.php');
            exit();
        }
    }		
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up | CrickFolio</title>
    <link rel="stylesheet" href="assets/css/signup.css">
    <link rel="stylesheet" href="assets/css/fontawesome-all.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/sweetalert2.css">
    <script src="assets/script/sweetalert2.js"></script>
    
    <!-- Google Sign-In API -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    
    <style>
        /* Custom Google Sign-In Button Styles */
        .google-btn {
            width: 100%;
            height: 50px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 16px;
            font-weight: 500;
            color: #3c4043;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .google-btn:hover {
            background: #f8f9fa;
            border-color: #d0d0d0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .google-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .google-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(66, 133, 244, 0.1), rgba(52, 168, 83, 0.1), rgba(251, 188, 5, 0.1), rgba(234, 67, 53, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .google-btn:hover::before {
            opacity: 1;
        }

        .google-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            z-index: 1;
        }

        .google-btn span {
            z-index: 1;
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
    </style>
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

                    <div class="form-group">
                        <button type="submit" name="register" class="signup-btn">Create Account</button>
                    </div>

                    <div class="divider">
                        <span>OR</span>
                    </div>
                    
                    <!-- Google Sign-In Button -->
                    <div class="form-group">
                        <!-- Hidden Official Google Button -->
                        <div id="g_id_onload"
                             data-client_id="1022110118702-okoo828eanqdt8rt7bg46c8r3gfm03m3.apps.googleusercontent.com"
                             data-callback="handleCredentialResponse"
                             data-auto_prompt="false">
                        </div>
                        <div id="hiddenGoogleButton" style="display: none;"></div>
                        
                        <!-- Custom Attractive Google Button -->
                        <button type="button" class="google-btn" id="customGoogleBtn">
                            <svg class="google-icon" viewBox="0 0 24 24" width="24" height="24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span>Sign up with Google</span>
                        </button>
                    </div>
                </form>

                <div class="login-link">
                    Already have an account? <a href="login.php">Login</a>                    
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
            });

            // Toggle Password visibility
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

        // Initialize Google Sign-In when script loads
        window.onload = function() {
            // Initialize Google Identity Services
            google.accounts.id.initialize({
                client_id: '1022110118702-okoo828eanqdt8rt7bg46c8r3gfm03m3.apps.googleusercontent.com',
                callback: handleCredentialResponse
            });

            // Render the hidden button
            google.accounts.id.renderButton(
                document.getElementById('hiddenGoogleButton'),
                { 
                    theme: 'outline', 
                    size: 'large',
                    type: 'standard',
                    text: 'signup_with',
                    width: 250
                }
            );

            // Custom button click handler
            document.getElementById('customGoogleBtn').addEventListener('click', function() {
                // Click the hidden Google button
                var hiddenButton = document.querySelector('#hiddenGoogleButton div[role="button"]');
                if (hiddenButton) {
                    hiddenButton.click();
                } else {
                    // Fallback: trigger the prompt
                    google.accounts.id.prompt();
                }
            });
        };

        // Google Sign-In Callback
        function handleCredentialResponse(response) {
            // Show loading
            Swal.fire({
                title: 'Signing up with Google...',
                text: 'Please wait',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send the credential to your server
            fetch('google_login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    credential: response.credential
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Account Created!',
                        text: data.message,
                        confirmButtonColor: '#4c6fff',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Signup Failed',
                        text: data.message,
                        confirmButtonColor: '#e74c3c',
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred during signup. Please try again.',
                    confirmButtonColor: '#e74c3c',
                });
            });
        }
    </script>    
</body>
</html>