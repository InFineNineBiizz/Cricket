<?php
    include "connection.php";

    error_reporting(0);
    ob_clean();

    $id     = $_POST['id'];       
    $status = $_POST['status'];   
    $table  = $_POST['table'];    

    $newStatus = ($status == 1) ? 0 : 1;

    // Allowed tables with their primary key
    $primary_keys = [
        "tournaments" => "tid",
        "seasons"     => "id",
        "organizers"  => "id",
        "sponsers"     => "id"
    ];

    if (!array_key_exists($table, $primary_keys)) {
        echo "0";
        exit;
    }

    $pk = $primary_keys[$table];

    $sql = "UPDATE $table SET status='$newStatus' WHERE $pk='$id'";

    if (mysqli_query($conn, $sql)) {
        echo "1";
    } else {
        echo "0";
    }
?>