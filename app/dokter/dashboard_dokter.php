<?php
session_start(); // Mulai sesi

// Jika belum login, redirect ke halaman login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../auth/login.php");
    exit();
}

include "../../includes/dokter/header.php";
include "../../includes/dokter/sidebar.php";
include "../../config/db.php";

// Ambil ID dokter yang sedang login
$id_dokter = $_SESSION['id_user'];

// Query untuk mendapatkan data dokter yang login
$query_dokter = mysqli_query($conn, "SELECT * FROM dokter WHERE akun_dokter = (SELECT username FROM user WHERE id = $id_dokter)");
$dokter = mysqli_fetch_assoc($query_dokter);
$id_dokter = $dokter['id_dokter'];

// Pagination Setup
$per_page = 10; // Banyak data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

// Query untuk mengambil data pasien yang sudah reservasi dengan dokter ini
$query_pasien = mysqli_query($conn, "
    SELECT DISTINCT p.* 
    FROM pasien p
    JOIN reservasi r ON p.id_pasien = r.id_pasien
    WHERE r.id_dokter = $id_dokter
    ORDER BY p.id_pasien DESC 
    LIMIT $start, $per_page
");

// Query untuk menghitung total pasien yang sudah reservasi dengan dokter ini
$total_pasien_query = mysqli_query($conn, "
    SELECT COUNT(DISTINCT p.id_pasien) AS total 
    FROM pasien p
    JOIN reservasi r ON p.id_pasien = r.id_pasien
    WHERE r.id_dokter = $id_dokter
");
$total_pasien_row = mysqli_fetch_assoc($total_pasien_query);
$total_pages = ceil($total_pasien_row['total'] / $per_page);

// Query Total Pasien yang sudah reservasi dengan dokter ini
$result_total_pasien = mysqli_query($conn, "
    SELECT COUNT(DISTINCT p.id_pasien) AS total_pasien 
    FROM pasien p
    JOIN reservasi r ON p.id_pasien = r.id_pasien
    WHERE r.id_dokter = $id_dokter
");
$total_pasien = mysqli_fetch_assoc($result_total_pasien)['total_pasien'];

// Query Total Reservasi Hari Ini untuk dokter ini
$tanggal_hari_ini = date('Y-m-d');
$query_total_reservasi_hari_ini = mysqli_query($conn, "
    SELECT COUNT(*) AS total_reservasi_hari_ini 
    FROM reservasi 
    WHERE tanggal = '$tanggal_hari_ini' AND id_dokter = $id_dokter
");
$total_reservasi_hari_ini = mysqli_fetch_assoc($query_total_reservasi_hari_ini)['total_reservasi_hari_ini'];

// Query Reservasi Aktif untuk dokter ini
$query_reservasi_aktif = mysqli_query($conn, "
    SELECT COUNT(*) AS reservasi_aktif 
    FROM reservasi 
    WHERE DATE(tanggal) >= '$tanggal_hari_ini' AND id_dokter = $id_dokter
");
$reservasi_aktif = mysqli_fetch_assoc($query_reservasi_aktif)['reservasi_aktif'];

// Query Stok Obat Tersedia
$query_stok_obat = mysqli_query($conn, "
    SELECT SUM(stok) AS total_stok 
    FROM obat
");
$total_stok = mysqli_fetch_assoc($query_stok_obat)['total_stok'];

// Query Reservasi Hari Ini untuk dokter ini
$query_reservasi_hari_ini = mysqli_query($conn, "
    SELECT r.*, p.nama_pasien, d.nama_dokter 
    FROM reservasi r
    JOIN pasien p ON r.id_pasien = p.id_pasien
    JOIN dokter d ON r.id_dokter = d.id_dokter
    WHERE r.tanggal = '$tanggal_hari_ini' AND r.id_dokter = $id_dokter
");

// Query Transaksi Pembayaran Terbaru untuk pasien dokter ini
$query_pembayaran_terbaru = mysqli_query($conn, "
    SELECT pb.*, p.nama_pasien 
    FROM pembayaran pb
    JOIN pasien p ON pb.id_pasien = p.id_pasien
    JOIN reservasi r ON pb.id_reservasi = r.id_reservasi
    WHERE r.id_dokter = $id_dokter
    ORDER BY pb.tanggal_pembayaran DESC
    LIMIT 5
");

// Query Laporan Transaksi Perawatan Bulan Ini untuk dokter ini
$bulan_ini = date('Y-m');
$query_perawatan_bulan_ini = mysqli_query($conn, "
    SELECT tp.*, p.nama_pasien, pr.nama_perawatan 
    FROM transaksi_perawatan tp
    JOIN pasien p ON tp.id_pasien = p.id_pasien
    JOIN perawatan pr ON tp.id_perawatan = pr.id_perawatan
    JOIN reservasi r ON tp.id_pasien = r.id_pasien
    WHERE DATE_FORMAT(tp.tanggal, '%Y-%m') = '$bulan_ini' AND r.id_dokter = $id_dokter
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dokter - Klinikku</title>
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

        .doctor-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .doctor-name {
            font-size: 22px;
            font-weight: bold;
        }

        .doctor-specialty {
            font-size: 16px;
            color: #aaa;
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

        /* Tabel */
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
            color: #333 !important;
            font-weight: bold;
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
    <!-- Content -->
    <div id="page-wrapper">
        <div id="page-inner">
            <!-- Heading -->
            <div class="row">
                <div class="col-md-12">
                    <h1 class="page-header">Dashboard Dokter</h1>
                    <div class="doctor-info">
                        <div>
                            <span class="doctor-name">Dr. <?php echo htmlspecialchars($dokter['nama_dokter']); ?></span>
                            <span class="doctor-specialty">Spesialis <?php echo htmlspecialchars($dokter['spesialis']); ?></span>
                        </div>
                        <div>
                            <span>ID Dokter: <?php echo htmlspecialchars($dokter['id_dokter']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Statistik -->
            <div class="row">
                <div class="col-md-3">
                    <div class="panel panel-primary">
                        <div class="panel-heading">Total Pasien Anda</div>
                        <div class="panel-body">
                            <h3><?php echo $total_pasien; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-success">
                        <div class="panel-heading">Reservasi Hari Ini</div>
                        <div class="panel-body">
                            <h3><?php echo $total_reservasi_hari_ini; ?></h3>
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
                        <div class="panel-heading">Data Pasien Anda</div>
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
                                        <th>Jumlah Kunjungan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($query_pasien) > 0) {
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($query_pasien)) {
                                            // Hitung jumlah kunjungan pasien ini ke dokter yang login
                                            $query_kunjungan = mysqli_query($conn, "
                                                SELECT COUNT(*) AS jumlah_kunjungan 
                                                FROM reservasi 
                                                WHERE id_pasien = {$row['id_pasien']} 
                                                AND id_dokter = $id_dokter
                                            ");
                                            $kunjungan = mysqli_fetch_assoc($query_kunjungan);
                                            
                                            // Query untuk mendapatkan ID reservasi terbaru untuk pasien ini
                                            $query_reservasi = mysqli_query($conn, "
                                                SELECT id_reservasi 
                                                FROM reservasi 
                                                WHERE id_pasien = {$row['id_pasien']} 
                                                AND id_dokter = $id_dokter
                                                ORDER BY tanggal DESC 
                                                LIMIT 1
                                            ");
                                            $reservasi = mysqli_fetch_assoc($query_reservasi);
                                            $id_reservasi = $reservasi ? $reservasi['id_reservasi'] : '';
                                            
                                            echo "<tr>";
                                            echo "<td>" . $no . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nama_pasien']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
                                            echo "<td style='text-align:center;'>" . htmlspecialchars($row['tanggal_lahir']) . "</td>";
                                            echo "<td style='text-align:center;'>" . ($row['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan') . "</td>";
                                            echo "<td style='text-align:center;'>" . htmlspecialchars($row['no_telepon']) . "</td>";
                                            echo "<td style='text-align:center;'>" . $kunjungan['jumlah_kunjungan'] . "</td>";
                                            echo "<td style='text-align:center;'>";
                                            if ($id_reservasi) {
                                                echo "<a href='struk_reservasi.php?id_reservasi=" . htmlspecialchars($id_reservasi) . "' class='btn btn-info btn-sm'>Lihat Struk</a>";
                                            } else {
                                                echo "<span class='btn btn-secondary btn-sm disabled'>Tidak ada struk</span>";
                                            }
                                            echo "</td>";
                                            echo "</tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' style='text-align: center;'>Belum ada pasien yang melakukan reservasi dengan Anda</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                            
                            <!-- Pagination -->
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>" class="page-link">Previous</a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>" class="page-link">Next</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Reservasi Pasien dengan Dokter Hari Ini -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Jadwal Reservasi Hari Ini</div>
                        <div class="panel-body">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Pasien</th>
                                        <th>Tanggal Reservasi</th>
                                        <th>Waktu Reservasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($query_reservasi_hari_ini) > 0) {
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($query_reservasi_hari_ini)) {
                                            echo "<tr>";
                                            echo "<td>" . $no . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nama_pasien']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['tanggal']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['waktu']) . "</td>";
                                            echo "<td style='text-align:center;'>";
                                            echo "<a href='struk_reservasi.php?id_reservasi=" . htmlspecialchars($row['id_reservasi']) . "' class='btn btn-info btn-sm'>Lihat Struk</a>";
                                            echo "</td>";
                                            echo "</tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' style='text-align: center;'>Tidak ada jadwal konsultasi hari ini</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Transaksi Pembayaran Terbaru -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Riwayat Pembayaran Pasien</div>
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
                                    if (mysqli_num_rows($query_pembayaran_terbaru) > 0) {
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($query_pembayaran_terbaru)) {
                                            echo "<tr>";
                                            echo "<td>" . $no . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nama_pasien']) . "</td>";
                                            echo "<td>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</td>";
                                            echo "<td>" . htmlspecialchars($row['metode_pembayaran']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['tanggal_pembayaran']) . "</td>";
                                            echo "</tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' style='text-align: center;'>Belum ada riwayat pembayaran</td></tr>";
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
</body>
</html>