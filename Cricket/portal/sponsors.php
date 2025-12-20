<?php
    session_start();
    include "connection.php";

    // ================= AJAX HANDLER =================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json');
        
        $action = $_POST['action'];

        // FETCH SPONSORS
        if ($action === 'fetch_sponsors') {
            $season_id = intval($_POST['season_id']);
            
            $query = "SELECT s.*, ss.id as ss_id 
                      FROM season_sponsors ss 
                      INNER JOIN sponsors s ON s.id = ss.sponsor_id 
                      WHERE ss.season_id = '$season_id'
                      ORDER BY s.id";
            
            $result = mysqli_query($conn, $query);
            
            if (!$result) {
                echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
                exit;
            }
            
            $sponsors = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $sponsors[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'data' => $sponsors]);
            exit;
        }

        // ADD SPONSOR
        if ($action === 'add_sponsor') {
            $season_id = intval($_POST['season_id']);
            $name = mysqli_real_escape_string($conn, trim($_POST['name']));
            $title = mysqli_real_escape_string($conn, trim($_POST['title']));
            $type = mysqli_real_escape_string($conn, trim($_POST['type']));
            $email = mysqli_real_escape_string($conn, trim($_POST['email']));
            $number = mysqli_real_escape_string($conn, trim($_POST['number']));
            
            // Validate inputs
            if (empty($name) || strlen($name) < 2) {
                echo json_encode(['status' => 'error', 'message' => 'Name must be at least 2 characters']);
                exit;
            }
            
            if (empty($title)) {
                echo json_encode(['status' => 'error', 'message' => 'Title is required']);
                exit;
            }
            
            if (empty($type)) {
                echo json_encode(['status' => 'error', 'message' => 'Type is required']);
                exit;
            }
            
            // Handle logo upload
            $logo = '';
            if (!empty($_FILES['logo']['name'])) {
                $logo = time() . '_' . $_FILES['logo']['name'];
                $upload_path = "../assets/images/" . $logo;
                
                if (!move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                    echo json_encode(['status' => 'error', 'message' => 'Logo upload failed']);
                    exit;
                }
            }
            
            // Insert into sponsors table
            $insert_query = "INSERT INTO sponsors (name, title, type, email, number, logo) 
                             VALUES ('$name', '$title', '$type', '$email', '$number', '$logo')";
            
            if (mysqli_query($conn, $insert_query)) {
                $sponsor_id = mysqli_insert_id($conn);
                
                // Insert into season_sponsors table
                $link_query = "INSERT INTO season_sponsors (sponsor_id, season_id) 
                               VALUES ('$sponsor_id', '$season_id')";
                
                if (mysqli_query($conn, $link_query)) {
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'Sponsor added successfully',
                        'sponsor_id' => $sponsor_id
                    ]);
                } else {
                    // Rollback: Delete the sponsor if linking fails
                    mysqli_query($conn, "DELETE FROM sponsors WHERE id = '$sponsor_id'");
                    echo json_encode(['status' => 'error', 'message' => 'Failed to link sponsor to season: ' . mysqli_error($conn)]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add sponsor: ' . mysqli_error($conn)]);
            }
            exit;
        }

        // GET SINGLE SPONSOR
        if ($action === 'get_sponsor') {
            $sponsor_id = intval($_POST['sponsor_id']);
            
            $query = "SELECT * FROM sponsors WHERE id = '$sponsor_id'";
            $result = mysqli_query($conn, $query);
            
            if ($row = mysqli_fetch_assoc($result)) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Sponsor not found']);
            }
            exit;
        }

        // UPDATE SPONSOR
        if ($action === 'update_sponsor') {
            $sponsor_id = intval($_POST['sponsor_id']);
            $name = mysqli_real_escape_string($conn, trim($_POST['name']));
            $title = mysqli_real_escape_string($conn, trim($_POST['title']));
            $type = mysqli_real_escape_string($conn, trim($_POST['type']));
            $email = mysqli_real_escape_string($conn, trim($_POST['email']));
            $number = mysqli_real_escape_string($conn, trim($_POST['number']));
            
            // Validate inputs
            if (empty($name) || strlen($name) < 2) {
                echo json_encode(['status' => 'error', 'message' => 'Name must be at least 2 characters']);
                exit;
            }
            
            if (empty($title)) {
                echo json_encode(['status' => 'error', 'message' => 'Title is required']);
                exit;
            }
            
            if (empty($type)) {
                echo json_encode(['status' => 'error', 'message' => 'Type is required']);
                exit;
            }
            
            // Handle logo upload if new logo provided
            $logo_update = '';
            if (!empty($_FILES['logo']['name'])) {
                $logo = time() . '_' . $_FILES['logo']['name'];
                $upload_path = "../assets/images/" . $logo;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                    $logo_update = ", logo='$logo'";
                }
            }
            
            // Update sponsor
            $update_query = "UPDATE sponsors 
                             SET name = '$name', title = '$title', type = '$type', 
                                 email = '$email', number = '$number' 
                                 $logo_update
                             WHERE id = '$sponsor_id'";
            
            if (mysqli_query($conn, $update_query)) {
                echo json_encode(['status' => 'success', 'message' => 'Sponsor updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update sponsor: ' . mysqli_error($conn)]);
            }
            exit;
        }

        // DELETE SPONSOR
        if ($action === 'delete_sponsor') {
            $sponsor_id = intval($_POST['sponsor_id']);
            $season_id = intval($_POST['season_id']);
            
            // Hard delete: Permanently remove from database
            $delete_link_query = "DELETE FROM season_sponsors 
                                  WHERE sponsor_id = '$sponsor_id' AND season_id = '$season_id'";
            
            $delete_sponsor_query = "DELETE FROM sponsors WHERE id = '$sponsor_id'";
            
            if (mysqli_query($conn, $delete_link_query) && mysqli_query($conn, $delete_sponsor_query)) {
                echo json_encode(['status' => 'success', 'message' => 'Sponsor deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete sponsor: ' . mysqli_error($conn)]);
            }
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }

    // ================= PAGE LOAD =================
    $id = "";

    if(isset($_GET['id'])) {
        $id = $_GET['id'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sponsors | CrickFolio</title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>    
    <link rel="stylesheet" href="../assets/css/sweetalert2.css">    
    <script src="../assets/script/sweetalert2.js"></script>
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

        .action-header {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .btn-action {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-add {
            background: #5b7fd6;
            color: white;
        }

        .btn-add:hover {
            background: #4a6bc5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 127, 214, 0.3);
        }

        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: left;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .empty-state-text {
            color: #7f8c8d;
            font-size: 1rem;
        }

        .sponsors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .sponsor-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
        }

        .sponsor-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        .sponsor-type-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-platinum { background: #E5E4E2; color: #4A4A4A; }
        .badge-gold { background: #FFD700; color: #8B6914; }
        .badge-silver { background: #C0C0C0; color: #4A4A4A; }
        .badge-bronze { background: #CD7F32; color: #5C3A1E; }

        .sponsor-logo {
            width: 100%;
            height: 120px;
            object-fit: contain;
            margin-bottom: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
        }

        .sponsor-info h3 {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .sponsor-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .sponsor-detail i {
            width: 16px;
            color: #5b7fd6;
        }

        .sponsor-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
        }

        .btn-edit, .btn-delete {
            flex: 1;
            padding: 0.6rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        .btn-edit {
            background: #5b7fd6;
            color: white;
        }

        .btn-edit:hover {
            background: #4a6bc5;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .modal-overlay {
            display: none !important;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999999;
            padding: 20px;
            overflow-y: auto;
        }

        .modal-overlay.show {
            display: flex !important;
            align-items: flex-start;
            justify-content: center;
            padding-top: 50px;
            padding-bottom: 50px;
        }

        .modal {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 900px;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            position: relative;
        }

        .modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #7f8c8d;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s;
            line-height: 1;
            padding: 0;
        }

        .btn-close:hover {
            color: #2c3e50;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-group label .required {
            color: #e74c3c;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
            background: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #5b7fd6;
        }

        .form-control.valid {
            border-color: #28a745;
        }

        .form-control.invalid {
            border-color: #dc3545;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        select.form-control {
            cursor: pointer;
        }

        .upload-section {
            margin-bottom: 1.5rem;
        }

        .upload-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-area:hover {
            border-color: #5b7fd6;
            background: #f0f4ff;
        }

        .upload-area.has-image {
            border-style: solid;
            border-color: #28a745;
        }

        .image-preview {
            max-width: 200px;
            max-height: 150px;
            margin: 1rem auto;
            display: none;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 8px;
        }

        .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            position: sticky;
            bottom: 0;
            background: white;
        }

        .btn-footer {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-close-footer {
            background: #e67e22;
            color: white;
        }

        .btn-close-footer:hover {
            background: #d35400;
        }

        .btn-submit {
            background: #5b7fd6;
            color: white;
        }

        .btn-submit:hover {
            background: #4a6bc5;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 127, 214, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
                margin: 1rem auto;
            }

            .tournament-topbar-container {
                padding: 0 1rem;
                overflow-x: auto;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .sponsors-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .sponsors-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .sponsor-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid #e0e0e0;
        }

        .sponsor-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .sponsor-card-actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
            z-index: 10;
        }

        .btn-card-edit, .btn-card-delete {
            width: 48px;
            height: 48px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-card-edit {
            background: #28a745;
        }

        .btn-card-edit:hover {
            background: #218838;
            transform: scale(1.05);
        }

        .btn-card-delete {
            background: #dc3545;
        }

        .btn-card-delete:hover {
            background: #c82333;
            transform: scale(1.05);
        }

        .sponsor-card-content {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }

        .sponsor-main-info {
            flex: 1;
            min-width: 0;
        }

        .sponsor-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            padding-right: 120px;
        }

        .sponsor-title-text {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 0.75rem;
        }

        .sponsor-contact-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .sponsor-contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #5a6c7d;
            font-size: 0.95rem;
        }

        .sponsor-contact-item i {
            width: 20px;
            color: #7f8c8d;
        }

        .sponsor-type-tag {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            background: #e9ecef;
            color: #495057;
        }

        .tag-auction-sponsor { 
            background: #d4edda; 
            color: #155724; 
        }

        .tag-banner-sponsor { 
            background: #d1ecf1; 
            color: #0c5460; 
        }

        .tag-man-of-the-match-award { 
            background: #fff3cd; 
            color: #856404; 
        }

        .tag-tournament-champions-trophy { 
            background: #f8d7da; 
            color: #721c24; 
        }

        .tag-tshirt-sponsor { 
            background: #e2e3e5; 
            color: #383d41; 
        }

        .sponsor-logo-display {
            flex-shrink: 0;
            width: 150px;
            height: 150px;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dee2e6;
        }

        .sponsor-logo-display img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Responsive - 2 columns on tablet and desktop, 1 column on mobile */
        @media (max-width: 992px) {
            .sponsors-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sponsor-card {
                padding: 1.5rem;
            }
            
            .sponsor-card-content {
                flex-direction: column;
            }
            
            .sponsor-name {
                font-size: 1.25rem;
                padding-right: 120px;
            }
            
            .sponsor-main-info {
                width: 100%;
            }
            
            .sponsor-card-actions {
                top: 0.75rem;
                right: 0.75rem;
            }
            
            .btn-card-edit, .btn-card-delete {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            
            .sponsor-logo-display {
                width: 120px;
                height: 120px;
                margin: 0 auto;
            }
        }

        @media (max-width: 480px) {
            .sponsor-name {
                font-size: 1.1rem;
                padding-right: 100px;
            }
            
            .sponsor-title-text {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'topbar.php'; ?>

    <div class="main-wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="content-wrapper">
            <div class="tournament-topbar">
                <div class="tournament-topbar-container">
                    <a href="sea-detail.php?id=<?php echo $id;?>" class="topbar-tab">Season Detail</a>
                    <a href="organizers.php?id=<?php echo $id;?>" class="topbar-tab">Organizers</a>
                    <a href="sponsors.php?id=<?php echo $id;?>" class="topbar-tab active">Sponsors</a>
                    <a href="tour-manage.php?id=<?php echo $id;?>" class="topbar-tab">Auction</a>
                </div>
            </div>
            
            <div class="container">
                <div class="action-header">
                    <button class="btn-action btn-add" onclick="openModal()">
                        <i class="fas fa-plus"></i>
                        ADD SPONSOR
                    </button>
                </div>

                <div id="sponsorsGrid"></div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="sponsorModal" onclick="closeModalOnOutside(event)">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 id="modalTitle">Create sponsor</h2>
                <button class="btn-close" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="sponsorForm" onsubmit="submitForm(event)" enctype="multipart/form-data">
                    <input type="hidden" id="sponsorId">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Sponsor Name<span class="required">*</span></label>
                            <input type="text" id="sponsorName" class="form-control" placeholder="Enter Sponsor name">
                            <span class="error-message" id="nameError">Name is required</span>
                        </div>
                        <div class="form-group">
                            <label>Sponsor Title<span class="required">*</span></label>
                            <input type="text" id="sponsorTitle" class="form-control" placeholder="Enter Sponsor Title">
                            <span class="error-message" id="titleError">Title is required</span>
                        </div>
                        <div class="form-group">
                            <label>Sponsor Type<span class="required">*</span></label>
                            <select id="sponsorType" class="form-control">
                                <option value="" selected disabled>Select Sponsor Type</option>
                                <option value="Auction Sponsor">Auction Sponsor</option>
                                <option value="Banner Sponsor">Banner Sponsor</option>
                                <option value="Man Of The Match Award">Man Of The Match Award</option>
                                <option value="Tournament Champions Trophy">Tournament Champions Trophy</option>
                                <option value="Tshirt Sponsor">Tshirt Sponsor</option>
                            </select>
                            <span class="error-message" id="typeError">Type is required</span>
                        </div>
                        <div class="form-group">
                            <label>Sponsor Number</label>
                            <input type="text" id="sponsorNumber" class="form-control" placeholder="Enter Sponsor number" maxlength="10">
                            <span class="error-message" id="numberError">Invalid phone number</span>
                        </div>
                        <div class="form-group full-width">
                            <label>Sponsor Email</label>
                            <input type="email" id="sponsorEmail" class="form-control" placeholder="Enter Sponsor email">
                            <span class="error-message" id="emailError">Invalid email</span>
                        </div>
                    </div>

                    <div class="upload-section">
                        <h3>Sponsor Logo</h3>
                        <div class="upload-area" id="logoUploadArea" onclick="document.getElementById('logoUpload').click()">
                            <div class="image-preview" id="logoPreview"></div>
                            <p>Drop file to upload or click to browse</p>
                            <small>Upload Logo (JPG, PNG - Max 5MB)</small>
                            <input type="file" id="logoUpload" style="display: none;" accept="image/*">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-footer btn-close-footer" onclick="closeModal()">Close</button>
                <button type="submit" class="btn-footer btn-submit" form="sponsorForm" id="submitBtn">Add Sponsor</button>
            </div>
        </div>
    </div>
    
    <script>
        const SEASON_ID = <?php echo $id; ?>;
        let editingId = null;

        $(document).ready(function() {
            loadSponsors();
            
            // Logo upload preview
            $('#logoUpload').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#logoPreview').html(`<img src="${e.target.result}">`).show();
                        $('#logoUploadArea').addClass('has-image');
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        function loadSponsors() {
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: { 
                    action: 'fetch_sponsors',
                    season_id: SEASON_ID
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        renderSponsors(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        function renderSponsors(sponsors) {
            const grid = $('#sponsorsGrid');
            grid.empty();
            
            if (sponsors.length === 0) {
                grid.html(`
                    <div class="empty-state">
                        <p class="empty-state-text">No available sponsor</p>
                    </div>
                `);
                grid.removeClass('sponsors-grid');
            } else {
                grid.addClass('sponsors-grid');
                sponsors.forEach(function(sponsor) {
                    const logoSrc = sponsor.logo ? `../uploads/sponsors/${sponsor.logo}` : '';
                    
                    const card = $(`
                        <div class="sponsor-card">
                            <div class="sponsor-card-actions">
                                <button class="btn-card-edit" data-id="${sponsor.id}" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn-card-delete" data-id="${sponsor.id}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            
                            <div class="sponsor-card-content">
                                <div class="sponsor-main-info">
                                    <h3 class="sponsor-name">${sponsor.name || 'N/A'}</h3>
                                    <p class="sponsor-title-text">Sponsor Title: ${sponsor.title || 'N/A'}</p>
                                    
                                    <div class="sponsor-contact-info">
                                        ${sponsor.email ? `
                                            <div class="sponsor-contact-item">
                                                <i class="fas fa-envelope"></i>
                                                <span>${sponsor.email}</span>
                                            </div>
                                        ` : ''}
                                        ${sponsor.number ? `
                                            <div class="sponsor-contact-item">
                                                <i class="fas fa-phone"></i>
                                                <span>${sponsor.number}</span>
                                            </div>
                                        ` : ''}
                                    </div>
                                    
                                    <span class="sponsor-type-tag tag-${sponsor.type.toLowerCase().replace(/\s+/g, '-')}">${sponsor.type || 'Standard'}</span>
                                </div>
                                
                                ${logoSrc ? `
                                    <div class="sponsor-logo-display">
                                        <img src="${logoSrc}" alt="${sponsor.title}">
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `);
                    grid.append(card);
                });
            }
        }

        function openModal(id = null) {
            const modal = $('#sponsorModal');
            const modalTitle = $('#modalTitle');
            const submitBtn = $('#submitBtn');
            
            $('.form-control').removeClass('valid invalid');
            $('.error-message').removeClass('show');
            $('#logoPreview').hide().html('');
            $('#logoUploadArea').removeClass('has-image');
            
            if (id) {
                editingId = id;
                
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { action: 'get_sponsor', sponsor_id: id },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        
                        if (response.status === 'success') {
                            const sponsor = response.data;
                            
                            $('#sponsorId').val(sponsor.id);
                            $('#sponsorName').val(sponsor.name);
                            $('#sponsorTitle').val(sponsor.title);
                            $('#sponsorType').val(sponsor.type);
                            $('#sponsorNumber').val(sponsor.number || '');
                            $('#sponsorEmail').val(sponsor.email || '');
                            
                            if (sponsor.logo) {
                                $('#logoPreview').html(`<img src="../assets/images/${sponsor.logo}">`).show();
                                $('#logoUploadArea').addClass('has-image');
                            }
                            
                            modalTitle.text('Edit sponsor');
                            submitBtn.text('Update Sponsor');
                            
                            modal.addClass('show');
                            $('body').css('overflow', 'hidden');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            } else {
                editingId = null;
                $('#sponsorForm')[0].reset();
                $('#sponsorId').val('');
                
                modalTitle.text('Create sponsor');
                submitBtn.text('Add Sponsor');
                
                modal.addClass('show');
                $('body').css('overflow', 'hidden');
            }
        }

        function closeModal() {
            $('#sponsorModal').removeClass('show');
            $('body').css('overflow', 'auto');
            editingId = null;
            $('#sponsorForm')[0].reset();
            $('.form-control').removeClass('valid invalid');
            $('.error-message').removeClass('show');
        }

        function closeModalOnOutside(event) {
            if (event.target.id === 'sponsorModal') {
                closeModal();
            }
        }

        function submitForm(event) {
            event.preventDefault();
            
            const name = $('#sponsorName').val().trim();
            const title = $('#sponsorTitle').val().trim();
            const type = $('#sponsorType').val();
            const email = $('#sponsorEmail').val().trim();
            const number = $('#sponsorNumber').val().trim();
            
            let isValid = true;
            
            if (name.length < 2) {
                $('#sponsorName').addClass('invalid');
                $('#nameError').addClass('show');
                isValid = false;
            }
            
            if (!title) {
                $('#sponsorTitle').addClass('invalid');
                $('#titleError').addClass('show');
                isValid = false;
            }
            
            if (!type) {
                $('#sponsorType').addClass('invalid');
                $('#typeError').addClass('show');
                isValid = false;
            }
            
            if (!isValid) {
                Swal.fire('Error', 'Please fill all required fields', 'error');
                return;
            }
            
            Swal.fire({
                title: editingId ? 'Updating...' : 'Adding...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            
            const formData = new FormData();
            formData.append('action', editingId ? 'update_sponsor' : 'add_sponsor');
            formData.append('season_id', SEASON_ID);
            formData.append('name', name);
            formData.append('title', title);
            formData.append('type', type);
            formData.append('email', email);
            formData.append('number', number);
            
            if (editingId) {
                formData.append('sponsor_id', editingId);
            }
            
            if ($('#logoUpload')[0].files.length) {
                formData.append('logo', $('#logoUpload')[0].files[0]);
            }
            
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        closeModal();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false,
                            timerProgressBar:true,
                        }).then(() => {                            
                            loadSponsors();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Request failed', 'error');
                    console.error('Response:', xhr.responseText);
                }
            });
        }

        $(document).on('click', '.btn-card-edit', function() {
            openModal($(this).data('id'));
        });

        $(document).on('click', '.btn-card-delete', function() {
            const id = $(this).data('id');
            
            Swal.fire({
                title: 'Delete Sponsor?',
                text: 'Sponsor will be removed from this season!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    
                    $.ajax({
                        url: window.location.href,
                        type: 'POST',
                        data: { 
                            action: 'delete_sponsor',
                            sponsor_id: id,
                            season_id: SEASON_ID
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false,
                                    timerProgressBar:true,
                                }).then(() => {
                                    loadSponsors();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        $(document).keydown(function(e) {
            if (e.key === 'Escape' && $('#sponsorModal').hasClass('show')) {
                closeModal();
            }
        });

        // Validation
        $('#sponsorName').on('input', function() {
            if ($(this).val().trim().length >= 2) {
                $(this).removeClass('invalid').addClass('valid');
                $('#nameError').removeClass('show');
            }
        });

        $('#sponsorTitle').on('input', function() {
            if ($(this).val().trim()) {
                $(this).removeClass('invalid').addClass('valid');
                $('#titleError').removeClass('show');
            }
        });

        $('#sponsorType').on('change', function() {
            if ($(this).val()) {
                $(this).removeClass('invalid').addClass('valid');
                $('#typeError').removeClass('show');
            }
        });
    </script>
</body>
</html>