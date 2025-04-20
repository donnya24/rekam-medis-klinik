<?php
session_start(); // Mulai sesi

// Jika belum login, redirect ke halaman login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../auth/login.php");
    exit();
}

include "../../includes/perawat/header.php";
include "../../includes/perawat/sidebar.php";
include "../../config/db.php";

// Ambil ID perawat yang sedang login
$id_perawat = $_SESSION['id_user'];

// Query untuk mendapatkan data perawat yang login
$query_perawat = mysqli_query($conn, "SELECT * FROM perawat WHERE akun_perawat = (SELECT username FROM user WHERE id = $id_perawat)");
$perawat = mysqli_fetch_assoc($query_perawat);
$id_perawat = $perawat['id_perawat'];

// Pagination Setup
$per_page = 10; // Banyak data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

// Query untuk mengambil data catatan medis dengan perawat ini
$query_catatan = mysqli_query($conn, "
    SELECT cm.*, p.nama_pasien, d.nama_dokter
    FROM catatan_medis cm
    JOIN reservasi r ON cm.id_reservasi = r.id_reservasi
    JOIN pasien p ON r.id_pasien = p.id_pasien
    JOIN dokter d ON r.id_dokter = d.id_dokter
    WHERE cm.id_perawat = $id_perawat
    ORDER BY cm.tanggal_periksa DESC
    LIMIT $start, $per_page
");

// Query untuk menghitung total catatan medis perawat ini
$total_catatan_query = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM catatan_medis
    WHERE id_perawat = $id_perawat
");
$total_catatan_row = mysqli_fetch_assoc($total_catatan_query);
$total_pages = ceil($total_catatan_row['total'] / $per_page);

// Query Total Catatan Medis perawat ini
$result_total_catatan = mysqli_query($conn, "
    SELECT COUNT(*) AS total_catatan 
    FROM catatan_medis
    WHERE id_perawat = $id_perawat
");
$total_catatan = mysqli_fetch_assoc($result_total_catatan)['total_catatan'];

// Query Catatan Medis Hari Ini untuk perawat ini
$tanggal_hari_ini = date('Y-m-d');
$query_catatan_hari_ini = mysqli_query($conn, "
    SELECT COUNT(*) AS catatan_hari_ini 
    FROM catatan_medis 
    WHERE DATE(tanggal_periksa) = '$tanggal_hari_ini' AND id_perawat = $id_perawat
");
$catatan_hari_ini = mysqli_fetch_assoc($query_catatan_hari_ini)['catatan_hari_ini'];

// Query Perawatan Aktif untuk perawat ini
$query_perawatan_aktif = mysqli_query($conn, "
    SELECT COUNT(*) AS perawatan_aktif 
    FROM reservasi r
    JOIN catatan_medis cm ON r.id_reservasi = cm.id_reservasi
    WHERE DATE(r.tanggal) >= '$tanggal_hari_ini' AND cm.id_perawat = $id_perawat
");
$perawatan_aktif = mysqli_fetch_assoc($query_perawatan_aktif)['perawatan_aktif'];

// Query Stok Obat Tersedia
$query_stok_obat = mysqli_query($conn, "
    SELECT SUM(stok) AS total_stok 
    FROM obat
");
$total_stok = mysqli_fetch_assoc($query_stok_obat)['total_stok'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Perawat - Klinikku</title>
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

        .nurse-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nurse-name {
        font-size: 18px;
        font-weight: bold;
        }

        .nurse-plain {
        font-weight: normal;
        font-size: 16px;
        }

        .nurse-specialty {
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
        
        /* Style untuk memotong teks panjang */
        .truncate-text {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }
        
        /* Style untuk kolom aksi */
        .aksi-column {
            width: 120px;
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
                    <h1 class="page-header">Selamat Datang di Dashboard Perawat</h1>
                    <div class="nurse-info">
                        <div>
                        <span class="nurse-name"><strong>Perawat:</strong> <span class="nurse-plain"><?php echo htmlspecialchars($perawat['nama_perawat']); ?></span></span>
                        </div>
                        <div>
                            <span>ID Perawat: <?php echo htmlspecialchars($perawat['id_perawat']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Statistik -->
            <div class="row">
                <div class="col-md-3">
                    <div class="panel panel-primary">
                        <div class="panel-heading">Total Perawatan</div>
                        <div class="panel-body">
                            <h3><?php echo $total_catatan; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Perawatan Pasien -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Perawatan Pasien</div>
                        <div class="panel-body">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Pasien</th>
                                        <th>Dokter</th>
                                        <th>Diagnosis</th>
                                        <th>Rencana Perawatan</th>
                                        <th>Catatan</th>
                                        <th>Tanggal Periksa</th>
                                        <th class="aksi-column">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($query_catatan) > 0) {
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($query_catatan)) {
                                            echo "<tr>";
                                            echo "<td>" . $no . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nama_pasien']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nama_dokter']) . "</td>";
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
                                                  </td>";
                                            echo "</tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' style='text-align: center;'>Belum ada data perawatan pasien</td></tr>";
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
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../../assets/js/jquery-1.10.2.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script src="../../assets/js/custom-scripts.js"></script>
</body>
</html>
