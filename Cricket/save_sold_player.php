<?php
    header('Content-Type: application/json');
    
    include "connection.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $player_id = isset($_POST['player_id']) ? (int)$_POST['player_id'] : 0;
        $team_id = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;
        $sold_price = isset($_POST['sold_price']) ? (int)$_POST['sold_price'] : 0;
        
        if ($player_id > 0 && $team_id > 0) {
            // Check if player already sold
            $check_sql = "SELECT * FROM team_player WHERE pid = $player_id";
            $check_res = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_res) > 0) {
                echo json_encode(['success' => false, 'message' => 'Player already sold']);
                exit;
            }
            
            // Insert into team_player table with sold_price
            $insert_sql = "INSERT INTO team_player (tid, pid, sold_price) VALUES ($team_id, $player_id, $sold_price)";
            
            if (mysqli_query($conn, $insert_sql)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Player sold successfully',
                    'data' => [
                        'player_id' => $player_id,
                        'team_id' => $team_id,
                        'sold_price' => $sold_price
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save: ' . mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid player or team ID']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }

    mysqli_close($conn);
?>