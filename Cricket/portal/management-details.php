<?php 
    session_start();
    include "connection.php";

    $fname=$lname=$number=$fname1=$lname1=$number1="";

    $id=$_GET['id']??""; 
    if(!empty($_GET['id']))
    {
        if(isset($_SESSION['add_auction']) || isset($_SESSION['cnf']))
        {
            $query="select * from auc_man where aid='".$_SESSION['add_auction']."'";
            $req=mysqli_query($conn,$query);
            $row=mysqli_fetch_array($req);
            $fname=$row['fname'];
            $lname=$row['lname'];
            $number=$row['number'];

            $que="select * from lead_auc where aid='".$_SESSION['add_auction']."'";
            $resq=mysqli_query($conn,$que);
            $result=mysqli_fetch_array($resq);
            $fname1=$result['fname'];
            $lname1=$result['lname'];
            $number1=$result['number'];
        }
    }
    if(isset($_POST['btn']))
    {
        if(!empty($_GET['id']))
        {   
            $id=$_GET['id'];            
            $auction_id = $_SESSION['add_auction'];            

            if(isset($_SESSION['cnf']))
            {
                $sl="update auc_man set fname='".$_POST['fname']."',lname='".$_POST['lname']."',number='".$_POST['mnumber']."' where aid='".$auction_id."'";
                $qk=mysqli_query($conn,$sl);
                
                $sl1="update lead_auc set fname='".$_POST['fname1']."',lname='".$_POST['lname1']."',number='".$_POST['lnumber']."' where aid='".$auction_id."'";
                $qk1=mysqli_query($conn,$sl1);

                header("location:confirm-auction.php?id=$id");
                exit;
            }
            else                
            {                
                $str="insert into auc_man(aid,fname,lname,number) values('".$auction_id."','".$_POST['fname']."','".$_POST['lname']."','".$_POST['mnumber']."')";
                $res=mysqli_query($conn,$str);

                $s="insert into lead_auc(aid,fname,lname,number) values('".$auction_id."','".$_POST['fname1']."','".$_POST['lname1']."','".$_POST['lnumber']."')";
                $r=mysqli_query($conn,$s);

                header("location:confirm-auction.php?id=$id");
                exit;
            }
        }
    }

    if(isset($_POST['prevBtn']))
    {
        $_SESSION['manage']=true;       
        header("location:add-auction.php?id=$id");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Details | <?php echo $title_name;?></title>
    
    <script src="../assets/script/jquery.min.js"></script>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">    
    <style>
        /* Additional styles for auction form only - doesn't modify home-style.css */
        .auction-form-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .form-card-header {
            padding: 24px 32px;
            border-bottom: 1px solid #e2e8f0;
        }

        .form-card-header h1 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2d3748;
        }

        /* Stepper */
        .form-stepper {
            padding: 40px 32px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .form-stepper::before {
            content: '';
            position: absolute;
            top: 60px;
            left: 20%;
            right: 20%;
            height: 2px;
            background: #e2e8f0;
            z-index: 0;
        }

        .stepper-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .stepper-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #a0aec0;
            font-size: 15px;
            transition: all 0.3s;
        }

        .stepper-step.active .stepper-circle {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-color: #f59e0b;
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .stepper-step.completed .stepper-circle {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }

        .stepper-step.completed .stepper-circle::before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .stepper-step.completed .stepper-circle span {
            display: none;
        }

        .stepper-label {
            font-size: 14px;
            font-weight: 500;
            color: #718096;
            text-align: center;
        }

        .stepper-step.active .stepper-label {
            color: #f59e0b;
            font-weight: 600;
        }

        /* Form Content */
        .form-card-body {
            padding: 32px;
        }

        .form-section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 28px;
        }

        .form-subsection-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin: 32px 0 20px 0;
        }

        .form-subsection-title:first-of-type {
            margin-top: 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .form-row.name-row {
            grid-template-columns: 1fr 1fr 2fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
        }

        .form-group label .required {
            color: #f59e0b;
        }

        .form-control {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #2d3748;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .form-control::placeholder {
            color: #a0aec0;
        }

        .form-control.error {
            border-color: #ef4444;
        }

        .form-control.success {
            border-color: #10b981;
        }

        .error-message {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        /* Management Box */
        .management-box {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 28px;
            margin: 24px 0;
        }

        .info-box {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
            border-left: 4px solid #f59e0b;
            padding: 16px;
            border-radius: 10px;
            display: flex;
            gap: 14px;
            align-items: start;
        }

        .info-box i {
            color: #f59e0b;
            font-size: 18px;
            margin-top: 2px;
        }

        .info-box p {
            margin: 0;
            font-size: 13px;
            color: #4a5568;
            line-height: 1.6;
        }

        .btn-set-self {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .btn-set-self:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
        }

        /* Card Footer */
        .form-card-footer {
            padding: 24px 32px;
            border-top: 1px solid #e2e8f0;
            background: #f7fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-previous,
        .btn-next {
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-previous {
            background: white;
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }

        .btn-previous:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
        }

        .btn-next {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .btn-next:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-stepper::before {
                left: 15%;
                right: 15%;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <?php include "topbar.php";?>
    
    <!-- Sidebar -->
    <?php include "sidebar.php";?>
    
    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="auction-form-card">
            <!-- Header -->
            <div class="form-card-header">
                <h1>Auction / Management Details</h1>
            </div>

            <!-- Stepper -->
            <div class="form-stepper">
                <div class="stepper-step completed">
                    <div class="stepper-circle"><span>1</span></div>
                    <div class="stepper-label">Basic Details</div>
                </div>
                <div class="stepper-step active">
                    <div class="stepper-circle"><span>2</span></div>
                    <div class="stepper-label">Management Details</div>
                </div>
                <div class="stepper-step">
                    <div class="stepper-circle"><span>3</span></div>
                    <div class="stepper-label">Confirm</div>
                </div>
            </div>

            <!-- Form Body -->
            <div class="form-card-body">
                <h2 class="form-section-title">Add Management Detail</h2>
                <form enctype="multipart/form-data" method="POST" id="managementForm">
                    <div class="management-box">
                        <h3 class="form-subsection-title" style="margin-top: 0;">Auction Manager</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone<span class="required">*</span></label>
                                <input type="text" name="mnumber" class="form-control" value="<?php echo $number;?>" id="managerPhone" placeholder="Enter Phone Number">
                                <span class="error-message" id="managerPhoneError">Please enter a valid 10-digit phone number</span>
                            </div>
                            <div class="form-group">
                                <div class="info-box">
                                    <i class="fas fa-info-circle"></i>
                                    <p>Only The User Added As "Auction Manager" Can Access The Auction Control Screen.</p>
                                </div>
                            </div>
                        </div>
                        <div class="form-row name-row">
                            <div class="form-group">
                                <label>First Name<span class="required">*</span></label>
                                <input type="text" name="fname" class="form-control" value="<?php echo $fname;?>" id="managerFirstName" placeholder="Enter First Name">
                                <span class="error-message" id="managerFirstNameError">First name is required</span>
                            </div>
                            <div class="form-group">
                                <label>Last Name<span class="required">*</span></label>
                                <input type="text" name="lname" class="form-control" value="<?php echo $lname;?>" id="managerLastName" placeholder="Enter Last Name">
                                <span class="error-message" id="managerLastNameError">Last name is required</span>
                            </div>
                            <div class="form-group">
                                <!-- Empty column for spacing -->
                            </div>
                        </div>
                        <button type="button" class="btn-set-self" id="setManagerSelf">
                            <i class="fas fa-user-check"></i> Set Your Self
                        </button>
                    </div>

                    <div class="management-box">
                        <h3 class="form-subsection-title" style="margin-top: 0;">Lead Auctioneer</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone<span class="required">*</span></label>
                                <input type="text" name="lnumber" class="form-control" value="<?php echo $number1;?>" id="auctioneerPhone" placeholder="Enter Phone Number">
                                <span class="error-message" id="auctioneerPhoneError">Please enter a valid 10-digit phone number</span>
                            </div>
                            <div class="form-group">
                                <div class="info-box">
                                    <i class="fas fa-info-circle"></i>
                                    <p>Only The User Added As "Lead Auctioneer" Can Access The Auctioneer Screen.</p>
                                </div>
                            </div>
                        </div>
                        <div class="form-row name-row">
                            <div class="form-group">
                                <label>First Name<span class="required">*</span></label>
                                <input type="text" name="fname1" class="form-control" value="<?php echo $fname1;?>" id="auctioneerFirstName" placeholder="Enter First Name">
                                <span class="error-message" id="auctioneerFirstNameError">First name is required</span>
                            </div>
                            <div class="form-group">
                                <label>Last Name<span class="required">*</span></label>
                                <input type="text" name="lname1" class="form-control" value="<?php echo $lname1;?>" id="auctioneerLastName" placeholder="Enter Last Name">
                                <span class="error-message" id="auctioneerLastNameError">Last name is required</span>
                            </div>
                            <div class="form-group">
                                <!-- Empty column for spacing -->
                            </div>
                        </div>
                        <button type="button" class="btn-set-self" id="setAuctioneerSelf">
                            <i class="fas fa-user-check"></i> Set Your Self
                        </button>
                    </div>
                    <div class="form-card-footer">
                        <button type="submit" name="prevBtn" class="btn-previous">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        <button type="submit" name="btn" class="btn-next" id="nextBtn">
                            Next <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>            
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Navigation
            const navLinks = document.querySelectorAll('.nav-link');
            const currentPage = window.location.pathname.split('/').pop();
            
            navLinks.forEach(link => {
                const linkPage = link.getAttribute('data-page');
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });

            // Validation functions
            function validatePhone(phone) {
                // Remove all non-digit characters
                const cleaned = phone.replace(/\D/g, '');
                // Check if it's 10 digits
                return cleaned.length === 10;
            }

            function validateName(name) {
                // Check if name is not empty and contains only letters and spaces
                return name.trim().length >= 2 && /^[a-zA-Z\s]+$/.test(name.trim());
            }

            function showError(fieldId, errorId) {
                $('#' + fieldId).addClass('error').removeClass('success');
                $('#' + errorId).addClass('show');
            }

            function showSuccess(fieldId, errorId) {
                $('#' + fieldId).addClass('success').removeClass('error');
                $('#' + errorId).removeClass('show');
            }

            function clearValidation(fieldId, errorId) {
                $('#' + fieldId).removeClass('error success');
                $('#' + errorId).removeClass('show');
            }

            // Live validation for Manager Phone
            $('#managerPhone').on('input blur', function() {
                const value = $(this).val();
                if (value === '') {
                    clearValidation('managerPhone', 'managerPhoneError');
                } else if (validatePhone(value)) {
                    showSuccess('managerPhone', 'managerPhoneError');
                } else {
                    showError('managerPhone', 'managerPhoneError');
                }
            });

            // Live validation for Manager First Name
            $('#managerFirstName').on('input blur', function() {
                const value = $(this).val();
                if (value === '') {
                    clearValidation('managerFirstName', 'managerFirstNameError');
                } else if (validateName(value)) {
                    showSuccess('managerFirstName', 'managerFirstNameError');
                } else {
                    $('#managerFirstNameError').text('Please enter a valid name (letters only)');
                    showError('managerFirstName', 'managerFirstNameError');
                }
            });

            // Live validation for Manager Last Name
            $('#managerLastName').on('input blur', function() {
                const value = $(this).val();
                if (value === '') {
                    clearValidation('managerLastName', 'managerLastNameError');
                } else if (validateName(value)) {
                    showSuccess('managerLastName', 'managerLastNameError');
                } else {
                    $('#managerLastNameError').text('Please enter a valid name (letters only)');
                    showError('managerLastName', 'managerLastNameError');
                }
            });

            // Live validation for Auctioneer Phone
            $('#auctioneerPhone').on('input blur', function() {
                const value = $(this).val();
                if (value === '') {
                    clearValidation('auctioneerPhone', 'auctioneerPhoneError');
                } else if (validatePhone(value)) {
                    showSuccess('auctioneerPhone', 'auctioneerPhoneError');
                } else {
                    showError('auctioneerPhone', 'auctioneerPhoneError');
                }
            });

            // Live validation for Auctioneer First Name
            $('#auctioneerFirstName').on('input blur', function() {
                const value = $(this).val();
                if (value === '') {
                    clearValidation('auctioneerFirstName', 'auctioneerFirstNameError');
                } else if (validateName(value)) {
                    showSuccess('auctioneerFirstName', 'auctioneerFirstNameError');
                } else {
                    $('#auctioneerFirstNameError').text('Please enter a valid name (letters only)');
                    showError('auctioneerFirstName', 'auctioneerFirstNameError');
                }
            });

            // Live validation for Auctioneer Last Name
            $('#auctioneerLastName').on('input blur', function() {
                const value = $(this).val();
                if (value === '') {
                    clearValidation('auctioneerLastName', 'auctioneerLastNameError');
                } else if (validateName(value)) {
                    showSuccess('auctioneerLastName', 'auctioneerLastNameError');
                } else {
                    $('#auctioneerLastNameError').text('Please enter a valid name (letters only)');
                    showError('auctioneerLastName', 'auctioneerLastNameError');
                }
            });

            // Set Your Self buttons
            $('#setManagerSelf').on('click', function() {
                // Replace with actual logged-in user's phone number and name
                $('#managerPhone').val('8140474100').trigger('input');
                $('#managerFirstName').val('Yash').trigger('input');
                $('#managerLastName').val('Katariya').trigger('input');
            });

            $('#setAuctioneerSelf').on('click', function() {
                // Replace with actual logged-in user's phone number and name
                $('#auctioneerPhone').val('8140474100').trigger('input');
                $('#auctioneerFirstName').val('Yash').trigger('input');
                $('#auctioneerLastName').val('Katariya').trigger('input');
            });                        

            // Form submission handler with validation
            $('#managementForm').on('submit', function(e) {
                let isValid = true;

                // Validate all fields
                const managerPhone = $('#managerPhone').val();
                const managerFirstName = $('#managerFirstName').val();
                const managerLastName = $('#managerLastName').val();
                const auctioneerPhone = $('#auctioneerPhone').val();
                const auctioneerFirstName = $('#auctioneerFirstName').val();
                const auctioneerLastName = $('#auctioneerLastName').val();

                // Check Manager Phone
                if (managerPhone === '') {
                    $('#managerPhoneError').text('Phone number is required');
                    showError('managerPhone', 'managerPhoneError');
                    isValid = false;
                } else if (!validatePhone(managerPhone)) {
                    showError('managerPhone', 'managerPhoneError');
                    isValid = false;
                }

                // Check Manager First Name
                if (managerFirstName === '') {
                    $('#managerFirstNameError').text('First name is required');
                    showError('managerFirstName', 'managerFirstNameError');
                    isValid = false;
                } else if (!validateName(managerFirstName)) {
                    $('#managerFirstNameError').text('Please enter a valid name (letters only)');
                    showError('managerFirstName', 'managerFirstNameError');
                    isValid = false;
                }

                // Check Manager Last Name
                if (managerLastName === '') {
                    $('#managerLastNameError').text('Last name is required');
                    showError('managerLastName', 'managerLastNameError');
                    isValid = false;
                } else if (!validateName(managerLastName)) {
                    $('#managerLastNameError').text('Please enter a valid name (letters only)');
                    showError('managerLastName', 'managerLastNameError');
                    isValid = false;
                }

                // Check Auctioneer Phone
                if (auctioneerPhone === '') {
                    $('#auctioneerPhoneError').text('Phone number is required');
                    showError('auctioneerPhone', 'auctioneerPhoneError');
                    isValid = false;
                } else if (!validatePhone(auctioneerPhone)) {
                    showError('auctioneerPhone', 'auctioneerPhoneError');
                    isValid = false;
                }

                // Check Auctioneer First Name
                if (auctioneerFirstName === '') {
                    $('#auctioneerFirstNameError').text('First name is required');
                    showError('auctioneerFirstName', 'auctioneerFirstNameError');
                    isValid = false;
                } else if (!validateName(auctioneerFirstName)) {
                    $('#auctioneerFirstNameError').text('Please enter a valid name (letters only)');
                    showError('auctioneerFirstName', 'auctioneerFirstNameError');
                    isValid = false;
                }

                // Check Auctioneer Last Name
                if (auctioneerLastName === '') {
                    $('#auctioneerLastNameError').text('Last name is required');
                    showError('auctioneerLastName', 'auctioneerLastNameError');
                    isValid = false;
                } else if (!validateName(auctioneerLastName)) {
                    $('#auctioneerLastNameError').text('Please enter a valid name (letters only)');
                    showError('auctioneerLastName', 'auctioneerLastNameError');
                    isValid = false;
                }

                // If validation fails, prevent PHP form submission
                if (!isValid) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: $('.error').first().offset().top - 100
                    }, 500);
                    return false;
                }
                
                // If all valid, allow normal PHP form submission
                return true;
            });
        });
    </script>
</body>
</html>