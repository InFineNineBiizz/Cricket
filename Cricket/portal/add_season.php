<?php
    session_start();
    include "connection.php";
    $tid=$sname=$logo=$cname=$gname=$sdate=$edate=$btype=$gtype=$mtype=$over=$img=$tour_id="";
    $maxDate = date('Y-m-d', strtotime('+1 year'));

    if(isset($_GET['tid']))
    {   
        $tour_id=$_GET['tid'];
        $sql="select * from tournaments where tid='".$tour_id."'";
        $resq=mysqli_query($conn,$sql);
    }
    else
    {
        $sql="select * from tournaments";
        $resq=mysqli_query($conn,$sql);
    }

    if(isset($_GET['id']))
    {
        $id=$_GET['id'];
        $str="select * from seasons where id=".$id."";
        $res=mysqli_query($conn,$str);
        $row=mysqli_fetch_array($res);
        $tid=$row['tid'];
        $sname=$row['name'];
        $logo=$row['logo'];
        $cname=$row['cname'];
        $gname=$row['gname'];
        $sdate=$row['sdate'];
        $edate=$row['edate'];
        $btype=$row['btype'];
        $gtype=$row['gtype'];
        $mtype=$row['mtype'];
        $over=$row['overs'];        
    }    

    if(isset($_POST['btn']))
    {
        // Check if new logo is uploaded
        if(!empty($_FILES['logo']['name']))
        {
            move_uploaded_file($_FILES['logo']['tmp_name'],"../assets/images/".$_FILES['logo']['name']);
            $img=$_FILES['logo']['name']; 
        }
        else
        {
            // Use existing logo if no new file is uploaded
            $img = isset($_POST['existing_logo']) ? $_POST['existing_logo'] : '';
        }

        $mt=$_POST['mtype'];
        if($mt == "Limited Overs")
        {
            $ov=$_POST['overs'];          
        }
        else
        {            
            $ov="NULL";            
        }

        if(empty($_GET['id']))
        {
            if(isset($_GET['tid']))
            {                
                $str="insert into seasons(name,tid,cname,gname,sdate,edate,btype,gtype,mtype,overs,logo) 
                values('".$_POST['sname']."','".$_POST['tname']."','".$_POST['cname']."','".$_POST['gname']."','".$_POST['sdate']."','".$_POST['edate']."','".$_POST['btype']."','".$_POST['gtype']."','".$_POST['mtype']."','".$ov."','".$img."')";            
                $res=mysqli_query($conn,$str);
                $_SESSION['add_season']=mysqli_insert_id($conn);
                header("location:organizers-list.php");
            }
            else
            {
                $str="insert into seasons(name,tid,cname,gname,sdate,edate,btype,gtype,mtype,overs,logo) 
                values('".$_POST['sname']."','".$_POST['tname']."','".$_POST['cname']."','".$_POST['gname']."','".$_POST['sdate']."','".$_POST['edate']."','".$_POST['btype']."','".$_POST['gtype']."','".$_POST['mtype']."','".$ov."','".$img."')";            
                $res=mysqli_query($conn,$str);
                $_SESSION['add_season']=mysqli_insert_id($conn);
                header("location:organizers-list.php");
            }
        }
        else
        {
            $str="update seasons set name='".$_POST['sname']."',tid='".$_POST['tname']."',cname='".$_POST['cname']."',
            gname='".$_POST['gname']."',sdate='".$_POST['sdate']."',edate='".$_POST['edate']."',
            btype='".$_POST['btype']."',gtype='".$_POST['gtype']."',mtype='".$_POST['mtype']."',overs='".$ov."',logo='".$img."' where id='".$id."'";
            
            $res=mysqli_query($conn,$str);
            header("location:organizers-list.php?id=$id");
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Season | <?php echo $title_name;?></title>
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

        .form-grid{
            display:grid;
            grid-template-columns: repeat(2,1fr);
            gap:20px 25px;
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

        .form-group input::placeholder{
            color:#9ca3af;
            font-weight:400;
        }

        .form-group select option:first-child{
            color:#9ca3af;
        }
 
        .form-group select:valid{
            color:#374151;
            font-weight:500;
        }

        .form-group input:focus,
        .form-group select:focus{
            color:#374151;
            font-weight:500;
            border-color: var(--primary);
        }

        input:-webkit-autofill{
            -webkit-text-fill-color:#374151 !important;
            transition: background-color 9999s ease-in-out 0s;
        }

        /* Valid state */
        .form-group input.valid,
        .form-group select.valid{
            border-color: var(--success);
        }

        /* Invalid state */
        .form-group input.invalid,
        .form-group select.invalid{
            border-color: var(--error);
            background: #fef2f2;
        }

        /* Error message */
        .error-msg{
            color: var(--error);
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .error-msg.show{
            display: block;
        }

        .form-group.new-row{
            grid-column: 1 / 2;
        }

        .upload-box{
            height:120px;
            border:2px dashed #f59e0b;
            border-radius:12px;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:8px;
            cursor:pointer;
            background:#fff;
            transition:all .25s ease;
        }

        .upload-box i{
            font-size:28px;
            color:#f59e0b;
        }

        .upload-box p{
            font-size:14px;
            color:#374151;
        }

        .upload-box:hover{
            background:#fff7ed;
            border-color:#f59e0b;
        }

        .upload-box:hover i{
            color:#d97706;
        }

        .upload-box:hover p{
            color:#374151;
        }

        .upload-box.invalid{
            border-color: var(--error);
            background: #fef2f2;
        }

        .preview-box{
            display:none;
            border:2px dashed #d1d5db;
            border-radius:12px;
            padding:15px;
            text-align:center;
            background:#fff;
        }

        .preview-box img{
            max-width:100%;
            max-height:220px;
            border-radius:8px;
            margin-bottom:15px;
        }

        .preview-actions{
            display:flex;
            justify-content:center;
            gap:12px;
        }

        .btn-remove{
            background:#ef4444;
            color:#fff;
            border:none;
            padding:10px 18px;
            border-radius:6px;
            font-weight:600;
            cursor:pointer;
        }

        .btn-change{
            background:#2563eb;
            color:#fff;
            border:none;
            padding:10px 18px;
            border-radius:6px;
            font-weight:600;
            cursor:pointer;
        }

        .btn-remove:hover{ background:#dc2626; }
        .btn-change:hover{ background:#1d4ed8; }

        .form-actions{
            display:flex;
            justify-content:flex-end;
            margin-top:30px;
        }

        .btn-primary{
            background:#f59e0b;
            border:none;
            color:#fff;
            padding:12px 28px;
            font-size:15px;
            border-radius:10px;
            cursor:pointer;
            font-weight:600;
        }

        @media(max-width:900px){
            .form-grid{
                grid-template-columns:1fr;
            }
            .page-wrapper{
                margin-left:0;
            }
        }

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

        .step.active .circle{
            background:#f59e0b;
            border-color:#f59e0b;
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

        .d-none {
            display: none !important;
        }

    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <?php include 'topbar.php'; ?>

    <br><br><br>
    <div class="page-wrapper">
        
        <div class="page-header">
            <h4>Season / Create</h4>
        </div>

        <div class="stepper">
            <div class="step active">
                <div class="circle">1</div>
                <span>Season Detail</span>
                <div class="line"></div>
            </div>

            <div class="step">
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

        <div class="card">
            <h3>Add Season</h3><br>
            <form method="POST" enctype="multipart/form-data" id="seasonForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Season Name <span class="required">*</span></label>
                        <input type="text" name="sname" id="sname" placeholder="Enter Season Name" value="<?php echo $sname;?>">
                        <span class="error-msg" id="sname-error"></span>
                    </div>

                    <div class="form-group">
                        <label>Tournaments <span class="required">*</span></label>
                        <select name="tname" id="tname">
                            <option value="" selected disabled>Select Tournament Name</option>
                            <?php while($row=mysqli_fetch_assoc($resq)){?>
                                <option value="<?php echo $row['tid'];?>" <?php if($tid==$row['tid']){ echo 'selected';}else if($tour_id==$row['tid']){ echo 'selected';}?>><?php echo $row['name'];?></option>
                            <?php }?>
                        </select>
                        <span class="error-msg" id="tname-error"></span>
                    </div>

                    <div class="form-group">
                        <label>City Name <span class="required">*</span></label>
                        <input type="text" name="cname" id="cname" placeholder="Enter City Name" value="<?php echo $cname;?>">
                        <span class="error-msg" id="cname-error"></span>
                    </div>

                    <div class="form-group">
                        <label>Ground Name <span class="required">*</span></label>
                        <input type="text" name="gname" id="gname" placeholder="Enter Ground Name" value="<?php echo $gname;?>">
                        <span class="error-msg" id="gname-error"></span>
                    </div>

                    <div class="form-group">
                        <label>Start Date <span class="required">*</span></label>
                        <input type="date" name="sdate" id="sdate" min="<?php echo date('Y-m-d'); ?>" max="<?php echo $maxDate;?>" value="<?php echo $sdate;?>">
                        <span class="error-msg" id="sdate-error"></span>
                    </div>

                    <div class="form-group">
                        <label>End Date <span class="required">*</span></label>
                        <input type="date" name="edate" id="edate" min="<?php echo date('Y-m-d'); ?>" max="<?php echo $maxDate;?>" value="<?php echo $edate;?>">
                        <span class="error-msg" id="edate-error"></span>
                    </div>

                    <div class="form-group">
                        <label>Ball Type <span class="required">*</span></label>
                        <select name="btype" id="btype">
                            <option value="" selected disabled>Select Ball Type</option>
                            <option value="soft tennis" <?php if($btype=="soft tennis"){echo 'selected';}?>>Soft Tennis</option>
                            <option value="hard tennis" <?php if($btype=="hard tennis"){echo 'selected';}?>>Hard Tennis</option>
                            <option value="leather" <?php if($btype=="leather"){echo 'selected';}?>>Leather</option>
                            <option value="plastic" <?php if($btype=="plastic"){echo 'selected';}?>>Plastic</option>
                            <option value="other" <?php if($btype=="other"){echo 'selected';}?>>Other</option>
                        </select>
                        <span class="error-msg" id="btype-error"></span>
                    </div>

                    <div class="form-group">
                        <label>Ground Type <span class="required">*</span></label>
                        <select name="gtype" id="gtype">
                            <option value="" selected disabled>Select Ground Type</option>
                            <option value="ground" <?php if($gtype=="ground"){echo 'selected';}?>>Ground</option>
                            <option value="box or turf" <?php if($gtype=="box or turf"){echo 'selected';}?>>Box Or Turf</option>
                            <option value="gully" <?php if($gtype=="gully"){echo 'selected';}?>>Gully</option>                                                    
                            <option value="other" <?php if($gtype=="other"){echo 'selected';}?>>Other</option>                                
                        </select>
                        <span class="error-msg" id="gtype-error"></span>
                    </div>

                    <div class="form-group">
                        <label>Match Type <span class="required">*</span></label>
                        <select name="mtype" id="mtype">
                            <option value="" selected disabled>Select Match Type</option>
                            <option value="T10" <?php if($mtype=="T10"){echo 'selected';}?>>T10</option>
                            <option value="T20" <?php if($mtype=="T20"){echo 'selected';}?>>T20</option>
                            <option value="OneDay 50" <?php if($mtype=="OneDay 50"){echo 'selected';}?>>OneDay 50</option>
                            <option value="Limited Overs" <?php if($mtype=="Limited Overs"){echo 'selected';}?>>Limited Overs</option>                            
                        </select>
                        <span class="error-msg" id="mtype-error"></span>
                    </div>

                    <div class="form-group <?php if($mtype!='Limited Overs'){ echo 'd-none'; }?>" id="over">
                        <label>Overs <span class="required">*</span></label>
                        <input type="text" name="overs" id="overs" placeholder="E.g.,25" value="<?php echo $over;?>" step="1">
                        <span class="error-msg" id="overs-error"></span>
                    </div>

                    <div class="form-group new-row">
                        <label>Upload Logo <span class="required">*</span></label>
                        <div class="upload-box" id="uploadBox" onclick="triggerFile()" <?php if(!empty($logo)){ echo 'style="display:none;"'; }?>>
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Drop logo here or <b>Browse</b></p>
                        </div>
                        <input type="file" name="logo" id="logoInput" accept="image/*" hidden onchange="previewImage(event)">

                        <div class="preview-box" id="previewBox" <?php if(!empty($logo)){ echo 'style="display:block;"'; }?>>
                            <img id="previewImg" src="<?php if(!empty($logo)){ echo '../assets/images/'.$logo; }?>" alt="Logo Preview">
                            <div class="preview-actions">
                                <button type="button" class="btn-remove" onclick="removeImage()">REMOVE</button>
                                <button type="button" class="btn-change" onclick="triggerFile()">CHANGE</button>
                            </div>
                        </div>
                        <span class="error-msg" id="logo-error"></span>
                        <?php if(!empty($logo)){ ?>
                            <input type="hidden" name="existing_logo" id="existing_logo" value="<?php echo $logo; ?>">
                        <?php } ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="btn" class="btn-primary">Next</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            
            // LIVE VALIDATION - Season Name
            $('#sname').on('input', function() {
                var val = $(this).val().trim();
                if (val.length == 0) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#sname-error').text('Season name is required').addClass('show');
                } else if (val.length < 3) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#sname-error').text('Minimum 3 characters required').addClass('show');
                } else {
                    $(this).removeClass('invalid').addClass('valid');
                    $('#sname-error').removeClass('show');
                }
            });

            // LIVE VALIDATION - City Name
            $('#cname').on('input', function() {
                var val = $(this).val().trim();
                if (val.length == 0) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#cname-error').text('City name is required').addClass('show');
                } else if (val.length < 2) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#cname-error').text('Minimum 2 characters required').addClass('show');
                } else {
                    $(this).removeClass('invalid').addClass('valid');
                    $('#cname-error').removeClass('show');
                }
            });

            // LIVE VALIDATION - Ground Name
            $('#gname').on('input', function() {
                var val = $(this).val().trim();
                if (val.length == 0) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#gname-error').text('Ground name is required').addClass('show');
                } else if (val.length < 3) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#gname-error').text('Minimum 3 characters required').addClass('show');
                } else {
                    $(this).removeClass('invalid').addClass('valid');
                    $('#gname-error').removeClass('show');
                }
            });

            // LIVE VALIDATION - Tournament
            $('#tname').on('change', function() {
                if ($(this).val() != '' && $(this).val() != null) {
                    $(this).removeClass('invalid').addClass('valid');
                    $('#tname-error').removeClass('show');
                } else {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#tname-error').text('Please select a tournament').addClass('show');
                }
            });

            // LIVE VALIDATION - Ball Type
            $('#btype').on('change', function() {
                if ($(this).val() != '' && $(this).val() != null) {
                    $(this).removeClass('invalid').addClass('valid');
                    $('#btype-error').removeClass('show');
                } else {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#btype-error').text('Please select ball type').addClass('show');
                }
            });

            // LIVE VALIDATION - Ground Type
            $('#gtype').on('change', function() {
                if ($(this).val() != '' && $(this).val() != null) {
                    $(this).removeClass('invalid').addClass('valid');
                    $('#gtype-error').removeClass('show');
                } else {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#gtype-error').text('Please select ground type').addClass('show');
                }
            });

            // LIVE VALIDATION - Match Type
            $('#mtype').on('change', function() {
                if ($(this).val() != '' && $(this).val() != null) {
                    $(this).removeClass('invalid').addClass('valid');
                    $('#mtype-error').removeClass('show');
                } else {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#mtype-error').text('Please select match type').addClass('show');
                }
                
            });

            // LIVE VALIDATION - Overs
            $('#overs').on('input', function () {
                let val = $(this).val().trim();

                // Check empty
                if (val === '') {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#overs-error').text('Overs is required').addClass('show');
                    return;
                }

                // Check integer only (no decimals)
                if (!Number.isInteger(Number(val))) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#overs-error').text('Only whole numbers are allowed').addClass('show');
                    return;
                }

                // Range check
                if (val < 1 || val > 50) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#overs-error').text('Overs must be between 1 and 50').addClass('show');
                    return;
                }

                // Valid
                $(this).removeClass('invalid').addClass('valid');
                $('#overs-error').removeClass('show');
            });


            // LIVE VALIDATION - Start Date
            $('#sdate').on('change', function() {
                var val = $(this).val();
                if (val == '') {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#sdate-error').text('Start date is required').addClass('show');
                } else {
                    $(this).removeClass('invalid').addClass('valid');
                    $('#sdate-error').removeClass('show');
                    
                    // Re-validate end date
                    if ($('#edate').val() != '') {
                        $('#edate').trigger('change');
                    }
                }
            });

            // LIVE VALIDATION - End Date
            $('#edate').on('change', function() {
                var val = $(this).val();
                var startDate = $('#sdate').val();
                
                if (val == '') {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#edate-error').text('End date is required').addClass('show');
                } else if (startDate != '' && val < startDate) {
                    $(this).removeClass('valid').addClass('invalid');
                    $('#edate-error').text('End date must be after start date').addClass('show');
                } else {
                    $(this).removeClass('invalid').addClass('valid');
                    $('#edate-error').removeClass('show');
                }
            });

            // SUBMIT VALIDATION - Check all fields when clicking Next
            $('#seasonForm').on('submit', function(e) {
                var isValid = true;

                // Validate Season Name
                var snameVal = $('#sname').val().trim();
                if (snameVal.length == 0) {
                    $('#sname').addClass('invalid');
                    $('#sname-error').text('Season name is required').addClass('show');
                    isValid = false;
                } else if (snameVal.length < 3) {
                    $('#sname').addClass('invalid');
                    $('#sname-error').text('Season name must be at least 3 characters').addClass('show');
                    isValid = false;
                }

                // Validate Tournament
                if ($('#tname').val() == '' || $('#tname').val() == null) {
                    $('#tname').addClass('invalid');
                    $('#tname-error').text('Please select a tournament').addClass('show');
                    isValid = false;
                }

                // Validate City Name
                var cnameVal = $('#cname').val().trim();
                if (cnameVal.length == 0) {
                    $('#cname').addClass('invalid');
                    $('#cname-error').text('City name is required').addClass('show');
                    isValid = false;
                } else if (cnameVal.length < 2) {
                    $('#cname').addClass('invalid');
                    $('#cname-error').text('City name must be at least 2 characters').addClass('show');
                    isValid = false;
                }

                // Validate Ground Name
                var gnameVal = $('#gname').val().trim();
                if (gnameVal.length == 0) {
                    $('#gname').addClass('invalid');
                    $('#gname-error').text('Ground name is required').addClass('show');
                    isValid = false;
                } else if (gnameVal.length < 3) {
                    $('#gname').addClass('invalid');
                    $('#gname-error').text('Ground name must be at least 3 characters').addClass('show');
                    isValid = false;
                }

                // Validate Start Date
                if ($('#sdate').val() == '') {
                    $('#sdate').addClass('invalid');
                    $('#sdate-error').text('Start date is required').addClass('show');
                    isValid = false;
                }

                // Validate End Date
                var edateVal = $('#edate').val();
                var sdateVal = $('#sdate').val();
                if (edateVal == '') {
                    $('#edate').addClass('invalid');
                    $('#edate-error').text('End date is required').addClass('show');
                    isValid = false;
                } else if (sdateVal != '' && edateVal < sdateVal) {
                    $('#edate').addClass('invalid');
                    $('#edate-error').text('End date must be after start date').addClass('show');
                    isValid = false;
                }

                // Validate Ball Type
                if ($('#btype').val() == '' || $('#btype').val() == null) {
                    $('#btype').addClass('invalid');
                    $('#btype-error').text('Please select ball type').addClass('show');
                    isValid = false;
                }

                // Validate Ground Type
                if ($('#gtype').val() == '' || $('#gtype').val() == null) {
                    $('#gtype').addClass('invalid');
                    $('#gtype-error').text('Please select ground type').addClass('show');
                    isValid = false;
                }

                // Validate Match Type
                if ($('#mtype').val() == '' || $('#mtype').val() == null) {
                    $('#mtype').addClass('invalid');
                    $('#mtype-error').text('Please select match type').addClass('show');
                    isValid = false;
                }

                // Validate Overs if visible
                if (!$('#over').hasClass('d-none')) {
                    let oversVal = $('#overs').val().trim();

                    if (oversVal === '') {
                        $('#overs').addClass('invalid');
                        $('#overs-error').text('Overs is required for Limited Overs match').addClass('show');
                        isValid = false;
                    }
                    else if (!Number.isInteger(Number(oversVal))) {
                        $('#overs').addClass('invalid');
                        $('#overs-error').text('Only whole numbers are allowed').addClass('show');
                        isValid = false;
                    }
                    else if (oversVal < 1 || oversVal > 50) {
                        $('#overs').addClass('invalid');
                        $('#overs-error').text('Overs must be between 1 and 50').addClass('show');
                        isValid = false;
                    }
                }

                // Validate Logo - Check if new file is uploaded or existing logo exists
                var hasNewFile = $('#logoInput')[0].files.length > 0;
                var hasExistingLogo = $('#existing_logo').length > 0 && $('#existing_logo').val() != '';
                
                if (!hasNewFile && !hasExistingLogo) {
                    $('#uploadBox').addClass('invalid');
                    $('#logo-error').text('Logo is required').addClass('show');
                    isValid = false;
                }

                // Prevent form submission if invalid
                if (!isValid) {
                    e.preventDefault();
                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.invalid').first().offset().top - 100
                    }, 500);
                }
            });

        });
    </script>
    <script>
        function triggerFile(){
            document.getElementById('logoInput').click();
        }

        function previewImage(event){
            const file = event.target.files[0];
            if(!file) return;

            const reader = new FileReader();
            reader.onload = function(){
                document.getElementById('previewImg').src = reader.result;
                document.getElementById('uploadBox').style.display = 'none';
                document.getElementById('previewBox').style.display = 'block';
                document.getElementById('uploadBox').classList.remove('invalid');
                document.getElementById('logo-error').classList.remove('show');
            };
            reader.readAsDataURL(file);
        }

        function removeImage(){
            document.getElementById('logoInput').value = "";
            document.getElementById('previewImg').src = "";
            document.getElementById('previewBox').style.display = 'none';
            document.getElementById('uploadBox').style.display = 'flex';
            
            // Remove existing logo hidden input if it exists
            var existingLogoInput = document.getElementById('existing_logo');
            if(existingLogoInput) {
                existingLogoInput.remove();
            }
        }

        function updateOversVisibility() {
            var selectedValue = $('#mtype').val() || '';

            // Show overs for 'Limited Overs' OR where the value contains digits (T10, T20, OneDay 50)
            var showOvers = selectedValue === 'Limited Overs';

            if (showOvers) {
                $('#over').removeClass('d-none');
                $('#overs').attr('required', true);
            } else {
                $('#over').addClass('d-none');
                $('#overs').removeAttr('required').val('');
            }
        }

        // run on change (and on load for preselected values)
        $('#mtype').on('change', updateOversVisibility);
        updateOversVisibility();
    </script>
</body>
</html>