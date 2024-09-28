<?php
$conn = pg_connect("host=localhost dbname=asah_otak user=postgres password=postgres");

if (!$conn) {
    die("Koneksi ke database gagal: " . pg_last_error());
}
?>