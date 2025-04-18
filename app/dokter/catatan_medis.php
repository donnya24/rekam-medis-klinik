<?php
session_start(); // Mulai session
include "../../includes/dokter/sidebar.php";
include "../../config/db.php";

// Cek apakah user sudah login dan role-nya adalah dokter
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'dokter') {
    echo "<script>alert('Anda harus login sebagai dokter!'); window.location='../../login.php';</script>";
    exit();
}

// Ambil id_dokter dari session
$id_dokter = $_SESSION['id_dokter'];

// Delete catatan medis jika ada aksi delete
if (isset($_GET['id_catatan'])) {
    $id_catatan = $_GET['id_catatan'];
    $delete_sql = "DELETE FROM catatan_medis WHERE id_catatan = '$id_catatan'";
    
    if ($conn->query($delete_sql) === TRUE) {
        echo "<script>alert('Catatan medis berhasil dihapus!'); window.location='catatan_medis.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat menghapus catatan medis: " . $conn->error . "');</script>";
    }
}

// Query untuk menampilkan data catatan medis sesuai dokter yang login
$query = "SELECT cm.*, p.nama_pasien, d.nama_dokter, per.akun_perawat 
          FROM catatan_medis cm
          JOIN reservasi r ON cm.id_reservasi = r.id_reservasi
          JOIN pasien p ON r.id_pasien = p.id_pasien
          JOIN dokter d ON r.id_dokter = d.id_dokter
          LEFT JOIN perawat per ON cm.id_perawat = per.id_perawat
          WHERE r.id_dokter = '$id_dokter'"; // Filter berdasarkan id_dokter yang login
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Medis</title>
    <!-- Include Bootstrap and custom styles -->
    <link href="../../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../../assets/css/font-awesome.css" rel="stylesheet" />
    <link href="../../assets/css/custom-styles.css" rel="stylesheet" />
    <style>
        /* Center the text in table headers */
        th {
            text-align: center;
        }
        /* Center the text in table data cells */
        td {
            text-align: center;
        }
        /* Style untuk memotong teks dan menambahkan elipsis */
        .truncate-text {
            max-width: 150px; /* Sesuaikan lebar maksimum sesuai kebutuhan */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }
        /* Style untuk kolom Aksi */
        .aksi-column {
            width: 150px; /* Sesuaikan lebar kolom Aksi sesuai kebutuhan */
        }
    </style>
</head>
<body>
<div id="wrapper">
    <div id="page-wrapper">
        <div id="page-inner">
            <h1 class="page-header" style="text-align: center;">Catatan Medis Pasien</h1>
            <a href="edit_catatan_medis.php" class="btn btn-primary" style="margin-bottom: 20px;">Tambah Catatan Medis</a>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>ID Catatan</th>
                            <th>Nama Pasien</th>
                            <th>Dokter</th>
                            <th>Perawat</th> <!-- Kolom baru untuk akun perawat -->
                            <th>ID Reservasi</th>
                            <th>Diagnosis</th>
                            <th>Rencana Perawatan</th>
                            <th>Catatan Pasca Pemeriksaan</th>
                            <th>Tanggal Periksa</th>
                            <th class="aksi-column">Aksi</th> <!-- Tambahkan class untuk kolom Aksi -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id_catatan']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_pasien']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_dokter']) . "</td>";
                                echo "<td>" . (empty($row['akun_perawat']) ? '-' : htmlspecialchars($row['akun_perawat'])) . "</td>"; // Tampilkan '-' jika akun perawat kosong
                                echo "<td>" . htmlspecialchars($row['id_reservasi']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['diagnosis']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['rencana_perawatan']) . "</td>";
                                echo "<td>
                                        <div class='truncate-text' title='" . htmlspecialchars($row['catatan_pasca_pemeriksaan']) . "'>
                                            " . htmlspecialchars($row['catatan_pasca_pemeriksaan']) . "
                                        </div>
                                      </td>";
                                echo "<td>" . htmlspecialchars($row['tanggal_periksa']) . "</td>";
                                echo "<td class='aksi-column' style='text-align:center;'>
                                        <a href='edit_catatan_medis.php?edit=" . htmlspecialchars($row['id_catatan']) . "' class='btn btn-warning btn-sm'>Edit</a>
                                        <a href='catatan_medis.php?id_catatan=" . htmlspecialchars($row['id_catatan']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus catatan ini?\")'>Hapus</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10' style='text-align: center;'>Tidak ada data catatan medis.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
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