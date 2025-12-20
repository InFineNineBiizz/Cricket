<?php 
    session_start();
    include "connection.php";
    $tid=$name=$logo=$cname=$gname=$sdate=$edate=$btype=$gtype=$mtype=$over=$img="";

    $str="select * from tournaments";
    $resq=mysqli_query($conn,$str);

    if(isset($_GET['id']))
    {
        $id=$_GET['id'];
        $sql="select * from seasons where id='".$id."'";
        $res=mysqli_query($conn,$sql);
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Season Details | CrickFolio</title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fef6e4;
            min-height: 100vh;
        }

        /* Main Container */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .content-wrapper {
            flex: 1;
            margin-left: 0;
            width: 100%;
        }

        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Tournament Top Bar */
        .tournament-topbar {
            background: white;
            border-bottom: 2px solid #e0e0e0;
            padding: 0;
            margin-bottom: 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .tournament-topbar-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            gap: 0;
            padding: 0 2rem;
        }

        .topbar-tab {
            padding: 1.25rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            color: #95a5a6;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
        }

        .topbar-tab:hover {
            color: #7f8c8d;
            background: #fef6e4;
        }

        .topbar-tab.active {
            color: #2c3e50;
            border-bottom-color: #f5a623;
        }

        /* Tournament Header Card */
        .tournament-header-card {
            background: linear-gradient(135deg, #5a6c7d 0%, #4a5568 100%);
            border-radius: 15px;
            padding: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }

        .tournament-badge-corner {
            position: absolute;
            top: 0;
            right: 0;
            background: linear-gradient(135deg, #6b9bd1 0%, #4a7ba7 100%);
            color: white;
            padding: 0.5rem 2rem;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1px;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 15% 100%);
            border-radius: 0 15px 0 0;
        }

        .tournament-logo-circle {
            width: 140px;
            height: 140px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .tournament-logo-circle img {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }

        .tournament-header-info {
            flex: 1;
        }

        .tournament-header-info h1 {
            color: white;
            font-size: 2rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .tournament-details-row {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .detail-item-inline {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            font-size: 0.95rem;
        }

        .detail-item-inline i {
            width: 18px;
            opacity: 0.9;
        }

        .tournament-info-right {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            color: white;
        }

        .info-right-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .info-label {
            opacity: 0.9;
        }

        .info-value {
            font-weight: 700;
        }

        .copy-icon {
            cursor: pointer;
            opacity: 0.8;
            margin-left: 0.25rem;
        }

        .copy-icon:hover {
            opacity: 1;
        }

        .tournament-header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-icon-action {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-view-tournament {
            background: #5b6e8d;
            color: white;
        }

        .btn-view-tournament:hover {
            background: #4a5568;
            transform: translateY(-2px);
        }

        .btn-edit-tournament {
            background: #4caf50;
            color: white;
        }

        .btn-edit-tournament:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        /* Bottom Tabs */
        .bottom-tabs {
            display: flex;
            gap: 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .bottom-tab {
            flex: 1;
            padding: 1rem 1.5rem;
            text-align: center;
            font-size: 0.95rem;
            font-weight: 600;
            color: #5a6c7d;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .bottom-tab:hover {
            background: #fef6e4;
            color: #4a5568;
        }

        .bottom-tab.active {
            color: #4a7ba7;
            border-bottom-color: #4a7ba7;
            background: #f8f9fa;
        }

        /* Teams Section */
        .teams-section {
            margin-top: 2rem;
        }

        .section-header {
            margin-bottom: 2rem;
        }

        .section-header h2 {
            font-size: 1.5rem;
            color: #2c3e50;
            font-weight: 700;
        }

        .section-header h2 span {
            color: #f5a623;
        }

        /* Teams Grid */
        .teams-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        /* Team Card */
        .team-card {
            background: linear-gradient(135deg, #2c3e50 0%, #1a1f2e 100%);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .team-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.05) 0%, transparent 60%);
            pointer-events: none;
        }

        .team-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(0,0,0,0.2);
        }

        .team-card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        .team-logo {
            width: 70px;
            height: 70px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 0.5rem;
        }

        .team-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .team-card-header h3 {
            font-size: 1.5rem;
            color: white;
            font-weight: 700;
        }

        .team-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-team-action {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .btn-edit {
            background: #4caf50;
            color: white;
        }

        .btn-edit:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
        }

        .btn-delete {
            background: #f44336;
            color: white;
        }

        .btn-delete:hover {
            background: #da190b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
        }

        /* ===== NEW STYLES FOR SEASON CARD ===== */
        .season-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            border: 1px solid #e8e8e8;
            position: relative;
        }

        .season-card-actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .btn-card-action {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .btn-card-edit {
            background: #22c55e;
            color: white;
        }

        .btn-card-edit:hover {
            background: #16a34a;
            transform: translateY(-2px);
        }

        .btn-card-delete {
            background: #ef4444;
            color: white;
        }

        .btn-card-delete:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .season-card-content {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }

        .season-logo-container {
            flex-shrink: 0;
        }

        .season-logo-box {
            width: 140px;
            height: 140px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 50%, #fbbf24 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 0;
            box-shadow: 0 4px 10px rgba(251, 191, 36, 0.2);
        }

        .season-logo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }

        .season-info {
            flex: 1;
        }

        .season-title {
            font-size: 1.75rem;
            color: #1f2937;
            font-weight: 700;
            margin-bottom: 1.25rem;
        }

        .season-details {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .season-detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .season-detail-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
        }

        .season-detail-icon i {
            font-size: 1rem;
        }

        .season-detail-text {
            font-size: 1rem;
            color: #6b7280;
            font-weight: 400;
        }

        .season-dates {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .season-date-badge {
            background: #e5e7eb;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            color: #4b5563;
            font-weight: 500;
        }

        .season-actions {
            display: flex;
            gap: 0.75rem;
        }

        .season-action-btn {
            padding: 0.75rem 1.75rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-organizers {
            background: #3b82f6;
            color: white;
        }

        .btn-organizers:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-sponsors {
            background: #fbbf24;
            color: white;
        }

        .btn-sponsors:hover {
            background: #f59e0b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }

        .btn-auction {
            background: #ef4444;
            color: white;
        }

        .btn-auction:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .tournament-header-card {
                padding: 1.5rem;
            }

            .tournament-logo-circle {
                width: 100px;
                height: 100px;
            }

            .tournament-logo-circle img {
                width: 60px;
                height: 60px;
            }

            .tournament-header-info h1 {
                font-size: 1.75rem;
            }

            .season-card-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .season-details {
                align-items: center;
            }

            .season-dates {
                justify-content: center;
            }

            .season-actions {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
                margin: 1rem auto;
            }

            .tournament-topbar-container {
                padding: 0 1rem;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .topbar-tab {
                padding: 1rem 1.5rem;
                font-size: 0.9rem;
            }

            .tournament-header-card {
                flex-direction: column;
                text-align: center;
                padding: 1.5rem;
            }

            .tournament-info-right {
                width: 100%;
                align-items: center;
            }

            .tournament-header-actions {
                justify-content: center;
            }

            .bottom-tabs {
                flex-direction: column;
            }

            .bottom-tab {
                border-bottom: 1px solid #e0e0e0;
                border-left: 3px solid transparent;
            }

            .bottom-tab.active {
                border-bottom-color: #e0e0e0;
                border-left-color: #4a7ba7;
            }

            .teams-grid {
                grid-template-columns: 1fr;
            }

            .team-card {
                flex-direction: column;
                gap: 1rem;
            }

            .team-card-header {
                width: 100%;
                justify-content: center;
            }

            .season-card {
                padding: 1.5rem;
            }

            .season-card-actions {
                top: 0.5rem;
                right: 0.5rem;
            }

            .btn-card-action {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .season-logo-box {
                width: 120px;
                height: 120px;
            }

            .season-title {
                font-size: 1.5rem;
            }

            .season-dates {
                flex-direction: column;
                width: 100%;
            }

            .season-date-badge {
                width: 100%;
                text-align: center;
            }

            .season-actions {
                flex-direction: column;
                width: 100%;
            }

            .season-action-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <?php
        include 'topbar.php';
    ?>

    <div class="main-wrapper">
        <!-- Sidebar -->
        <?php
            include 'sidebar.php';
        ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Tournament Top Bar -->
            <div class="tournament-topbar">
                <div class="tournament-topbar-container">
                    <a href="sea-detail.php?id=<?php echo $id;?>" class="topbar-tab active">
                        Season Detail
                    </a>
                    <a href="organizers.php?id=<?php echo $id;?>" class="topbar-tab">
                        Organizers
                    </a>
                    <a href="sponsors.php?id=<?php echo $id;?>" class="topbar-tab">
                        Sponsors
                    </a>
                    <a href="tour-manage.php?id=<?php echo $id;?>" class="topbar-tab">
                        Auction
                    </a>
                </div>
            </div>
            
            <div class="container">
                <!-- Season Card -->
                <div class="season-card">
                    <!-- Edit and Delete buttons in top right corner -->
                    <div class="season-card-actions">
                        <button class="btn-card-action btn-card-edit"><a href="add_season.php?id=<?php echo $id;?>">
                            <i class="fas fa-pen"></i></a>
                        </button>
                        <button class="btn-card-action btn-card-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <div class="season-card-content">
                        <div class="season-logo-container">
                            <div class="season-logo-box">
                                <img src="../assets/images/<?php echo $logo;?>" alt="<?php echo $sname;?> Logo" />
                            </div>
                        </div>

                        <div class="season-info">
                            <h1 class="season-title"><?php echo $sname;?></h1>
                            
                            <div class="season-details">
                                <div class="season-detail-item">
                                    <div class="season-detail-icon">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <span class="season-detail-text"><?php while($rows=mysqli_fetch_assoc($resq)){ 
                                    if($tid==$rows['tid']){echo $rows['name'];}}?></span>
                                </div>

                                <div class="season-detail-item">
                                    <div class="season-detail-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <span class="season-detail-text"><?php echo $cname;?></span>
                                </div>

                                <div class="season-detail-item">
                                    <div class="season-detail-icon">
                                        <i class="fas fa-baseball-ball"></i>
                                    </div>
                                    <span class="season-detail-text"><?php echo $gname;?></span>
                                </div>

                                <div class="season-detail-item">
                                    <div class="season-detail-icon">
                                        <i class="fas fa-stopwatch"></i>
                                    </div>
                                    <span class="season-detail-text"><?php echo $mtype;?></span>
                                </div>
                            </div>

                            <div class="season-dates">
                                <div class="season-date-badge">
                                    Start Date: <?php echo $sdate;?>
                                </div>
                                <div class="season-date-badge">
                                    End Date: <?php echo $edate;?>
                                </div>
                            </div>

                            <div class="season-actions">
                                <button class="season-action-btn btn-organizers" onclick="window.location.href='organizers.php?id=<?php echo $id;?>'">
                                    Organizers
                                </button>
                                <button class="season-action-btn btn-sponsors" onclick="window.location.href='sponsors.php?id=<?php echo $id;?>'">
                                    Sponsors
                                </button>
                                <button class="season-action-btn btn-auction" onclick="window.location.href='tour-manage.php?id=<?php echo $id;?>'">
                                    Auction
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>