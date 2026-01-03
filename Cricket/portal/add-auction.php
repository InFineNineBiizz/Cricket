<?php
    session_start();
    include "connection.php";

    $sname=$tname=$tid=$img="";
    $aname=$venue=$sdate=$cr_type=$edate=$bidamt=$min=$max=$logo=$reserve=$camt=$bprice="";
    $maxDate = date('Y-m-d', strtotime('+1 year'));

    $base_type = $_POST['base_type'] ?? "";
    $base_price = $_POST['bprice'] ?? '500';

    // ========== GROUP AJAX HANDLERS - ADD THIS BLOCK ==========
    if(isset($_POST['action']) && $_POST['action'] == 'add_group')
    {
        $response = array();
        $group_name = mysqli_real_escape_string($conn, $_POST['group_name']);
        $player_base = mysqli_real_escape_string($conn, $_POST['player_base']);
        $min_per_team = mysqli_real_escape_string($conn, $_POST['min_per_team']);
        $max_per_team = mysqli_real_escape_string($conn, $_POST['max_per_team']);
        $bid_increment = mysqli_real_escape_string($conn, $_POST['bid_increment']);
        $max_bid_player = mysqli_real_escape_string($conn, $_POST['max_bid_player']);
        $total_max_group = mysqli_real_escape_string($conn, $_POST['total_max_group']);
        
        $qr3 = "INSERT INTO group_auction(gname, bprice, minplayer, maxplayer, bidamt, mbidamt, maxbid) 
                VALUES('".$group_name."', '".$player_base."', '".$min_per_team."', '".$max_per_team."', 
                       '".$bid_increment."', '".$max_bid_player."', '".$total_max_group."')";
        
        if (mysqli_query($conn, $qr3)) { 
            if(!isset($_SESSION['auction_groups'])) {
                $_SESSION['auction_groups'] = array();
            }
            $_SESSION['auction_groups'][] = mysqli_insert_id($conn);
            $response['success'] = true;
            $response['message'] = 'Group added successfully!';
        } else {
            $response['success'] = false;
            $response['message'] = 'Error: ' . mysqli_error($conn);
        }
        echo json_encode($response);
        exit;
    }

    if(isset($_POST['action']) && $_POST['action'] == 'get_groups')
    {
        $groups = array();
        if(isset($_SESSION['auction_groups']) && !empty($_SESSION['auction_groups']))
        {
            $group_ids = implode(',', $_SESSION['auction_groups']);
            $result = mysqli_query($conn, "SELECT * FROM group_auction WHERE gid IN (".$group_ids.")");
            while($row = mysqli_fetch_assoc($result)) {
                $groups[] = $row;
            }
        }
        echo json_encode($groups);
        exit;
    }

    if(isset($_POST['action']) && $_POST['action'] == 'delete_group')
    {
        if(isset($_SESSION['auction_groups']))
        {
            $key = array_search(intval($_POST['group_id']), $_SESSION['auction_groups']);
            if($key !== false) {
                unset($_SESSION['auction_groups'][$key]);
                $_SESSION['auction_groups'] = array_values($_SESSION['auction_groups']);
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }
    // ========== END GROUP AJAX HANDLERS ==========

    if(isset($_GET['id']))
    {
        $sid=$_GET['id'];
        $str="select s.*,t.name as tname from seasons s,tournaments t 
        where s.tid=t.tid and s.id='".$sid."'";

        $res=mysqli_query($conn,$str);
        $req=mysqli_fetch_array($res);
        $sname=$req['name'];
        $tname=$req['tname'];
        $tid=$req['tid'];
        
        if(isset($_SESSION['cnf']) || isset($_SESSION['manage']))
        {                        
            $st="select * from auctions where id='".$_SESSION['add_auction']."'";
            $q=mysqli_query($conn,$st);
            $r=mysqli_fetch_array($q);
            $aname=$r['name'];
            $venue=$r['venue'];
            $sdate=$r['sdate'];
            $edate=$r['edate'];
            $cr_type=$r['credit_type'];
            $bidamt=$r['bidamt'];
            $min=$r['minplayer'];
            $max=$r['maxplayer'];
            $reserve=$r['resplayer'];
            $camt=$r['camt'];
            $bprice=$r['bprice'];
            $logo=$r['logo'];            
        }
    }

    if(isset($_POST['btn']))
    {        
        move_uploaded_file($_FILES['alogo']['tmp_name'],"../assets/images/".$_FILES['alogo']['name']);
        $img=$_FILES['alogo']['name'];
            
        if(!empty($_GET['id']))
        {       
            if(isset($_SESSION['add_auction']) || isset($_SESSION['cnf']) || isset($_SESSION['manage']))
            {
                if ($base_type === "same" && !empty($_POST['bprice'])) {
                    $bp = (int)$_POST['bprice'];
                } else {
                    $bp = "NULL"; // real SQL NULL
                }
                
                move_uploaded_file($_FILES['alogo']['tmp_name'],"../assets/images/".$_FILES['alogo']['name']);
                $img=$_FILES['alogo']['name'];  
                
                $s="update auctions set name='".$_POST['aname']."',venue='".$_POST['venue']."',sdate='".$_POST['sdate']."',edate='".$_POST['edate']."',logo='".$img."',credit_type='".$_POST['creditType']."',minplayer='".$_POST['min']."',maxplayer='".$_POST['max']."',resplayer='".$_POST['reserve']."',camt='".$_POST['camt']."',bidamt='".$_POST['bidamt']."',bprice='".$bp."'
                where id='".$_SESSION['add_auction']."'";
                $que=mysqli_query($conn,$s);
                header("location:management-details.php?id=$sid");
                exit;
            }
            else{
                if ($base_type === "same" && !empty($_POST['bprice'])) {
                    $bp = (int)$_POST['bprice'];
                } else {
                    $bp = "NULL"; // real SQL NULL
                }

                $sdate = date('Y-m-d H:i:s', strtotime($_POST['sdate']));
                $edate = date('Y-m-d H:i:s', strtotime($_POST['edate']));

                $str="insert into auctions(name,tour_id,sea_id,venue,sdate,edate,logo,credit_type,minplayer,maxplayer,resplayer,camt,bidamt,bprice) 
                values('".$_POST['aname']."','".$_POST['tname']."','".$_POST['sname']."','".$_POST['venue']."','".$sdate."','".$edate."','".$img."','".$_POST['creditType']."','".$_POST['min']."','".$_POST['max']."','".$_POST['reserve']."','".$_POST['camt']."','".$_POST['bidamt']."',".$bp.")";

                $response=mysqli_query($conn,$str);
                $_SESSION['add_auction'] = mysqli_insert_id($conn);
    
                // ========== ADD GROUP LINKING ==========
                if(isset($_SESSION['auction_groups']) && !empty($_SESSION['auction_groups']))
                {
                    mysqli_query($conn, "DELETE FROM grp_auc WHERE aid='".$_SESSION['add_auction']."'");
                    foreach($_SESSION['auction_groups'] as $gid) {
                        mysqli_query($conn, "INSERT INTO grp_auc(aid, gid) VALUES('".$_SESSION['add_auction']."', '".$gid."')");
                    }
                    unset($_SESSION['auction_groups']);
                }
                // ========== END GROUP LINKING ==========
                
                header("location:management-details.php?id=$sid");
                exit;
            }
            
        }        
    }
?>
<?php
function formatAmount($value) 
{
    if ($value >= 1000 && $value < 100000) 
    {
        return ($value / 1000) . " Thousand";
    } 
    elseif ($value == 100000) 
    {
        return "1 Lakh";
    } 
    else 
    {
        return $value;
    }
}


$amounts = [];

// 100 to 900 (step 100)
for ($i = 100; $i <= 900; $i += 100) {
    $amounts[] = $i;
}

// 1,000 to 95,000 (step 1,000)
for ($i = 1000; $i <= 99000; $i += 1000) {
    $amounts[] = $i;
}

// 1 Lakh
$amounts[] = 100000;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Auction | <?php echo $title_name;?></title>

    <!-- <script src="../assets/script/jquery.min.js"></script> -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">    
    
    <style>
        /* ========== GROUP TABLE ==========*/
        .groups-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .groups-table thead { background: #f7fafc; }
        .groups-table th { padding: 12px; text-align: left; font-weight: 600; color: #4a5568; border-bottom: 2px solid #e2e8f0; }
        .groups-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .groups-table tbody tr:hover { background: #f7fafc; }

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

        .form-row-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-bottom: 24px;
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

        /* Validation Styles */
        .form-control.error {
            border-color: #ef4444;
            background-color: #fef2f2;
        }

        .form-control.success {
            border-color: #10b981;
        }

        .error-message {
            font-size: 13px;
            color: #ef4444;
            font-weight: 500;
            margin-top: 4px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .radio-group-error {
            font-size: 13px;
            color: #ef4444;
            font-weight: 500;
            margin-top: 8px;
            display: none;
        }

        .radio-group-error.show {
            display: block;
        }

        /* Alert Box */
        .alert-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
            border-left: 4px solid #f59e0b;
            padding: 14px 18px;
            border-radius: 10px;
            margin: 24px 0;
        }

        .alert-warning p {
            margin: 0;
            font-size: 14px;
            font-weight: 500;
            color: #2d3748;
        }

        /* Upload Box */
        .upload-area {
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 48px;
            text-align: center;
            background: #f7fafc;
            margin: 24px 0;
            max-width: 450px;
            transition: all 0.3s;
        }

        .upload-area:hover {
            border-color: #f59e0b;
            background: rgba(245, 158, 11, 0.05);
        }

        .upload-area p {
            color: #718096;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .upload-area small {
            color: #a0aec0;
            display: block;
            margin-bottom: 16px;
        }

        .upload-area button {
            background: none;
            border: none;
            color: #f59e0b;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .upload-area input[type="file"] {
            display: none;
        }

        .upload-preview {
            margin-top: 20px;
            display: none;
        }

        .upload-preview.show {
            display: block;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            margin-bottom: 10px;
        }

        .file-name {
            font-size: 14px;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .btn-remove-file {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-remove-file:hover {
            background: #dc2626;
        }

        /* Divider */
        .form-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 36px 0;
        }

        /* Option Grid */
        .option-grid {
            display: grid;
            gap: 16px;
            margin: 20px 0;
        }

        .option-grid.cols-5 {
            grid-template-columns: repeat(5, 1fr);
        }

        .option-grid.cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .option-card {
            padding: 18px;
            border: 2px solid #e2e8f0;
            background: #f7fafc;
            border-radius: 12px;
            text-align: center;
            font-weight: 600;
            color: #4a5568;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .option-card:hover {
            border-color: #f59e0b;
            background: rgba(245, 158, 11, 0.05);
            transform: translateY(-2px);
        }

        .option-card.selected {
            border-color: #f59e0b;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
            color: #d97706;
        }

        /* Radio Button - Hidden but functional */
        .option-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* Apply selected styles when radio is checked */
        .option-card:has(input[type="radio"]:checked) {
            border-color: #f59e0b;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
            color: #d97706;
        }

        /* Check Mark Icon */
        .check-mark {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #f59e0b;
            font-size: 16px;
            display: none;
        }

        .option-card input[type="radio"]:checked ~ .check-mark {
            display: block;
        }

        /* Empty State */
        .empty-box {
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 48px;
            text-align: center;
            background: #f7fafc;
            margin: 24px 0;
        }

        .empty-box p {
            color: #718096;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .btn-add-group {
            background: linear-gradient(135deg, #4C6FFF 0%, #3B5BDB 100%);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(76, 111, 255, 0.3);
        }

        .btn-add-group:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(76, 111, 255, 0.4);
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

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-dialog {
            background: white;
            border-radius: 20px;
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            padding: 24px 28px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #718096;
            cursor: pointer;
            line-height: 1;
            transition: all 0.3s;
        }

        .modal-close:hover {
            color: #2d3748;
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 28px;
        }

        .modal-footer {
            padding: 20px 28px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-modal-cancel {
            background: #E67E22;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-modal-cancel:hover {
            background: #D35400;
        }

        .btn-modal-save {
            background: linear-gradient(135deg, #4C6FFF 0%, #3B5BDB 100%);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-modal-save:hover {
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-row,
            .form-row-3,
            .option-grid.cols-5 {
                grid-template-columns: 1fr;
            }

            .option-grid.cols-2 {
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
    <!-- Top Navigation-->
    <?php include "topbar.php"; ?>
    
    <?php include "sidebar.php"; ?>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="auction-form-card">
            <!-- Header -->
            <div class="form-card-header">
                <h1>Auction / Basic Details</h1>
            </div>

            <!-- Stepper -->
            <div class="form-stepper">
                <div class="stepper-step active">
                    <div class="stepper-circle"><span>1</span></div>
                    <div class="stepper-label">Basic Details</div>
                </div>
                <div class="stepper-step">
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
                <h2 class="form-section-title">Add Auction</h2>
                <form enctype="multipart/form-data" method="POST" id="auctionForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tournament Name<span class="required">*</span></label>
                            <select class="form-control" name="tname" id="tournamentName" required>
                                <option value="" selected disabled>Select Tournament</option>
                                <option value="<?php echo $tid;?>" selected><?php echo $tname;?></option>                                
                            </select>
                            <span class="error-message" data-error="tournamentName">Please select a tournament</span>
                        </div>
                        <div class="form-group">
                            <label>Season Name<span class="required">*</span></label>
                            <select class="form-control" name="sname" id="seasonName" required>
                                <option value="" selected disabled>Select Season</option>
                                <option value="<?php echo $sid;?>" selected><?php echo $sname;?></option>                                
                            </select>
                            <span class="error-message" data-error="seasonName">Please select a season</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Auction Name<span class="required">*</span></label>
                            <input type="text" value="<?php echo $aname;?>" class="form-control" name="aname" id="auctionName" placeholder="Enter Auction Name" required>
                            <span class="error-message" data-error="auctionName">Please enter auction name</span>
                        </div>
                        <div class="form-group">
                            <label>Auction Venue<span class="required">*</span></label>
                            <input type="text" value="<?php echo $venue;?>" class="form-control" name="venue" id="auctionVenue" placeholder="Enter Auction Venue" required>
                            <span class="error-message" data-error="auctionVenue">Please enter auction venue</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Auction Start Date<span class="required">*</span></label>
                            <input type="datetime-local" value="<?php echo $sdate;?>" class="form-control" name="sdate" id="startDate" max="<?php echo $maxDate;?>"  min="<?php echo date('Y-m-d\TH:i'); ?>" required>
                            <span class="error-message" data-error="startDate">Please select start date and time</span>
                        </div>
                        <div class="form-group">
                            <label>Auction End Date<span class="required">*</span></label>
                            <input type="datetime-local" value="<?php echo $edate;?>" class="form-control" name="edate" id="endDate" max="<?php echo $maxDate;?>" min="<?php echo date('Y-m-d\TH:i'); ?>" required>
                            <span class="error-message" data-error="endDate">Please select end date and time</span>
                            <span class="error-message" data-error="dateCompare">End date must be after start date</span>
                        </div>
                    </div>

                    <div class="alert-warning">
                        <p>Date Should Be Before Season Start Date</p>
                    </div>

                    <h3 class="form-subsection-title">Add Auctions Logo / Image/ Banner</h3>
                    <div class="upload-area" id="uploadArea">
                        <p>Drop file to upload</p>
                        <small>Upload Logo (PNG, JPG, JPEG - Max 2MB)</small>
                        <input type="file" id="logoInput" name="alogo" accept="image/png,image/jpg,image/jpeg"><?php echo $logo;?>
                        <button type="button" id="browseBtn">BROWSE</button>
                    </div>
                    <div class="upload-preview" id="uploadPreview">
                        <div class="file-name" id="fileName"></div>
                        <img id="previewImage" class="preview-image" alt="Logo Preview">
                        <br>
                        <button type="button" class="btn-remove-file" id="removeFileBtn">
                            <i class="fas fa-times"></i> Remove File
                        </button>
                    </div>

                    <div class="form-divider"></div>

                    <h3 class="form-subsection-title">Auction Credit Type</h3>
                    <div class="option-grid cols-5">                        
                        <label class="option-card">
                            <input type="radio" name="creditType" value="Points" <?php if($cr_type=='Points'){echo 'checked';}?>>
                            <span class="check-mark"><i class="fas fa-check-circle"></i></span>
                            Points
                        </label>
                        <label class="option-card">
                            <input type="radio" name="creditType" value="Credits" <?php if($cr_type=='Credits'){echo 'checked';}?>>
                            <span class="check-mark"><i class="fas fa-check-circle"></i></span>
                            Credits
                        </label>
                        <label class="option-card">
                            <input type="radio" name="creditType" value="Coins" <?php if($cr_type=='Coins'){echo 'checked';}?>>
                            <span class="check-mark"><i class="fas fa-check-circle"></i></span>
                            Coins
                        </label>                        
                    </div>
                    <span class="radio-group-error" data-error="creditType">Please select a credit type</span>

                    <h3 class="form-subsection-title">Auction Information</h3>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label>Minimum Player Per Team<span class="required">*</span></label>
                            <input type="text" name="min" value="<?php echo $min;?>" class="form-control" id="minPlayers" placeholder="Enter minimum player per team" required>
                            <span class="error-message" data-error="minPlayers">Minimum players cannot be more than 11</span>
                        </div>
                        <div class="form-group">
                            <label>Maximum Player Per Team<span class="required">*</span></label>
                            <input type="text" name="max" value="<?php echo $max;?>" class="form-control" id="maxPlayers" placeholder="Enter maximum player per team" required>
                            <span class="error-message" data-error="maxPlayers">Maximum players cannot be more than 20</span>
                        </div>
                        <div class="form-group">
                            <label>Reserve Player Per Team<span class="required">*</span></label>
                            <input type="text" name="reserve" value="<?php echo $reserve;?>" class="form-control" id="reservePlayers" placeholder="Enter reserve player per team" required>
                            <span class="error-message" data-error="reservePlayers">Reserve players cannot be more than 5</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Credit Available Per Team<span class="required">*</span></label>
                            <select class="form-control" name="camt" id="creditAvailable" required>
                                <option value="" selected disabled>Select Credit Amount</option>
                                <?php foreach ($amounts as $value): ?>
                                    <option value="<?= $value ?>" <?php if($camt==$value){echo 'selected';}?>><?= formatAmount($value) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="error-message" data-error="creditAvailable">Please select credit amount</span>
                        </div>
                        <div class="form-group">
                            <label>Bid Increase<span class="required">*</span></label>
                            <select class="form-control" name="bidamt" id="bidIncrease" required>
                                <option value="" selected disabled>Select Bid Increase</option>
                                <?php foreach ($amounts as $value): ?>
                                    <option value="<?= $value ?>" <?php if($bidamt==$value){echo 'selected';}?>><?= formatAmount($value) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="error-message" data-error="bidIncrease">Please select bid increase</span>
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <h3 class="form-subsection-title">Auction Basic Price Type</h3>
                    <div class="option-grid cols-2">
                        <label class="option-card" id="samePrice">
                            <input type="radio" name="base_type" value="same" id="priceTypeSame" <?php if($base_type == 'same'){ echo 'checked';}?>>
                            <span class="check-mark"><i class="fas fa-check-circle"></i></span>
                            Same Base Price For All Players
                        </label>
                        <label class="option-card" id="groupPrice">
                            <input type="radio" name="base_type" value="group" id="priceTypeGroup" <?php if($base_type == 'group'){ echo 'checked';}?>>
                            <span class="check-mark"><i class="fas fa-check-circle"></i></span>
                            Group-Wise Base Price [Slab Wise]
                        </label>
                    </div>
                    <span class="radio-group-error" data-error="base_type">Please select a price type</span>

                    <div id="samePriceContent" style="display: none; margin-top: 24px;">
                        <div class="form-group" style="max-width: 50%;">
                            <label>Base Price<span class="required">*</span></label>
                            <select class="form-control" name="bprice" id="basePrice">
                                <option value="" selected disabled>Select Base Price</option>
                                <?php foreach ($amounts as $value): ?>
                                    <option value="<?= $value ?>" <?php if($bprice==$value){ echo 'selected';}?>><?= formatAmount($value) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="error-message" data-error="basePrice">Please select base price</span>
                        </div>
                    </div>

                    <div id="groupPriceContent" style="display: none; margin-top: 24px;">
                        <div id="groupsList"></div>
                        <div style="text-align: center; margin-top: 20px;">
                            <button type="button" class="btn-add-group" id="openModal">
                                <i class="fas fa-plus"></i> Add Group
                            </button>
                        </div>
                    </div>
                    <br><br>                                            
                    <button type="submit" name="btn" class="btn-next" id="nextBtn">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>                    
                </form>                
            </div>            
        </div>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3>Add auction price slab</h3>
                <button type="button" class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Group Name</label>
                        <input type="text" id="group_name" class="form-control" placeholder="Enter group name">
                    </div>
                    <div class="form-group">
                        <label>Player Base Price</label>
                        <select class="form-control" id="player_base">
                            <option value="" selected disabled>Select Price</option>
                            <?php foreach ($amounts as $value): ?>
                                <option value="<?= $value ?>"><?= formatAmount($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Min Player Per Team</label>
                        <input type="number" id="min_per_team" class="form-control" placeholder="Enter min">
                    </div>
                    <div class="form-group">
                        <label>Max Player Per Team</label>
                        <input type="number" id="max_per_team" class="form-control" placeholder="Enter max">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Bid Increment Amount</label>
                        <select class="form-control" id="bid_increment">
                            <option value="" selected disabled>Select Price</option>
                            <?php foreach ($amounts as $value): ?>
                                <option value="<?= $value ?>"><?= formatAmount($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Max Bid Amount Per Player</label>
                        <select class="form-control" id="max_bid_player">
                            <option value="" selected disabled>Select Price</option>
                            <?php foreach ($amounts as $value): ?>
                                <option value="<?= $value ?>"><?= formatAmount($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Total Max Bid Allow For Group</label>
                        <select class="form-control" id="total_max_group">
                            <option value="" selected disabled>Select Price</option>
                            <?php foreach ($amounts as $value): ?>
                                <option value="<?= $value ?>"><?= formatAmount($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" id="cancelBtn">Close</button>
                <button type="button" class="btn-modal-save" id="saveGroupBtn">Add</button>
            </div>
        </div>
    </div>

    <script>

        // ========== GROUP FUNCTIONS - START ==========
        function formatAmountJS(value) {
            return value >= 1000 && value < 100000 ? (value / 1000) + " Thousand" : value == 100000 ? "1 Lakh" : value;
        }

        loadGroups();

        $('#saveGroupBtn').click(function() {
            var formData = {
                action: 'add_group',
                group_name: $('#group_name').val(),
                player_base: $('#player_base').val(),
                min_per_team: $('#min_per_team').val(),
                max_per_team: $('#max_per_team').val(),
                bid_increment: $('#bid_increment').val(),
                max_bid_player: $('#max_bid_player').val(),
                total_max_group: $('#total_max_group').val()
            };
            
            if(!formData.group_name || !formData.player_base || !formData.min_per_team || !formData.max_per_team || !formData.bid_increment || !formData.max_bid_player || !formData.total_max_group) {
                alert('Please fill all fields');
                return;
            }
            
            $.ajax({
                url: '', type: 'POST', data: formData, dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('Group added successfully!');
                        $('#modal').removeClass('show');
                        $('#group_name, #player_base, #min_per_team, #max_per_team, #bid_increment, #max_bid_player, #total_max_group').val('');
                        loadGroups();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            });
        });

        function loadGroups() {
            $.ajax({
                url: '', type: 'POST', data: { action: 'get_groups' }, dataType: 'json',
                success: function(groups) {
                    var html = '';
                    if(groups.length > 0) {
                        html = '<table class="groups-table"><thead><tr><th>Group Name</th><th>Base Price</th><th>Min</th><th>Max</th><th>Bid Inc</th><th>Max Bid/P</th><th>Total Max</th><th>Action</th></tr></thead><tbody>';
                        groups.forEach(function(g) {
                            html += '<tr><td>'+g.gname+'</td><td>'+formatAmountJS(parseInt(g.bprice))+'</td><td>'+g.minplayer+'</td><td>'+g.maxplayer+'</td><td>'+formatAmountJS(parseInt(g.bidamt))+'</td><td>'+formatAmountJS(parseInt(g.mbidamt))+'</td><td>'+formatAmountJS(parseInt(g.maxbid))+'</td><td><button type="button" class="delete-group" data-id="'+g.gid+'" style="background:#ef4444;color:white;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;"><i class="fas fa-trash"></i> Delete</button></td></tr>';
                        });
                        html += '</tbody></table>';
                    }
                    $('#groupsList').html(html);
                }
            });
        }

        $(document).on('click', '.delete-group', function() {
            if(confirm('Remove this group?')) {
                $.ajax({
                    url: '', type: 'POST', data: { action: 'delete_group', group_id: $(this).data('id') }, dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            alert('Group deleted!');
                            loadGroups();
                        }
                    }
                });
            }
        });
        // ========== GROUP FUNCTIONS - END ==========

        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            
            // Function to get current datetime in the required format
            function getCurrentDateTime() {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                return `${year}-${month}-${day}T${hours}:${minutes}`;
            }
            
            // Update minimum datetime for both inputs
            function updateMinDateTime() {
                const currentDateTime = getCurrentDateTime();
                startDateInput.min = currentDateTime;
                
                // If start date is selected, end date min should be start date
                // Otherwise, end date min should be current datetime
                if (startDateInput.value) {
                    endDateInput.min = startDateInput.value;
                } else {
                    endDateInput.min = currentDateTime;
                }
            }
            
            // Update min on page load
            updateMinDateTime();
            
            // Update min every minute to keep it current
            setInterval(updateMinDateTime, 60000);
            
            // Validate start date on change
            startDateInput.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const now = new Date();
                
                if (selectedDate < now) {
                    alert('Please select a future date and time. Past times are not allowed.');
                    this.value = '';
                }
                
                // Update end date minimum to be after start date
                updateMinDateTime();
                
                // Validate end date if already selected
                if (endDateInput.value) {
                    validateEndDate();
                }
            });
            
            // Validate end date on change
            endDateInput.addEventListener('change', function() {
                validateEndDate();
            });
            
            function validateEndDate() {
                const selectedEndDate = new Date(endDateInput.value);
                const now = new Date();
                
                // Check if end date is in the past
                if (selectedEndDate < now) {
                    alert('Please select a future date and time. Past times are not allowed.');
                    endDateInput.value = '';
                    return;
                }
                
                // Check if end date is after start date
                if (startDateInput.value) {
                    const selectedStartDate = new Date(startDateInput.value);
                    
                    if (selectedEndDate <= selectedStartDate) {
                        alert('End date must be after start date.');
                        endDateInput.value = '';
                    }
                }
            }
        });

        $(document).ready(function() {
            console.log('jQuery loaded, initializing...');

            let uploadedFile = null;

            // Live validation for text/select fields
            $('.form-control').not('#minPlayers, #maxPlayers, #reservePlayers')
            .on('blur change', function () {
                validateField($(this));
            });


            // Live validation for radio buttons
            $('input[name="creditType"], input[name="base_type"]').on('change', function() {
                validateRadioGroup($(this).attr('name'));
            });
    
            function validateDateRange() {
                const startVal = $('#startDate').val();
                const endVal   = $('#endDate').val();
                const $error   = $('[data-error="dateCompare"]');

                if (!startVal || !endVal) {
                    $error.removeClass('show');
                    return true;
                }

                const startDate = new Date(startVal);
                const endDate   = new Date(endVal);

                if (endDate <= startDate) {
                    $('#endDate').addClass('error').removeClass('success');
                    $error.addClass('show');
                    return false;
                } else {
                    $('#endDate').removeClass('error').addClass('success');
                    $error.removeClass('show');
                    return true;
                }
            }

            // Live check when dates change
            $('#startDate, #endDate').on('change', function () {
                validateDateRange();
            });

            // Validation function for individual fields
            function validateField($field) {
                const fieldId = $field.attr('id');
                const value = $field.val().trim();
                const $errorMsg = $(`[data-error="${fieldId}"]`);

                if (!value) {
                    $field.addClass('error').removeClass('success');
                    $errorMsg.addClass('show');
                    return false;
                } else {
                    $field.removeClass('error').addClass('success');
                    $errorMsg.removeClass('show');
                    return true;
                }
            }

            function addDays(date, days) {
                const result = new Date(date);
                result.setDate(result.getDate() + days);
                return result;
            }

            function formatDateForInput(date) {
                return date.toISOString().slice(0, 16); // yyyy-mm-ddThh:mm
            }

            function validateDateRangeWithLimit() {
                const startVal = $('#startDate').val();
                const endVal   = $('#endDate').val();
                const $error   = $('[data-error="dateCompare"]');

                if (!startVal || !endVal) {
                    $error.removeClass('show');
                    return true;
                }

                const startDate = new Date(startVal);
                const endDate   = new Date(endVal);
                const maxEnd    = addDays(startDate, 3);

                if (endDate <= startDate) {
                    $error.text('End date must be after start date').addClass('show');
                    $('#endDate').addClass('error');
                    return false;
                }

                if (endDate > maxEnd) {
                    $error.text('End date can be maximum 3 days after start date').addClass('show');
                    $('#endDate').addClass('error');
                    return false;
                }

                $('#endDate').removeClass('error').addClass('success');
                $error.removeClass('show');
                return true;
            }

            $('#startDate').on('change', function () {
                const startVal = $(this).val();
                if (!startVal) return;

                const startDate = new Date(startVal);
                const maxEnd    = addDays(startDate, 3);

                // Set min & max dynamically
                $('#endDate').attr('min', formatDateForInput(startDate));
                $('#endDate').attr('max', formatDateForInput(maxEnd));

                // If end date already exceeds max → reset
                const endVal = $('#endDate').val();
                if (endVal && new Date(endVal) > maxEnd) {
                    $('#endDate').val('');
                }

                validateDateRangeWithLimit();
            });

            $('#endDate').on('change', function () {
                validateDateRangeWithLimit();
            });

            // Validation function for radio groups
            function validateRadioGroup(groupName) {
                const isChecked = $(`input[name="${groupName}"]:checked`).length > 0;
                const $errorMsg = $(`[data-error="${groupName}"]`);

                if (!isChecked) {
                    $errorMsg.addClass('show');
                    return false;
                } else {
                    $errorMsg.removeClass('show');
                    return true;
                }
            }

            // File Upload
            $('#browseBtn').click(function() {
                $('#logoInput').click();
            });

            $('#logoInput').change(function() {
                handleFile(this.files[0]);
            });

            $('#uploadArea').on('dragover', function(e) {
                e.preventDefault();
                $(this).css({'border-color': '#f59e0b', 'background': 'rgba(245, 158, 11, 0.1)'});
            }).on('dragleave', function(e) {
                e.preventDefault();
                $(this).css({'border-color': '#e2e8f0', 'background': '#f7fafc'});
            }).on('drop', function(e) {
                e.preventDefault();
                $(this).css({'border-color': '#e2e8f0', 'background': '#f7fafc'});
                handleFile(e.originalEvent.dataTransfer.files[0]);
            });

            function validatePlayerLimits() {
                let valid = true;

                const min = parseInt($('#minPlayers').val(), 10);
                const max = parseInt($('#maxPlayers').val(), 10);
                const reserve = parseInt($('#reservePlayers').val(), 10);

                // Minimum players (1–11)
                if (isNaN(min) || min < 1 || min > 11) {
                    $('#minPlayers').addClass('error');
                    $('[data-error="minPlayers"]').addClass('show');
                    valid = false;
                } else {
                    $('#minPlayers').removeClass('error').addClass('success');
                    $('[data-error="minPlayers"]').removeClass('show');
                }

                // Maximum players (1–20)
                if (isNaN(max) || max < 1 || max > 20) {
                    $('#maxPlayers').addClass('error');
                    $('[data-error="maxPlayers"]').addClass('show');
                    valid = false;
                } else {
                    $('#maxPlayers').removeClass('error').addClass('success');
                    $('[data-error="maxPlayers"]').removeClass('show');
                }

                // Reserve players (0–5)
                if (isNaN(reserve) || reserve < 0 || reserve > 5) {
                    $('#reservePlayers').addClass('error');
                    $('[data-error="reservePlayers"]').addClass('show');
                    valid = false;
                } else {
                    $('#reservePlayers').removeClass('error').addClass('success');
                    $('[data-error="reservePlayers"]').removeClass('show');
                }

                // Logical rule
                if (!isNaN(min) && !isNaN(max) && min > max) {
                    $('#minPlayers, #maxPlayers').addClass('error');
                    alert('Minimum players cannot be greater than maximum players');
                    valid = false;
                }

                return valid;
            }


            $('#minPlayers, #maxPlayers, #reservePlayers').on('input', function () {
                validatePlayerLimits();
            });

            function handleFile(file) {
                if (!file) return;

                const validTypes = ['image/png', 'image/jpg', 'image/jpeg'];
                if (!validTypes.includes(file.type)) {
                    alert('Please upload a valid image file (PNG, JPG, JPEG)');
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    return;
                }

                uploadedFile = file;
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImage').attr('src', e.target.result);
                    $('#fileName').text(file.name);
                    $('#uploadArea').hide();
                    $('#uploadPreview').addClass('show');
                };
                reader.readAsDataURL(file);
            }

            $('#removeFileBtn').click(function() {
                uploadedFile = null;
                $('#logoInput').val('');
                $('#previewImage').attr('src', '');
                $('#fileName').text('');
                $('#uploadPreview').removeClass('show');
                $('#uploadArea').show();
            });

            // Price type toggle
            $('#priceTypeSame').change(function() {
                if ($(this).is(':checked')) {
                    console.log('Same price selected - showing content');
                    $('#samePriceContent').show();
                    $('#groupPriceContent').hide();
                }
            });

            $('#priceTypeGroup').change(function() {
                if ($(this).is(':checked')) {
                    console.log('Group price selected - showing content');
                    $('#groupPriceContent').show();
                    $('#samePriceContent').hide();
                    loadGroups();
                }
            });

            // Modal
            $('#openModal').click(function() {
                $('#modal').addClass('show');
            });

            $('#closeModal, #cancelBtn').click(function() {
                $('#modal').removeClass('show');
            });

            $('#modal').click(function(e) {
                if ($(e.target).is('#modal')) {
                    $(this).removeClass('show');
                }
            });

            // Form submission - prevent default and validate
            $('#auctionForm').on('submit', function(e) {                

                let isValid = true;

                $('#auctionForm .form-control[required]').each(function() {
                    if (!validateField($(this))) isValid = false;
                });

                if (!validateRadioGroup('creditType')) isValid = false;
                if (!validateRadioGroup('base_type')) isValid = false;

                const selectedPriceType = $('input[name="base_type"]:checked').val();
                if (selectedPriceType === 'same') {
                    if (!validateField($('#basePrice'))) isValid = false;
                }

                if (!isValid) {
                    const $err = $('.error:first');
                    if ($err.length) {
                        $('html, body').animate({
                            scrollTop: $err.offset().top - 100
                        }, 500);
                    }
                    return;
                }

                if (!validateDateRangeWithLimit()) {
                    $('html, body').animate({
                        scrollTop: $('#endDate').offset().top - 120
                    }, 500);
                    return;
                }
                
                if (!validateDateRange()) {
                    $('html, body').animate({
                        scrollTop: $('#endDate').offset().top - 120
                    }, 500);
                    return;
                }

                if (!validatePlayerLimits()) {
                    $('html, body').animate({
                        scrollTop: $('#minPlayers').offset().top - 120
                    }, 500);
                    return;
                }
            });

            console.log('All jQuery events attached successfully');
        });
    </script>
</body>
</html>