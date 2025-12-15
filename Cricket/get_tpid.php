<?php
    include "connection.php";

    $player_id = (int)$_POST['player_id'];
    $team_id = (int)$_POST['team_id'];

    $query = "SELECT tpid FROM team_player WHERE pid = ? AND tid = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $player_id, $team_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        echo json_encode(['success' => true, 'tpid' => $row['tpid']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Not found']);
    }

    mysqli_close($conn);
?>