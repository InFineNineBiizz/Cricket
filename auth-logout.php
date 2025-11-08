<!DOCTYPE html>
<html lang="en" data-layout="">

<head>
    <meta charset="utf-8" />
    <title>Log Out</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <?php 
        include "links.php";
    ?>
    
</head>

<body class="h-100">

    <div class="auth-bg d-flex min-vh-100">
        <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xxl-3 col-lg-5 col-md-6">
                <a href="index-2.html" class="auth-brand d-flex justify-content-center mb-2">
                    <img src="assets/images/logo-dark.png" alt="dark logo" height="26" class="logo-dark">
                    <img src="assets/images/logo.png" alt="logo light" height="26" class="logo-light">
                </a>
                
                <br>
                <div class="card overflow-hidden text-center p-xxl-4 p-3 mb-0">

                    <h4 class="fw-semibold mb-2 fs-18">You are Logged Out</h4>

                    <div class="text-center">
                        <div class="mt-4">
                            <div class="logout-checkmark">
                                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2">
                                    <circle class="path circle" fill="none" stroke="#4bd396" stroke-width="6"
                                        stroke-miterlimit="10" cx="65.1" cy="65.1" r="62.1"></circle>
                                    <polyline class="path check" fill="none" stroke="#4bd396" stroke-width="6"
                                        stroke-linecap="round" stroke-miterlimit="10"
                                        points="100.2,40.2 51.5,88.8 29.8,67.5 "></polyline>
                                </svg>
                            </div>
                        </div>

                        <h3 class="mt-2">See you again !</h3>

                        <p class="text-muted"> You are now successfully sign out. </p>
                    </div>

                    <p class="text-muted fs-14 mt-3 mb-0">
                        Back to <a href="auth-login.php" class="text-danger fw-semibold ms-1">Login !</a>
                    </p>

                </div>

            </div>
        </div>
    </div>


    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>


<!-- Mirrored from coderthemes.com/adminto/layouts/auth-logout.html by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 07 Nov 2025 04:58:39 GMT -->
</html>