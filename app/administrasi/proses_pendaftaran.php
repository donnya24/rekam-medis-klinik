<?php
// Include the header and database connection
include "../../includes/administrasi/sidebar.php";
include "../../config/db.php";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $no_telepon = $_POST['no_telepon'];

    // Validate the inputs
    if (empty($nama) || empty($tgl_lahir) || empty($jenis_kelamin)) {
        echo "<p>Data tidak lengkap. Pastikan semua kolom diisi.</p>";
    } else {
        // Prepare SQL query to insert data
        $sql = "INSERT INTO pasien (nama_pasien, alamat_pasien, tanggal_lahir, jenis_kelamin, no_telepon)
                VALUES ('$nama', '$alamat', '$tgl_lahir', '$jenis_kelamin', '$no_telepon')";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p>Pasien baru berhasil didaftarkan.</p>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Close the connection
$conn->close();
?>

