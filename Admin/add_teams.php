<?php 
    include "connection.php";
    $name=$ttid=$logo=$img="";

    if(isset($_GET['id']))
    {
        $id=$_GET['id'];
        $str="select * from teams where id=".$id."";
        $res=mysqli_query($conn,$str);
        $row=mysqli_fetch_array($res);
        $name=$row['name'];
        $ttid=$row['tid'];
        $logo=$row['logo'];        
    }

    $sql="select * from tournaments";
    $tournaments_result = mysqli_query($conn,$sql);
    if (!$tournaments_result) 
    {
        die("<div class='alert alert-danger'>Error fetching tournaments: " . mysqli_error($conn) . "</div>");
    }

    if(isset($_POST['btn']))
    {       
        move_uploaded_file($_FILES['ttlogo']['tmp_name'],'images/'.$_FILES['ttlogo']['name']);
        $img=$_FILES['ttlogo']['name'];         

        $rem = 0;
        if (isset($_POST['tourname']) && $_POST['tourname'] !== '') {
            $selectedTid = (int) $_POST['tourname'];

            // try to find an active auction for this tournament
            $qr = "SELECT camt FROM auctions WHERE tour_id = " . $selectedTid . " AND status = 1 LIMIT 1";
            $qr1 = mysqli_query($conn, $qr);

            if ($qr1 && mysqli_num_rows($qr1) > 0) {
                $r = mysqli_fetch_assoc($qr1);
                $rem = isset($r['camt']) ? (int)$r['camt'] : 0;
            } else {
                // No auction found for selected tournament — fallback to 0
                $rem = 0;
            }
        } else {
            // no tournament selected (shouldn't happen because form requires it) — default 0
            $rem = 0;
        }


        if(!empty($_GET['id']))
        {                        
            $str="update teams set tid='".$_POST['tourname']."',name='".$_POST['ttname']."',logo='".$img."' where id=".$id."";
            $res=mysqli_query($conn,$str);
            header("location:manage_team.php");
        }        
        else
        {                     
            $str="insert into teams(tid,name,logo,remaining) values('".$_POST['tourname']."','".$_POST['ttname']."','".$img."','".$rem."')";
            $res=mysqli_query($conn,$str);            
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Add Teams | CrickFolio</title>
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
                        <h4 class="page-title fs-20 fw-semibold mb-0">Auction / Teams / Create</h4>
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
                                <h4 class="card-title mb-0 flex-grow-1">Add Team Details</h4>
                            </div>                                                                                                                              
                            <br>                            
                            <div class="card-body">
                            <form class="needs-validation" novalidate method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label lb" for="teamSelect">Tournament Name</label>
                                    <select class="form-select" data-choices name="tourname" id="teamSelect" required>
                                        <option disabled selected value="">Select Tournament Name</option>
                                        <?php while ($row = mysqli_fetch_assoc($tournaments_result)){ ?>
                                        <option value="<?php echo $row['tid'];?>"<?php if($ttid==$row['tid']){ echo 'selected';}?>><?php echo $row['name'];?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a team.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label lb" for="ttname">Team name</label>
                                    <input type="text" class="form-control" placeholder="Enter Team name" name="ttname" id="ttname" value="<?php echo $name;?>" required>
                                    <div class="invalid-feedback">
                                        please enter a team name
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label lb" for="ttlogo">logo</label>                                      
                                    <input type="file" class="form-control" name="ttlogo" id="ttlogo" required><?php echo $logo;?>
                                    <div class="invalid-feedback">
                                        Please choose a Team logo
                                    </div>
                                </div>                                
                                <button class="btn btn-primary lb w-25" name="btn" type="submit"><?php if(isset($_GET['id'])){ echo 'Update';}else { echo 'Insert';}?></button>
                            </form>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->            
                </div> <!-- end row-->
            </div>
        </div>
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
        <?php if(isset($_POST['btn'])):?>
            Swal.fire({
                icon: 'success',
                title: 'Team Added!',
                text: "New team has been added successfully!",                
                confirmButtonText: 'Ok',                
                confirmButtonColor: '#0d6efd',
                timer: 2500,
                timerProgressBar: true                
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "manage_team.php";
                }
            });
        <?php endif;?>
    </script>
</body>
</html>