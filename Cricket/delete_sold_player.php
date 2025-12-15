<?php
    include "connection.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tpid = isset($_POST['tpid']) ? (int)$_POST['tpid'] : 0;
        
        if ($tpid <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }
        
        // Get team ID and sold price before deleting
        $query = "SELECT tid, sold_price FROM team_player WHERE tpid = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $tpid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Player not found']);
            exit;
        }
        
        $team_id = $row['tid'];
        $sold_price = $row['sold_price'];
        
        // Delete the player
        $delete = "DELETE FROM team_player WHERE tpid = ?";
        $delete_stmt = mysqli_prepare($conn, $delete);
        mysqli_stmt_bind_param($delete_stmt, "i", $tpid);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            // Add sold price back to team's remaining budget
            $update = "UPDATE teams SET remaining = remaining + ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($update_stmt, "ii", $sold_price, $team_id);
            mysqli_stmt_execute($update_stmt);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Player deleted and budget restored',
                'refunded_amount' => $sold_price
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }

    mysqli_close($conn);
?>