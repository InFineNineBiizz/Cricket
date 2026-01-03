<?php
    session_start();
    if(!isset($_SESSION['user_email']))
    {
        header("location:../login.php");
    }
    include "connection.php";

    $st="select s.id as sid,a.*,t.name as tname from seasons s,auctions a,tournaments t where t.tid=a.tour_id and s.id=a.sea_id";
    $req=mysqli_query($conn,$st);        
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo $title_name;?></title>

    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">    
    <link rel="stylesheet" href="../assets/css/home-style.css">
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
            <!-- Auction Card 1 -->
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
            <?php }?>
        </div>
    </main>    
</body>
</html>