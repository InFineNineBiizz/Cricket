<?php 
    include "connection.php";
    $fn=$ln=$no=$logo=$tn=$tss=$tno="";

    if(isset($_GET['id']))
    {
        $id=$_GET['id'];
        $str="select * from players where id=".$id."";
        $res=mysqli_query($conn,$str);
        $row=mysqli_fetch_array($res);
        $fn=$row['fname'];
        $ln=$row['lname'];
        $no=$row['number'];
        $logo=$row['logo'];
        $tn=$row['tname'];
        $tss=$row['tsize'];
        $tno=$row['tnumber']; 
        
        $roles_str = $row['role'];
        $bat_str   = $row['batstyle'];
        $bowl_str  = $row['bowlstyle'];
       
    }

    if(isset($_POST['btn']))
    {
        // ----- ROLES -----
        $roles = $_POST['roles'] ?? [];   // array
        // ----- CHILD TYPES -----
        $bat_type  = $_POST['bat_type']  ?? [];
        $bowl_type = $_POST['bowl_type'] ?? [];
        $wk_type   = $_POST['wk_type']   ?? [];
        // ----- CONVERT TO STRING -----
        $roles_str = implode(", ", $roles);
        $bat_str   = implode(", ", $bat_type);
        $bowl_str  = implode(", ", $bowl_type);
        $wk_str    = implode(", ", $wk_type);

        if(empty($_GET['id']))
        {
            move_uploaded_file($_FILES['plogo']['tmp_name'],"images/".$_FILES['plogo']['name']);
            $img=$_FILES['plogo']['name'];                
            
            $str="insert into players(fname,lname,number,logo,role,batstyle,bowlstyle,tname,tsize,tnumber) 
            values('".$_POST['fname']."','".$_POST['lname']."','".$_POST['number']."','".$img."','".$roles_str."','".$bat_str."','".$bowl_str."','".$_POST['tname']."','".$_POST['tsize']."','".$_POST['tnumber']."')";
            $res=mysqli_query($conn,$str);            
        }
        else
        {
            move_uploaded_file($_FILES['plogo']['tmp_name'],"images/".$_FILES['plogo']['name']);
            $img=$_FILES['plogo']['name'];

            $str="update players set fname='".$_POST['fname']."',lname='".$_POST['lname']."',number='".$_POST['number']."',logo='".$img."',role='".$roles_str."',batstyle='".$bat_str."',bowlstyle='".$bowl_str."',
            tname='".$_POST['tname']."',tsize='".$_POST['tsize']."',tnumber='".$_POST['tnumber']."' where id=".$id."";
            $res=mysqli_query($conn,$str);
            header("location:manage_players.php");
        }
    }
    

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Add Players | CrickFolio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
                        <h4 class="page-title fs-20 fw-semibol mb-0">Auction / Teams / Players / Add</h4>
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
                                <h4 class="card-title mb-0 flex-grow-1">Add players</h4>
                            </div>                                                                
                            <br>                            
                            <div class="card-body">
                            <form id="myForm" class="needs-validation" method="POST" enctype="multipart/form-data" novalidate>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="name">First Name</label>
                                            <input type="text" value="<?php echo $fn;?>" class="form-control" id="name" placeholder="Enter First Name" name="fname" required>
                                            <div class="invalid-feedback">
                                                Please Enter First Name..
                                            </div>
                                        </div>  
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="lname">Last Name</label>
                                            <input type="text" class="form-control" id="lname" placeholder="Enter Last Name" name="lname" value="<?php echo $ln;?>" required>
                                            <div class="invalid-feedback">
                                                Please Enter Last Name..
                                            </div>
                                        </div>
                                    </div>       
                                </div>

                                <div class="row">   
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="number">Phone Number</label>
                                            <input type="text" class="form-control" id="number" placeholder="Enter Phone Number" name="number" value="<?php echo $no;?>" required>
                                            <div class="invalid-feedback">
                                                Please Enter Valid Number..
                                            </div>
                                        </div>
                                    </div>  

                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="logo">Player logo</label>
                                            <input type="file"  name="plogo" id="logo" class="form-control" 
                                            placeholder="add logo" required ><?php echo $logo;?>
                                            <div class="invalid-feedback">
                                                Please provide a logo.
                                            </div>
                                        </div>
                                    </div> 
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="tname">Tshirt Name</label>
                                            <input type="text" class="form-control" id="tname" placeholder="Enter Tshirt Name" name="tname" value="<?php echo $tn;?>" required>
                                            <div class="invalid-feedback">
                                                Please Enter Tshirt Name..
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="ts">Tshirt Size</label>
                                            <select name="tsize"  id="ts"  class="form-select" data-choices required>
                                                <option value="" selected disabled>Select Size</option>                                                    
                                                <option value="S" <?php if($tss == "S") echo "selected";?>>S</option>
                                                <option value="M" <?php if($tss == "M") echo "selected";?>>M</option>
                                                <option value="L" <?php if($tss == "L") echo "selected";?>>L</option>
                                                <option value="XL" <?php if($tss == "XL") echo "selected";?>>XL</option>
                                                <option value="XXL" <?php if($tss == "XXL") echo "selected";?>>XXL</option>
                                                <option value="XXXL" <?php if($tss == "XXXL") echo "selected";?>>XXXL</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                select Tshirt Size
                                            </div>
                                        </div>
                                    </div> 
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="tnumber">Tshirt Number</label>
                                            <input type="text" class="form-control" id="tnumber" placeholder="Enter Tshirt Number" name="tnumber" value="<?php echo $tno;?>" required>
                                            <div class="invalid-feedback">
                                                Please Enter Tshirt Number..
                                            </div>
                                        </div>
                                    </div>
                                </div>   
                                
                                <!-- ================= PLAYER ROLE SELECTION ================= -->
                                <!-- All Rounder -->
                                <div class="mb-3">
                                    <h5>All Rounder</h5>
                                    <input type="checkbox" class="btn-check role-check" name="roles[]" id="allrounder" value="All Rounder">
                                    <label class="btn btn-outline-primary" for="allrounder">All Rounder</label>
                                </div>
                                
                                <div class="mb-3">
                                    <h5>Batter</h5>

                                    <input type="checkbox" class="btn-check parent" name="roles[]" id="batsman" value="Batsman">
                                    <label class="btn btn-outline-primary" for="batsman">Batsman</label>
                                    &nbsp;&nbsp;&nbsp;

                                    <input type="checkbox" class="btn-check child-bat" data-parent="batsman" name="bat_type[]" id="rightbat" value="Right Hand Bat">
                                    <label class="btn btn-outline-primary" for="rightbat">Right Hand Bat</label>&nbsp;

                                    <input type="checkbox" class="btn-check child-bat" data-parent="batsman" name="bat_type[]" id="leftbat" value="Left Hand Bat">
                                    <label class="btn btn-outline-primary" for="leftbat">Left Hand Bat</label>
                                </div>

                                <div class="mb-3">
                                    <h5>Bowler</h5>

                                    <input type="checkbox" class="btn-check parent" name="roles[]" id="bowler" value="Bowler">
                                    <label class="btn btn-outline-primary" for="bowler">Bowler</label>
                                    &nbsp;&nbsp;&nbsp;

                                    <input type="checkbox" class="btn-check child-bowl" data-parent="bowler" name="bowl_type[]" id="rightfast" value="Right-Arm-Fast">
                                    <label class="btn btn-outline-primary" for="rightfast">Right-Arm-Fast</label>&nbsp;

                                    <input type="checkbox" class="btn-check child-bowl" data-parent="bowler" name="bowl_type[]" id="rightmedium" value="Right-Arm-Medium">
                                    <label class="btn btn-outline-primary" for="rightmedium">Right-Arm-Medium</label>&nbsp;

                                    <input type="checkbox" class="btn-check child-bowl" data-parent="bowler" name="bowl_type[]" id="rightoff" value="Right-Arm-Off-Break">
                                    <label class="btn btn-outline-primary" for="rightoff">Right-Arm-Off-Break</label>&nbsp;

                                    <input type="checkbox" class="btn-check child-bowl" data-parent="bowler" name="bowl_type[]" id="rightleg" value="Right-Arm-Leg-Break">
                                    <label class="btn btn-outline-primary" for="rightleg">Right-Arm-Leg-Break</label>&nbsp;

                                    <input type="checkbox" class="btn-check child-bowl" data-parent="bowler" name="bowl_type[]" id="leftfast" value="Left-Arm-Fast">
                                    <label class="btn btn-outline-primary" for="leftfast">Left-Arm-Fast</label>&nbsp;

                                    <input type="checkbox" class="btn-check child-bowl" data-parent="bowler" name="bowl_type[]" id="leftmedium" value="Left-Arm-Medium">
                                    <label class="btn btn-outline-primary" for="leftmedium">Left-Arm-Medium</label>&nbsp;

                                    <input type="checkbox" class="btn-check child-bowl" data-parent="bowler" name="bowl_type[]" id="leftorthodox" value="Left-Arm-Orthodox">
                                    <label class="btn btn-outline-primary" for="leftorthodox">Left-Arm-Orthodox</label>
                                </div>
                                
                                <h5>Wicket Keeper</h5>

                                <input type="checkbox" class="btn-check parent" name="roles[]" id="wk" value="Wicket Keeper">
                                <label class="btn btn-outline-primary" for="wk">Wicket Keeper</label>&nbsp;&nbsp;&nbsp;

                                <input type="checkbox" class="btn-check child-wk" data-parent="wk" name="wk_type[]" id="wkbat" value="WK-Batsman">
                                <label class="btn btn-outline-primary" for="wkbat">WK-Batsman</label>

                                <script>
                                    // AUTO SELECT PARENT IF CHILD CLICKED
                                    $(".child-bat").on("change", function () {
                                        $("#batsman").prop("checked", true);
                                    });

                                    $(".child-bowl").on("change", function () {
                                        $("#bowler").prop("checked", true);
                                    });

                                    $(".child-wk").on("change", function () {
                                        $("#wk").prop("checked", true);
                                    });

                                    // ONLY ONE CHILD PER GROUP
                                    $(".child-bat").on("change", function () {
                                        $(".child-bat").not(this).prop("checked", false);
                                    });

                                    $(".child-bowl").on("change", function () {
                                        $(".child-bowl").not(this).prop("checked", false);
                                    });

                                    $(".child-wk").on("change", function () {
                                        $(".child-wk").not(this).prop("checked", false);
                                    });

                                    // UNCHECK PARENT → REMOVE CHILDREN
                                    $("#batsman").on("change", function() {
                                        if (!this.checked) $(".child-bat").prop("checked", false);
                                    });

                                    $("#bowler").on("change", function() {
                                        if (!this.checked) $(".child-bowl").prop("checked", false);
                                    });

                                    $("#wk").on("change", function() {
                                        if (!this.checked) $(".child-wk").prop("checked", false);
                                    });                                    
                                    </script>                                    
                                <br></br>
                                <button class="btn btn-primary lb w-25" name="btn" type="submit"><?php if(isset($_GET['id'])){ echo 'Update';}else { echo 'Insert';}?></button>
                            </form>
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
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
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
    $(document).ready(function(){

        // Restore roles checkboxes
        let roles = "<?php echo $roles_str ?? '' ?>".split(", ");
        roles.forEach(function(r){
            let id = r.replace(/ /g, '').toLowerCase(); 
            $("#"+id).prop("checked", true);
        });

        // Restore Bat Style
        let bat = "<?php echo $bat_str ?? '' ?>";
        if(bat != ""){
            $("input[value='"+bat+"']").prop("checked", true);
            $("#batsman").prop("checked", true);
        }

        // Restore Bowl Style
        let bowl = "<?php echo $bowl_str ?? '' ?>";
        if(bowl != ""){
            $("input[value='"+bowl+"']").prop("checked", true);
            $("#bowler").prop("checked", true);
        }

        // Restore Wicket Keeper role
        let wk_role = "<?php echo $roles_str ?? '' ?>";
        if (wk_role.includes("Wicket Keeper")) {
            $("#wk").prop("checked", true);
        }

        });
    </script>
    <script>
        <?php if(isset($_POST['btn'])){?>
            Swal.fire({
                icon: 'success',
                title: 'Player Added!',
                text: "New Player has been added successfully!",                
                confirmButtonText: 'Ok',                
                confirmButtonColor: '#0d6efd',                                 
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "manage_players.php";
                }
            });
        <?php } ?>
    </script>
</body>
</html>