<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "labtrack";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database LabTrack gagal: " . mysqli_connect_error());
}
?>