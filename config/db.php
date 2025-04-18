<?php
$localhost = "localhost:3306"; // Server MySQL dengan port 3306
$username = "root";            // Username MySQL
$password = "";                // Password MySQL
$dbname = "klinikku";       // Nama database

// Membuat koneksi
$conn = new mysqli($localhost, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
