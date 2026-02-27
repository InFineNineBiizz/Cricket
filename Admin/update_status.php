<?php
include "connection.php";

error_reporting(0);
ob_clean();

$id     = $_POST['id'] ?? '';
$status = $_POST['status'] ?? '';
$table  = $_POST['table'] ?? '';

$newStatus = ($status == 1) ? 0 : 1;

// Allowed tables with their primary key
$primary_keys = [
    "tournaments" => "tid",
    "seasons"     => "id",
    "organizers"  => "id",
    "sponsers"    => "id",
    "auctions"    => "id",
    "teams"       => "id",
    "players"    => "id",
    "auc_man"     => "amid",
    "lead_auc"    => "lid",
    "group_auction"=> "gid",
    "grp_auc"     => "gaid",
    "season_organizer" => "id"
];

if (!array_key_exists($table, $primary_keys)) {
    echo "error";
    exit;
}

$pk = $primary_keys[$table];

$sql = "UPDATE $table SET status='$newStatus' WHERE $pk='$id'";

if (mysqli_query($conn, $sql)) {
    echo $newStatus; // 🔥 KEY FIX
} else {
    echo "error";
}
?>
