<?php
    session_start();
    include "connection.php";    
    
    // Initialize variables
    $season_id = null;
    $added = null;
    $result = null;
    
    if(isset($_GET['id']))
    {
        $season_id=$_GET['id'];

        // Fetch organizers from database
        $sql = "SELECT o.*,os.* FROM organizers o,season_organizer os where os.organizer_id=o.id and os.season_id='".$season_id."' order by os.organizer_id";
        $result = mysqli_query($conn, $sql);
    }
    else if(isset($_SESSION['add_season']))
    {
        $added=$_SESSION['add_season'];
    
        // Fetch organizers from database
        $sql = "SELECT o.*,os.* FROM organizers o,season_organizer os where os.organizer_id=o.id and os.season_id='".$added."' order by os.organizer_id";        
        $result = mysqli_query($conn, $sql);        
    }
    
    // Count organizers
    $hasOrganizers = ($result && mysqli_num_rows($result) > 0);
        
    // Handle form submission
    if(isset($_POST['save_organizer'])) {
        $oname = $_POST['oname'];
        $onumber = $_POST['onumber'];
        $oemail = $_POST['oemail'];
        
        $insert = "INSERT INTO organizers(name, number, email) VALUES('$oname', '$onumber', '$oemail')";
        $res=mysqli_query($conn, $insert);
        if($res)
        {
            $organizer_id = mysqli_insert_id($conn);
            
            if(isset($season_id))
            {                       
                $ins="insert into season_organizer(organizer_id,season_id) values('".$organizer_id."','".$season_id."')";
                $resq=mysqli_query($conn,$ins);
                header("Location: organizers-list.php?id=".$season_id);
            }                       
            else if(isset($added))
            {                       
                $ins="insert into season_organizer(organizer_id,season_id) values('".$organizer_id."','".$added."')";
                $resq=mysqli_query($conn,$ins);
                header("Location: organizers-list.php");
            }
            exit();
        }
    }    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organizer Details | CrickFolio Portal</title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">    
    <script src="../assets/script/jquery.min.js"></script>

    <style>
        :root{
            --primary: #f59e0b;
            --primary-dark:#d97706;
            --bg-light:#f8fafc;
            --border:#e5e7eb;
            --text:#1f2937;
            --success:#10b981;
            --error:#ef4444;
            --blue:#2563eb;
        }

        body{
            background: var(--bg-light);
        }

        .page-wrapper{
            margin-left: 260px;
            padding-top: 80px;
            padding: 30px;
        }

        .page-header{
            display:flex;
            justify-content: space-between;
            align-items:center;
            margin-bottom: 25px;
        }

        .page-header h4{
            font-size: 18px;
            font-weight: 700;
            color: grey;
        }
        
        .card{
            background:#fff;
            border-radius:14px;
            padding:30px;
            box-shadow:0 10px 25px rgba(0,0,0,0.06);
        }

        .card h3{
            font-size:18px;
            margin-bottom:20px;
            font-weight:600;
        }

        /* Stepper */
        .stepper{
            display:flex;
            align-items:center;
            gap:0;
            margin-bottom:35px;
            background:#fff;
            padding:20px 30px;
            border-radius:12px;
            border:1px solid #e5e7eb;
        }

        .step{
            display:flex;
            align-items:center;
            gap:10px;
            position:relative;
            flex:1;
        }

        .step:last-child{
            flex:0;
        }

        .step span{
            font-size:15px;
            font-weight:500;
            color:#6b7280;
            white-space:nowrap;
        }

        .circle{
            width:32px;
            height:32px;
            border-radius:50%;
            border:2px solid #d1d5db;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:14px;
            font-weight:600;
            color:#6b7280;
            background:#fff;
        }

        .step.completed .circle{
            background:#10b981;
            border-color:#10b981;
            color:#fff;
        }

        .step.completed .circle i{
            font-size: 14px;
        }

        .step.active .circle{
            background: #f59e0b;
            border-color: #f59e0b;
            color:#fff;
        }

        .step.active span{
            color:#111827;
            font-weight:600;
        }

        .line{
            flex:1;
            height:1px;
            background:#9ca3af;
            margin-left:14px;
        }

        .step:last-child .line{
            display:none;
        }

        /* Empty State */
        .empty-state{
            text-align: center;
            padding: 80px 20px;
        }

        .add-btn-circle{
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #f59e0b;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            border: none;
            outline: none;
        }

        .add-btn-circle i{
            pointer-events: none;
        }

        .add-btn-circle:hover{
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
        }

        .empty-state h3{
            font-size: 20px;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .empty-state p{
            color: #6b7280;
            font-size: 14px;
        }

        /* Form Section */
        .form-section{
            display: none;
        }

        .form-section.show{
            display: block;
        }

        .form-grid{
            display:grid;
            grid-template-columns: repeat(2,1fr);
            gap:20px 25px;
            margin-top: 30px;
        }

        .form-group{
            display:flex;
            flex-direction:column;
        }

        .form-group label{
            font-size:14px;
            font-weight:600;
            margin-bottom:6px;
            color:#111827;
        }

        .form-group label .required{
            color: var(--error);
        }

        .form-group input{
            padding:11px 12px;
            border-radius:8px;
            border:1px solid var(--border);
            font-size:14px;
            outline:none;
            color:#374151;
            font-weight:500; 
            background:#fff;
            transition: border 0.3s;
        }

        .form-group input::placeholder{
            color:#9ca3af;
            font-weight:400;
        }

        .form-group input:focus{
            color:#374151;
            font-weight:500;
            border-color: var(--primary);
        }

        .form-group input.valid{
            border-color: var(--success);
        }

        .form-group input.invalid{
            border-color: var(--error);
            background: #fef2f2;
        }

        .error-msg{
            color: var(--error);
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .error-msg.show{
            display: block;
        }

        /* Buttons */
        .form-actions{
            display:flex;
            gap: 15px;
            margin-top:30px;
        }

        .btn-cancel{
            background: #fff;
            border: 1px solid var(--border);
            color: #374151;
            padding:12px 28px;
            font-size:15px;
            border-radius:10px;
            cursor:pointer;
            font-weight:600;
            transition: all 0.3s;
        }

        .btn-cancel:hover{
            background: #f3f4f6;
        }

        .btn-save{
            background: #f59e0b;
            border:none;
            color:#fff;
            padding:12px 28px;
            font-size:15px;
            border-radius:10px;
            cursor:pointer;
            font-weight:600;
            transition: all 0.3s;
        }

        .btn-save:hover{
            background: #d97706;
        }

        /* Footer Buttons */
        .footer-actions{
            display:flex;
            justify-content: space-between;
            margin-top:30px;
        }

        .btn-outline{
            background: #fff;
            border: 1px solid var(--border);
            color: #f59e0b;
            padding:12px 28px;
            font-size:15px;
            border-radius:10px;
            cursor:pointer;
            font-weight:600;
            transition: all 0.3s;
        }

        .btn-outline:hover{
            background: #eff6ff;
            border-color: #f59e0b;
        }

        .btn-primary{
            background: #f59e0b;
            border:none;
            color:#fff;
            padding:12px 28px;
            font-size:15px;
            border-radius:10px;
            cursor:pointer;
            font-weight:600;
            transition: all 0.3s;
        }

        .btn-primary:hover{
            background: #d97706;
        }

        .d-none{
            display: none !important;
        }

        /* Grid container for organizer cards */
        .organizer-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .organizer-item{
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 20px;
            position: relative;
            transition: all 0.3s;
            height: 100%;
        }

        .organizer-item:hover{
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
    
        @media(max-width:900px){
            .form-grid{
                grid-template-columns:1fr;
            }
            .page-wrapper{
                margin-left:0;
            }
            
            /* Make organizer cards stack on mobile */
            .organizer-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Organizer List */
        .organizer-list{
            display: none;
        }

        .organizer-list.show{
            display: block;
        }

        .action-buttons{
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 8px;
        }

        .btn-edit{
            background: #16a34a;
            color: #fff;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s;
        }

        .btn-edit:hover{
            background: #15803d;
        }

        .btn-delete-icon{
            background: #dc2626;
            color: #fff;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s;
        }

        .btn-delete-icon:hover{
            background: #b91c1c;
        }

        .organizer-info h4{
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
            padding-right: 100px;
        }

        .organizer-detail{
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
            color: #6b7280;
            font-size: 14px;
        }

        .organizer-detail:last-child{
            margin-bottom: 0;
        }

        .organizer-detail i{
            color: #9ca3af;
            font-size: 18px;
            width: 20px;
        }

    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <br><br><br>
    <div class="page-wrapper">
        
        <div class="page-header">
            <h4>Season / Organizer</h4>
        </div>

        <!-- Stepper -->
        <div class="stepper">
            <div class="step completed">
                <div class="circle"><i class="fas fa-check"></i></div>
                <span>Season Detail</span>
                <div class="line"></div>
            </div>

            <div class="step active">
                <div class="circle">2</div>
                <span>Organizer Details</span>
                <div class="line"></div>
            </div>

            <div class="step">
                <div class="circle">3</div>
                <span>Sponsor Details</span>
                <div class="line"></div>
            </div>

            <div class="step">
                <div class="circle">4</div>
                <span>Confirm</span>
            </div>
        </div>

        <!-- Card -->
        <div class="card">
            
            <!-- Empty State - Shows when no organizer -->
            <div class="empty-state" id="emptyState" style="<?php echo $hasOrganizers ? 'display:none;' : ''; ?>">
                <button type="button" class="add-btn-circle" id="addOrganizerBtn">
                    <i class="fas fa-plus"></i>
                </button>
                <h3>Add New Organizer</h3>
                <p>You have no organizer at the moment!</p>
            </div>

            <!-- Organizer List - Shows existing organizers -->
            <div class="organizer-list <?php echo $hasOrganizers ? 'show' : ''; ?>" id="organizerList">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Organizers</h3>
                    <button type="button" class="btn-save" id="addMoreBtn">
                        <i class="fas fa-plus"></i> Add Organizer
                    </button>
                </div>

                <!-- Grid container for cards -->
                <div class="organizer-grid">
                    <?php 
                    if($hasOrganizers) {
                        mysqli_data_seek($result, 0);
                        while($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <div class="organizer-item">
                        <div class="action-buttons">
                            <button class="btn-edit" onclick="editOrganizer(<?php echo $row['id']; ?>)">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn-delete-icon" onclick="deleteOrganizer(<?php echo $row['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                        <div class="organizer-info">
                            <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                            <div class="organizer-detail">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($row['email']); ?></span>
                            </div>
                            <div class="organizer-detail">
                                <i class="fas fa-phone"></i>
                                <span><?php echo htmlspecialchars($row['number']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    }
                    ?>
                </div>
            </div>

            <!-- Form Section - Shows when adding organizer -->
            <div class="form-section" id="formSection">
                <h3>Add Organizer</h3>
                
                <form method="POST" id="organizerForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Organizer Name <span class="required">*</span></label>
                            <input type="text" name="oname" id="oname" placeholder="Enter Organizer name">
                            <span class="error-msg" id="oname-error"></span>
                        </div>

                        <div class="form-group">
                            <label>Organizer Number <span class="required">*</span></label>
                            <input type="text" name="onumber" id="onumber" placeholder="Enter Organizer number">
                            <span class="error-msg" id="onumber-error"></span>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Organizer Email <span class="required">*</span></label>
                            <input type="email" name="oemail" id="oemail" placeholder="Enter Organizer email">
                            <span class="error-msg" id="oemail-error"></span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-cancel" id="cancelBtn" onclick="window.location.href='<?php if(isset($season_id)){ echo 'organizers-list.php?id='.$season_id;}else{ echo 'organizers-list.php';}?>'">Cancel</button>
                        <button type="submit" name="save_organizer" class="btn-save">Save</button>
                    </div>
                </form>
            </div>
            
            <div class="footer-actions" id="footerActions">
                <button type="button" class="btn-outline" onclick="window.location.href='<?php if(isset($season_id)){ echo 'add_season.php?id='.$season_id;}else{ echo 'add_season.php';}?>'">Previous</button>
                <button type="button" class="btn-primary" onclick="window.location.href='<?php if(isset($season_id)){ echo 'sponsor_details.php?id='.$season_id;}else{ echo 'sponsor_details.php';}?>'">SKIP</button>
            </div>
        </div>
    </div>

    <script>                

        $(document).ready(function() {
            
            // Check if there are organizers
            var hasOrganizers = <?php echo $hasOrganizers ? 'true' : 'false'; ?>;
            
            console.log('Has organizers:', hasOrganizers);

            // Show form when clicking add button
            $('#addOrganizerBtn, #addMoreBtn').on('click', function() {
                $('#emptyState').hide();
                $('#organizerList').hide();
                $('#formSection').addClass('show');
                $('#footerActions').hide();
            });

            // Hide form when clicking cancel
            $('#cancelBtn').on('click', function() {
                $('#formSection').removeClass('show');
                $('#organizerForm')[0].reset();
                $('.invalid').removeClass('invalid');
                $('.error-msg').removeClass('show');
                
                if(hasOrganizers) {
                    $('#organizerList').addClass('show');
                } else {
                    $('#emptyState').show();
                }
                $('#footerActions').show();
            });

            // LIVE VALIDATION - Organizer Name
            $('#oname').on('input', function() {
                var val = $(this).val().trim();
                if (val.length == 0) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#oname-error').text('Organizer name is required').addClass('show');
                } else if (val.length < 3) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#oname-error').text('Minimum 3 characters required').addClass('show');
                } else {
                    $(this).removeClass('invalid').addClass('valid');
                    $('#oname-error').removeClass('show');
                }
            });

            // LIVE VALIDATION - Organizer Number
            $('#onumber').on('input', function() {
                var val = $(this).val().trim();
                if (val.length == 0) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#onumber-error').text('Organizer number is required').addClass('show');
                } else if (!/^[0-9]{10}$/.test(val)) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#onumber-error').text('Enter valid 10 digit number').addClass('show');
                } else {
                    $(this).removeClass('invalid').addClass('valid');
                    $('#onumber-error').removeClass('show');
                }
            });

            // LIVE VALIDATION - Organizer Email
            $('#oemail').on('input', function() {
                var val = $(this).val().trim();
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (val.length == 0) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#oemail-error').text('Email is required').addClass('show');
                } else if (!emailPattern.test(val)) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#oemail-error').text('Enter valid email address').addClass('show');
                } else {
                    $(this).removeClass('invalid').addClass('valid');
                    $('#oemail-error').removeClass('show');
                }
            });

            // SUBMIT VALIDATION
            $('#organizerForm').on('submit', function(e) {
                var isValid = true;

                // Validate Name
                var onameVal = $('#oname').val().trim();
                if (onameVal.length == 0) {
                    $('#oname').addClass('invalid');
                    $('#oname-error').text('Organizer name is required').addClass('show');
                    isValid = false;
                } else if (onameVal.length < 3) {
                    $('#oname').addClass('invalid');
                    $('#oname-error').text('Minimum 3 characters required').addClass('show');
                    isValid = false;
                }

                // Validate Number
                var onumberVal = $('#onumber').val().trim();
                if (onumberVal.length == 0) {
                    $('#onumber').addClass('invalid');
                    $('#onumber-error').text('Organizer number is required').addClass('show');
                    isValid = false;
                } else if (!/^[0-9]{10}$/.test(onumberVal)) {
                    $('#onumber').addClass('invalid');
                    $('#onumber-error').text('Enter valid 10 digit number').addClass('show');
                    isValid = false;
                }

                // Validate Email
                var oemailVal = $('#oemail').val().trim();
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (oemailVal.length == 0) {
                    $('#oemail').addClass('invalid');
                    $('#oemail-error').text('Email is required').addClass('show');
                    isValid = false;
                } else if (!emailPattern.test(oemailVal)) {
                    $('#oemail').addClass('invalid');
                    $('#oemail-error').text('Enter valid email address').addClass('show');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: $('.invalid').first().offset().top - 100
                    }, 500);
                }
            });

        });        
    </script>
</body>
</html>