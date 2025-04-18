<?php
session_start(); // Mulai sesi

// Jika belum login, redirect ke halaman login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../auth/login.php");
    exit();
}

include "../../includes/admin/header.php";
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

// Query untuk mendapatkan data pasien
$query_pasien = mysqli_query($conn, "SELECT * FROM pasien");

// Pagination Setup
$per_page = 10; // Banyak data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

// Query untuk mengambil data pasien dengan paginasi
$query_pasien = mysqli_query($conn, "SELECT * FROM pasien ORDER BY id_pasien DESC LIMIT $start, $per_page");

// Query untuk menghitung total pasien
$total_pasien_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pasien");
$total_pasien_row = mysqli_fetch_assoc($total_pasien_query);
$total_pages = ceil($total_pasien_row['total'] / $per_page);

// Query Total Pasien
$result_total_pasien = mysqli_query($conn, "SELECT COUNT(*) AS total_pasien FROM pasien");
$total_pasien = mysqli_fetch_assoc($result_total_pasien)['total_pasien'];

// Query Pasien Hari Ini
$tanggal_hari_ini = date('Y-m-d');
$result_pasien_hari_ini = mysqli_query($conn, "SELECT COUNT(*) AS pasien_hari_ini FROM reservasi WHERE tanggal = '$tanggal_hari_ini'");
$pasien_hari_ini = mysqli_fetch_assoc($result_pasien_hari_ini)['pasien_hari_ini'];

// Query Reservasi Aktif
$result_reservasi_aktif = mysqli_query($conn, "SELECT COUNT(*) AS reservasi_aktif FROM reservasi WHERE DATE(tanggal) >= '$tanggal_hari_ini'");
$reservasi_aktif = mysqli_fetch_assoc($result_reservasi_aktif)['reservasi_aktif'];

// Query Total Obat Terjual
$result_obat_terjual = mysqli_query($conn, "SELECT SUM(stok) AS stok_total FROM obat");
$stok_obat = mysqli_fetch_assoc($result_obat_terjual)['stok_total'];

// Query untuk menampilkan Stok Obat
$result_stok_obat = mysqli_query($conn, "SELECT SUM(stok) AS total_stok FROM obat");
$total_stok = mysqli_fetch_assoc($result_stok_obat)['total_stok'];

// Query untuk Data Pasien
$query_pasien = mysqli_query($conn, "SELECT * FROM pasien ORDER BY id_pasien DESC");

// Query untuk Daftar Transaksi Pembayaran Terbaru
$query_transaksi_pembayaran = mysqli_query($conn, "
    SELECT pembayaran.*, pasien.nama_pasien 
    FROM pembayaran 
    JOIN pasien ON pembayaran.id_pasien = pasien.id_pasien 
    ORDER BY pembayaran.tanggal_pembayaran DESC 
    LIMIT 5
");

// Query untuk Laporan Transaksi Perawatan Bulan Ini
$bulan_ini = date('Y-m');
$query_transaksi_perawatan = mysqli_query($conn, "
    SELECT transaksi_perawatan.*, pasien.nama_pasien, perawatan.nama_perawatan 
    FROM transaksi_perawatan 
    JOIN pasien ON transaksi_perawatan.id_pasien = pasien.id_pasien 
    JOIN perawatan ON transaksi_perawatan.id_perawatan = perawatan.id_perawatan 
    WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini' 
    ORDER BY tanggal DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Klinik</title>
    <link href="../../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../../assets/css/font-awesome.css" rel="stylesheet" />
    <link href="../../assets/css/custom-styles.css" rel="stylesheet" />

    <style>
        /* Global Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0a192f, #1f4068, #2a5298);
            color: white;
            margin: 0;
            padding: 0;
        }

        /* Wrapper */
        #wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        /* Page Wrapper */
        #page-wrapper {
            width: 85%;
            background: rgba(26, 26, 26, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        /* Header */
        h1.page-header {
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            color: #ffffff;
            text-transform: uppercase;
        }

        /* Statistik Panels */
        .panel {
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            color: white;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            border: none;
        }

        .panel:hover {
            transform: scale(1.05);
            box-shadow: 0px 5px 15px rgba(255, 255, 255, 0.2);
        }

        /* Custom Panel Colors */
        .panel-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }

        .panel-success {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }

        .panel-info {
            background: linear-gradient(135deg, #17a2b8, #117a8b);
        }

        .panel-warning {
            background: linear-gradient(135deg, #ffc107, #d39e00);
        }

        /* Statistik Angka */
        .panel-body h3 {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
            color: #ffffff;
        }

        /* Tabel Pasien */
        .table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background: rgba(255, 255, 255, 0.2);
        }

        .table th, .table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .table th {
            color: #ffffff;
            font-weight: bold;
        }

        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .table tbody tr td {
            color: #333 !important; /* Warna teks gelap */
            font-weight: bold; /* Biar lebih jelas */
        }

        .table-bordered th, .table-bordered td {
            border: 1px solid #ddd !important;
            text-align: center;
            vertical-align: middle;
        }

        .table thead th {
            background-color: #007bff !important;
            color: white !important;
        }

        .table tbody tr:nth-child(odd) {
            background-color: #f9f9f9 !important;
        }

        /* Paginasi */
        .pagination {
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .pagination .page-item {
            list-style: none;
            margin: 0 5px;
        }

        .pagination .page-link {
            padding: 10px 15px;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 5px;
            transition: 0.3s;
        }

        .pagination .page-item.active .page-link {
            background: #007bff;
            color: white;
            font-weight: bold;
        }

        .pagination .page-link:hover {
            background: rgba(255, 255, 255, 0.4);
        }
    </style>    
</head>
<body>
<div id="wrapper">
    <!-- Top Navbar -->

    <!-- Content -->
    <div id="page-wrapper">
        <div id="page-inner">
            <!-- Heading -->
            <div class="row">
                <div class="col-md-12">
                    <h1 class="page-header">Selamat Datang di Dashboard Klinik</h1>
                </div>
            </div>

            <!-- Panel Statistik -->
            <div class="row">
                <div class="col-md-3">
                    <div class="panel panel-primary">
                        <div class="panel-heading">Total Pasien</div>
                        <div class="panel-body">
                            <h3><?php echo $total_pasien; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-success">
                        <div class="panel-heading">Reservasi Hari Ini</div>
                        <div class="panel-body">
                            <h3><?php echo $pasien_hari_ini; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-info">
                        <div class="panel-heading">Reservasi Aktif</div>
                        <div class="panel-body">
                            <h3><?php echo $reservasi_aktif; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-warning">
                        <div class="panel-heading">Stok Obat Tersedia</div>
                        <div class="panel-body">
                            <h3><?php echo $total_stok > 0 ? $total_stok : '0'; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Data Pasien -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Data Pasien</div>
                        <div class="panel-body">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Pasien</th>
                                        <th>Alamat</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Jenis Kelamin</th>
                                        <th>No. Telepon</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($query_pasien) > 0) {
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($query_pasien)) {
                                            echo "<tr>";
                                            echo "<td>" . $no . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nama_pasien']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
                                            echo "<td style='text-align:center;'>" . htmlspecialchars($row['tanggal_lahir']) . "</td>";
                                            echo "<td style='text-align:center;'>" . ($row['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan') . "</td>";
                                            echo "<td style='text-align:center;'>" . htmlspecialchars($row['no_telepon']) . "</td>";
                                            echo "<td style='text-align:center;'>
                                                    <a href='pendaftaran.php?action=edit&id=" . htmlspecialchars($row['id_pasien']) . "' class='btn btn-warning btn-sm'>Edit</a>
                                                    <a href='pendaftaran.php?action=delete&id=" . htmlspecialchars($row['id_pasien']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>
                                                  </td>";
                                            echo "</tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' style='text-align: center;'>Tidak ada data pasien</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <a href="pasien.php" class="btn btn-primary">Lihat Semua Pasien</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Transaksi Pembayaran Terbaru -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Daftar Transaksi Pembayaran Terbaru</div>
                        <div class="panel-body">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Pasien</th>
                                        <th>Total Harga</th>
                                        <th>Metode Pembayaran</th>
                                        <th>Tanggal Pembayaran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($query_transaksi_pembayaran) > 0) {
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($query_transaksi_pembayaran)) {
                                            echo "<tr>";
                                            echo "<td>" . $no . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nama_pasien']) . "</td>";
                                            echo "<td>Rp. " . number_format($row['total_harga'], 0, ',', '.') . "</td>";
                                            echo "<td>" . htmlspecialchars($row['metode_pembayaran']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['tanggal_pembayaran']) . "</td>";
                                            echo "</tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' style='text-align: center;'>Tidak ada transaksi pembayaran terbaru</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Laporan Transaksi Perawatan Bulan Ini -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Laporan Transaksi Perawatan Bulan Ini</div>
                        <div class="panel-body">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Pasien</th>
                                        <th>Nama Perawatan</th>
                                        <th>Tanggal Transaksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($query_transaksi_perawatan) > 0) {
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($query_transaksi_perawatan)) {
                                            echo "<tr>";
                                            echo "<td>" . $no . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nama_pasien']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nama_perawatan']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['tanggal']) . "</td>";
                                            echo "</tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' style='text-align: center;'>Tidak ada transaksi perawatan bulan ini</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../../assets/js/jquery-1.10.2.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script src="../../assets/js/custom-scripts.js"></script>

<script>
    setInterval(function() {
        location.reload();
    }, 10000); // Refresh setiap 10 detik
</script>

</body>
</html>