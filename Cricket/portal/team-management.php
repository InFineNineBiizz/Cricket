<?php
    session_start();
    include("connection.php"); // Your database connection file

    // Get team ID from URL
    $team_id = isset($_GET['team_id']) ? intval($_GET['team_id']) : 0;

    if ($team_id == 0) {
        header("Location: tour-manage.php");
        exit();
    }

    // Handle player deletion
    if (isset($_GET['delete_tpid']) && isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        $delete_tpid = intval($_GET['delete_tpid']);
        
        // Get player details before deletion for updating team remaining credits
        $player_query = "SELECT sold_price FROM team_player WHERE tpid = ? AND tid = ?";
        $stmt = $conn->prepare($player_query);
        $stmt->bind_param("ii", $delete_tpid, $team_id);
        $stmt->execute();
        $player_result = $stmt->get_result();
        
        if ($player_result->num_rows > 0) {
            $player_data = $player_result->fetch_assoc();
            $sold_price = $player_data['sold_price'];
            
            // Delete the player from team_player table
            $delete_query = "DELETE FROM team_player WHERE tpid = ? AND tid = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("ii", $delete_tpid, $team_id);
            
            if ($stmt->execute()) {
                // Update team's remaining credits
                $update_team = "UPDATE teams SET remaining = remaining + ? WHERE id = ?";
                $stmt = $conn->prepare($update_team);
                $stmt->bind_param("ii", $sold_price, $team_id);
                $stmt->execute();
                
                // Set success message
                $_SESSION['delete_success'] = true;
                $_SESSION['deleted_player_price'] = $sold_price;
                
                // Redirect to refresh the page
                header("Location: team-management.php?team_id=" . $team_id);
                exit();
            } else {
                // Set error message
                $_SESSION['delete_error'] = true;
                header("Location: team-management.php?team_id=" . $team_id);
                exit();
            }
        } else {
            // Player not found
            $_SESSION['delete_error'] = true;
            header("Location: team-management.php?team_id=" . $team_id);
            exit();
        }
    }

    // Fetch team details with season and tournament info
    $team_query = "SELECT t.*, s.name as season_name, s.cname as city_name, 
                        tour.name as tournament_name, tour.category as tournament_category
                FROM teams t
                LEFT JOIN seasons s ON t.season_id = s.id
                LEFT JOIN tournaments tour ON s.tid = tour.tid
                WHERE t.id = ?";

    $stmt = $conn->prepare($team_query);
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $team_result = $stmt->get_result();

    if ($team_result->num_rows == 0) {
        header("Location: tour-manage.php");
        exit();
    }

    $team = $team_result->fetch_assoc();

    // Fetch all players sold to this team
    $players_query = "SELECT p.*, tp.sold_price, tp.tpid
                    FROM team_player tp
                    INNER JOIN players p ON tp.pid = p.id
                    WHERE tp.tid = ? AND tp.season_id = ? ";

    $stmt = $conn->prepare($players_query);
    $stmt->bind_param("ii", $team_id, $team['season_id']);
    $stmt->execute();
    $players_result = $stmt->get_result();

    $title_name = $team['name'] . " - Team Management | CrickFolio";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title_name; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">    
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">
    <script src="../assets/script/sweetalert2.js"></script>
    
    <style>
        /* Active state styling for sidebar */
        .nav-link.active {
            background-color: #f0f0f0;
            border-left: 4px solid #ff8c42;
            font-weight: 600;
            color: #ff8c42;
        }

        .nav-link.active .nav-icon {
            color: #ff8c42;
        }

        /* Breadcrumb Navigation */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            color: #4a5568;
        }

        .breadcrumb a {
            color: #f5a623;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb a:hover {
            color: #f5a623;
            text-decoration: underline;
        }

        .breadcrumb i {
            font-size: 0.7rem;
            color: #a0aec0;
        }

        .breadcrumb span {
            color: #1a202c;
            font-weight: 500;
        }

        /* Team Header Banner */
        .team-header {
            margin-bottom: 32px;
        }

        .team-banner {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            border-radius: 16px;
            padding: 40px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .team-banner::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            top: -150px;
            right: -150px;
        }

        .team-badge {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
            position: relative;
            z-index: 2;
            overflow: hidden;
        }

        .team-badge img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .team-badge i {
            font-size: 3.5rem;
            color: #f5a623;
        }

        .team-info {
            flex: 1;
            position: relative;
            z-index: 2;
        }

        .team-name {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .team-location {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
        }

        .team-location i {
            color: #f5a623;
        }

        .team-stats {
            display: flex;
            gap: 24px;
            margin-top: 16px;
        }

        .team-stat {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 8px;
            color: white;
        }

        .team-stat-label {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .team-stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            margin-top: 4px;
        }

        .team-league-badge {
            position: absolute;
            top: 24px;
            right: 80px;
            background: #f5a623;
            color: white;
            padding: 8px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 1px;            
            z-index: 2;
        }

        .edit-team-btn {
            position: absolute;
            top: 24px;
            right: 24px;
            width: 48px;
            height: 48px;
            background: #10b981;
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            z-index: 2;
        }

        .edit-team-btn:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        }

        /* Tabs */
        .tabs-container {
            display: flex;
            gap: 0;
            margin-bottom: 32px;
            border-bottom: 2px solid #e2e8f0;
        }

        .tab {
            padding: 14px 32px;
            font-size: 1rem;
            font-weight: 600;
            color: #718096;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            bottom: -2px;
        }

        .tab:hover {
            color: #f5a623;
            background: rgba(59, 130, 246, 0.05);
        }

        .tab.active {
            color: #f5a623;
            border-bottom-color: #f5a623;
        }

        /* Players Section */
        .players-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 28px;
        }

        .no-players {
            text-align: center;
            padding: 60px 20px;
            background: #f7fafc;
            border-radius: 12px;
            border: 2px dashed #cbd5e0;
        }

        .no-players i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 16px;
        }

        .no-players p {
            font-size: 1.1rem;
            color: #718096;
        }

        /* Players Grid */
        .players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
            gap: 24px;
        }

        /* Player Card */
        .player-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: flex-start;
            gap: 20px;
            position: relative;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .player-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            border-color: #cbd5e0;
        }

        .player-number {
            width: 40px;
            height: 40px;
            background: #f7fafc;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #4a5568;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .player-avatar {
            width: 80px;
            height: 80px;
            background: #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .player-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .player-avatar i {
            font-size: 2.5rem;
            color: #718096;
        }

        .player-details {
            flex: 1;
        }

        .player-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 12px;
        }

        .player-contact {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4a5568;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        .player-contact i {
            color: #718096;
            font-size: 0.9rem;
        }

        .player-role {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4a5568;
            font-size: 0.9rem;
            margin-bottom: 6px;
        }

        .player-role i {
            color: #718096;
            font-size: 0.85rem;
        }

        .player-price {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 8px;
        }

        .delete-btn {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 40px;
            height: 40px;
            background: #ef4444;
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .delete-btn:hover {
            background: #dc2626;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .players-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .team-banner {
                flex-direction: column;
                align-items: flex-start;
                padding: 28px 24px;
            }

            .team-badge {
                width: 90px;
                height: 90px;
            }

            .team-badge i {
                font-size: 2.5rem;
            }

            .team-name {
                font-size: 1.8rem;
            }

            .team-league-badge {
                position: static;
                display: inline-block;
                margin-top: 12px;
            }

            .edit-team-btn {
                top: 16px;
                right: 16px;
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .tabs-container {
                overflow-x: auto;
            }

            .tab {
                padding: 12px 24px;
                white-space: nowrap;
            }

            .players-grid {
                grid-template-columns: 1fr;
            }

            .player-card {
                padding: 20px;
                gap: 16px;
            }

            .player-avatar {
                width: 60px;
                height: 60px;
            }

            .player-avatar i {
                font-size: 2rem;
            }

            .player-name {
                font-size: 1.1rem;
            }

            .delete-btn {
                top: 12px;
                right: 12px;
                width: 36px;
                height: 36px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .player-card {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .player-number {
                margin-bottom: 8px;
            }

            .player-details {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .player-contact,
            .player-role {
                justify-content: center;
            }

            .delete-btn {
                position: static;
                width: 100%;
                margin-top: 16px;
            }
        }
    </style>
</head>

<body>
    <!-- Top Navigation -->
    <?php include("topbar.php"); ?>

    <!-- Sidebar -->
    <?php include("sidebar.php"); ?>

    <!-- Main Content -->
    <div class="main-wrapper">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="tournament.php"><?php echo htmlspecialchars($team['tournament_name']); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="sea-detail.php?id=<?php echo $team['season_id']; ?>"><?php echo htmlspecialchars($team['season_name']); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="tour-manage.php?id=<?php echo $team['season_id']; ?>">Teams</a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($team['name']); ?></span>
        </div>

        <!-- Team Header -->
        <div class="team-header">
            <div class="team-banner">
                <div class="team-badge">
                    <?php if (!empty($team['logo']) && file_exists($team['logo'])): ?>
                        <img src="<?php echo htmlspecialchars($team['logo']); ?>" alt="<?php echo htmlspecialchars($team['name']); ?>">
                    <?php else: ?>
                        <i class="fas fa-users"></i>
                    <?php endif; ?>
                </div>
                <div class="team-info">
                    <h1 class="team-name"><?php echo htmlspecialchars($team['name']); ?></h1>
                    <div class="team-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($team['city_name']); ?></span>
                    </div>
                    <div class="team-stats">
                        <div class="team-stat">
                            <div class="team-stat-label">Players</div>
                            <div class="team-stat-value"><?php echo $players_result->num_rows; ?></div>
                        </div>
                        <div class="team-stat">
                            <div class="team-stat-label">Credits Remaining</div>
                            <div class="team-stat-value">₹<?php echo number_format($team['remaining']); ?></div>
                        </div>
                    </div>
                </div>
                <div class="team-league-badge"><?php echo htmlspecialchars($team['tournament_name']); ?></div>                
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tab active">Players</div>
        </div>

        <!-- Players Section -->
        <div class="players-section">
            <h2 class="section-title">Squad (<?php echo $players_result->num_rows; ?> Players)</h2>

            <?php if ($players_result->num_rows > 0): ?>
                <!-- Players Grid -->
                <div class="players-grid">
                    <?php
                    $player_counter = 1;
                    while ($player = $players_result->fetch_assoc()):
                    ?>
                        <!-- Player Card -->
                        <div class="player-card">
                            <div class="player-number"><?php echo $player_counter++; ?></div>
                            <div class="player-avatar">
                                <?php if (!empty($player['logo']) && file_exists($player['logo'])): ?>
                                    <img src="<?php echo htmlspecialchars($player['logo']); ?>" alt="<?php echo htmlspecialchars($player['fname'] . ' ' . $player['lname']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="player-details">
                                <h3 class="player-name"><?php echo htmlspecialchars($player['fname'] . ' ' . $player['lname']); ?></h3>
                                <div class="player-contact">
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo htmlspecialchars($player['number']); ?></span>
                                </div>
                                <div class="player-role">
                                    <i class="fas fa-circle-info"></i>
                                    <span><?php echo htmlspecialchars($player['role']); ?></span>
                                </div>
                                <div class="player-role">
                                    <i class="fas fa-baseball-ball"></i>
                                    <span>
                                        <?php
                                        $batting = !empty($player['batstyle']) ? $player['batstyle'] : 'N/A';
                                        $bowling = !empty($player['bowlstyle']) ? $player['bowlstyle'] : 'N/A';
                                        echo htmlspecialchars($batting . ' | ' . $bowling);
                                        ?>
                                    </span>
                                </div>
                                <?php if (!empty($player['tname'])): ?>
                                    <div class="player-role">
                                        <i class="fas fa-shirt"></i>
                                        <span><?php echo htmlspecialchars($player['tname'] . ' #' . $player['tnumber']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="player-price">
                                    Sold: ₹<?php echo number_format($player['sold_price']); ?>
                                </div>
                            </div>
                            <button type="button" class="delete-btn" 
                                    data-tpid="<?php echo $player['tpid']; ?>" 
                                    data-player-name="<?php echo htmlspecialchars($player['fname'] . ' ' . $player['lname'], ENT_QUOTES); ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <!-- No Players Message -->
                <div class="no-players">
                    <i class="fas fa-users-slash"></i>
                    <p>No players have been sold to this team yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include jQuery if not already included -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Wait for everything to load
        window.addEventListener('load', function() {
            console.log('Page loaded');
            
            // Find all delete buttons
            var deleteButtons = document.querySelectorAll('.delete-btn');
            console.log('Found delete buttons:', deleteButtons.length);
            
            // Add click handler to each delete button
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var tpid = this.getAttribute('data-tpid');
                    var playerName = this.getAttribute('data-player-name');
                    
                    console.log('Delete clicked:', tpid, playerName);
                    
                    // Show SweetAlert confirmation
                    Swal.fire({
                        title: 'Delete Player?',
                        html: 'Are you sure you want to permanently remove <strong>' + playerName + '</strong> from this team?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, Delete Player',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        focusCancel: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading
                            Swal.fire({
                                title: 'Deleting...',
                                text: 'Please wait while we remove the player.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Redirect to same page with delete parameters
                            window.location.href = 'team-management.php?team_id=<?php echo $team_id; ?>&delete_tpid=' + tpid + '&confirm=yes';
                        }
                    });
                });
            });
            
            // Tab switching
            var tabElements = document.querySelectorAll('.tab');
            tabElements.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    tabElements.forEach(function(t) {
                        t.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });
        });

        // Show success message after deletion
        <?php if (isset($_SESSION['delete_success']) && $_SESSION['delete_success']): ?>
            window.addEventListener('load', function() {
                Swal.fire({
                    title: 'Deleted!',
                    html: 'Player has been removed successfully.<br>₹<?php echo number_format($_SESSION['deleted_player_price']); ?> has been refunded to team credits.',
                    icon: 'success',                    
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            });
            <?php 
                unset($_SESSION['delete_success']);
                unset($_SESSION['deleted_player_price']);
            ?>
        <?php endif; ?>

        // Show error message if deletion failed
        <?php if (isset($_SESSION['delete_error']) && $_SESSION['delete_error']): ?>
            window.addEventListener('load', function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to remove player. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#ef4444',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            });
            <?php unset($_SESSION['delete_error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>