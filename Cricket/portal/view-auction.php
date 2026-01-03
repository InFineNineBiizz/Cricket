<?php
    session_start();
    include "connection.php";
    
    // Fetch auction details
    $aname=$tour_id=$sea_id=$venue=$sdate=$edate=$logo=$ctype=$max=$min=$reserve=$camt=$bidamt=$bprice=$img="";
    $tour_name=$s_date=$e_date=$sname=$tlogo=$t_id="";
    $team_count = 0;
    $player_count = 0;
    $is_group_auction = false;
    $group_name = "";
    $group_min = $group_max = $group_bid = $group_max_bid = 0;

    if(isset($_GET['id']))
    {
        $id=$_GET['id']; // This is season_id
        
        // First, get the auction for this season
        $auction_query = "SELECT a.* FROM auctions a WHERE a.sea_id = '".$id."'";
        $auction_res = mysqli_query($conn, $auction_query);
        
        if($auction_res && mysqli_num_rows($auction_res) > 0) {
            $auction_row = mysqli_fetch_assoc($auction_res);
            $auction_id = $auction_row['id'];
            
            // Now check if this auction has group auctions
            $group_check = "SELECT ga.gid FROM grp_auc ga WHERE ga.aid = '".$auction_id."' AND ga.status = 1 LIMIT 1";
            $group_check_res = mysqli_query($conn, $group_check);
            
            if($group_check_res && mysqli_num_rows($group_check_res) > 0) {
                // This auction has group auctions
                $is_group_auction = true;
                $group_row = mysqli_fetch_assoc($group_check_res);
                $group_id = $group_row['gid'];
                
                // Get group auction details
                $group_detail_query = "SELECT * FROM group_auction WHERE gid = '".$group_id."'";
                $group_detail_res = mysqli_query($conn, $group_detail_query);
                
                if($group_detail_res && mysqli_num_rows($group_detail_res) > 0) {
                    $group_detail = mysqli_fetch_assoc($group_detail_res);
                    $group_name = $group_detail['gname'];
                    $group_min = $group_detail['minplayer'];
                    $group_max = $group_detail['maxplayer'];
                    $group_bprice = $group_detail['bprice'];
                    $group_bid = $group_detail['bidamt'];
                    $group_max_bid = $group_detail['maxbid'];
                }
            }
        }
        
        // Get full auction details with tournament and season info
        $str="SELECT a.*,t.tid as tourid,t.name as tour_name,t.logo as tlogo,s.name as sname,s.sdate as start_date,s.edate as end_date 
        FROM auctions a,tournaments t,seasons s WHERE a.tour_id=t.tid AND a.sea_id=s.id AND a.sea_id='".$id."'";
        
        $res=mysqli_query($conn,$str);
        if($res && mysqli_num_rows($res) > 0) {
            $row=mysqli_fetch_assoc($res);
            $aname=$row['name'];
            $logo = $row['logo'];
            $tour_id=$row['tour_id'];
            $sea_id=$row['sea_id'];
            $venue=$row['venue'];        
            $sdate=$row['sdate'];
            $edate=$row['edate'];
            $ctype=$row['credit_type'];
            $min=$row['minplayer'];
            $max=$row['maxplayer'];
            $reserve=$row['resplayer'];
            $camt=$row['camt'];
            $bidamt=$row['bidamt'];
            $bprice=$row['bprice'];
            $tour_name=$row['tour_name'];
            $s_date=$row['start_date'];
            $e_date=$row['end_date'];
            $sname=$row['sname'];
            $tlogo=$row['tlogo'];
            $t_id=$row['tourid'];
            
            // Get team count
            $team_query = "SELECT COUNT(*) as count FROM teams WHERE season_id = '".$id."' AND status = 1";
            $team_result = mysqli_query($conn, $team_query);
            if($team_result) {
                $team_row = mysqli_fetch_assoc($team_result);
                $team_count = $team_row['count'];
            }
            
            // Get player count
            $player_query = "SELECT COUNT(*) as count FROM season_players WHERE season_id = '".$id."'";
            $player_result = mysqli_query($conn, $player_query);
            if($player_result) {
                $player_row = mysqli_fetch_assoc($player_result);
                $player_count = $player_row['count'];
            }
        } else {
            header("Location: sea-auction.php?id=$id");
            exit;
        }
    } else {
        header("Location: sea-auction.php?id=$id");
        exit;
    }
    
    // Format dates
    $auction_start = date('d M Y h:i A', strtotime($sdate));
    $auction_end = date('d M Y h:i A', strtotime($edate));
    $season_start = date('d M Y', strtotime($s_date));
    $season_end = date('d M Y', strtotime($e_date));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $aname . " | CrickFolio Portal"; ?></title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #2d3748;
        }

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
            margin: 0 auto;
            padding: 2.5rem 2rem;
        }

        /* Tournament Topbar */
        .tournament-topbar {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0;
            margin-bottom: 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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
            color: #a0aec0;
            text-decoration: none;
            transition: all 0.2s ease;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
            position: relative;
        }

        .topbar-tab:hover {
            color: #718096;
            background: rgba(0, 0, 0, 0.02);
        }

        .topbar-tab.active {
            color: #2d3748;
            border-bottom-color: #f6ad55;
            background: transparent;
        }

        /* Back Button */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            background: white;
            color: #4a5568;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .back-button:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
            color: #2d3748;
            transform: translateX(-4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .back-button i {
            font-size: 1rem;
            transition: transform 0.3s ease;
        }

        .back-button:hover i {
            transform: translateX(-2px);
        }

        /* Hero Section - Original Layout Preserved */
        .auction-hero {
            background: linear-gradient(135deg, #5a6c7d 0%, #4a5568 100%);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }

        .auction-hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .auction-hero-content {
            display: flex;
            align-items: center;
            gap: 2rem;
            position: relative;
            z-index: 1;
        }

        .auction-logo-large {
            width: 140px;
            height: 140px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .auction-logo-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .auction-hero-text {
            flex: 1;
        }

        .auction-hero-text h1 {
            color: white;
            font-size: 1.75rem;
            margin-bottom: 0.3rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .auction-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 0.95rem;
            margin-bottom: 0.85rem;
            line-height: 1.3;
        }

        .auction-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: #ffffff !important;
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.55rem 1rem;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: 500;
        }

        .meta-item i {
            font-size: 1rem;
            opacity: 1;
            color: #ffffff !important;
        }

        .meta-item span {
            color: #ffffff !important;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3.5rem;
        }

        .stat-card {
            background: #667eea !important;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.4);
        }

        .stat-card:nth-child(2) {
            background: #4caf50 !important;
        }

        .stat-card:nth-child(2):hover {
            box-shadow: 0 12px 40px rgba(76, 175, 80, 0.4);
        }

        .stat-icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: rgba(255, 255, 255, 0.25);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-icon-wrapper i {
            font-size: 2.5rem;
            color: white;
        }

        .stat-value {
            font-size: 3.5rem;
            font-weight: 800;
            color: white !important;
            margin-bottom: 0.5rem;
            line-height: 1;
            letter-spacing: -1px;
        }

        .stat-label {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.95) !important;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
        }

        /* Empty State */
        .empty-state {
            background: linear-gradient(135deg, #fff5e6 0%, #ffe5cc 100%);
            border: 2px solid #ffb366;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 3rem;
        }

        .empty-state-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #ff9933, #ff6600);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 8px 20px rgba(255, 102, 0, 0.3);
        }

        .empty-state-icon i {
            font-size: 2rem;
            color: white;
        }

        .empty-state h3 {
            color: #b35900;
            font-size: 1.35rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .empty-state p {
            color: #996300;
            font-size: 1rem;
            line-height: 1.6;
        }

        /* Section Header */
        .section-header {
            margin-bottom: 2.5rem;
        }

        .section-header h2 {
            font-size: 2rem;
            color: #2d3748;
            font-weight: 800;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .section-header p {
            color: #718096;
            font-size: 1.05rem;
            font-weight: 500;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .info-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .info-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f7fafc;
        }

        .info-icon {
            width: 65px;
            height: 65px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .info-icon.blue {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }

        .info-icon.green {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }

        .info-icon.orange {
            background: linear-gradient(135deg, #fa709a, #fee140);
        }

        .info-card-header h3 {
            font-size: 1.1rem;
            color: #4a5568;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-card-body {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f7fafc;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            font-size: 0.95rem;
            color: #718096;
            font-weight: 600;
        }

        .info-value {
            font-size: 1.15rem;
            color: #2d3748;
            font-weight: 700;
        }

        .info-value.highlight {
            color: #4facfe;
            font-size: 1.4rem;
        }

        /* Timeline Section */
        .timeline-section {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 3rem;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .timeline {
            position: relative;
            padding: 2.5rem 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 32px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, #4facfe, #00f2fe);
        }

        .timeline-item {
            position: relative;
            padding-left: 90px;
            margin-bottom: 2.5rem;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-icon {
            position: absolute;
            left: 0;
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
            border: 3px solid white;
        }

        .timeline-content {
            background: #f7fafc;
            border-radius: 16px;
            padding: 1.75rem;
            border-left: 4px solid #4facfe;
        }

        .timeline-content h4 {
            font-size: 1.25rem;
            color: #2d3748;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .timeline-content p {
            color: #718096;
            font-size: 1rem;
            font-weight: 500;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1.25rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.875rem;
            padding: 1.125rem 2.25rem;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .btn-action::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-action:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-action span {
            position: relative;
            z-index: 1;
        }

        .btn-action i {
            font-size: 1.15rem;
            position: relative;
            z-index: 1;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #fa709a, #fee140);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(250, 112, 154, 0.4);
        }

        @media (max-width: 1024px) {
            .auction-hero-content {
                gap: 1.75rem;
            }

            .auction-logo-large {
                width: 120px;
                height: 120px;
            }

            .auction-hero-text h1 {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1.5rem 1rem;
            }

            .tournament-topbar-container {
                padding: 0 1rem;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .topbar-tab {
                padding: 1rem 1.25rem;
                font-size: 0.9rem;
            }

            .auction-hero {
                padding: 1.25rem 1.5rem;
            }

            .auction-hero-content {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .auction-logo-large {
                width: 100px;
                height: 100px;
            }

            .auction-hero-text h1 {
                font-size: 1.35rem;
            }

            .auction-subtitle {
                font-size: 0.85rem;
            }

            .auction-meta {
                justify-content: center;
                gap: 0.75rem;
            }

            .meta-item {
                font-size: 0.85rem;
                padding: 0.5rem 0.9rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-value {
                font-size: 3rem;
            }

            .timeline::before {
                left: 17px;
            }

            .timeline-item {
                padding-left: 70px;
            }

            .timeline-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'topbar.php'; ?>

    <div class="main-wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="content-wrapper">
            <?php include "auc_topbar.php";?>

            <div class="container">
                <a href="tour-manage.php?id=<?php echo $id; ?>" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Management</span>
                </a>

                <!-- Hero Section -->
                <div class="auction-hero">
                    <div class="auction-hero-content">
                        <div class="auction-logo-large">
                            <?php if($tlogo): ?>
                                <img src="../assets/images/<?php echo $tlogo; ?>" alt="<?php echo $tour_name; ?>">
                            <?php else: ?>
                                <i class="fas fa-trophy" style="font-size: 4rem; color: #667eea;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="auction-hero-text">
                            <h1><?php echo $aname; ?><?php if($is_group_auction) echo " - " . $group_name; ?></h1>
                            <p class="auction-subtitle"><?php echo $tour_name; ?> - <?php echo $sname; ?></p>
                            <div class="auction-meta">
                                <div class="meta-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo $venue; ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?php echo $season_start; ?> - <?php echo $season_end; ?></span>
                                </div>
                                <?php if($is_group_auction): ?>
                                <div class="meta-item">
                                    <i class="fas fa-layer-group"></i>
                                    <span>Group Auction</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-value"><?php echo $team_count; ?></div>
                        <div class="stat-label">Total Teams</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="stat-value"><?php echo $player_count; ?></div>
                        <div class="stat-label">Total Players</div>
                    </div>
                </div>

                <?php if($team_count == 0 && $player_count == 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3>No Data Available Yet</h3>
                    <p>Start building your auction by adding teams and players to get started!</p>
                </div>
                <?php endif; ?>

                <!-- Information Grid -->
                <div class="section-header">
                    <h2>Auction Details</h2>
                    <p>Complete information about the auction settings and rules</p>
                </div>

                <div class="info-grid">
                    <!-- Credit Information -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon blue">
                                <i class="fas fa-coins"></i>
                            </div>
                            <h3>Credit Details</h3>
                        </div>
                        <div class="info-card-body">
                            <div class="info-row">
                                <span class="info-label">Credit Type</span>
                                <span class="info-value"><?php echo ucfirst($ctype); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Credit Amount</span>
                                <span class="info-value highlight"><?php echo number_format($camt); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Bid Amount</span>
                                <span class="info-value"><?php echo number_format($bidamt); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Base Price</span>
                                <span class="info-value"><?php echo number_format($bprice); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Player Limits -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon green">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <h3>Player Limits</h3>
                        </div>
                        <div class="info-card-body">
                            <div class="info-row">
                                <span class="info-label">Minimum Players</span>
                                <span class="info-value"><?php echo $min; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Maximum Players</span>
                                <span class="info-value highlight"><?php echo $max; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Reserve Players</span>
                                <span class="info-value"><?php echo $reserve; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Auction Schedule -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon orange">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3>Auction Schedule</h3>
                        </div>
                        <div class="info-card-body">
                            <div class="info-row">
                                <span class="info-label">Start Time</span>
                                <span class="info-value"><?php echo $auction_start; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">End Time</span>
                                <span class="info-value"><?php echo $auction_end; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if($is_group_auction): ?>
                <!-- Group Auction Details Section -->
                <div class="section-header">
                    <h2>Group Wise Base Price Details</h2>
                    <p>Additional group-specific auction settings</p>
                </div>

                <div class="info-grid">
                    <!-- Group Information -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon purple" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <h3>Group Details</h3>
                        </div>
                        <div class="info-card-body">
                            <div class="info-row">
                                <span class="info-label">Group Name</span>
                                <span class="info-value highlight"><?php echo $group_name; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Base Price</span>
                                <span class="info-value"><?php echo number_format($group_bprice); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Bid Amount</span>
                                <span class="info-value"><?php echo number_format($group_bid); ?></span>
                            </div>                            
                            <div class="info-row">
                                <span class="info-label">Max Bid</span>
                                <span class="info-value"><?php echo number_format($group_max_bid); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Group Player Limits -->
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon teal" style="background: linear-gradient(135deg, #1abc9c, #16a085);">
                                <i class="fas fa-user-friends"></i>
                            </div>
                            <h3>Group Player Limits</h3>
                        </div>
                        <div class="info-card-body">
                            <div class="info-row">
                                <span class="info-label">Minimum Players</span>
                                <span class="info-value"><?php echo $group_min; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Maximum Players</span>
                                <span class="info-value highlight"><?php echo $group_max; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Timeline -->
                <div class="timeline-section">
                    <div class="section-header">
                        <h2>Auction Timeline</h2>
                        <p>Key milestones and important dates</p>
                    </div>

                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-flag"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Season Start</h4>
                                <p><?php echo $season_start; ?></p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-gavel"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Auction Period</h4>
                                <p><?php echo $auction_start; ?> - <?php echo $auction_end; ?></p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Season End</h4>
                                <p><?php echo $season_end; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="tour-manage.php?id=<?php echo $id; ?>" class="btn-action btn-primary">
                        <i class="fas fa-users"></i>
                        <span>Manage Teams</span>
                    </a>
                    <a href="player.php?id=<?php echo $id; ?>" class="btn-action btn-success">
                        <i class="fas fa-user-friends"></i>
                        <span>Manage Players</span>
                    </a>                    
                </div>
            </div>
        </div>
    </div>
</body>
</html>