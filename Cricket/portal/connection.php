<?php
    // ================== DB CONNECTION ==================
    $conn = mysqli_connect('127.0.0.1', 'root', '', 'cricket_db');
    if (!$conn) {
        die('DB Connection failed: ' . mysqli_connect_error());
    }
    $title="CrickFolio";
?>