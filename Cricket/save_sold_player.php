<?php
include "connection.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player_id = isset($_POST['player_id']) ? (int)$_POST['player_id'] : 0;
    $team_id = isset($_POST['team_id']) ? (int)$_POST['team_id'] : 0;
    $sold_price = isset($_POST['sold_price']) ? (int)$_POST['sold_price'] : 0;
    $season_id = isset($_POST['season_id']) ? (int)$_POST['season_id'] : 0;
    
    if ($player_id <= 0 || $team_id <= 0 || $sold_price <= 0 || $season_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid parameters provided'
        ]);
        exit;
    }
    
    // Check if player is already sold to this team in this season
    $check_sql = "SELECT tpid FROM team_player 
                  WHERE pid = ? AND tid = ? AND season_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iii", $player_id, $team_id, $season_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Player already sold to this team'
        ]);
        exit;
    }
    
    // Insert into team_player table
    $sql = "INSERT INTO team_player (season_id, tid, pid, sold_price, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $season_id, $team_id, $player_id, $sold_price);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Player sold successfully',
            'tpid' => $stmt->insert_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $conn->error
        ]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>