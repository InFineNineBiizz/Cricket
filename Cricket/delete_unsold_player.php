<?php
    header('Content-Type: application/json');

    include "connection.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $player_id = isset($_POST['player_id']) ? (int)$_POST['player_id'] : 0;
        
        if ($player_id > 0) {
            // Delete from unsold_players table
            $delete_sql = "DELETE FROM unsold_players WHERE pid = $player_id";
            
            if (mysqli_query($conn, $delete_sql)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Player removed from unsold list',
                    'data' => ['player_id' => $player_id]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete: ' . mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid player ID']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }

    mysqli_close($conn);
?>