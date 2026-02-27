<?php
   session_name('admin_session');
   session_start();
   include 'connection.php';

   $swalUserError = "";        // For non-admin login
   $swalLogoutMsg = "";        // For logout success

   // Check logout message
   if(isset($_SESSION['logout_message'])) 
   {
        $swalLogoutMsg = $_SESSION['logout_message'];
        unset($_SESSION['logout_message']); 
   }

   if(isset($_POST['login']))
   {
        $str = "SELECT * FROM users WHERE email='".$_POST['mail']."' AND password='".$_POST['pswd']."'"; 
        $result = mysqli_query($conn, $str);
        $row = mysqli_fetch_array($result);
        $count = mysqli_num_rows($result);

        if($count > 0)
        {
            if($row['role'] == 'Admin')
            {
                $_SESSION['done']  = true;
                $_SESSION['email'] = $row['email'];
                $_SESSION['name']  = $row['uname'];
                $_SESSION['role']  = $row['role'];
   
                header('location:index.php');
                exit();
            }
            else
            {
                // Non-admin user trying to login to admin
                $swalUserError = "Users cannot log in to the admin panel!";
            }
        }
        else
        {
            $invalid = "<center><p style='color:red;font-weight:bold;'>Invalid email or password!</p></center>";
        }
   }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Login | Admin</title>
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

                    <h4 class="fw-semibold mb-3 fs-22">Log in to your account</h4>

                    <form method="POST" class="text-start mb-3" id="loginForm">
                        
                        <div class="mb-3">
                            <label class="form-label" for="em">Email</label>
                            <input type="email" id="em" name="mail" class="form-control" placeholder="Enter your email">
                            <small id="email_error" class="text-danger"></small>
                        </div> 

                        <div class="mb-3">
                            <label class="form-label" for="pass">Password</label>
                            <input type="password" id="pass" name="pswd" class="form-control"
                                placeholder="Enter your password">
                            <small id="password_error" class="text-danger"></small>
                        </div>

                        <?php 
                            if(isset($invalid)) {
                                echo $invalid;  
                            }
                        ?>
                        <p class="text-muted mt-2">
                             <a href="forgotpass.php" class="forgot-link">Forgot Password?</a>
                        </p>

                        <div class="d-grid mt-3">
                            <button class="btn btn-primary fw-semibold" name="login" type="submit">Login</button>
                        </div>
                    </form>

                    <p class="text-muted fs-14 mb-0">Don't have an account?
                        <a href="auth-register.php" class="fw-semibold text-danger ms-1">Sign Up !</a>
                    </p>

                </div>                  
            </div>
        </div>
    </div>

    <?php include "scripts.php"; ?>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- LIVE VALIDATION SCRIPT -->
    <script>
        const emailInput = document.getElementById('em');
        const passwordInput = document.getElementById('pass');
        const emailError = document.getElementById('email_error');
        const passwordError = document.getElementById('password_error');
        const form = document.getElementById('loginForm');

        // Email validation
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

        // Password validation
        function validatePasswordField() {
            const pass = passwordInput.value.trim();

            if (pass === "") {
                passwordError.textContent = "Password is required.";
                return false;
            } else if (pass.length < 8) {
                passwordError.textContent = "Password must be at least 8 characters.";
                return false;
            } else {
                passwordError.textContent = "";
                return true;
            }
        }

        // Live typing validation
        emailInput.addEventListener('input', validateEmailField);
        emailInput.addEventListener('blur', validateEmailField);

        passwordInput.addEventListener('input', validatePasswordField);
        passwordInput.addEventListener('blur', validatePasswordField);

        // Stop form submit if invalid
        form.addEventListener('submit', function (e) {
            let e1 = validateEmailField();
            let e2 = validatePasswordField();

            if (!e1 || !e2) {
                e.preventDefault();
            }
        });
    </script>

    <!-- SHOW SWEETALERT WHEN NORMAL USER TRIES TO LOGIN AS ADMIN -->
    <?php if(!empty($swalUserError)): ?>
    <script>
        Swal.fire({
            icon: "error",
            title: "Access Denied",
            text: "Users cannot log in to the admin panel!",
            confirmButtonText: "OK",
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
            willClose: () => {                    
                window.location.href = "auth-login.php";
            }
            });
    </script>
    <?php endif; ?>

    <!-- SHOW SWEETALERT ON LOGOUT SUCCESS -->
    <?php if(!empty($swalLogoutMsg)): ?>
    <script>
        Swal.fire({
            icon: "success",
            title: "Logged Out",
            text: "You have been logged out successfully!",
            confirmButtonText: "OK",
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
            willClose: () => {                    
                window.location.href = "auth-login.php";
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>
