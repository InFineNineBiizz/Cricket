<?php
    session_start();
    include "connection.php";
    $name=$tour_id=$sea_id=$venue=$sdate=$edate=$logo=$ctype=$max=$min=$reserve=$camt=$bidamt=$bprice=$img="";
    $tour_name=$s_date=$e_date=$sname="";

    if(isset($_GET['id']))
    {
        $id=$_GET['id'];
        $str="select a.*,t.name as tour_name,s.name as sname,s.sdate as start_date,s.edate as end_date 
        from auctions a,tournaments t,seasons s where a.tour_id=t.tid and a.sea_id=s.id and sea_id='".$id."'";
        
        $res=mysqli_query($conn,$str);
        $row=mysqli_fetch_assoc($res);
        $name=$row['name'];
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
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tour_name;?> - Manage</title>
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

/* Information Section Styles */
.information-section {
    margin-top: 2rem;
}

.url-card {
    background: linear-gradient(135deg, #c8dcf0 0%, #b5d4ed 100%);
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.url-header {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.url-title {
    font-weight: 600;
    color: #2c2c2c;
    margin: 0;
    font-size: 16px;
}

.url-link {
    color: #4a7ba7;
    text-decoration: none;
    word-break: break-all;
    flex: 1;
    margin: 0 15px;
}

.url-link:hover {
    text-decoration: underline;
}

.copy-btn {
    background-color: #5b7fd6;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    transition: background-color 0.3s;
    font-size: 14px;
}

.copy-btn:hover {
    background-color: #4a6bc5;
}

.copy-btn.copied {
    background-color: #28a745;
}

.description-box {
    background: white;
    border-left: 4px solid #5b7fd6;
    border-radius: 6px;
    padding: 18px 20px;
    color: #555;
    line-height: 1.6;
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

    .url-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .url-link {
        margin: 10px 0;
    }
    
    .copy-btn {
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

    <div class="main-wrapper">
        <!-- Sidebar -->
        <?php
            include 'sidebar.php';
        ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
                        
            <?php include "auc_topbar.php";?>
            
            <div class="container">
                
                <?php include "auc_header.php";?>

                <!-- Bottom Tabs -->
                <div class="bottom-tabs">
                    <a href="tour-manage.php?id=<?php echo $id;?>" class="bottom-tab">Teams</a>
                    <a href="player.php?id=<?php echo $id;?>" class="bottom-tab">Players</a>
                    <a href="information.php?id=<?php echo $id;?>" class="bottom-tab active">Information</a>
                </div>

                <!-- Information Section -->
                <div class="information-section">
                    <!-- TV Screen URL -->
                    <div class="url-card">
                        <div class="url-header">
                            <span class="url-title">TV Screen Url :</span>
                            <a href="https://crickhunt.com/auction/auction-of-dcpl-dream-class/tvdashboard" class="url-link" target="_blank">
                                https://crickhunt.com/auction/auction-of-dcpl-dream-class/tvdashboard
                            </a>
                            <button class="copy-btn" onclick="copyUrlToClipboard(this, 'https://crickhunt.com/auction/auction-of-dcpl-dream-class/tvdashboard')">
                                <i class="fas fa-link"></i> Copy
                            </button>
                        </div>
                        <div class="description-box">
                            Opens the auction TV dashboard. This is the main display screen usually shown to the audience.
                        </div>
                    </div>
                    
                    <!-- Control URL -->
                    <div class="url-card">
                        <div class="url-header">
                            <span class="url-title">Control Url :</span>
                            <a href="https://crickhunt.com/auction/auction-of-dcpl-dream-class/control" class="url-link" target="_blank">
                                https://crickhunt.com/auction/auction-of-dcpl-dream-class/control
                            </a>
                            <button class="copy-btn" onclick="copyUrlToClipboard(this, 'https://crickhunt.com/auction/auction-of-dcpl-dream-class/control')">
                                <i class="fas fa-link"></i> Copy
                            </button>
                        </div>
                        <div class="description-box">
                            Opens the control panel for the auction. Admins can manage the auction flow from here (start, pause, resume, end, etc.).
                        </div>
                    </div>
                    
                    <!-- Team Owner URL -->
                    <div class="url-card">
                        <div class="url-header">
                            <span class="url-title">Team Owner Url :</span>
                            <a href="https://crickhunt.com/auction/auction-of-dcpl-dream-class/team-Owner" class="url-link" target="_blank">
                                https://crickhunt.com/auction/auction-of-dcpl-dream-class/team-Owner
                            </a>
                            <button class="copy-btn" onclick="copyUrlToClipboard(this, 'https://crickhunt.com/auction/auction-of-dcpl-dream-class/team-Owner')">
                                <i class="fas fa-link"></i> Copy
                            </button>
                        </div>
                        <div class="description-box">
                            Opens the bidding interface for team owners. They can place and manage their bids here.
                        </div>
                    </div>
                    
                    <!-- Public URL -->
                    <div class="url-card">
                        <div class="url-header">
                            <span class="url-title">Public Url :</span>
                            <a href="https://crickhunt.com/auction/auction-of-dcpl-dream-class/detail" class="url-link" target="_blank">
                                https://crickhunt.com/auction/auction-of-dcpl-dream-class/detail
                            </a>
                            <button class="copy-btn" onclick="copyUrlToClipboard(this, 'https://crickhunt.com/auction/auction-of-dcpl-dream-class/detail')">
                                <i class="fas fa-link"></i> Copy
                            </button>
                        </div>
                        <div class="description-box">
                            Opens the public view of the auction. Anyone can watch the auction progress in real-time but cannot participate.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Copied: ' + text);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }

        function copyUrlToClipboard(btn, text) {
            navigator.clipboard.writeText(text).then(function() {
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                btn.classList.add('copied');
                
                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('copied');
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</body>
</html>