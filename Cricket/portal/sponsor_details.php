<?php
    session_start();
    include "connection.php";
    
    // Handle form submission FIRST
    if(isset($_POST['save_sponsor'])) {
        $stype = mysqli_real_escape_string($conn, $_POST['stype']);
        $sname = mysqli_real_escape_string($conn, $_POST['sname']);
        $stitle = mysqli_real_escape_string($conn, $_POST['stitle']);
        $snumber = mysqli_real_escape_string($conn, $_POST['snumber']);
        $semail = mysqli_real_escape_string($conn, $_POST['semail']);
        
        // Handle file upload
        $logo = '';
        if(isset($_FILES['slogo']) && $_FILES['slogo']['error'] == 0) {
            $target_dir = "../uploads/sponsors/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $logo = time() . '_' . basename($_FILES["slogo"]["name"]);
            $target_file = $target_dir . $logo;
            move_uploaded_file($_FILES["slogo"]["tmp_name"], $target_file);
        }
        
        $insert = "INSERT INTO sponsors(type, name, title, email, number, logo) VALUES('$stype', '$sname', '$stitle', '$semail', '$snumber', '$logo')";
        $res = mysqli_query($conn, $insert);
        
        if($res)
        {
            $sponsor_id = mysqli_insert_id($conn);
            
            // Determine which season_id to use
            $target_season_id = null;
            if(isset($_GET['id'])) {
                $target_season_id = $_GET['id'];
            } else if(isset($_SESSION['add_season'])) {
                $target_season_id = $_SESSION['add_season'];
            }
            
            if($target_season_id) {
                $ins = "INSERT INTO season_sponsors(sponsor_id, season_id) VALUES('".$sponsor_id."', '".$target_season_id."')";
                $resq = mysqli_query($conn, $ins);
                
                // Redirect based on how we got the season_id
                if(isset($_GET['id'])) {
                    header("Location: sponsor_details.php?id=".$target_season_id);
                } else {
                    header("Location: sponsor_details.php");
                }
                exit();
            }
        }
    }
    
    // Initialize variables
    $result = null;
    $season_id = null;
    $query_executed = false;
    
    // Try to get season_id from GET or SESSION
    if(isset($_GET['id'])) {
        $season_id = $_GET['id'];
        $sql = "SELECT s.*, ss.* FROM sponsors s 
                INNER JOIN season_sponsors ss ON ss.sponsor_id = s.id 
                WHERE ss.season_id = '".$season_id."' 
                ORDER BY ss.sponsor_id";
        $result = mysqli_query($conn, $sql);
        $query_executed = true;
            
    }
    else if(isset($_SESSION['add_season'])) {
        $season_id = $_SESSION['add_season'];
        $sql = "SELECT s.*, ss.* FROM sponsors s 
                INNER JOIN season_sponsors ss ON ss.sponsor_id = s.id 
                WHERE ss.season_id = '".$season_id."' 
                ORDER BY ss.sponsor_id";
        $result = mysqli_query($conn, $sql);
        $query_executed = true;
                
    }
    
    // If no season_id found at all
    if(!$query_executed) {
        echo "<!-- WARNING: No season_id found in GET or SESSION! -->";
        // Create empty result set
        $result = mysqli_query($conn, "SELECT * FROM sponsors WHERE 1=0");
    }

    $count = 0;
    if($result && mysqli_num_rows($result) > 0) {
        $count = mysqli_num_rows($result);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sponsor Details | CrickFolio</title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">        
    <link rel="stylesheet" href="../assets/css/home-style.css">
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            margin: 0 auto 20px;
            position: relative;
            z-index: 10;
        }

        .add-btn-circle:hover{
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
            background: #f59e0b;
        }

        .add-btn-circle:active{
            transform: scale(0.95);
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

        .form-group input,
        .form-group select{
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

        .form-group select{
            cursor: pointer;
        }

        .form-group input::placeholder{
            color:#9ca3af;
            font-weight:400;
        }

        .form-group input:focus,
        .form-group select:focus{
            color:#374151;
            font-weight:500;
            border-color: var(--primary);
        }

        .form-group input.valid,
        .form-group select.valid{
            border-color: var(--success);
        }

        .form-group input.invalid,
        .form-group select.invalid{
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

        /* File Upload Styles */
        .file-upload-area{
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: #fafafa;
        }

        .file-upload-area:hover{
            border-color: #f59e0b;
            background: #f0f4ff;
        }

        .upload-icon{
            font-size: 48px;
            color: #9ca3af;
            margin-bottom: 15px;
        }

        .upload-text{
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .upload-subtext{
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 15px;
        }

        .btn-browse{
            background: #f59e0b;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-browse:hover{
            background: #f59e0b;
        }

        .file-preview{
            position: relative;
            display: inline-block;
            margin-top: 15px;
        }

        .file-preview img{
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }

        .btn-remove-file{
            position: absolute;
            top: -10px;
            right: -10px;
            background: #ef4444;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-remove-file:hover{
            background: #dc2626;
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
            background: #f59e0b;
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
            background: #f59e0b;
        }

        /* Sponsor List */
        .sponsor-list{
            display: none;
        }

        .sponsor-list.show{
            display: block;
        }

        /* Grid container for sponsor cards */
        .sponsor-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .sponsor-item{
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 20px;
            position: relative;
            transition: all 0.3s;
            height: 100%;
        }

        .sponsor-item:hover{
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
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

        .sponsor-info h4{
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
            padding-right: 100px;
        }

        .sponsor-detail{
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
            color: #6b7280;
            font-size: 14px;
        }

        .sponsor-detail:last-child{
            margin-bottom: 0;
        }

        .sponsor-detail i{
            color: #9ca3af;
            font-size: 18px;
            width: 20px;
        }

        @media(max-width:900px){
            .form-grid{
                grid-template-columns:1fr;
            }
            .page-wrapper{
                margin-left:0;
            }
            
            /* Make sponsor cards stack on mobile */
            .sponsor-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <br><br><br>
    <div class="page-wrapper">
        
        <div class="page-header">
            <h4>Season / Sponsor</h4>
        </div>

        <!-- Stepper -->
        <div class="stepper">
            <div class="step completed">
                <div class="circle"><i class="fas fa-check"></i></div>
                <span>Season Detail</span>
                <div class="line"></div>
            </div>

            <div class="step completed">
                <div class="circle"><i class="fas fa-check"></i></div>
                <span>Organizer Details</span>
                <div class="line"></div>
            </div>

            <div class="step active">
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
            
            <!-- Empty State - Shows when no sponsor -->
            <div class="empty-state" id="emptyState" style="<?php echo ($count == 0) ? 'display:block;' : 'display:none;'; ?>">
                <div class="add-btn-circle" id="addSponsorBtn">
                    <i class="fas fa-plus"></i>
                </div>
                <h3>Add New Sponsor</h3>
                <p>You have no Sponsor at the moment!</p>
            </div>

            <!-- Sponsor List - Shows existing sponsors -->
            <div class="sponsor-list" id="sponsorList" style="<?php echo ($count > 0) ? 'display:block;' : 'display:none;'; ?>">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Sponsors</h3>
                    <button type="button" class="btn-save" id="addMoreBtn">
                        <i class="fas fa-plus"></i> Add Sponsor
                    </button>
                </div>

                <!-- Grid container for cards -->
                <div class="sponsor-grid">
                    <?php 
                    // Make sure $result exists and has rows
                    if($result && mysqli_num_rows($result) > 0) {
                        // Reset pointer to beginning just in case
                        mysqli_data_seek($result, 0);
                        
                        while($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <div class="sponsor-item">
                        <div class="action-buttons">
                            <button class="btn-edit" onclick="editSponsor(<?php echo $row['id']; ?>)">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn-delete-icon" onclick="deleteSponsor(<?php echo $row['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                        <div class="sponsor-info">
                            <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                            <?php if(isset($row['type']) && !empty($row['type'])): ?>
                            <div class="sponsor-detail">
                                <i class="fas fa-tag"></i>
                                <span><?php echo htmlspecialchars($row['type']); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="sponsor-detail">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($row['email']); ?></span>
                            </div>
                            <div class="sponsor-detail">
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

            <!-- Form Section - Shows when adding sponsor -->
            <div class="form-section" id="formSection">
                <h3>Add Sponsor</h3>
                
                <form method="POST" id="sponsorForm" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Sponsor Type <span class="required">*</span></label>
                            <select name="stype" id="stype">
                                <option value="" selected disabled>Select Sponsor Type</option>
                                <option value="Auction Sponsor">Auction Sponsor</option>
                                <option value="Banner Sponsor">Banner Sponsor</option>
                                <option value="Man Of The Match Award">Man Of The Match Award</option>
                                <option value="Tournament Champions Trophy">Tournament Champions Trophy</option>
                                <option value="Tshirt Sponsor">Tshirt Sponsor</option>
                            </select>
                            <span class="error-msg" id="stype-error"></span>
                        </div>

                        <div class="form-group">
                            <label>Sponsor Name <span class="required">*</span></label>
                            <input type="text" name="sname" id="sname" placeholder="Enter Sponsor name">
                            <span class="error-msg" id="sname-error"></span>
                        </div>

                        <div class="form-group">
                            <label>Sponsor Title <span class="required">*</span></label>
                            <input type="text" name="stitle" id="stitle" placeholder="Enter Sponsor Title">
                            <span class="error-msg" id="stitle-error"></span>
                        </div>

                        <div class="form-group">
                            <label>Sponsor Number <span class="required">*</span></label>
                            <input type="text" name="snumber" id="snumber" placeholder="Enter Sponsor number">
                            <span class="error-msg" id="snumber-error"></span>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Sponsor Email <span class="required">*</span></label>
                            <input type="email" name="semail" id="semail" placeholder="Enter Sponsor email">
                            <span class="error-msg" id="semail-error"></span>
                        </div>

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Upload Logo</label>
                            <div class="file-upload-area" id="fileUploadArea" onclick="document.getElementById('slogo').click()">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <p class="upload-text">Drop file to upload</p>
                                <p class="upload-subtext">Upload Logo</p>
                                <button type="button" class="btn-browse">BROWSE</button>
                                <input type="file" name="slogo" id="slogo" accept="image/*" style="display: none;">
                            </div>
                            <div class="file-preview" id="filePreview" style="display: none;">
                                <img id="logoPreview" src="" alt="Logo Preview">
                                <button type="button" class="btn-remove-file" onclick="removeFile(); event.stopPropagation();">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                        <button type="submit" name="save_sponsor" class="btn-save">Save</button>
                    </div>
                </form>
            </div>

            <!-- Footer Navigation - Shows when NOT in form -->
            <div class="footer-actions" id="footerActions">
                <button type="button" class="btn-outline" onclick="window.location.href='<?php if(isset($season_id)){ echo 'organizers-list?id='.$season_id;}else{ echo 'organizers-list.php';}?>'">Previous</button>
                <button type="button" class="btn-primary" onclick="window.location.href='<?php if(isset($season_id)){ echo 'confirm.php?id='.$season_id;}else{ echo 'confirm.php';}?>'">SKIP</button>
            </div>

        </div>
    </div>

    <script>
        // Simple click handler - no jQuery needed
        window.onload = function() {
            
            // Add Sponsor Button Click
            var addBtn = document.getElementById('addSponsorBtn');
            if(addBtn) {
                addBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    showAddForm();
                });
            }
            
            // Add More Button Click  
            var addMoreBtn = document.getElementById('addMoreBtn');
            if(addMoreBtn) {
                addMoreBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    showAddForm();
                });
            }
            
            // Cancel Button
            var cancelBtn = document.getElementById('cancelBtn');
            if(cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    hideAddForm();
                });
            }
            
            // File Upload
            var fileInput = document.getElementById('slogo');
            if(fileInput) {
                fileInput.addEventListener('change', function(e) {
                    handleFileSelect(e);
                });
            }
            
            // Form Validation
            setupValidation();
        };
        
        function showAddForm() {
            document.getElementById('emptyState').style.display = 'none';
            document.getElementById('sponsorList').style.display = 'none';
            document.getElementById('formSection').style.display = 'block';
            document.getElementById('formSection').classList.add('show');
            document.getElementById('footerActions').style.display = 'none';
        }
        
        function hideAddForm() {
            var sponsorList = document.getElementById('sponsorList');
            var emptyState = document.getElementById('emptyState');
            
            document.getElementById('formSection').style.display = 'none';
            document.getElementById('formSection').classList.remove('show');
            document.getElementById('sponsorForm').reset();
            removeFile();
            
            // Clear validation
            var invalids = document.querySelectorAll('.invalid');
            invalids.forEach(function(el) {
                el.classList.remove('invalid');
            });
            var errors = document.querySelectorAll('.error-msg');
            errors.forEach(function(el) {
                el.classList.remove('show');
            });
            
            if(sponsorList.innerHTML.trim().includes('sponsor-item')) {
                sponsorList.style.display = 'block';
                emptyState.style.display = 'none';
            } else {
                sponsorList.style.display = 'none';
                emptyState.style.display = 'block';
            }
            
            document.getElementById('footerActions').style.display = 'flex';
        }
        
        function handleFileSelect(e) {
            var file = e.target.files[0];
            if (file) {
                if (file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('logoPreview').src = e.target.result;
                        document.getElementById('fileUploadArea').style.display = 'none';
                        document.getElementById('filePreview').style.display = 'inline-block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    alert('Please select an image file');
                    e.target.value = '';
                }
            }
        }
        
        function removeFile() {
            document.getElementById('slogo').value = '';
            document.getElementById('filePreview').style.display = 'none';
            document.getElementById('fileUploadArea').style.display = 'block';
        }
        
        function setupValidation() {
            var form = document.getElementById('sponsorForm');
            
            // Type validation
            document.getElementById('stype').addEventListener('change', function() {
                validateField(this, 'stype-error', 'Sponsor type is required', function(val) {
                    return val.length > 0;
                });
            });
            
            // Name validation
            document.getElementById('sname').addEventListener('input', function() {
                validateField(this, 'sname-error', 'Sponsor name is required (min 3 chars)', function(val) {
                    return val.length >= 3;
                });
            });
            
            // Title validation
            document.getElementById('stitle').addEventListener('input', function() {
                validateField(this, 'stitle-error', 'Sponsor title is required (min 2 chars)', function(val) {
                    return val.length >= 2;
                });
            });
            
            // Number validation
            document.getElementById('snumber').addEventListener('input', function() {
                validateField(this, 'snumber-error', 'Enter valid 10 digit number', function(val) {
                    return /^[0-9]{10}$/.test(val);
                });
            });
            
            // Email validation
            document.getElementById('semail').addEventListener('input', function() {
                validateField(this, 'semail-error', 'Enter valid email address', function(val) {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
                });
            });
            
            // Form submit
            form.addEventListener('submit', function(e) {
                var isValid = true;
                
                var fields = [
                    {id: 'stype', error: 'stype-error', msg: 'Sponsor type is required', check: function(v) { return v.length > 0; }},
                    {id: 'sname', error: 'sname-error', msg: 'Sponsor name required (min 3)', check: function(v) { return v.length >= 3; }},
                    {id: 'stitle', error: 'stitle-error', msg: 'Sponsor title required (min 2)', check: function(v) { return v.length >= 2; }},
                    {id: 'snumber', error: 'snumber-error', msg: 'Valid 10 digit number required', check: function(v) { return /^[0-9]{10}$/.test(v); }},
                    {id: 'semail', error: 'semail-error', msg: 'Valid email required', check: function(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }}
                ];
                
                fields.forEach(function(field) {
                    var el = document.getElementById(field.id);
                    var val = el.value.trim();
                    if (!field.check(val)) {
                        el.classList.add('invalid');
                        var errorEl = document.getElementById(field.error);
                        errorEl.textContent = field.msg;
                        errorEl.classList.add('show');
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        }
        
        function validateField(element, errorId, message, checkFunc) {
            var val = element.value.trim();
            var errorEl = document.getElementById(errorId);
            
            if (!checkFunc(val)) {
                element.classList.add('invalid');
                element.classList.remove('valid');
                errorEl.textContent = message;
                errorEl.classList.add('show');
            } else {
                element.classList.remove('invalid');
                element.classList.add('valid');
                errorEl.classList.remove('show');
            }
        }        
    </script>
</body>
</html>