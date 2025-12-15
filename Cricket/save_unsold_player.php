<?php
    header('Content-Type: application/json');

    include "connection.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $player_id = isset($_POST['player_id']) ? (int)$_POST['player_id'] : 0;
        $tournament_id = isset($_POST['tournament_id']) ? (int)$_POST['tournament_id'] : 0;
        $last_bid_price = isset($_POST['last_bid_price']) ? (int)$_POST['last_bid_price'] : 0;
        
        if ($player_id > 0 && $tournament_id > 0) {
            // Check if player already marked as unsold
            $check_sql = "SELECT * FROM unsold_players WHERE pid = $player_id AND tid = $tournament_id";
            $check_res = mysqli_query($conn, $check_sql);
            
            if (mysqli_num_rows($check_res) > 0) {
                echo json_encode(['success' => false, 'message' => 'Player already marked as unsold']);
                exit;
            }
            
            // Check if player was sold (shouldn't be both sold and unsold)
            $sold_check = "SELECT * FROM team_player WHERE pid = $player_id";
            $sold_res = mysqli_query($conn, $sold_check);
            
            if (mysqli_num_rows($sold_res) > 0) {
                echo json_encode(['success' => false, 'message' => 'Player is already sold']);
                exit;
            }
            
            // Insert into unsold_players table
            $insert_sql = "INSERT INTO unsold_players (pid, tid, last_bid_price) VALUES ($player_id, $tournament_id, $last_bid_price)";
            
            if (mysqli_query($conn, $insert_sql)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Player marked as unsold successfully',
                    'data' => [
                        'player_id' => $player_id,
                        'tournament_id' => $tournament_id,
                        'last_bid_price' => $last_bid_price
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save: ' . mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid player or tournament ID']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }

    mysqli_close($conn);
?>