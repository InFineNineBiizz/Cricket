<?php
    include "connection.php";

    header('Content-Type: application/json');

    if(isset($_POST['action'])) {
        
        // Fetch single season data
        if($_POST['action'] === 'fetch_season') {
            $seasonId = mysqli_real_escape_string($conn, $_POST['season_id']);
            
            $sql = "SELECT s.*, t.name as tournament_name 
                    FROM seasons s 
                    LEFT JOIN tournaments t ON s.tid = t.tid 
                    WHERE s.id = '$seasonId'";
            
            $result = mysqli_query($conn, $sql);
            
            if($result && mysqli_num_rows($result) > 0) {
                $season = mysqli_fetch_assoc($result);
                echo json_encode([
                    'success' => true,
                    'data' => $season
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Season not found'
                ]);
            }
            exit;
        }
        
        // Update season data
        if($_POST['action'] === 'update_season') {
            $seasonId = mysqli_real_escape_string($conn, $_POST['season_id']);
            $sname = mysqli_real_escape_string($conn, $_POST['edit_sname']);
            $tid = mysqli_real_escape_string($conn, $_POST['edit_tourname']);
            $cname = mysqli_real_escape_string($conn, $_POST['edit_cname']);
            $gname = mysqli_real_escape_string($conn, $_POST['edit_gname']);
            $sdate = mysqli_real_escape_string($conn, $_POST['edit_sdate']);
            $edate = mysqli_real_escape_string($conn, $_POST['edit_edate']);
            $btype = mysqli_real_escape_string($conn, $_POST['edit_btype']);
            $gtype = mysqli_real_escape_string($conn, $_POST['edit_gtype']);
            $mtype = mysqli_real_escape_string($conn, $_POST['edit_mtype']);
            
            // Handle overs
            $overs = "NULL";
            if($mtype == "Limited Overs" && !empty($_POST['edit_overs'])) {
                $overs = "'" . mysqli_real_escape_string($conn, $_POST['edit_overs']) . "'";
            }
            
            // Handle logo upload
            $logoUpdate = "";
            if(isset($_FILES['edit_logo']) && $_FILES['edit_logo']['error'] === 0) {
                $allowed = ['jpg', 'jpeg', 'png'];
                $filename = $_FILES['edit_logo']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if(in_array($ext, $allowed) && $_FILES['edit_logo']['size'] <= 2097152) {
                    $newFilename = uniqid() . '_' . $filename;
                    $uploadPath = "../assets/images/" . $newFilename;
                    
                    if(move_uploaded_file($_FILES['edit_logo']['tmp_name'], $uploadPath)) {
                        $logoUpdate = ", logo='" . mysqli_real_escape_string($conn, $newFilename) . "'";
                        
                        // Delete old logo if exists
                        if(!empty($_POST['old_logo'])) {
                            $oldLogoPath = "../assets/images/" . $_POST['old_logo'];
                            if(file_exists($oldLogoPath)) {
                                unlink($oldLogoPath);
                            }
                        }
                    }
                }
            }
            
            $updateSql = "UPDATE seasons SET 
                name='$sname',
                tid='$tid',
                cname='$cname',
                gname='$gname',
                sdate='$sdate',
                edate='$edate',
                btype='$btype',
                gtype='$gtype',
                mtype='$mtype',
                overs=$overs
                $logoUpdate
                WHERE id='$seasonId'";
            
            if(mysqli_query($conn, $updateSql)) {
                // Fetch updated season data
                $fetchSql = "SELECT s.*, t.name as tournament_name 
                            FROM seasons s 
                            LEFT JOIN tournaments t ON s.tid = t.tid 
                            WHERE s.id = '$seasonId'";
                
                $result = mysqli_query($conn, $fetchSql);
                $updatedSeason = mysqli_fetch_assoc($result);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Season updated successfully!',
                    'data' => $updatedSeason
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update season: ' . mysqli_error($conn)
                ]);
            }
            exit;
        }
        
        // Delete season
        if($_POST['action'] === 'delete_season') {
            $seasonId = mysqli_real_escape_string($conn, $_POST['season_id']);
            
            // Get logo filename before deleting
            $logoSql = "SELECT logo FROM seasons WHERE id='$seasonId'";
            $logoResult = mysqli_query($conn, $logoSql);
            $logoData = mysqli_fetch_assoc($logoResult);
            
            $deleteSql = "DELETE FROM seasons WHERE sid='$seasonId'";
            
            if(mysqli_query($conn, $deleteSql)) {
                // Delete logo file if exists
                if(!empty($logoData['logo'])) {
                    $logoPath = "../assets/images/" . $logoData['logo'];
                    if(file_exists($logoPath)) {
                        unlink($logoPath);
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Season deleted successfully!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete season'
                ]);
            }
            exit;
        }
    }

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
?>