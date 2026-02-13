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
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_name'] = $row['uname'];
            $_SESSION['user_role'] = $row['role'];
            $_SESSION['login_type'] = 'email';

            // Update last login
            $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = " . $row['id'];
            mysqli_query($conn, $updateQuery);

            header('location:index.php');                
            exit();
        }
        else
        {
            $invalid="<div class='alert alert-danger text-center py-2 mb-2'>Invalid email or password!</div>";
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login | CrickFolio</title>
    <link rel="stylesheet" href="assets/css/login.css">
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
                            <span>Sign in with Google</span>
                        </button>
                    </div><br>
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

            $('#email').on('keyup', function() {
                $('.alert').remove();
            });

            // Email validation
            $('#email').on('input blur', function() {
                var email = $(this).val().trim();
                var emailError = $('#emailError');
                
                if (email === '') {
                    $(this).removeClass('valid').addClass('error');
                    emailError.text('Email is required').show();
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
                // Check if this is a regular form submission (not Google Sign-In)
                if (e.originalEvent && e.originalEvent.submitter && e.originalEvent.submitter.name !== 'login') {
                    return; // Let Google Sign-In handle it
                }
                
                // Trigger validations
                $('#email').trigger('blur');
                $('#password').trigger('blur');
                
                var emailValid    = $('#email').hasClass('valid');
                var passwordValid = $('#password').hasClass('valid');
                
                // If any invalid, stop submission
                if (!(emailValid && passwordValid)) {
                    e.preventDefault();
                }
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
                    text: 'signin_with',
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
                title: 'Signing in with Google...',
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
                        title: 'Login Successful!',
                        text: data.message,                        
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
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
                    text: 'An error occurred during login. Please try again.',
                    confirmButtonColor: '#e74c3c',
                });
            });
        }
    </script>
    
    <?php if (isset($_SESSION['log'])) {?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Register Successful',
            text: 'Your account has been registered successfully.',            
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    </script>
    <?php } unset($_SESSION['log']); ?>
</body>
</html>