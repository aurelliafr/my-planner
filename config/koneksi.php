<?php

$host     = "localhost";
$user     = "root";
$password = "";
$database = "my_planner";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>