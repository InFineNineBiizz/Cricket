<?php 
    session_start();   
    include "connection.php";
    $name=$tour_id=$sea_id=$venue=$sdate=$edate=$logo=$cr_type=$max=$min=$res=$camt=$bidamt=$bprice=$img="";
    $fname=$lname=$num=$fname1=$lname1=$num1="";    
    
    if(isset($_GET['id']))
    {
        $auc_id = $_GET['id'];
        
        //Auction table values
        $qk = "select * from auctions where id='".$auc_id."'";
        $qk1 = mysqli_query($conn,$qk);
        $qk2 = mysqli_fetch_assoc($qk1);
        $name = $qk2['name'];
        $tour_id = $qk2['tour_id'];
        $sea_id = $qk2['sea_id'];     
        $venue = $qk2['venue'];     
        $sdate = $qk2['sdate'];     
        $edate = $qk2['edate'];     
        $logo = $qk2['logo'];     
        $cr_type = $qk2['credit_type'];     
        $min = $qk2['minplayer'];     
        $max = $qk2['maxplayer'];     
        $res = $qk2['resplayer'];     
        $camt = $qk2['camt'];     
        $bidamt = $qk2['bidamt'];     
        $bprice = $qk2['bprice'];
        
        //Auction manager table values 
        $qk3 = "select * from auc_man where aid='".$auc_id."'";
        $qk4 = mysqli_query($conn,$qk3);
        $qk5 = mysqli_fetch_assoc($qk4);
        $fname=$qk5['fname'];
        $lname=$qk5['lname'];
        $num=$qk5['number'];

        //Lead auctioner table values 
        $qk6 = "select * from lead_auc where aid='".$auc_id."'";
        $qk7 = mysqli_query($conn,$qk6);
        $qk8 = mysqli_fetch_assoc($qk7);
        $fname1=$qk8['fname'];
        $lname1=$qk8['lname'];
        $num1=$qk8['number'];
    }

    if(isset($_POST['saveGroupBtn']))
    {
        $qr3="insert into group_auction(gname,bprice,minplayer,maxplayer,bidamt,mbidamt,maxbid) values('".$_POST['group_name']."','".$_POST['player_base']."','".$_POST['min_per_team']."','".$_POST['max_per_team']."','".$_POST['bid_increment']."','".$_POST['max_bid_player']."','".$_POST['total_max_group']."')";
        $res3=mysqli_query($conn,$qr3);
        
        if ($res3) 
        { 
            $_SESSION['last_gid'] = mysqli_insert_id($conn);
            $valid = "<div class='alert alert-success text-center'><strong>Group Added!</strong></div>";
        } 
        else 
        {
            $valid = "<div class='alert alert-danger text-center'><strong>Error:</strong> " . mysqli_error($conn) . "</div>";
        }
    }    

    $st="select * from tournaments";
    $result=mysqli_query($conn,$st);

    $sql="select * from seasons";
    $resq=mysqli_query($conn,$sql);

    $base_type = $_POST['base_type'] ?? 'same'; // default 'same'
    $base_price = $_POST['base_price'] ?? '500';

    if(isset($_POST['btn']))
    {
        move_uploaded_file($_FILES['alogo']['tmp_name'],"images/".$_FILES['alogo']['name']);
        $img=$_FILES['alogo']['name'];

        if(empty($_GET['id']))
        {            
            if ($base_type == "same" && isset($_POST['base_price']) && $_POST['base_price'] !== '') 
            {
                $bp = $_POST['base_price']; // numeric, will be inserted as number
            } 
            else 
            {
                $bp = "NULL"; // SQL NULL (must NOT be quoted in SQL)
            }

            $qr="insert into auctions(name,tour_id,sea_id,venue,sdate,edate,logo,credit_type,minplayer,maxplayer,resplayer,camt,bidamt,bprice)
            values('".$_POST['aname']."','".$_POST['tname']."','".$_POST['sname']."','".$_POST['avenue']."','".$_POST['sdate']."','".$_POST['edate']."','".$img."','".$_POST['credit_type']."','".$_POST['min']."','".$_POST['max']."','".$_POST['reserve']."','".$_POST['camt']."','".$_POST['bamt']."','".$bp."')";
            $res=mysqli_query($conn,$qr);

            if($res)
            {
                $_SESSION['last_aid'] = mysqli_insert_id($conn);
                $aid = $_SESSION['last_aid'];
            }

            $qr1="insert into auc_man(aid,fname,lname,number) values('".$aid."','".$_POST['fname']."','".$_POST['lname']."','".$_POST['num']."')";
            $res1=mysqli_query($conn,$qr1);

            $qr2="insert into lead_auc(aid,fname,lname,number) values('".$aid."','".$_POST['fname1']."','".$_POST['lname1']."','".$_POST['num1']."')";
            $res2=mysqli_query($conn,$qr2);  
            
            if ($res && $res1 && $res2) 
            {               
                if(isset($_SESSION['last_gid']))
                {                
                    $gid = $_SESSION['last_gid'];

                    $link = "insert into grp_auc(aid,gid) values('".$aid."','".$gid."')";
                    mysqli_query($conn, $link);
                }
                
                $valid = "<div class='alert alert-success text-center'><strong>Auction Added!</strong></div>";
            }
            else 
            {
                $valid = "<div class='alert alert-danger text-center'><strong>Error:</strong> " . mysqli_error($conn) . "</div>";
            }
        }
        else
        {
            if ($base_type == "same" && isset($_POST['base_price']) && $_POST['base_price'] !== '') 
            {
                $bp = $_POST['base_price']; // numeric, will be inserted as number
            } 
            else 
            {
                $bp = "NULL"; // SQL NULL (must NOT be quoted in SQL)
            }

            $upd="update auctions set name='".$_POST['aname']."',tour_id='".$_POST['tname']."',sea_id='".$_POST['sname']."',venue='".$_POST['avenue']."',sdate='".$_POST['sdate']."',edate='".$_POST['edate']."',logo='".$img."',credit_type='".$_POST['credit_type']."',minplayer='".$_POST['min']."',maxplayer='".$_POST['max']."',resplayer='".$_POST['reserve']."',camt='".$_POST['camt']."',bidamt='".$_POST['bamt']."',bprice='".$bp."' where id='".$auc_id."'";
            $upd1=mysqli_query($conn,$upd);

            $upd2="update auc_man set fname='".$_POST['fname']."',lname='".$_POST['lname']."',number='".$_POST['num']."' where aid='".$auc_id."'";
            $upd3=mysqli_query($conn,$upd2);

            $upd4="update lead_auc set fname='".$_POST['fname1']."',lname='".$_POST['lname1']."',number='".$_POST['num1']."' where aid='".$auc_id."'";
            $upd5=mysqli_query($conn,$upd4);

            header("location:manage_auctions.php");
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
    <meta charset="utf-8" />
    <title>Add Auctions | CrickFolio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <?php 
        include "links.php";
    ?>     
</head>

<body>
    <div class="wrapper">
        <!-- Menu -->

        <!-- Sidenav Menu Start -->
        
        <?php 
            include "sidebar.php";
        ?>
        
        <!-- Sidenav Menu End -->
        
        <!-- Topbar Start -->
        <header class="app-topbar" id="header">
        <div class="page-container topbar-menu">
            <div class="d-flex align-items-center gap-2">    
                <!-- Topbar Page Title -->
                <div class="topbar-item d-none d-md-flex px-2">                 
                    <div>
                        <h4 class="page-title fs-20 fw-semibold mb-0">Auction / Create</h4>
                    </div>
                </div>
            </div>
        </div>
        </header>
        <?php 
            include "topbar.php";
        ?>
        
        <!-- Topbar End -->
        
        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->
        
        <div class="page-content">
            <div class="page-container">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom border-dashed">
                                <h4 class="card-title mb-0 flex-grow-1">Add Auction Details</h4>
                            </div>                                                                
                            <br>
                            <?php 
                                if(isset($valid))
                                {
                                    echo $valid;
                                }
                            ?>
                            <div class="card-body">
                            <form id="myForm" class="needs-validation" method="POST" enctype="multipart/form-data" novalidate>
                                <div class="row">                                        
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="tname">Tournament Name</label>
                                            <select class="form-select" data-choices name="tname" id="tname" required>
                                                <option value="" selected disabled>Select Tournament Name</option>
                                                <?php while($row=mysqli_fetch_assoc($result)){?>
                                                    <option value="<?php echo $row['tid'];?>" <?php if($tour_id == $row['tid']){ echo 'selected';}?>><?php echo $row['name'];?></option>
                                                <?php }?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please Provide Tournament Name..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="sname">Season Name</label>
                                            <select class="form-select" data-choices name="sname" id="sname" required>
                                                <option value="" selected disabled>Select Season Name</option>
                                                <?php while($row=mysqli_fetch_assoc($resq)){?>
                                                    <option value="<?php echo $row['id'];?>" <?php if($sea_id==$row['id']){echo 'selected';}?>><?php echo $row['name'];?></option>
                                                <?php }?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please Provide Season Name..
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="aname">Auction Name</label>
                                            <input type="text" name="aname" class="form-control" id="aname" value="<?php echo $name;?>" placeholder="Enter Auction Name" required>
                                            <div class="invalid-feedback">
                                                Please Enter Auction Name..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="avenue">Auction Venue</label>
                                            <input type="text" name="avenue" class="form-control" id="avenue" value="<?php echo $venue;?>" placeholder="Enter Auction Venue" required>
                                            <div class="invalid-feedback">
                                                Please Enter Auction Venue..
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="sdate">Auction Start Date</label>                                                
                                            <div class="input-group has-validation">
                                                <input type="text" class="form-control" data-provider="flatpickr" placeholder="Select Auction Start Date" data-date-format="Y-m-d" data-enable-time id="sdate" data-min-date="today" value="<?php echo $sdate;?>" name="sdate" required>
                                                <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                            </div>
                                            <div id="season_start_error" class="invalid-feedback d-none">
                                                Please Provide Auction Start Date..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="edate">Auction End Date</label>                                                
                                            <div class="input-group has-validation">
                                                <input type="text" class="form-control" data-provider="flatpickr" placeholder="Select Auction End Date" data-date-format="Y-m-d" data-enable-time id="edate" data-min-date="today" value="<?php echo $edate;?>" name="edate" required>
                                                <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                            </div>
                                            <div id="season_end_error" class="invalid-feedback d-none">
                                                Please Provide Auction End Date..
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                                                                    
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="alogo">Upload Logo</label>
                                            <input type="file" class="form-control" id="alogo" name="alogo" required><?php echo $logo;?>
                                            <div class="invalid-feedback">
                                                Please Choose Logo..
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <br>
                                <div class="border-bottom border-dashed">
                                    <h4 class="card-title mb-0 flex-grow-1">Add Management Details</h4>
                                    <br>
                                </div>
                                <br>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <h4 class="card-title mb-0 flex-grow-1">Auction Manager</h4>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="card-title mb-0 flex-grow-1">Lead Auctioneer</h4>
                                    </div>
                                </div>

                                <br>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="num">Phone number</label>
                                            <input type="text" class="form-control" id="num" name="num" placeholder="Enter Phone Number" value="<?php echo $num;?>" required>
                                            <div class="invalid-feedback">
                                                Enter Phone Number..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="num1">Phone number</label>
                                            <input type="text" class="form-control" id="num1" name="num1" placeholder="Enter Phone Number" value="<?php echo $num1;?>" required>
                                            <div class="invalid-feedback">
                                                Enter Phone Number..
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-3">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="fname">First Name</label>
                                            <input type="text" class="form-control" id="fname" name="fname" placeholder="Enter First Name" value="<?php echo $fname;?>" required>
                                            <div class="invalid-feedback">
                                                Enter First Name..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="lname">Last Name</label>
                                            <input type="text" class="form-control" id="lname" name="lname" placeholder="Enter Last Name" value="<?php echo $lname;?>" required>
                                            <div class="invalid-feedback">
                                                Enter Last Name..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="fname1">First Name</label>
                                            <input type="text" class="form-control" id="fname1" name="fname1" placeholder="Enter First Name" value="<?php echo $fname1;?>" required>
                                            <div class="invalid-feedback">
                                                Enter First Name..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="lname1">Last Name</label>
                                            <input type="text" class="form-control" id="lname1" name="lname1" placeholder="Enter Last Name" value="<?php echo $lname1;?>" required>
                                            <div class="invalid-feedback">
                                                Enter Last Name..
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <br>
                                <div class="border-bottom border-dashed">
                                    <h4 class="card-title mb-0 flex-grow-1">Auction Information</h4>
                                    <br>
                                </div>
                                <br>
                                
                                <label class="form-label lb">Auction Credit Type</label>
                                
                                <div class="d-flex gap-3 flex-wrap">
                                    <input type="radio" class="btn-check" name="credit_type" id="type1" value="Points" <?php if($cr_type=="Points"){echo 'checked';}?>>
                                    <label class="btn btn-outline-primary rounded-3 px-3 py-2 position-relative" for="type1">Points</label>

                                    <input type="radio" class="btn-check" name="credit_type" id="type2" value="Credits" <?php if($cr_type=="Credits"){echo 'checked';}?>>
                                    <label class="btn btn-outline-primary rounded-3 px-3 py-2 position-relative" for="type2">Credits</label>

                                    <input type="radio" class="btn-check" name="credit_type" id="type3" value="Coins" <?php if($cr_type=="Coins"){echo 'checked';}?>>
                                    <label class="btn btn-outline-primary rounded-3 px-3 py-2 position-relative" for="type3">Coins</label>

                                    <input type="radio" class="btn-check" name="credit_type" id="type4" value="None" <?php if($cr_type=="None"){echo 'checked';}?>>
                                    <label class="btn btn-outline-primary rounded-3 px-3 py-2 position-relative" for="type4">None</label>
                                </div>
                                
                                <br>

                                <div class="row">
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="min">Minimum Player Per Team</label>
                                            <input type="text" class="form-control" id="min" name="min" value="<?php echo $min;?>" placeholder="Enter Minimum Player Per Team" required>
                                            <div class="invalid-feedback">
                                                Enter Minimum Player Per Team..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="max">Maximum Player Per Team</label>
                                            <input type="text" class="form-control" id="max" name="max" value="<?php echo $max;?>" placeholder="Enter Maximum Player Per Team" required>
                                            <div class="invalid-feedback">
                                                Enter Maximum Player Per Team..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="reserve">Maximum Reserve Player Per Team</label>
                                            <input type="text" class="form-control" id="reserve" name="reserve" value="<?php echo $res;?>" placeholder="Enter Maximum Reserve Player Per Team" required>
                                            <div class="invalid-feedback">
                                                Enter Maximum Reserve Player Per Team..
                                            </div>
                                        </div>
                                    </div>                                        
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="camt">Credit Available Per Team</label>
                                            <select class="form-select" name="camt" id="camt" required>
                                                <option value="" selected disabled>Select Credit Amount Per Team</option>
                                                <?php foreach ($amounts as $value): ?>
                                                    <option value="<?= $value ?>" <?php if($camt==$value){echo 'selected';}?>><?= formatAmount($value) ?></option>
                                                <?php endforeach; ?>
                                            </select>                                                
                                            <div class="invalid-feedback">
                                                Select Credit Available Per Team..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="bamt">Bid Increase</label>
                                            <select class="form-select" name="bamt" id="bamt" required>
                                                <option value="" selected disabled>Select Bid Increase Amount</option>
                                                <?php foreach ($amounts as $value): ?>
                                                    <option value="<?= $value ?>" <?php if($bidamt==$value){echo 'selected';}?>><?= formatAmount($value) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Select Bid Increase..
                                            </div>
                                        </div>
                                    </div>                                        
                                </div>
                                
                                <br>
                                <div class="border-bottom border-dashed">
                                    <h4 class="card-title mb-0 flex-grow-1">Auction Basic Price Type</h4>
                                    <br>
                                </div>
                                <br>

                                <div class="d-flex gap-3 mb-4">
                                    <input class="btn-check" type="radio" name="base_type" id="sameType" autocomplete="off" value="same" <?= ($base_type === 'same') ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-primary rounded-3 px-4 py-3 flex-fill text-center" for="sameType">
                                        Same Base Price For All Players
                                    </label>

                                    <input class="btn-check" type="radio" name="base_type" id="groupType" autocomplete="off" value="group" <?= ($base_type === 'group') ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-primary rounded-3 px-4 py-3 flex-fill text-center" for="groupType">
                                        Group-Wise Base Price[Slab Wise]
                                    </label>
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div id="sameArea" class="mb-3">
                                            <label class="form-label lb" for="base_price">Base Price</label>
                                            <select class="form-select" name="base_price" id="base_price" required>
                                                <option value="" selected disabled>Select Base Price</option>
                                                <?php foreach ($amounts as $value): ?>
                                                    <option value="<?= $value ?>" <?php if($bprice==$value){echo 'selected';}?>><?= formatAmount($value) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Select Base Price..
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- <div class="text-end">
                                    <button id="addGroupBtn" type="button" class="btn btn-primary d-none" data-bs-toggle="modal" data-bs-target="#addGroupModal">Add Group</button>
                                </div> -->

                                <!-- ADD GROUP BUTTON (kept outside the hidden container) -->
                                <div class="text-end mb-3">
                                <button id="addGroupBtn" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGroupModal">Add Group</button>
                                </div>                                    
                                                                    
                                <button class="btn btn-primary lb w-25" name="btn" type="submit"><?php if(isset($_GET['id'])){ echo 'Update';} else { echo 'Insert';}?></button>
                            </form>
                            
                            <!-- Modal -->
                                <div class="modal fade" id="addGroupModal" tabindex="-1" aria-labelledby="addGroupModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                    <form method="POST" id="addGroupForm" novalidate> <!-- set action if needed -->
                                        <div class="modal-header">
                                        <h4 class="modal-title" id="addGroupModalLabel">Add auction price slab</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                            <label class="form-label lb" for="group_name">Group Name</label>
                                            <input id="group_name" name="group_name" type="text" class="form-control" placeholder="Enter group name" required>
                                            <div class="invalid-feedback">
                                                Enter Group Name..
                                            </div>
                                            </div>

                                            <div class="col-md-6">
                                            <label class="form-label lb" for="player_base">Player Base Price</label>
                                            <select class="form-select" name="player_base" id="player_base" required>
                                                <option value="" selected disabled>Select Player Base Price</option>
                                                <?php foreach ($amounts as $value): ?>
                                                    <option value="<?= $value ?>"><?= formatAmount($value) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Select Player Base Price..
                                            </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label lb" for="min_per_team">Min Player Per Team</label>
                                                <input id="min_per_team" name="min_per_team" type="number" min="0" class="form-control" placeholder="Enter min player per team" required>
                                                <div class="invalid-feedback">
                                                    Enter Minimum Player Per Team..
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label lb" for="max_per_team">Max Player Per Team</label>
                                                <input id="max_per_team" name="max_per_team" type="number" min="0" class="form-control" placeholder="Enter max player per team" required>
                                                <div class="invalid-feedback">
                                                    Enter Maximum Player Per Team..
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label lb" for="bid_increment">Bid Increment Amount</label>
                                                <select class="form-select" name="bid_increment" id="bid_increment" required>
                                                    <option value="" selected disabled>Select Bid Increment Amount</option>
                                                    <?php foreach ($amounts as $value): ?>
                                                        <option value="<?= $value ?>"><?= formatAmount($value) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Select Bid Increment Amount..
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label lb" for="">Max Bid Amount Per Player</label>
                                                <select class="form-select" name="max_bid_player" id="max_bid_player" required>
                                                    <option value="" selected disabled>Select Max Bid Amount Per Player</option>
                                                    <?php foreach ($amounts as $value): ?>
                                                        <option value="<?= $value ?>"><?= formatAmount($value) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="invalid-feedback">
                                                    Select Max Bid Amount Per Player..
                                                </div>
                                                <div class="form-text">No team can bid above this amount for a single player.</div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label lb" for="total_max_group">Total Max Bid Allow For Group</label>
                                                <select class="form-select" name="total_max_group" id="total_max_group" required>
                                                    <option value="" selected disabled>Select Max Bid Amount Per Player</option>
                                                    <?php foreach ($amounts as $value): ?>
                                                        <option value="<?= $value ?>"><?= formatAmount($value) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="form-text">The total sum of the maximum bids agreed for all players in this category group.</div>
                                            </div>                                            
                                        </div> <!-- /row -->
                                        </div> <!-- /modal-body -->

                                        <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="saveGroupBtn" class="btn btn-primary">Save Group</button>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                            </div>
                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div> <!-- end row-->
            </div>
        </div>

        <!-- Footer Start -->
        <?php 
            include "footer.php";
        ?>
        <!-- Footer End -->
    </div>
    <!-- END wrapper -->

    <!-- Theme Settings -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="theme-settings-offcanvas">
        <div class="d-flex align-items-center gap-2 px-3 py-3 offcanvas-header border-bottom border-dashed">
            <h5 class="flex-grow-1 fs-16 fw-bold mb-0">Theme Settings</h5>

            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body p-0 h-100" data-simplebar>
            <div class="p-3 border-bottom border-dashed">
                <h5 class="mb-3 fs-13 text-uppercase fw-bold">Color Scheme</h5>

                <div class="row">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-color-light"
                                value="light">
                            <label class="form-check-label p-3 w-100 d-flex justify-content-center align-items-center"
                                for="layout-color-light">
                                <iconify-icon icon="solar:sun-bold-duotone" class="fs-32 text-muted"></iconify-icon>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Light</h5>
                    </div>

                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-color-dark"
                                value="dark">
                            <label class="form-check-label p-3 w-100 d-flex justify-content-center align-items-center"
                                for="layout-color-dark">
                                <iconify-icon icon="solar:cloud-sun-2-bold-duotone" class="fs-32 text-muted"></iconify-icon>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Dark</h5>
                    </div>
                </div>
            </div>

            <div class="p-3 border-bottom border-dashed sidebarMode">
                <h5 class="mb-3 fs-13 text-uppercase fw-bold">Layout Mode</h5>

                <div class="row">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-layout-mode" id="layout-mode-fluid"
                                value="fluid">
                            <label class="form-check-label p-0 avatar-xl w-100" for="layout-mode-fluid">
                                <div>
                                    <span class="d-flex h-100">
                                        <span class="flex-shrink-0">
                                            <span class="bg-light d-flex h-100 border-end flex-column p-1 px-2">
                                                <span class="d-block p-1 bg-dark-subtle rounded mb-1"></span>
                                                <span
                                                    class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                <span
                                                    class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                <span
                                                    class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                <span
                                                    class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            </span>
                                        </span>
                                        <span class="flex-grow-1">
                                            <span class="d-flex h-100 flex-column rounded-2">
                                                <span class="bg-light d-block p-1"></span>
                                            </span>
                                        </span>
                                    </span>
                                </div>

                                <div>
                                    <span class="d-flex h-100 flex-column">
                                        <span
                                            class="bg-light d-flex p-1 align-items-center border-bottom border-secondary border-opacity-25">
                                            <span class="d-block p-1 bg-dark-subtle rounded me-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded ms-auto"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                        </span>
                                        <span class="bg-light d-block p-1"></span>
                                    </span>
                                </div>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Fluid</h5>
                    </div>

                    <div class="col-4">
                        <div class="form-check sidebar-setting card-radio">
                            <input class="form-check-input" type="radio" name="data-layout-mode" id="data-layout-detached"
                                value="detached">
                            <label class="form-check-label p-0 avatar-xl w-100" for="data-layout-detached">
                                <span class="d-flex h-100 flex-column">
                                    <span class="bg-light d-flex p-1 align-items-center border-bottom ">
                                        <span class="d-block p-1 bg-dark-subtle rounded me-1"></span>
                                        <span
                                            class="d-block border border-3 border-secondary border-opacity-25 rounded ms-auto"></span>
                                        <span
                                            class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                        <span
                                            class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                        <span
                                            class="d-block border border-3 border-secondary border-opacity-25 rounded ms-1"></span>
                                    </span>
                                    <span class="d-flex h-100 p-1 px-2">
                                        <span class="flex-shrink-0">
                                            <span class="bg-light d-flex h-100 flex-column p-1 px-2">
                                                <span
                                                    class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                <span
                                                    class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                                <span
                                                    class="d-block border border-3 border-secondary border-opacity-25 rounded w-100"></span>
                                            </span>
                                        </span>
                                    </span>
                                    <span class="bg-light d-block p-1 mt-auto px-2"></span>
                                </span>

                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Detached</h5>
                    </div>
                </div>
            </div>

            <div class="p-3 border-bottom border-dashed">
                <h5 class="mb-3 fs-16 fw-bold">Topbar Color</h5>

                <div class="row">
                    <div class="col-3 darkMode">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-topbar-color" id="topbar-color-light"
                                value="light">
                            <label class="form-check-label p-0 avatar-lg w-100 bg-light" for="topbar-color-light">
                                <span class="d-flex align-items-center justify-content-center h-100">
                                    <span class="p-2 d-inline-flex shadow rounded-circle bg-white"></span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Light</h5>
                    </div>

                    <div class="col-3">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-topbar-color" id="topbar-color-dark"
                                value="dark">
                            <label class="form-check-label p-0 avatar-lg w-100 bg-light" for="topbar-color-dark">
                                <span class="d-flex align-items-center justify-content-center h-100">
                                    <span class="p-2 d-inline-flex shadow rounded-circle"
                                        style="background-color: #000000;"></span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Dark</h5>
                    </div>

                    <div class="col-3">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-topbar-color" id="topbar-color-brand"
                                value="brand">
                            <label class="form-check-label p-0 avatar-lg w-100 bg-light" for="topbar-color-brand">
                                <span class="d-flex align-items-center justify-content-center h-100">
                                    <span class="p-2 d-inline-flex shadow rounded-circle bg-primary"></span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Brand</h5>
                    </div>
                </div>
            </div>

            <div class="p-3 border-bottom border-dashed">
                <h5 class="mb-3 fs-16 fw-bold">Menu Color</h5>

                <div class="row">
                    <div class="col-3">
                        <div class="form-check sidebar-setting card-radio">
                            <input class="form-check-input" type="radio" name="data-menu-color" id="sidenav-color-light"
                                value="light">
                            <label class="form-check-label p-0 avatar-lg w-100 bg-light" for="sidenav-color-light">
                                <span class="d-flex align-items-center justify-content-center h-100">
                                    <span class="p-2 d-inline-flex shadow rounded-circle bg-white"></span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Light</h5>
                    </div>

                    <div class="col-3">
                        <div class="form-check sidebar-setting card-radio">
                            <input class="form-check-input" type="radio" name="data-menu-color" id="sidenav-color-dark"
                                value="dark">
                            <label class="form-check-label p-0 avatar-lg w-100 bg-light" for="sidenav-color-dark">
                                <span class="d-flex align-items-center justify-content-center h-100">
                                    <span class="p-2 d-inline-flex shadow rounded-circle"
                                        style="background-color: #000000;"></span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Dark</h5>
                    </div>
                    <div class="col-3">
                        <div class="form-check sidebar-setting card-radio">
                            <input class="form-check-input" type="radio" name="data-menu-color" id="sidenav-color-brand"
                                value="brand">
                            <label class="form-check-label p-0 avatar-lg w-100 bg-light" for="sidenav-color-brand">
                                <span class="d-flex align-items-center justify-content-center h-100">
                                    <span class="p-2 d-inline-flex shadow rounded-circle bg-primary"></span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Brand</h5>
                    </div>
                </div>
            </div>

            <div class="p-3 .border-bottom .border-dashed sidebarMode">
                <h5 class="mb-3 fs-13 text-uppercase fw-bold">Sidebar Size</h5>

                <div class="row">
                    <div class="col-4">
                        <div class="form-check sidebar-setting card-radio">
                            <input class="form-check-input" type="radio" name="data-sidenav-size" id="sidenav-size-default"
                                value="default">
                            <label class="form-check-label p-0 avatar-xl w-100" for="sidenav-size-default">
                                <span class="d-flex h-100">
                                    <span class="flex-shrink-0">
                                        <span class="bg-light d-flex h-100 border-end  flex-column p-1 px-2">
                                            <span class="d-block p-1 bg-dark-subtle rounded mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-block p-1"></span>
                                        </span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Default</h5>
                    </div>

                    <div class="col-4">
                        <div class="form-check sidebar-setting card-radio">
                            <input class="form-check-input" type="radio" name="data-sidenav-size" id="sidenav-size-compact"
                                value="compact">
                            <label class="form-check-label p-0 avatar-xl w-100" for="sidenav-size-compact">
                                <span class="d-flex h-100">
                                    <span class="flex-shrink-0">
                                        <span class="bg-light d-flex h-100 border-end  flex-column p-1">
                                            <span class="d-block p-1 bg-dark-subtle rounded mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-block p-1"></span>
                                        </span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Compact</h5>
                    </div>

                    <div class="col-4">
                        <div class="form-check sidebar-setting card-radio">
                            <input class="form-check-input" type="radio" name="data-sidenav-size" id="sidenav-size-small"
                                value="condensed">
                            <label class="form-check-label p-0 avatar-xl w-100" for="sidenav-size-small">
                                <span class="d-flex h-100">
                                    <span class="flex-shrink-0">
                                        <span class="bg-light d-flex h-100 border-end flex-column" style="padding: 2px;">
                                            <span class="d-block p-1 bg-dark-subtle rounded mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-block p-1"></span>
                                        </span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Condensed</h5>
                    </div>

                    <div class="col-4">
                        <div class="form-check sidebar-setting card-radio">
                            <input class="form-check-input" type="radio" name="data-sidenav-size"
                                id="sidenav-size-small-hover" value="sm-hover">
                            <label class="form-check-label p-0 avatar-xl w-100" for="sidenav-size-small-hover">
                                <span class="d-flex h-100">
                                    <span class="flex-shrink-0">
                                        <span class="bg-light d-flex h-100 border-end flex-column" style="padding: 2px;">
                                            <span class="d-block p-1 bg-dark-subtle rounded mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                            <span
                                                class="d-block border border-3 border-secondary border-opacity-25 rounded w-100 mb-1"></span>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-block p-1"></span>
                                        </span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Hover View</h5>
                    </div>

                    <div class="col-4">
                        <div class="form-check sidebar-setting card-radio">
                            <input class="form-check-input" type="radio" name="data-sidenav-size" id="sidenav-size-full"
                                value="full">
                            <label class="form-check-label p-0 avatar-xl w-100" for="sidenav-size-full">
                                <span class="d-flex h-100">
                                    <span class="flex-shrink-0">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="d-block p-1 bg-dark-subtle mb-1"></span>
                                        </span>
                                    </span>
                                    <span class="flex-grow-1">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-block p-1"></span>
                                        </span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Full Layout</h5>
                    </div>

                    <div class="col-4">
                        <div class="form-check sidebar-setting card-radio">
                            <input class="form-check-input" type="radio" name="data-sidenav-size"
                                id="sidenav-size-fullscreen" value="fullscreen">
                            <label class="form-check-label p-0 avatar-xl w-100" for="sidenav-size-fullscreen">
                                <span class="d-flex h-100">
                                    <span class="flex-grow-1">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-block p-1"></span>
                                        </span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        <h5 class="fs-14 text-center text-muted mt-2">Hidden</h5>
                    </div>
                </div>
            </div>

            <div class="p-3 border-bottom border-dashed d-none">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fs-16 mb-0">Container Width</h5>

                    <div class="btn-group radio" role="group">
                        <input type="radio" class="btn-check" name="data-container-position" id="container-width-fixed"
                            value="fixed">
                        <label class="btn btn-sm btn-soft-primary w-sm" for="container-width-fixed">Full</label>

                        <input type="radio" class="btn-check" name="data-container-position" id="container-width-scrollable"
                            value="scrollable">
                        <label class="btn btn-sm btn-soft-primary w-sm ms-0" for="container-width-scrollable">Boxed</label>
                    </div>
                </div>
            </div>

            <div class="p-3 border-bottom border-dashed d-none">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fs-16 mb-0">Layout Position</h5>

                    <div class="btn-group radio" role="group">
                        <input type="radio" class="btn-check" name="data-layout-position" id="layout-position-fixed"
                            value="fixed">
                        <label class="btn btn-sm btn-soft-primary w-sm" for="layout-position-fixed">Fixed</label>

                        <input type="radio" class="btn-check" name="data-layout-position" id="layout-position-scrollable"
                            value="scrollable">
                        <label class="btn btn-sm btn-soft-primary w-sm ms-0"
                            for="layout-position-scrollable">Scrollable</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php 
        include "scripts.php";
    ?>
    
    <script>
        // $(document).ready(function() {
        //     $('#mtype').on('change', function() {
                
        //         // Get the currently selected value
        //         var selectedValue = $(this).val();

        //         // Check if the value is 'limited overs'
        //         if (selectedValue === 'Limited Overs') {
        //             // If yes, find the textbox div and remove 'd-none' to show it
        //             $('#overs-textbox-div').removeClass('d-none');
        //         } else {
        //             // Otherwise, add 'd-none' to hide it
        //             $('#overs-textbox-div').addClass('d-none');
        //         }
        //     });
        // });
    </script>

    <script>
        $(document).ready(function() {
        function updateOversVisibility() {
            var selectedValue = $('#mtype').val() || '';

            // Show overs for 'Limited Overs' OR where the value contains digits (T10, T20, OneDay 50)
            var showOvers = selectedValue === 'Limited Overs';

            if (showOvers) {
                $('#overs-textbox-div').removeClass('d-none');
                $('#overs').attr('required', true);
            } else {
                $('#overs-textbox-div').addClass('d-none');
                $('#overs').removeAttr('required').val('');
            }
        }

        // run on change (and on load for preselected values)
        $('#mtype').on('change', updateOversVisibility);
        updateOversVisibility();
        });
    </script>
    <script>
        <?php if(isset($_POST['btn'])):?>
            Swal.fire({
                icon: 'success',
                title: 'Auction Added!',
                text: "New auction has been added successfully!",                
                confirmButtonText: 'Ok',                
                confirmButtonColor: '#0d6efd',            
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "manage_auctions.php";
                }
            });
        <?php endif;?>
    </script>
</body>
</html>