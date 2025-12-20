<?php
    session_start();    
    include "connection.php";

    if (!isset($_GET['tid']) || empty($_GET['tid'])) {
        header("Location: tournament.php");
        exit;
    }

    $tid = intval($_GET['tid']);

    // Handle delete tournament
    if (isset($_GET['delete'])) {
        $delete_tid = intval($_GET['delete']);
        mysqli_query($conn, "DELETE FROM tournaments WHERE tid = $delete_tid");
        header("Location: tournament.php");
        exit;
    }
    
    $q = mysqli_query($conn, "SELECT * FROM tournaments WHERE tid = $tid");
    if (mysqli_num_rows($q) == 0) {
        header("Location: tournament.php");
        exit;
    }

    $t = mysqli_fetch_assoc($q);

    // Fetch seasons for this tournament
    $seasonSql = "SELECT * FROM seasons WHERE tid = $tid ORDER BY sdate DESC";
    $seasonResult = mysqli_query($conn, $seasonSql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Tournament | CrickFolio Portal</title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">    
    <script src="../assets/script/sweetalert2.js"></script>

    <style>
        .main-wrapper {
            margin-left: 260px;
            margin-top: 70px;
            padding: 30px;
            min-height: calc(100vh - 70px);
            background: #f5f5f5;
        }

        /* Breadcrumb */
        .breadcrumb {
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            font-size: 14px;
        }

        .breadcrumb-link {
            color: #9ca3af;
            text-decoration: none;
        }

        .breadcrumb-link:hover {
            color: #1f2937;
        }

        .breadcrumb-current {
            font-weight: 600;
            color: #1f2937;
        }

        /* Tournament Header */
        .tournament-header-section {
            background: linear-gradient(135deg, #4b5563, #374151);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
        }

        .tournament-header-content {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .tournament-logo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .tournament-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tournament-logo i {
            font-size: 48px;
        }

        .tournament-name {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .tournament-meta i {
            color: #fbbf24;
        }

        .tournament-actions {
            display: flex;
            gap: 12px;
        }

        .action-btn {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
        }

        .btn-edit {
            background: #22c55e;
        }

        .btn-delete {
            background: #ef4444;
        }

        /* Seasons Section */
        .seasons-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .seasons-header h2 {
            font-size: 22px;
            color: #1f2937;
        }

        .btn-add-season {
            background: #2563eb;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .btn-add-season:hover {
            background: #1d4ed8;
        }

        /* Season Cards Grid */
        .seasons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        /* Season Card */
        .season-card-new {
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .season-card-new:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .season-card-header {
            position: relative;
            padding: 20px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .season-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }

        .season-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .season-logo i {
            font-size: 40px;
            color: white;
        }

        .season-action-icons {
            display: flex;
            gap: 8px;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s;
            color: white;
            text-decoration: none;
        }

        .btn-view-blue {
            background: #2563eb;
        }

        .btn-view-blue:hover {
            background: #1d4ed8;
        }

        .btn-edit-green {
            background: #16a34a;
        }

        .btn-edit-green:hover {
            background: #15803d;
        }

        .btn-delete-red {
            background: #dc2626;
        }

        .btn-delete-red:hover {
            background: #b91c1c;
        }

        .season-card-body {
            padding: 0 20px 20px;
        }

        .season-card-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
        }

        .season-card-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #6b7280;
            font-size: 14px;
        }

        .info-row i {
            color: #9ca3af;
            width: 18px;
        }

        .season-card-dates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        .date-badge {
            background: #f3f4f6;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            color: #6b7280;
        }

        .date-badge small {
            display: block;
            font-size: 10px;
            color: #9ca3af;
            margin-bottom: 2px;
        }

        .season-card-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
            border-top: 1px solid #e5e7eb;
        }

        .card-btn {
            padding: 15px;
            border: none;
            background: white;
            color: white;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-right: 1px solid #e5e7eb;
            text-decoration: none;
        }

        .card-btn:last-child {
            border-right: none;
        }

        .card-btn i {
            font-size: 14px;
        }

        .btn-organizers {
            background: #2563eb;
        }

        .btn-organizers:hover {
            background: #1d4ed8;
        }

        .btn-sponsors {
            background: #f59e0b;
        }

        .btn-sponsors:hover {
            background: #d97706;
        }

        .btn-auction {
            background: #dc2626;
        }

        .btn-auction:hover {
            background: #b91c1c;
        }

        /* Empty State */
        .empty-state {
            background: #fff;
            padding: 60px 20px;
            border-radius: 12px;
            text-align: center;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #374151;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 14px;
            color: #9ca3af;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-wrapper {
                margin-left: 0;
                padding: 20px;
            }

            .tournament-header-section {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .seasons-grid {
                grid-template-columns: 1fr;
            }

            .season-card-dates {
                grid-template-columns: 1fr;
            }

            .season-card-buttons {
                grid-template-columns: 1fr;
            }

            .card-btn {
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }

            .card-btn:last-child {
                border-bottom: none;
            }
        }
    </style>
</head>

<body>

    <?php include 'topbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-wrapper">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="tournament.php" class="breadcrumb-link">Tournament</a>
            <span>/</span>
            <span class="breadcrumb-current">
                <?php echo htmlspecialchars($t['name']); ?>
            </span>
        </div>

        <!-- Tournament Header -->
        <div class="tournament-header-section">

            <div class="tournament-header-content">
                <div class="tournament-logo">
                    <?php if (!empty($t['logo'])) { ?>
                        <img src="../assets/images/<?php echo htmlspecialchars($t['logo']); ?>" alt="Tournament Logo">
                    <?php } else { ?>
                        <i class="fas fa-trophy"></i>
                    <?php } ?>
                </div>

                <div>
                    <h1 class="tournament-name">
                        <?php echo htmlspecialchars($t['name']); ?>
                    </h1>
                    <div class="tournament-meta">
                        <i class="fas fa-award"></i>
                        <?php echo htmlspecialchars($t['category']); ?>
                    </div>
                </div>
            </div>

            <div class="tournament-actions">
                <a href="add-tournament.php?tid=<?php echo $t['tid']; ?>" class="action-btn btn-edit">
                    <i class="fas fa-pen"></i>
                </a>
                
                <a href="javascript:void(0)" class="action-btn btn-delete" onclick="deleteTournament(<?php echo $t['tid']; ?>);">
                    <i class="fas fa-trash"></i>
                </a>
            </div>

        </div>

        <!-- Seasons -->
        <div class="seasons-section">
            <div class="seasons-header">
                <h2>Seasons</h2>
                <a href="add_season.php?tid=<?php echo $t['tid']; ?>" class="btn-add-season">
                    <i class="fas fa-plus-circle"></i> Add Season
                </a>
            </div>

            <?php if (mysqli_num_rows($seasonResult) > 0) { ?>
                <div class="seasons-grid">
                    <?php while($season = mysqli_fetch_assoc($seasonResult)) { 
                        // Determine season status
                        $startDate = new DateTime($season['sdate']);
                        $endDate = new DateTime($season['edate']);
                        $today = new DateTime();
                        
                        if($today < $startDate) {
                            $status = 'upcoming';
                        } else if($today > $endDate) {
                            $status = 'completed';
                        } else {
                            $status = 'ongoing';
                        }
                        
                        $isCompleted = ($status === 'completed');
                    ?>
                        <div class="season-card-new">
                            <div class="season-card-header">
                                <div class="season-logo">
                                    <?php if (!empty($season['logo'])) { ?>
                                        <img src="../assets/images/<?php echo htmlspecialchars($season['logo']); ?>" alt="<?php echo htmlspecialchars($season['name']); ?>">
                                    <?php } else { ?>
                                        <i class="fas fa-trophy"></i>
                                    <?php } ?>
                                </div>
                                
                                <div class="season-action-icons">
                                    <a href="sea-detail.php?id=<?php echo $season['id']; ?>" class="icon-btn btn-view-blue" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (!$isCompleted) { ?>
                                        <a href="add_season.php?id=<?php echo $season['id']; ?>" class="icon-btn btn-edit-green" title="Edit Season">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="javascript:void(0);" class="icon-btn btn-delete-red" onclick="confirmDeleteSeason(<?php echo $season['id']; ?>)" title="Delete Season">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                            
                            <div class="season-card-body">
                                <h3 class="season-card-title"><?php echo htmlspecialchars($season['name']); ?></h3>
                                
                                <div class="season-card-info">
                                    <div class="info-row">
                                        <i class="fas fa-trophy"></i>
                                        <span><?php echo htmlspecialchars($t['name']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($season['cname']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <i class="fas fa-baseball-ball"></i>
                                        <span><?php echo htmlspecialchars($season['btype']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <i class="fas fa-chess"></i>
                                        <span>
                                            <?php 
                                                echo htmlspecialchars($season['mtype']);
                                                if ($season['overs'] && $season['overs'] != 'NULL') {
                                                    echo ' - ' . htmlspecialchars($season['overs']);
                                                }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="season-card-dates">
                                    <div class="date-badge">
                                        <small>Start Date:</small>
                                        <?php echo date('d M Y', strtotime($season['sdate'])); ?>
                                    </div>
                                    <div class="date-badge">
                                        <small>End Date:</small>
                                        <?php echo date('d M Y', strtotime($season['edate'])); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="season-card-buttons">
                                <a href="organizers.php?id=<?php echo $season['id']; ?>" class="card-btn btn-organizers">
                                    <i class="fas fa-user-tie"></i> Organizers
                                </a>
                                <a href="sponsors.php?id=<?php echo $season['id']; ?>" class="card-btn btn-sponsors">
                                    <i class="fas fa-handshake"></i> Sponsors
                                </a>
                                <a href="tour-manage.php?id=<?php echo $season['id']; ?>" class="card-btn btn-auction">
                                    <i class="fas fa-gavel"></i> Auction
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No seasons added yet</h3>
                    <p>Click the "Add Season" button to create your first season for this tournament.</p>
                </div>
            <?php } ?>
        </div>

    </main>

    <script>
        function deleteTournament(tid) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Tournament and all its seasons will be removed!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon:'success',
                        title: 'Delete Success...',
                        text: 'Tournament Deleted Successfully!',                
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        willClose: () => {                    
                            window.location.href = "?delete=" + tid;
                        }
                    });
                }
            });
        }

        function confirmDeleteSeason(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon:'success',
                        title: 'Delete Success...',
                        text: 'Season Deleted Successfully!',                
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        willClose: () => {                    
                            window.location.href = "sea-auction.php?id=" + id;
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>