<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";
// Handle delete request
if (isset($_GET['delete'])) {
    $id_hasil = $_GET['delete'];

    // Query to delete the record from the database
    $delete_query = "DELETE FROM hasil_pemeriksaan WHERE id_hasil = '$id_hasil'";

    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('Data berhasil dihapus!'); window.location='hasil_pemeriksaan.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "'); window.location='hasil_pemeriksaan.php';</script>";
    }
}

// Mengambil data hasil pemeriksaan
$hasil_pemeriksaan_result = mysqli_query($conn, "
    SELECT hp.id_hasil, r.id_reservasi, p.nama_pasien, d.nama_dokter, 
           hp.jenis_pemeriksaan, hp.hasil_pemeriksaan, hp.tanggal_pemeriksaan, o.nama_obat 
    FROM hasil_pemeriksaan hp
    JOIN reservasi r ON hp.id_reservasi = r.id_reservasi
    JOIN pasien p ON r.id_pasien = p.id_pasien
    JOIN dokter d ON r.id_dokter = d.id_dokter
    JOIN obat o ON hp.id_obat = o.id_obat
    ORDER BY hp.tanggal_pemeriksaan DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Hasil Pemeriksaan</title>
    <!-- Include Bootstrap and custom styles -->
    <link href="../../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../../assets/css/font-awesome.css" rel="stylesheet" />
    <link href="../../assets/css/custom-styles.css" rel="stylesheet" />
</head>
<body>
<div id="wrapper">
    <!-- Sidebar is already included via include('sidebar.php') -->
    <div id="page-wrapper">
        <div id="page-inner">
            <!-- Content -->
            <h1 class="page-header text-center">Data Hasil Pemeriksaan</h1>
            <a href="edit_hasil_pemeriksaan.php" class="btn btn-primary" style="margin-bottom: 20px;">Tambah Hasil Pemeriksaan</a>
            <table class="table table-bordered table-striped">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="text-center">No.</th>
                        <th class="text-center">ID Hasil</th>
                        <th class="text-center">ID Reservasi</th>
                        <th class="text-center">Nama Pasien</th>
                        <th class="text-center">Nama Dokter</th>
                        <th class="text-center">Jenis Pemeriksaan</th>
                        <th class="text-center">Hasil Pemeriksaan</th>
                        <th class="text-center">Tanggal Pemeriksaan</th>
                        <th class="text-center">Nama Obat</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($hasil = mysqli_fetch_assoc($hasil_pemeriksaan_result)) {
                        echo "<tr>";
                        echo "<td class='text-center'>" . $no . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($hasil['id_hasil']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($hasil['id_reservasi']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($hasil['nama_pasien']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($hasil['nama_dokter']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($hasil['jenis_pemeriksaan']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($hasil['hasil_pemeriksaan']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($hasil['tanggal_pemeriksaan']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($hasil['nama_obat']) . "</td>";
                        echo "<td class='text-center'>
                                <div class='d-flex justify-content-center'>
                                    <a href='edit_hasil_pemeriksaan.php?edit=" . htmlspecialchars($hasil['id_hasil']) . "' class='btn btn-warning btn-sm me-1'>Edit</a>
                                    <a href='hasil_pemeriksaan.php?delete=" . htmlspecialchars($hasil['id_hasil']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>
                                </div>
                              </td>";
                        echo "</tr>";
                        $no++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include Scripts -->
<script src="../../assets/js/jquery-1.10.2.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script src="../../assets/js/custom-scripts.js"></script>
</body>
</html>

<?php $conn->close(); ?>
