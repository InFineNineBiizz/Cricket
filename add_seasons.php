<?php 
    include "connection.php";
    $tid=$name=$logo=$cname=$gname=$sdate=$edate=$btype=$gtype=$mtype=$over=$img="";

    $sql="select * from tournaments";
    $resq=mysqli_query($conn,$sql);
                                                    
    if(isset($_GET['id']))
    {
        $id=$_GET['id'];
        $str="select * from seasons where id=".$id."";
        $res=mysqli_query($conn,$str);
        $row=mysqli_fetch_array($res);
        $tid=$row['tid'];
        $name=$row['name'];
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
        if(empty($_GET['id']))
        {    
            move_uploaded_file($_FILES['slogo']['tmp_name'],"images/".$_FILES['slogo']['name']);
            $img=$_FILES['slogo']['name']; 
            
            $mt=$_POST['mtype'];
            if($mt == "Limited Overs")
            {
                $ov=$_POST['overs'];          
            }
            else
            {            
                $ov="NULL";            
            }

            $str="insert into seasons(name,tid,cname,gname,sdate,edate,btype,gtype,mtype,overs,logo) values('".$_POST['sname']."','".$_POST['tname']."','".$_POST['cname']."','".$_POST['gname']."','".$_POST['sdate']."','".$_POST['edate']."','".$_POST['btype']."','".$_POST['gtype']."','".$_POST['mtype']."','".$ov."','".$img."')";
            $res=mysqli_query($conn,$str);            
        }
        else
        {
            move_uploaded_file($_FILES['slogo']['tmp_name'],"images/".$_FILES['slogo']['name']);
            $img=$_FILES['slogo']['name'];

            $mt=$_POST['mtype'];
            if($mt == "Limited Overs")
            {
                $ov=$_POST['overs'];          
            }
            else
            {            
                $ov="NULL";            
            }
            
            $str="update seasons set tid='".$_POST['tname']."',name='".$_POST['sname']."',logo='".$img."',cname='".$_POST['cname']."',gname='".$_POST['gname']."',sdate='".$_POST['sdate']."',edate='".$_POST['edate']."',btype='".$_POST['btype']."',gtype='".$_POST['gtype']."',mtype='".$_POST['mtype']."',overs='".$ov."' where id=".$id."";
            $res=mysqli_query($conn,$str);
            header('location:manage_seasons.php');
        }  
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Add Seasons | CrickFolio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <?php 
        include "links.php";
    ?>
    
</head>

<body>
    <div class="wrapper">

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
                        <h4 class="page-title fs-20 fw-semibold mb-0">Seasons / Create</h4>
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
                                <h4 class="card-title mb-0 flex-grow-1">Add Seasons</h4>
                            </div>                                                                
                            <br>                            
                            <div class="card-body">
                            <form id="myForm" class="needs-validation" method="POST" enctype="multipart/form-data" novalidate>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="name">Season Name</label>
                                            <input type="text" class="form-control" id="name" placeholder="Enter Season Name" name="sname" value="<?php echo $name;?>" required>
                                            <div class="invalid-feedback">
                                                Please Enter Season Name..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="tname">Tournaments</label>
                                            <select class="form-select" data-choices name="tname" id="tname" required>
                                                <option value="" selected disabled>Selct Tournament Name</option>
                                                <?php while($row=mysqli_fetch_assoc($resq)){?>
                                                    <option value="<?php echo $row['tid'];?>" <?php if($tid==$row['tid']){ echo 'selected';}?>><?php echo $row['name'];?></option>
                                                <?php }?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please Provide Tournament Name..
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="cname">City Name</label>
                                            <input type="text" class="form-control" id="cname" placeholder="Enter City Name" name="cname" value="<?php echo $cname;?>" required>
                                            <div class="invalid-feedback">
                                                Please Enter City Name..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="gname">Ground Name</label>
                                            <input type="text" class="form-control" id="gname" placeholder="Enter Ground Name" name="gname" value="<?php echo $gname;?>" required>
                                            <div class="invalid-feedback">
                                                Please Enter Ground Name..
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="sdate">Season Start Date</label>                                                
                                            <div class="input-group has-validation">
                                                <input type="text" class="form-control flatpickr-input" placeholder="Select Season Start Date" id="sdate" name="sdate" data-provider="flatpickr" data-date-format="Y-m-d" readonly="readonly" value="<?php echo $sdate;?>" required>
                                                <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                            </div>
                                            <div id="season_start_error" class="invalid-feedback d-none">
                                                Please Provide Season Start Date..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="edate">Season End Date</label>
                                            <div class="input-group has-validation">
                                                <input type="text" class="form-control flatpickr-input" placeholder="Select Season End Date" id="edate" name="edate" data-provider="flatpickr" data-date-format="Y-m-d" readonly="readonly" value="<?php echo $edate;?>" required>
                                                <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
                                            </div>
                                            <div id="season_end_error" class="invalid-feedback d-none">
                                                Please Provide Season End Date..
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="btype">Ball Type</label>
                                            <select class="form-select" data-choices name="btype" id="btype" required>
                                                <option value="" selected disabled>Select Ball Type</option>
                                                <option value="soft tennis" <?php if($btype=="soft tennis"){echo 'selected';}?>>Soft Tennis</option>
                                                <option value="hard tennis" <?php if($btype=="hard tennis"){echo 'selected';}?>>Hard Tennis</option>
                                                <option value="leather" <?php if($btype=="leather"){echo 'selected';}?>>Leather</option>
                                                <option value="plastic" <?php if($btype=="plastic"){echo 'selected';}?>>Plastic</option>
                                                <option value="other" <?php if($btype=="other"){echo 'selected';}?>>Other</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please Provide Ball Type..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="gtype">Ground Type</label>
                                            <select class="form-select" data-choices name="gtype" id="gtype" required>
                                                <option value="" selected disabled>Select Ground Type</option>
                                                <option value="ground" <?php if($gtype=="ground"){echo 'selected';}?>>Ground</option>
                                                <option value="box or turf" <?php if($gtype=="box or turf"){echo 'selected';}?>>Box Or Turf</option>
                                                <option value="gully" <?php if($gtype=="gully"){echo 'selected';}?>>Gully</option>                                                    
                                                <option value="other" <?php if($gtype=="other"){echo 'selected';}?>>Other</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please Provide Ground Type..
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="mtype">Match Type</label>
                                            <select class="form-select" data-choices name="mtype" id="mtype" required>
                                                <option value="" selected disabled>Select Match Type</option>
                                                <option value="T10" <?php if($mtype=="T10"){echo 'selected';}?>>T10</option>
                                                <option value="T20" <?php if($mtype=="T20"){echo 'selected';}?>>T20</option>
                                                <option value="OneDay 50" <?php if($mtype=="OneDay 50"){echo 'selected';}?>>OneDay 50</option>
                                                <option value="Limited Overs" <?php if($mtype=="Limited Overs"){echo 'selected';}?>>Limited Overs</option>
                                                <option value="Test Match" <?php if($mtype=="Test Match"){echo 'selected';}?>>Test Match</option>
                                            </select>
                                            <div class="invalid-feedback">
                                                Please Provide Match Type..
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3 d-none" id="overs-textbox-div">
                                            <label for="overs" class="form-label lb">Overs</label>
                                            <input type="text" class="form-control" id="overs" name="overs" placeholder="E.g., 35" value="<?php echo $over;?>" required>
                                            <div class="invalid-feedback">
                                                Please Provide Overs..
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label lb" for="slogo">Upload Logo</label>
                                            <input type="file" class="form-control" id="slogo" name="slogo" required><?php echo $logo;?>
                                            <div class="invalid-feedback">
                                                Please Choose Logo..
                                            </div>
                                        </div>
                                    </div>
                                </div>                                                                        
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
            title: 'Season Added!',
            text: "New season has been added successfully!",                
            confirmButtonText: 'Ok',                
            confirmButtonColor: '#0d6efd',            
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "manage_seasons.php";
            }
        });
    <?php endif;?>
</script>

</body>
</html>