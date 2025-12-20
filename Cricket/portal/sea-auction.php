<?php
    session_start();
    include "connection.php";
    
    if(isset($_GET['id']))
    {   
        $id=$_GET['id'];
        $sql="delete from seasons where id=".$id."";
        mysqli_query($conn,$sql);
        header("location:sea-auction.php");
    }

    $sql="select * from tournaments";
    $resq=mysqli_query($conn,$sql);

    if(isset($_POST['btn']))
    {
        move_uploaded_file($_FILES['logo']['tmp_name'],"../assets/images/".$_FILES['logo']['name']);
        $img=$_FILES['logo']['name']; 

        $mt=$_POST['mtype'];
        if($mt == "Limited Overs")
        {
            $ov=$_POST['overs'];          
        }
        else
        {            
            $ov="NULL";            
        }

        $str="insert into seasons(name,tid,cname,gname,sdate,edate,btype,gtype,mtype,overs,logo) values('".$_POST['sname']."','".$_POST['tourname']."','".$_POST['cname']."','".$_POST['gname']."','".$_POST['sdate']."','".$_POST['edate']."','".$_POST['btype']."','".$_POST['gtype']."','".$_POST['mtype']."','".$ov."','".$img."')";
        $res=mysqli_query($conn,$str);
        
        header('location:organizers-list.php');
    }
    
    // Fetch all seasons from database
    $seasonSql = "SELECT s.*, t.name as tournament_name FROM seasons s LEFT JOIN tournaments t ON s.tid = t.tid ORDER BY s.sdate";
    $seasonResult = mysqli_query($conn, $seasonSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auction & Seasons | CrickFolio Portal</title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">
    <link rel="stylesheet" href="../assets/css/auction-style.css">
    <script src="../assets/script/jquery.min.js"></script>
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">
    <script src="../assets/script/sweetalert2.js"></script>

    <style>
        /* New Season Card Styles */
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

        /* Responsive */
        @media (max-width: 768px) {
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

        /* Success Toast */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
            align-items: center;
            gap: 10px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
        }

        .toast.show {
            display: flex;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <?php include 'topbar.php'; ?>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Seasons</h1>
            <button class="add-season-btn" onclick="window.location.href='add_season.php'">
                <i class="fas fa-plus-circle"></i> ADD SEASON
            </button>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <button class="tab-btn active" onclick="switchTab('ongoing')">
                <i class="fas fa-play-circle"></i> Ongoing
                <span class="tab-count" id="ongoingCount">0</span>
            </button>
            <button class="tab-btn" onclick="switchTab('upcoming')">
                <i class="fas fa-clock"></i> Upcoming
                <span class="tab-count" id="upcomingCount">0</span>
            </button>
            <button class="tab-btn" onclick="switchTab('completed')">
                <i class="fas fa-check-circle"></i> Completed
                <span class="tab-count" id="completedCount">0</span>
            </button>
        </div>

        <!-- Ongoing Seasons -->
        <div class="tab-content active" id="ongoingTab">
            <div class="seasons-grid" id="ongoingGrid">
                <!-- Ongoing seasons will be rendered here -->
            </div>
        </div>

        <!-- Upcoming Seasons -->
        <div class="tab-content" id="upcomingTab">
            <div class="seasons-grid" id="upcomingGrid">
                <!-- Upcoming seasons will be rendered here -->
            </div>
        </div>

        <!-- Completed Seasons -->
        <div class="tab-content" id="completedTab">
            <div class="seasons-grid" id="completedGrid">
                <!-- Completed seasons will be rendered here -->
            </div>
        </div>
    </main>

    <!-- Success Message Toast -->
    <div id="successToast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Action completed successfully!</span>
    </div>

    <script>
        let activeTab = 'ongoing';

        // Seasons array - populate from PHP
        let seasons = [<?php 
            mysqli_data_seek($seasonResult, 0);
            if(mysqli_num_rows($seasonResult) > 0) {
                while($seasonRow = mysqli_fetch_assoc($seasonResult)) {
                    $startDate = new DateTime($seasonRow['sdate']);
                    $endDate = new DateTime($seasonRow['edate']);
                    $today = new DateTime();
                    
                    if($today < $startDate) {
                        $status = 'upcoming';
                    } else if($today > $endDate) {
                        $status = 'completed';
                    } else {
                        $status = 'ongoing';
                    }
                    
                    $logoPath = !empty($seasonRow['logo']) ? '../assets/images/' . $seasonRow['logo'] : '';
                    
                    echo "{
                        id: '" . $seasonRow['id'] . "',
                        tid: '" . $seasonRow['tid'] . "',
                        name: '" . addslashes($seasonRow['name']) . "',
                        tournament: '" . addslashes($seasonRow['tournament_name']) . "',
                        status: '" . $status . "',
                        startDate: '" . $seasonRow['sdate'] . "',
                        endDate: '" . $seasonRow['edate'] . "',
                        totalTeams: '" . addslashes($seasonRow['cname']) . "',
                        groundName: '" . addslashes($seasonRow['gname']) . "',
                        ballType: '" . addslashes($seasonRow['btype']) . "',
                        groundType: '" . addslashes($seasonRow['gtype']) . "',
                        matchType: '" . addslashes($seasonRow['mtype']) . "',
                        overs: '" . addslashes($seasonRow['overs']) . "',
                        logo: '" . $logoPath . "',
                        logoName: '" . addslashes($seasonRow['logo']) . "'
                    },";
                }
            }
        ?>];

        // Initialize
        window.onload = function() {
            renderAllSeasons();
            updateTabCounts();
        };

        // Render all seasons
        function renderAllSeasons() {
            renderSeasonsByStatus('ongoing');
            renderSeasonsByStatus('upcoming');
            renderSeasonsByStatus('completed');
        }

        // Render seasons by status
        function renderSeasonsByStatus(status) {
            const grid = document.getElementById(`${status}Grid`);
            const filteredSeasons = seasons.filter(s => s.status === status);
            
            if (filteredSeasons.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No ${status} seasons</h3>
                        <p>There are no ${status} seasons at the moment.</p>
                    </div>
                `;
            } else {
                grid.innerHTML = filteredSeasons.map(season => 
                    createSeasonCard(season)
                ).join('');
            }
        }

        // Create season card HTML
        function createSeasonCard(season) {
            const seasonId = season.id;
            const isCompleted = season.status === 'completed';

            // Logo section
            const logoHtml = season.logo 
                ? `<img src="${season.logo}" alt="${season.name}">`
                : `<i class="fas fa-trophy"></i>`;

            // Action icons - Hide edit and delete for completed seasons
            const actionIcons = isCompleted 
                ? `
                    <div class="season-action-icons">
                        <button class="icon-btn btn-view-blue" onclick="viewSeasonDetails('${seasonId}')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                `
                : `
                    <div class="season-action-icons">
                        <button class="icon-btn btn-view-blue" onclick="viewSeasonDetails('${seasonId}')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="icon-btn btn-edit-green" onclick="window.location.href='add_season.php?id=${seasonId}'">
                            <i class="fas fa-pen"></i>
                        </button>
                        <a class="icon-btn btn-delete-red" href="javascript:void(0);" onclick="confirmDelete('${seasonId}')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                `;

            const actionButtons = `
                <div class="season-card-buttons">
                    <button class="card-btn btn-organizers" onclick="window.location.href='organizers.php?id=${seasonId}'">
                        <i class="fas fa-user-tie"></i> Organizers
                    </button>
                    <button class="card-btn btn-sponsors" onclick="window.location.href='sponsors.php?id=${seasonId}'">
                        <i class="fas fa-handshake"></i> Sponsors
                    </button>
                    <button class="card-btn btn-auction" onclick="window.location.href='tour-manage.php?id=${seasonId}'">
                        <i class="fas fa-gavel"></i> Auction
                    </button>
                </div>
            `;

            return `
                <div class="season-card-new">
                    <div class="season-card-header">
                        <div class="season-logo">
                            ${logoHtml}
                        </div>
                        ${actionIcons}
                    </div>
                    
                    <div class="season-card-body">
                        <h3 class="season-card-title">${season.name}</h3>
                        
                        <div class="season-card-info">
                            <div class="info-row">
                                <i class="fas fa-trophy"></i>
                                <span>${season.tournament}</span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>${season.totalTeams}</span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-baseball-ball"></i>
                                <span>${season.ballType}</span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-chess"></i>
                                <span>${season.matchType}${season.overs && season.overs !== 'NULL' ? ' - ' + season.overs : ''}</span>
                            </div>
                        </div>
                        
                        <div class="season-card-dates">
                            <div class="date-badge">
                                <small>Start Date:</small> ${formatDate(season.startDate)}
                            </div>
                            <div class="date-badge">
                                <small>End Date:</small> ${formatDate(season.endDate)}
                            </div>
                        </div>
                    </div>
                    
                    ${actionButtons}
                </div>
            `;
        }

        // View season details
        function viewSeasonDetails(seasonId) {
            window.location.href = 'sea-detail.php?id=' + seasonId;
        }

        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { day: '2-digit', month: 'short', year: 'numeric' };
            return date.toLocaleDateString('en-GB', options);
        }

        // Switch tabs
        function switchTab(tabName) {
            activeTab = tabName;

            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.tab-btn').classList.add('active');

            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(`${tabName}Tab`).classList.add('active');
        }

        // Update tab counts
        function updateTabCounts() {
            document.getElementById('ongoingCount').textContent = 
                seasons.filter(s => s.status === 'ongoing').length;
            document.getElementById('upcomingCount').textContent = 
                seasons.filter(s => s.status === 'upcoming').length;
            document.getElementById('completedCount').textContent = 
                seasons.filter(s => s.status === 'completed').length;
        }

        // Show toast
        function showToast(message, type = 'success') {
            const toast = document.getElementById('successToast');
            const toastMessage = document.getElementById('toastMessage');
            
            toastMessage.textContent = message;
            
            if (type === 'error') {
                toast.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
            } else {
                toast.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
            }
            
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    </script>

    <script>
        function confirmDelete(id) {
            Swal.fire({
            icon: 'warning',
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#dc3545'
            }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                icon:'success',
                title: 'Delete Success...',
                text: 'Record Deleted Successfully!',                
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
                willClose: () => {                    
                    window.location.href = "?id=" + id;
                }
                });            
            }
            });
        }
    </script>
</body>
</html>