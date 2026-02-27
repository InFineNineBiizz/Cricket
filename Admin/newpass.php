<?php
    session_start();
    include 'connection.php';

    // If email not set in session, redirect back to Forgot Password
    if (!isset($_SESSION['email'])) {
        header("Location: forgotpass.php");
        exit;
    }

    $errorMsg     = "";
    $swalSuccess  = "";

    if (isset($_POST['reset'])) {
        $newpass   = trim($_POST['newpass'] ?? '');
        $confpass  = trim($_POST['confpass'] ?? '');

        if ($newpass === "" || $confpass === "") {
            $errorMsg = "<div class='alert alert-danger text-center mt-3'>All fields are required.</div>";
        } elseif (strlen($newpass) < 8) {
            $errorMsg = "<div class='alert alert-danger text-center mt-3'>Password must be at least 8 characters.</div>";
        } elseif ($newpass !== $confpass) {
            $errorMsg = "<div class='alert alert-danger text-center mt-3'>Password and Confirm Password do not match.</div>";
        } else {
            // Update password in DB 
            $email      = mysqli_real_escape_string($conn, $_SESSION['email']);
            $newpass_db = mysqli_real_escape_string($conn, $newpass);

            $update = "UPDATE users SET password='$newpass_db' WHERE email='$email'";
            if (mysqli_query($conn, $update)) {
                // Clear OTP-related data
                unset($_SESSION['otp']);
                unset($_SESSION['otp_exp']);

                $swalSuccess = "Password reset successfully! You can now login with your new password.";
            } else {
                $errorMsg = "<div class='alert alert-danger text-center mt-3'>Something went wrong. Please try again.</div>";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Create New Password | Admin</title>
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

                    <h4 class="fw-semibold mb-2 fs-22">Create New Password</h4>
                    <p class="text-muted mb-3">
                        Set a strong password for your account. Use at least 8 characters.
                    </p>

                    <!-- PHP error message -->
                    <?php 
                        if (!empty($errorMsg)) {
                            echo $errorMsg;
                        }
                    ?>

                    <form method="POST" class="text-start mb-3" id="newPassForm">

                        <div class="mb-3">
                            <label class="form-label" for="newpass">New Password</label>
                            <input type="password" id="newpass" name="newpass" class="form-control" placeholder="Enter new password">
                            <small id="newpass_error" class="text-danger"></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="confpass">Confirm New Password</label>
                            <input type="password" id="confpass" name="confpass" class="form-control" placeholder="Re-enter new password">
                            <small id="confpass_error" class="text-danger"></small>
                        </div>

                        <div class="d-grid mt-3">
                            <button class="btn btn-primary fw-semibold" name="reset" type="submit">
                                Reset Password
                            </button>
                        </div>

                        <p class="text-muted fs-14 mt-3 mb-0 text-center">
                            Remember your password?
                            <a href="login.php" class="fw-semibold text-danger ms-1">Back to Login</a>
                        </p>

                    </form>

                </div>                  
            </div>
        </div>
    </div>

    <?php include "scripts.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const newInput   = document.getElementById('newpass');
        const confInput  = document.getElementById('confpass');
        const newError   = document.getElementById('newpass_error');
        const confError  = document.getElementById('confpass_error');
        const form       = document.getElementById('newPassForm');

        function validateNewPass() {
            const pass = newInput.value.trim();

            if (pass === "") {
                newError.textContent = "New password is required.";
                return false;
            } else if (pass.length < 8) {
                newError.textContent = "Password must be at least 8 characters.";
                return false;
            } else {
                newError.textContent = "";
                return true;
            }
        }

        function validateConfPass() {
            const pass  = newInput.value.trim();
            const cpass = confInput.value.trim();

            if (cpass === "") {
                confError.textContent = "Confirm password is required.";
                return false;
            } else if (cpass !== pass) {
                confError.textContent = "Passwords do not match.";
                return false;
            } else {
                confError.textContent = "";
                return true;
            }
        }

        newInput.addEventListener('input', validateNewPass);
        newInput.addEventListener('blur', validateNewPass);

        confInput.addEventListener('input', validateConfPass);
        confInput.addEventListener('blur', validateConfPass);

        form.addEventListener('submit', function (e) {
            let v1 = validateNewPass();
            let v2 = validateConfPass();

            if (!v1 || !v2) {
                e.preventDefault();
            }
        });
    </script>

    <?php if (!empty($swalSuccess)): ?>
    <script>
        Swal.fire({
            icon: "success",
            title: "Password Reset",
            text: "<?php echo $swalSuccess; ?>",
            timer: 3000,
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
