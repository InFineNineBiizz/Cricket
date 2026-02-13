<?php
    session_start();
    if(!isset($_SESSION['user_email']))
    {
        header("location:../login.php");
    }
    include "connection.php";

    $st="select s.id as sid,a.*,t.name as tname from seasons s,auctions a,tournaments t where t.tid=a.tour_id and s.id=a.sea_id";
    $req=mysqli_query($conn,$st);
    $auction_count = mysqli_num_rows($req);
    
    // Check if user has created tournaments
    $tournament_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM tournaments WHERE created_by='".$_SESSION['user_id']."'");
    $tournament_result = mysqli_fetch_assoc($tournament_check);
    $has_tournaments = $tournament_result['count'] > 0;
    
    // Check if user has created seasons
    $season_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM seasons WHERE created_by='".$_SESSION['user_id']."'");
    $season_result = mysqli_fetch_assoc($season_check);
    $has_seasons = $season_result['count'] > 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo $title_name;?></title>

    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">    
    <link rel="stylesheet" href="../assets/css/home-style.css">
    <style>
        /* Empty State Styles */
        .empty-auctions {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            border: 2px dashed #cbd5e0;
        }

        .empty-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
        }

        .empty-icon i {
            font-size: 56px;
            color: white;
        }

        .empty-auctions h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 12px;
        }

        .empty-auctions p {
            font-size: 16px;
            color: #64748b;
            margin: 0 0 32px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .create-auction-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .create-auction-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
        }

        .create-auction-btn i {
            font-size: 18px;
        }

        /* Prerequisites Section */
        .prerequisites-container {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .prerequisites-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
        }

        .prerequisites-title i {
            color: #f59e0b;
        }

        .prerequisite-steps {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .prerequisite-step {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 10px;
            border-left: 4px solid #cbd5e0;
            transition: all 0.3s ease;
        }

        .prerequisite-step.completed {
            border-left-color: #10b981;
            background: #f0fdf4;
        }

        .prerequisite-step.pending {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }

        .step-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .step-icon.completed {
            background: #10b981;
            color: white;
        }

        .step-icon.pending {
            background: #f59e0b;
            color: white;
        }

        .step-content {
            flex: 1;
        }

        .step-title {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 4px;
        }

        .step-description {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }

        .step-action {
            flex-shrink: 0;
        }

        .step-action a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #f59e0b;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .step-action a:hover {
            background: #d97706;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
        }

        .step-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #10b981;
            color: white;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }

        /* Hide section header when no auctions */
        .section-header.hidden {
            display: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .prerequisite-step {
                flex-direction: column;
                align-items: flex-start;
            }

            .step-action {
                width: 100%;
            }

            .step-action a {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <?php
        include 'topbar.php';
    ?>

    <!-- Sidebar -->
    <?php
        include 'sidebar.php';
    ?>

    <!-- Main Content -->
    <main class="main-wrapper">
        <!-- Welcome Section -->
        <section class="welcome-section">
            <div class="welcome-content">
                <h1 class="welcome-title">Welcome back, <?php echo ucfirst($_SESSION['user_name']);?>! 👋</h1>
                <p class="welcome-subtitle">Here's what's happening with your auctions today</p>                
            </div>
        </section>

        <!-- Auctions Section -->
        <div class="section-header">
            <div class="section-title-group">
                <h2>Your Active Auctions</h2>
                <p>Manage and monitor your ongoing cricket auctions</p>
            </div>
            <a href="../upauction.php" class="view-all-btn">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="auction-grid">
            <?php if ($auction_count > 0) { ?>
                <!-- Auction Cards -->
                <?php while($row=mysqli_fetch_array($req)){?>
                <div class="auction-card">
                    <div class="auction-banner">
                        <div class="auction-badge">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                    <div class="auction-body">
                        <div class="auction-header-text">
                            <h3 class="auction-title"><?php echo $row['name'];?></h3>
                            <span class="auction-league">
                                <i class="fas fa-star"></i> <?php echo $row['tname'];?>
                            </span>
                        </div>
                        <div class="auction-meta">
                            <div class="meta-item">
                                <div class="meta-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <span><?php echo $row['venue'];?></span>
                            </div>
                            <div class="meta-item">
                                <div class="meta-icon">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <span><?php echo $row['sdate']." - ". $row['edate'];?></span>
                            </div>
                        </div>
                        <div class="auction-footer">
                            <a href="tour-manage.php?id=<?php echo $row['sid'];?>" class="manage-btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-pen"></i> Manage
                            </a>                        
                        </div>
                    </div>
                </div>
                <?php } ?>
            <?php } else { ?>
                <!-- Empty State -->
                <div class="empty-auctions">
                    <div class="empty-icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h2>No Active Auctions</h2>
                    
                    <?php if ($has_tournaments && $has_seasons) { ?>
                        <!-- User has both tournaments and seasons -->
                        <p>You haven't created any auctions yet. Start by creating your first cricket auction and begin building your dream team!</p>
                        <a href="../upauction.php" class="create-auction-btn">
                            <i class="fas fa-plus-circle"></i> Create Your First Auction
                        </a>
                    <?php } else { ?>
                        <!-- User is missing prerequisites -->
                        <p>Before creating an auction, you need to complete the following steps:</p>
                        
                        <div class="prerequisites-container">
                            <div class="prerequisites-title">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Setup Required</span>
                            </div>
                            
                            <div class="prerequisite-steps">
                                <!-- Tournament Step -->
                                <div class="prerequisite-step <?php echo $has_tournaments ? 'completed' : 'pending'; ?>">
                                    <div class="step-icon <?php echo $has_tournaments ? 'completed' : 'pending'; ?>">
                                        <?php if ($has_tournaments) { ?>
                                            <i class="fas fa-check"></i>
                                        <?php } else { ?>
                                            <i class="fas fa-trophy"></i>
                                        <?php } ?>
                                    </div>
                                    <div class="step-content">
                                        <h3 class="step-title">Create a Tournament</h3>
                                        <p class="step-description">Set up your cricket tournament with name, category, and logo</p>
                                    </div>
                                    <div class="step-action">
                                        <?php if ($has_tournaments) { ?>
                                            <span class="step-status">
                                                <i class="fas fa-check-circle"></i> Completed
                                            </span>
                                        <?php } else { ?>
                                            <a href="add-tournament.php">
                                                <i class="fas fa-plus"></i> Create Tournament
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>

                                <!-- Season Step -->
                                <div class="prerequisite-step <?php echo $has_seasons ? 'completed' : 'pending'; ?>">
                                    <div class="step-icon <?php echo $has_seasons ? 'completed' : 'pending'; ?>">
                                        <?php if ($has_seasons) { ?>
                                            <i class="fas fa-check"></i>
                                        <?php } else { ?>
                                            <i class="fas fa-calendar-alt"></i>
                                        <?php } ?>
                                    </div>
                                    <div class="step-content">
                                        <h3 class="step-title">Create a Season</h3>
                                        <p class="step-description">Define your tournament season with dates and details</p>
                                    </div>
                                    <div class="step-action">
                                        <?php if ($has_seasons) { ?>
                                            <span class="step-status">
                                                <i class="fas fa-check-circle"></i> Completed
                                            </span>
                                        <?php } else { ?>
                                            <a href="add_season.php">
                                                <i class="fas fa-plus"></i> Create Season
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </main>    
</body>
</html>