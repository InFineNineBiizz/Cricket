<?php 
    session_start();
    include 'connection.php';	

    if(isset($_POST['register']))
    {
        // Basic server-side validation (still important!)
        $name      = trim($_POST['nm']);
        $email     = trim($_POST['email']);
        $password  = trim($_POST['pwd']);
        $cpassword = trim($_POST['con']);
        $role      = "User";

        if($name != "" && $email != "" && $password != "" && $cpassword != "" && $password == $cpassword)
        {
            // NOTE: For real project, you should hash password:
            //$password = password_hash($password, PASSWORD_BCRYPT);

            $str = "INSERT INTO users(name,email,password,cpassword,role)
                    VALUES('".$name."','".$email."','".$password."','".$cpassword."','".$role."')";
            $res = mysqli_query($conn,$str);

            if($res)
            {   
                $_SESSION['log'] = true;
                $_SESSION['reg_success'] = true;

                // reload same page so JS can show SweetAlert
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }		
?>

<!DOCTYPE html>
<html lang="en" data-layout="">

<head>
    <meta charset="utf-8" />
    <title>Sign Up</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <?php 
        include "links.php";
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    
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

                    <h4 class="fw-semibold mb-3 fs-18">Sign Up to your account</h4>

                    <form method="POST" id="registerForm" class="text-start mb-3" novalidate>
                       
                        <div class="mb-3">
                            <label class="form-label" for="fname">Your Name</label>
                            <input type="text" id="fname" name="nm" class="form-control"
                                placeholder="Enter your name">
                            <small id="nmError" class="text-danger"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="em">Email</label>
                            <input type="email" id="em" name="email" class="form-control"
                                placeholder="Enter your email">
                            <small id="emailError" class="text-danger"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" id="password" name="pwd" class="form-control"
                                placeholder="Enter your password">
                            <small id="pwdError" class="text-danger"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="cpassword">Confirm Password</label>
                            <input type="password" id="cpassword"  name="con" class="form-control"
                                placeholder="Enter your password again">
                            <small id="conError" class="text-danger"></small>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="checkbox-signin">
                                <label class="form-check-label" for="checkbox-signin">
                                    I agree to all 
                                    <a href="#!" class="link-dark text-decoration-underline">
                                        Terms & Condition
                                    </a>
                                </label>
                            </div>
                        </div>
                        <small id="termsError" class="text-danger d-block mb-2"></small>

                        <div class="d-grid">
                            <button class="btn btn-primary fw-semibold" name="register" type="submit">
                                Sign Up
                            </button>
                        </div>
                    </form>

                    <p class="text-nuted fs-14 mb-0">
                        Already have an account? 
                        <a href="auth-login.php" class="fw-semibold text-danger ms-1">Login !</a>
                    </p>
                </div>
                
            </div>
        </div>
    </div>
    
    <?php 
        include "scripts.php";    
    ?>

    <!-- LIVE VALIDATION SCRIPT -->
    <script>
        const form           = document.getElementById('registerForm');
        const nameInput      = document.getElementById('fname');
        const emailInput     = document.getElementById('em');
        const passwordInput  = document.getElementById('password');
        const cpasswordInput = document.getElementById('cpassword');
        const termsCheckbox  = document.getElementById('checkbox-signin');

        const nmError     = document.getElementById('nmError');
        const emailError  = document.getElementById('emailError');
        const pwdError    = document.getElementById('pwdError');
        const conError    = document.getElementById('conError');
        const termsError  = document.getElementById('termsError');

        function validateName() {
            const value = nameInput.value.trim();
            if (value === "") {
                nmError.textContent = "Name is required.";
                return false;
            } else if (value.length < 3) {
                nmError.textContent = "Name must be at least 3 characters.";
                return false;
            } else {
                nmError.textContent = "";
                return true;
            }
        }

        function validateEmail() {
            const value = emailInput.value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (value === "") {
                emailError.textContent = "Email is required.";
                return false;
            } else if (!emailPattern.test(value)) {
                emailError.textContent = "Please enter a valid email address.";
                return false;
            } else {
                emailError.textContent = "";
                return true;
            }
        }

        function validatePassword() {
            const value = passwordInput.value;

            if (value === "") {
                pwdError.textContent = "Password is required.";
                return false;
            } else if (value.length < 8) {
                pwdError.textContent = "Password must be at least 8 characters.";
                return false;
            } else {
                pwdError.textContent = "";
                return true;
            }
        }

        function validateConfirmPassword() {
            const pwd  = passwordInput.value;
            const cpwd = cpasswordInput.value;

            if (cpwd === "") {
                conError.textContent = "Please confirm your password.";
                return false;
            } else if (pwd !== cpwd) {
                conError.textContent = "Passwords do not match.";
                return false;
            } else {
                conError.textContent = "";
                return true;
            }
        }

        function validateTerms() {
            if (!termsCheckbox.checked) {
                termsError.textContent = "You must agree to the Terms & Conditions.";
                return false;
            } else {
                termsError.textContent = "";
                return true;
            }
        }

        // Live validation on input/blur
        nameInput.addEventListener('input', validateName);
        emailInput.addEventListener('input', validateEmail);
        passwordInput.addEventListener('input', () => {
            validatePassword();
            validateConfirmPassword(); // keep confirm in sync
        });
        cpasswordInput.addEventListener('input', validateConfirmPassword);
        termsCheckbox.addEventListener('change', validateTerms);

        // On submit
        form.addEventListener('submit', function (e) {
            let isValid = true;

            if (!validateName())            isValid = false;
            if (!validateEmail())           isValid = false;
            if (!validatePassword())        isValid = false;
            if (!validateConfirmPassword()) isValid = false;
            if (!validateTerms())           isValid = false;

            if (!isValid) {
                // Stop form submission if any validation fails
                e.preventDefault();
            }
        });
    </script>
    <?php if(isset($_SESSION['reg_success'])): ?>
    <script>
        Swal.fire({
            title: "Registration Successful!",
            text: "Your account has been created.",
            icon: "success",
            confirmButtonText: "OK"
        }).then(() => {
            window.location = "auth-login.php"; // redirect to login
        });
    </script>
    <?php unset($_SESSION['reg_success']); endif; ?>

</body>
</html>
