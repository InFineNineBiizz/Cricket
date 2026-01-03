<?php
    session_start();
    include "connection.php";
    
    // Check if auction exists for this season
    $auction_exists = false;
    if(isset($_GET['id'])) {
        $season_id = $_GET['id'];
        $auction_check = "SELECT COUNT(*) as count FROM auctions WHERE sea_id = '$season_id'";
        $check_result = mysqli_query($conn, $auction_check);
        if($check_result) {
            $check_row = mysqli_fetch_assoc($check_result);
            $auction_exists = $check_row['count'] > 0;
        }
    }
    
    // Handle AJAX requests
    if(isset($_POST['ajax_action'])) {
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => '', 'data' => null];
        
        try {
            $action = $_POST['ajax_action'];
            
            // ADD TEAM
            if($action == 'add') {
                $season_id = mysqli_real_escape_string($conn, $_POST['season_id']);
                $team_name = mysqli_real_escape_string($conn, $_POST['team_name']);
                $logo_data = $_POST['team_logo_data'] ?? '';
                
                // Get remaining amount from auctions table
                $auc_query = "SELECT camt FROM auctions WHERE sea_id = '$season_id' LIMIT 1";
                $auc_result = mysqli_query($conn, $auc_query);
                
                if($auc_result && mysqli_num_rows($auc_result) > 0) {
                    $auc_row = mysqli_fetch_assoc($auc_result);
                    $remaining = $auc_row['camt'];
                    
                    // Handle logo upload
                    $logo_path = '';
                    if(!empty($logo_data)) {
                        if(preg_match('/^data:image\/(\w+);base64,/', $logo_data, $type)) {
                            $logo_data = substr($logo_data, strpos($logo_data, ',') + 1);
                            $type = strtolower($type[1]);
                            $logo_data = base64_decode($logo_data);
                            
                            if($logo_data !== false) {
                                $upload_dir = '../uploads/teams/';
                                if(!file_exists($upload_dir)) {
                                    mkdir($upload_dir, 0777, true);
                                }
                                
                                $filename = 'team_' . time() . '_' . uniqid() . '.' . $type;
                                $logo_path = $upload_dir . $filename;
                                
                                if(file_put_contents($logo_path, $logo_data)) {
                                    $logo_path = 'uploads/teams/' . $filename;
                                }
                            }
                        }
                    }
                    
                    // Insert team
                    $insert_query = "INSERT INTO teams (season_id, name, logo, remaining, status, created_at) 
                                     VALUES ('$season_id', '$team_name', '$logo_path', '$remaining', 1, NOW())";
                    
                    if(mysqli_query($conn, $insert_query)) {
                        $team_id = mysqli_insert_id($conn);
                        $response['success'] = true;
                        $response['message'] = 'Team added successfully';
                        $response['data'] = [
                            'id' => $team_id,
                            'name' => $team_name,
                            'logo' => $logo_path,
                            'remaining' => $remaining
                        ];
                    } else {
                        $response['message'] = 'Failed to add team: ' . mysqli_error($conn);
                    }
                } else {
                    $response['message'] = 'Auction not found';
                }
            }
            
            // EDIT TEAM
            elseif($action == 'edit') {
                $team_id = mysqli_real_escape_string($conn, $_POST['team_id']);
                $team_name = mysqli_real_escape_string($conn, $_POST['team_name']);
                $logo_data = $_POST['team_logo_data'] ?? '';
                $existing_logo = $_POST['existing_logo'] ?? '';
                
                $logo_path = $existing_logo;
                
                // Handle new logo upload
                if(!empty($logo_data) && strpos($logo_data, 'data:image') === 0) {
                    if(preg_match('/^data:image\/(\w+);base64,/', $logo_data, $type)) {
                        $logo_data = substr($logo_data, strpos($logo_data, ',') + 1);
                        $type = strtolower($type[1]);
                        $logo_data = base64_decode($logo_data);
                        
                        if($logo_data !== false) {
                            $upload_dir = '../uploads/teams/';
                            if(!file_exists($upload_dir)) {
                                mkdir($upload_dir, 0777, true);
                            }
                            
                            // Delete old logo
                            if(!empty($existing_logo) && file_exists('../' . $existing_logo)) {
                                unlink('../' . $existing_logo);
                            }
                            
                            $filename = 'team_' . time() . '_' . uniqid() . '.' . $type;
                            $logo_path = $upload_dir . $filename;
                            
                            if(file_put_contents($logo_path, $logo_data)) {
                                $logo_path = 'uploads/teams/' . $filename;
                            }
                        }
                    }
                }
                
                // Update team
                $update_query = "UPDATE teams SET name = '$team_name', logo = '$logo_path' WHERE id = '$team_id'";
                
                if(mysqli_query($conn, $update_query)) {
                    $response['success'] = true;
                    $response['message'] = 'Team updated successfully';
                    $response['data'] = [
                        'id' => $team_id,
                        'name' => $team_name,
                        'logo' => $logo_path
                    ];
                } else {
                    $response['message'] = 'Failed to update team: ' . mysqli_error($conn);
                }
            }
            
            // DELETE TEAM
            elseif($action == 'delete') {
                $team_id = mysqli_real_escape_string($conn, $_POST['team_id']);
                
                // Get team logo before deleting
                $select_query = "SELECT logo FROM teams WHERE id = '$team_id'";
                $result = mysqli_query($conn, $select_query);
                
                if($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $logo_path = $row['logo'];
                    
                    // Hard delete team
                    $delete_query = "DELETE FROM teams WHERE id = '$team_id'";
                    
                    if(mysqli_query($conn, $delete_query)) {
                        // Delete logo file
                        if(!empty($logo_path) && file_exists('../' . $logo_path)) {
                            unlink('../' . $logo_path);
                        }
                        
                        $response['success'] = true;
                        $response['message'] = 'Team deleted successfully';
                        $response['data'] = ['id' => $team_id];
                    } else {
                        $response['message'] = 'Failed to delete team: ' . mysqli_error($conn);
                    }
                } else {
                    $response['message'] = 'Team not found';
                }
            }
            
            // FETCH TEAMS
            elseif($action == 'fetch') {
                $season_id = mysqli_real_escape_string($conn, $_POST['season_id']);
                
                $query = "SELECT * FROM teams WHERE season_id = '$season_id' AND status = 1 ORDER BY created_at DESC";
                $result = mysqli_query($conn, $query);
                
                if($result) {
                    $teams = [];
                    while($row = mysqli_fetch_assoc($result)) {
                        $teams[] = [
                            'id' => $row['id'],
                            'name' => $row['name'],
                            'logo' => $row['logo'],
                            'remaining' => $row['remaining']
                        ];
                    }
                    
                    $response['success'] = true;
                    $response['data'] = $teams;
                } else {
                    $response['message'] = 'Failed to fetch teams: ' . mysqli_error($conn);
                }
            }
            
        } catch(Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        
        echo json_encode($response);
        exit;
    }
    
    // Regular page load
    $name=$tour_id=$sea_id=$venue=$sdate=$edate=$logo=$ctype=$max=$min=$reserve=$camt=$bidamt=$bprice=$img="";
    $tour_name=$s_date=$e_date=$sname=$tlogo=$t_id="";

    if(isset($_GET['id']))
    {
        $id=$_GET['id'];
        $str="select a.*,t.tid as tourid,t.name as tour_name,t.logo as tlogo,s.name as sname,s.sdate as start_date,s.edate as end_date 
        from auctions a,tournaments t,seasons s where a.tour_id=t.tid and a.sea_id=s.id and sea_id='".$id."'";
        
        $res=mysqli_query($conn,$str);
        if($res && mysqli_num_rows($res) > 0) {
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
            $tlogo=$row['tlogo'];
            $t_id=$row['tourid'];
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php if($auction_exists){echo $tour_name . " - Manage | CrickFolio Portal";}else{echo 'CrickFolio Portal';}?> </title>
    <link rel="stylesheet" href="../assets/css/fontawesome-all.css">    
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
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        .team-action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: flex-start;
        }

        .btn-primary-action {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.75rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            background: #6b9bd1;
            color: white;
            text-decoration: none;
        }

        .btn-primary-action:hover {
            background: #5a8bc1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(107, 155, 209, 0.3);
        }

        .btn-primary-action i {
            font-size: 1.1rem;
        }

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

        .teams-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

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
            text-decoration: none;
            color: inherit;
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
        }

        .team-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #95a5a6;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            font-size: 1rem;
        }

        /* Empty Auction State Styles */
        .empty-auction-container {
            background: white;
            border-radius: 15px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin: 2rem 0;
        }

        .empty-auction-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #6b9bd1 0%, #4a7ba7 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 8px 20px rgba(107, 155, 209, 0.3);
        }

        .empty-auction-icon i {
            font-size: 3.5rem;
            color: white;
        }

        .empty-auction-title {
            font-size: 2rem;
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .empty-auction-description {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .btn-create-auction {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, #6b9bd1, #4a7ba7);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(107, 155, 209, 0.3);
            text-decoration: none;
        }

        .btn-create-auction:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(107, 155, 209, 0.4);
        }

        .btn-create-auction i {
            font-size: 1.2rem;
        }

        .empty-auction-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }

        .feature-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: left;
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #6b9bd1, #4a7ba7);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .feature-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .feature-title {
            font-size: 1.1rem;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .feature-description {
            font-size: 0.95rem;
            color: #7f8c8d;
            line-height: 1.5;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-container {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            padding: 2rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.75rem;
            color: #2c3e50;
            font-weight: 700;
        }

        .btn-close-modal {
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            font-size: 1.5rem;
            color: #7f8c8d;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .btn-close-modal:hover {
            background: #f8f9fa;
            color: #2c3e50;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-size: 0.95rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .form-label .required {
            color: #f44336;
        }

        .form-input {
            padding: 0.875rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #6b9bd1;
            box-shadow: 0 0 0 3px rgba(107, 155, 209, 0.1);
        }

        .form-input::placeholder {
            color: #bbb;
        }

        .upload-section {
            margin-bottom: 1.5rem;
        }

        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .upload-area:hover {
            border-color: #6b9bd1;
            background: #f0f7ff;
        }

        .upload-area.has-file {
            border-color: #4caf50;
            background: #f1f8f4;
        }

        .upload-content h3 {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .upload-content p {
            color: #95a5a6;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .upload-preview {
            margin-top: 1rem;
            display: none;
        }

        .upload-preview.show {
            display: block;
        }

        .upload-preview img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-browse {
            background: transparent;
            color: #6b9bd1;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-browse:hover {
            text-decoration: underline;
        }

        .file-input {
            display: none;
        }

        .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn-modal {
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background: #95a5a6;
            color: white;
        }

        .btn-cancel:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }

        .btn-submit {
            background: linear-gradient(135deg, #6b9bd1, #4a7ba7);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(107, 155, 209, 0.3);
        }

        .btn-submit:disabled {
            background: #ddd;
            cursor: not-allowed;
            transform: none;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 2000;
            animation: slideInRight 0.3s ease;
            max-width: 400px;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast.success {
            border-left: 4px solid #4caf50;
        }

        .toast.error {
            border-left: 4px solid #f44336;
        }

        .toast i {
            font-size: 1.5rem;
        }

        .toast.success i {
            color: #4caf50;
        }

        .toast.error i {
            color: #f44336;
        }

        .toast-message {
            flex: 1;
            color: #2c3e50;
        }

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

            .team-action-buttons {
                flex-direction: column;
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

            .modal-container {
                width: 95%;
            }

            .modal-header {
                padding: 1.5rem;
            }

            .modal-body {
                padding: 1.5rem;
            }

            .toast {
                bottom: 1rem;
                right: 1rem;
                left: 1rem;
                max-width: none;
            }

            .empty-auction-container {
                padding: 3rem 1.5rem;
            }

            .empty-auction-icon {
                width: 100px;
                height: 100px;
            }

            .empty-auction-icon i {
                font-size: 2.5rem;
            }

            .empty-auction-title {
                font-size: 1.5rem;
            }

            .empty-auction-description {
                font-size: 1rem;
            }

            .btn-create-auction {
                padding: 0.875rem 2rem;
                font-size: 1rem;
            }

            .empty-auction-features {
                grid-template-columns: 1fr;
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

                <?php if($auction_exists){ ?>
                <?php include "auc_header.php";?>

                <div class="bottom-tabs">
                    <a href="tour-manage.php?id=<?php echo $id;?>" class="bottom-tab active">Teams</a>
                    <a href="player.php?id=<?php echo $id;?>" class="bottom-tab">Players</a>
                    <a href="information.php?id=<?php echo $id;?>" class="bottom-tab">Information</a>
                </div>
                <?php }?>
                
                <?php if(!$auction_exists): ?>
                    <!-- Empty Auction State -->
                    <div class="empty-auction-container">
                        <div class="empty-auction-icon">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <h1 class="empty-auction-title">No Auction Created Yet</h1>
                        <p class="empty-auction-description">
                            Create an auction to start managing teams, players, and conduct exciting bidding sessions for this season.
                        </p>
                        <a href="add-auction?id=<?php echo $id;?>" class="btn-create-auction">
                            <i class="fas fa-plus-circle"></i>
                            Create Auction
                        </a>

                        <div class="empty-auction-features">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h3 class="feature-title">Manage Teams</h3>
                                <p class="feature-description">Create and organize teams with custom logos and budgets for the auction.</p>
                            </div>

                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-user-friends"></i>
                                </div>
                                <h3 class="feature-title">Add Players</h3>
                                <p class="feature-description">Build your player pool with detailed profiles and base prices for bidding.</p>
                            </div>

                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-hammer"></i>
                                </div>
                                <h3 class="feature-title">Live Bidding</h3>
                                <p class="feature-description">Conduct live auction sessions with real-time bidding and team budget tracking.</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Existing Teams Management Section -->
                    <div class="team-action-buttons">                    
                        <button class="btn-primary-action btn-add-team" onclick="openModal()">
                            <i class="fas fa-plus-circle"></i>
                            ADD TEAM
                        </button>
                    </div>

                    <div class="teams-section">
                        <div class="section-header">
                            <h2>Total Teams: <span id="team-count">0</span></h2>
                        </div>

                        <div class="teams-grid" id="teams-grid">
                            <!-- Teams loaded here -->
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($auction_exists): ?>
    <!-- Team Modal -->
    <div class="modal-overlay" id="teamModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2 id="modal-title">Add Team</h2>
                <button class="btn-close-modal" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="team-form">
                    <input type="hidden" id="team_id" value="">
                    <input type="hidden" id="existing_logo" value="">
                    
                    <div class="form-group">
                        <label class="form-label">Team Name<span class="required">*</span></label>
                        <input type="text" class="form-input" id="team-name" placeholder="Enter team name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Team Logo</label>
                        <div class="upload-area" onclick="document.getElementById('logo-upload').click()">
                            <input type="file" id="logo-upload" class="file-input" accept="image/*" onchange="handleLogoUpload(event)">
                            <div class="upload-content">
                                <h3>Drop file to upload</h3>
                                <p>Upload team logo (PNG, JPG, GIF)</p>
                                <button type="button" class="btn-browse">BROWSE</button>
                            </div>
                        </div>
                        <div class="upload-preview" id="upload-preview">
                            <img id="preview-image" src="" alt="Logo Preview">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn-modal btn-submit" id="submit-btn" onclick="submitTeam()">
                    <span id="submit-text">Add Team</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        const SEASON_ID = <?php echo $id; ?>;
        let editingTeamId = null;
        let uploadedLogoDataURL = null;

        // Load teams on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTeams();
        });

        // Load teams
        function loadTeams() {
            const formData = new FormData();
            formData.append('ajax_action', 'fetch');
            formData.append('season_id', SEASON_ID);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    renderTeams(data.data);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Render teams - UPDATED TO MAKE CARDS CLICKABLE
        function renderTeams(teams) {
            const grid = document.getElementById('teams-grid');
            grid.innerHTML = '';

            if(teams.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Teams Yet</h3>
                        <p>Click "ADD TEAM" to create your first team</p>
                    </div>
                `;
                document.getElementById('team-count').textContent = '0';
                return;
            }

            teams.forEach(team => {
                const teamCard = document.createElement('a');
                teamCard.href = 'team-management.php?team_id=' + team.id;
                teamCard.className = 'team-card';
                teamCard.innerHTML = `
                    <div class="team-card-header">
                        <div class="team-logo">
                            ${team.logo ? '<img src="../' + team.logo + '" alt="' + team.name + '" />' : '<i class="fas fa-users"></i>'}
                        </div>
                        <h3>${team.name}</h3>
                    </div>
                    <div class="team-actions" onclick="event.preventDefault(); event.stopPropagation();">
                        <button class="btn-team-action btn-edit" onclick="editTeam(${team.id})">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="btn-team-action btn-delete" onclick="deleteTeam(${team.id}, '${team.name.replace(/'/g, "\\'")}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                grid.appendChild(teamCard);
            });

            document.getElementById('team-count').textContent = teams.length;
        }

        // Open modal
        function openModal() {
            editingTeamId = null;
            document.getElementById('modal-title').textContent = 'Add Team';
            document.getElementById('submit-text').textContent = 'Add Team';
            document.getElementById('team-form').reset();
            document.getElementById('team_id').value = '';
            document.getElementById('existing_logo').value = '';
            uploadedLogoDataURL = null;
            document.querySelector('.upload-area').classList.remove('has-file');
            document.getElementById('upload-preview').classList.remove('show');
            document.getElementById('teamModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Close modal
        function closeModal() {
            document.getElementById('teamModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Edit team
        function editTeam(id) {
            const formData = new FormData();
            formData.append('ajax_action', 'fetch');
            formData.append('season_id', SEASON_ID);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    const team = data.data.find(t => t.id == id);
                    if(team) {
                        editingTeamId = id;
                        document.getElementById('modal-title').textContent = 'Edit Team';
                        document.getElementById('submit-text').textContent = 'Update Team';
                        document.getElementById('team_id').value = team.id;
                        document.getElementById('team-name').value = team.name;
                        document.getElementById('existing_logo').value = team.logo;
                        
                        if(team.logo) {
                            uploadedLogoDataURL = '../' + team.logo;
                            document.getElementById('preview-image').src = uploadedLogoDataURL;
                            document.getElementById('upload-preview').classList.add('show');
                            document.querySelector('.upload-area').classList.add('has-file');
                        }
                        
                        document.getElementById('teamModal').classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                }
            });
        }

        // Delete team
        function deleteTeam(id, name) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete '" + name + "'?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f44336',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData();
                    formData.append('ajax_action', 'delete');
                    formData.append('team_id', id);

                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Team has been deleted successfully.',
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar:true,
                            });
                            loadTeams();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Failed to delete team'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete team'
                        });
                    });
                }
            });
        }

        // Submit team
        function submitTeam() {
            const teamName = document.getElementById('team-name').value.trim();
            
            if(!teamName) {
                showToast('Please enter team name', 'error');
                return;
            }

            const submitBtn = document.getElementById('submit-btn');
            const submitText = document.getElementById('submit-text');
            const originalText = submitText.textContent;
            
            submitBtn.disabled = true;
            submitText.innerHTML = '<span class="spinner"></span>Processing...';

            const formData = new FormData();
            formData.append('ajax_action', editingTeamId ? 'edit' : 'add');
            formData.append('season_id', SEASON_ID);
            formData.append('team_name', teamName);
            
            if(editingTeamId) {
                formData.append('team_id', editingTeamId);
                formData.append('existing_logo', document.getElementById('existing_logo').value);
            }
            
            if(uploadedLogoDataURL) {
                formData.append('team_logo_data', uploadedLogoDataURL);
            }

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showToast(data.message, 'success');
                    closeModal();
                    loadTeams();
                } else {
                    showToast(data.message || 'Operation failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitText.textContent = originalText;
            });
        }

        // Handle logo upload
        function handleLogoUpload(event) {
            const file = event.target.files[0];
            if(file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    uploadedLogoDataURL = e.target.result;
                    document.getElementById('preview-image').src = uploadedLogoDataURL;
                    document.getElementById('upload-preview').classList.add('show');
                    document.querySelector('.upload-area').classList.add('has-file');
                };
                reader.readAsDataURL(file);
            } else {
                showToast('Please select a valid image file', 'error');
            }
        }

        // Show toast
        function showToast(message, type = 'success') {
            const existingToasts = document.querySelectorAll('.toast');
            existingToasts.forEach(toast => toast.remove());

            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <div class="toast-message">${message}</div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideInRight 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Close modal on outside click
        document.getElementById('teamModal').addEventListener('click', function(e) {
            if(e.target === this) {
                closeModal();
            }
        });

        // Handle ESC key
        document.addEventListener('keydown', function(e) {
            if(e.key === 'Escape' && document.getElementById('teamModal').classList.contains('active')) {
                closeModal();
            }
        });

        // Prevent form submission
        document.getElementById('team-form').addEventListener('submit', function(e) {
            e.preventDefault();
            submitTeam();
        });
    </script>
    <?php endif; ?>
</body>
</html>