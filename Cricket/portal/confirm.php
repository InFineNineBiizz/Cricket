<?php
    session_start();
    include "connection.php";
    
    $season_id = isset($_GET['id']) ? $_GET['id'] : (isset($_SESSION['add_season']) ? $_SESSION['add_season'] : 0);
    
    // Initialize variables
    $season = null;
    $org_result = null;
    $sponsor_result = null;
    
    // Fetch season details
    if($season_id > 0) {
        $season_sql = "SELECT t.name as tname,s.* FROM seasons s,tournaments t WHERE s.tid=t.tid and s.id = '$season_id'";
        $season_result = mysqli_query($conn, $season_sql);
        $season = mysqli_fetch_assoc($season_result);
        
        // Fetch organizers for this season - FIXED TABLE NAME
        $org_sql = "SELECT o.* FROM organizers o 
                    INNER JOIN season_organizer os ON o.id = os.organizer_id 
                    WHERE os.season_id = '$season_id'";
        $org_result = mysqli_query($conn, $org_sql);
        
        // Check for SQL errors
        if(!$org_result) {
            echo "<!-- Organizer Query Error: " . mysqli_error($conn) . " -->";
            $org_result = mysqli_query($conn, "SELECT * FROM organizers WHERE 1=0");
        }
        
        // Fetch sponsors for this season - FIXED TABLE NAME
        $sponsor_sql = "SELECT s.* FROM sponsors s 
                        INNER JOIN season_sponsors ss ON s.id = ss.sponsor_id 
                        WHERE ss.season_id = '$season_id'";
        $sponsor_result = mysqli_query($conn, $sponsor_sql);
        
        // Check for SQL errors
        if(!$sponsor_result) {
            echo "<!-- Sponsor Query Error: " . mysqli_error($conn) . " -->";
            $sponsor_result = mysqli_query($conn, "SELECT * FROM sponsors WHERE 1=0");
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Season | <?php echo $title_name;?></title>

    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">        
    <!-- <link rel="stylesheet" href="../assets/css/home-style.css"> -->
    <script src="../assets/script/jquery.min.js"></script>
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">    
    <script src="../assets/script/sweetalert2.js"></script>

    <style>
        :root{
            --primary: #f59e0b;
            --primary-dark:#d97706;
            --bg-light:#f8fafc;
            --border:#e5e7eb;
            --text:#1f2937;
            --success:#10b981;
            --error:#ef4444;
            --blue:#2563eb;
        }

        body{
            background: var(--bg-light);
        }

        .page-wrapper{
            margin-left: 260px;
            padding-top: 80px;
            padding: 30px;
        }

        .page-header{
            display:flex;
            justify-content: space-between;
            align-items:center;
            margin-bottom: 25px;
        }

        .page-header h4{
            font-size: 18px;
            font-weight: 700;
            color: grey;
        }
        
        .card{
            background:#fff;
            border-radius:14px;
            padding:30px;
            box-shadow:0 10px 25px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }

        .card h3{
            font-size:18px;
            margin-bottom:20px;
            font-weight:600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Stepper */
        .stepper{
            display:flex;
            align-items:center;
            gap:0;
            margin-bottom:35px;
            background:#fff;
            padding:20px 30px;
            border-radius:12px;
            border:1px solid #e5e7eb;
        }

        .step{
            display:flex;
            align-items:center;
            gap:10px;
            position:relative;
            flex:1;
        }

        .step:last-child{
            flex:0;
        }

        .step span{
            font-size:15px;
            font-weight:500;
            color:#6b7280;
            white-space:nowrap;
        }

        .circle{
            width:32px;
            height:32px;
            border-radius:50%;
            border:2px solid #d1d5db;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:14px;
            font-weight:600;
            color:#6b7280;
            background:#fff;
        }

        .step.completed .circle{
            background:#10b981;
            border-color:#10b981;
            color:#fff;
        }

        .step.completed .circle i{
            font-size: 14px;
        }

        .step.active .circle{
            background: #f59e0b;
            border-color: #f59e0b;
            color:#fff;
        }

        .step.active span{
            color:#111827;
            font-weight:600;
        }

        .line{
            flex:1;
            height:1px;
            background:#10b981;
            margin-left:14px;
        }

        .step:last-child .line{
            display:none;
        }

        /* Season Card */
        .season-card{
            display: flex;
            gap: 20px;
            padding: 20px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .season-logo{
            width: 120px;
            height: 120px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .season-logo img{
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .season-info{
            flex: 1;
        }

        .season-info h2{
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
        }

        .season-details{
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .detail-item{
            display: flex;
            align-items: center;
            gap: 10px;
            color: #6b7280;
            font-size: 14px;
        }

        .detail-item i{
            color: #9ca3af;
            font-size: 16px;
            width: 20px;
        }

        .date-badges{
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .date-badge{
            background: #f3f4f6;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            color: #6b7280;
        }

        /* Edit Button */
        .edit-btn{
            background: transparent;
            border: none;
            color: #6b7280;
            cursor: pointer;
            font-size: 18px;
            padding: 5px;
            transition: all 0.3s;
        }

        .edit-btn:hover{
            color: #f59e0b;
        }

        /* Details Section */
        .details-section{
            margin-top: 15px;
        }

        /* Grid container for detail cards */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .detail-card{
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
        }

        .detail-card:hover{
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .detail-card h4{
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .detail-row{
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
            color: #6b7280;
            font-size: 14px;
        }

        .detail-row:last-child{
            margin-bottom: 0;
        }

        .detail-row i{
            color: #9ca3af;
            font-size: 16px;
            width: 20px;
        }

        .no-data{
            text-align: center;
            color: #9ca3af;
            padding: 40px;
            font-size: 14px;
            background: #f9fafb;
            border-radius: 8px;
        }

        /* Footer Buttons */
        .footer-actions{
            display:flex;
            justify-content: space-between;
            margin-top:30px;
        }

        .btn-outline{
            background: #fff;
            border: 1px solid var(--border);
            color: #f59e0b;
            padding:12px 28px;
            font-size:15px;
            border-radius:10px;
            cursor:pointer;
            font-weight:600;
            transition: all 0.3s;
        }

        .btn-outline:hover{
            background: #fff7ed;
            border-color: #f59e0b;
        }

        .btn-primary{
            background: #f59e0b;
            border:none;
            color:#fff;
            padding:12px 28px;
            font-size:15px;
            border-radius:10px;
            cursor:pointer;
            font-weight:600;
            transition: all 0.3s;
        }

        .btn-primary:hover{
            background: #d97706;
        }

        @media(max-width:900px){
            .season-card{
                flex-direction: column;
            }
            .season-details{
                grid-template-columns: 1fr;
            }
            .details-grid{
                grid-template-columns: 1fr;
            }
            .page-wrapper{
                margin-left:0;
            }
        }

    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <br><br><br>
    <div class="page-wrapper">
        
        <div class="page-header">
            <h4>Seasons / Confirm</h4>
        </div>

        <!-- Stepper -->
        <div class="stepper">
            <div class="step completed">
                <div class="circle"><i class="fas fa-check"></i></div>
                <span>Season Detail</span>
                <div class="line"></div>
            </div>

            <div class="step completed">
                <div class="circle"><i class="fas fa-check"></i></div>
                <span>Organizer Details</span>
                <div class="line"></div>
            </div>

            <div class="step completed">
                <div class="circle"><i class="fas fa-check"></i></div>
                <span>Sponsor Details</span>
                <div class="line"></div>
            </div>

            <div class="step active">
                <div class="circle">4</div>
                <span>Confirm</span>
            </div>
        </div>

        <!-- Season Details Card -->
        <div class="card">
            <h3>
                Season Details
                <button class="edit-btn" onclick="window.location.href='add_season.php?id=<?php echo $season_id; ?>'">
                    <i class="fas fa-pen"></i>
                </button>
            </h3>

            <?php if($season): ?>
            <div class="season-card">
                <div class="season-logo">
                    <?php if(!empty($season['logo'])): ?>
                        <img src="../assets/images/<?php echo htmlspecialchars($season['logo']); ?>" alt="Season Logo">
                    <?php else: ?>
                        <i class="fas fa-trophy" style="font-size: 48px; color: white;"></i>
                    <?php endif; ?>
                </div>

                <div class="season-info">
                    <h2><?php echo htmlspecialchars($season['name']); ?></h2>
                    
                    <div class="season-details">
                        <div class="detail-item">
                            <i class="fas fa-trophy"></i>
                            <span><?php echo htmlspecialchars($season['tname'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($season['gname'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-baseball-ball"></i>
                            <span><?php echo htmlspecialchars($season['mtype'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo htmlspecialchars($season['overs'] ?? 'N/A'); ?></span>
                        </div>
                    </div>

                    <div class="date-badges">
                        <div class="date-badge">
                            <strong>Start:</strong> <?php echo date('d/m/Y', strtotime($season['sdate'])); ?>
                        </div>
                        <div class="date-badge">
                            <strong>End:</strong> <?php echo date('d/m/Y', strtotime($season['edate'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="no-data">No season details available</div>
            <?php endif; ?>
        </div>

        <!-- Organizer Details Card -->
        <div class="card">
            <h3>
                Organizer Details
                <button class="edit-btn" onclick="window.location.href='organizers-list.php?id=<?php echo $season_id; ?>'">
                    <i class="fas fa-pen"></i>
                </button>
            </h3>

            <div class="details-section">
                <?php 
                $org_count = 0;
                if($org_result && mysqli_num_rows($org_result) > 0):
                ?>
                <div class="details-grid">
                    <?php while($organizer = mysqli_fetch_assoc($org_result)): 
                        $org_count++;
                    ?>
                    <div class="detail-card">
                        <h4><?php echo htmlspecialchars($organizer['name']); ?></h4>
                        <div class="detail-row">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($organizer['email']); ?></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($organizer['number']); ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-users" style="font-size: 32px; margin-bottom: 10px; opacity: 0.3;"></i>
                    <p>No organizer details available</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sponsor Details Card -->
        <div class="card">
            <h3>
                Sponsor Details
                <button class="edit-btn" onclick="window.location.href='sponsor_details.php?id=<?php echo $season_id; ?>'">
                    <i class="fas fa-pen"></i>
                </button>
            </h3>

            <div class="details-section">
                <?php 
                $sponsor_count = 0;
                if($sponsor_result && mysqli_num_rows($sponsor_result) > 0):
                ?>
                <div class="details-grid">
                    <?php while($sponsor = mysqli_fetch_assoc($sponsor_result)): 
                        $sponsor_count++;
                    ?>
                    <div class="detail-card">
                        <h4><?php echo htmlspecialchars($sponsor['name']); ?></h4>
                        <?php if(isset($sponsor['type']) && !empty($sponsor['type'])): ?>
                        <div class="detail-row">
                            <i class="fas fa-tag"></i>
                            <span><?php echo htmlspecialchars($sponsor['type']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if(isset($sponsor['title']) && !empty($sponsor['title'])): ?>
                        <div class="detail-row">
                            <i class="fas fa-heading"></i>
                            <span><?php echo htmlspecialchars($sponsor['title']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($sponsor['email']); ?></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($sponsor['number']); ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-handshake" style="font-size: 32px; margin-bottom: 10px; opacity: 0.3;"></i>
                    <p>No sponsor details available</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="footer-actions">
            <button type="button" class="btn-outline" onclick="window.location.href='<?php if(isset($season_id)){ echo 'sponsor_details.php?id='.$season_id;}else{ echo 'sponsor_details.php';}?>'">
                Previous
            </button>
            <button type="button" class="btn-primary" onclick="confirmSeason()">
                Confirm & Finish
            </button>
        </div>
    </div>

    <script>
        function confirmSeason() {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "Season <?php if(isset($season_id)){echo "Updated";}else{echo "Created";}?> Successfully!",
                timer: 2000,
                showConfirmButton: false,
                timerProgressBar:true,
                willClose: () => {                    
                    window.location.href = "sea-auction.php";
                }    
            });
        }
    </script>
</body>
</html>