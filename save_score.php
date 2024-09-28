<?php
include('database/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = pg_escape_string($conn, $_POST['nama']); 
    $score = intval($_POST['score']); 

    $query = "INSERT INTO point_game (nama_user, total_point) VALUES ($1, $2)";
    $result = pg_query_params($conn, $query, array($name, $score));

    if ($result) {
        session_start();
        $_SESSION['message'] = "Score berhasil disimpan!";
        header("Location: index.php");
        exit();
    } else {
        die("Gagal menyimpan score!: " . pg_last_error());
    }
}
?>