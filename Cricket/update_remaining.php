<?php
    include "connection.php";

    $team_id   = (int)$_POST['team_id'];
    $new_rem   = (int)$_POST['remaining'];

    mysqli_query($conn, "UPDATE teams SET remaining = $new_rem WHERE id = $team_id");
    echo "OK";
?>