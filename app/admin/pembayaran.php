<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

// Proses Hapus Pembayaran
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id_pembayaran'])) {
    $id_pembayaran = mysqli_real_escape_string($conn, $_GET['id_pembayaran']);

    $delete_query = "DELETE FROM pembayaran WHERE id_pembayaran = '$id_pembayaran'";
    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('Pembayaran berhasil dihapus!'); window.location='pembayaran.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
    }
}

// Mengambil data pembayaran
$query = "SELECT pembayaran.*, pasien.nama_pasien, obat.nama_obat, perawatan.nama_perawatan
          FROM pembayaran
          JOIN pasien ON pembayaran.id_pasien = pasien.id_pasien
          LEFT JOIN obat ON pembayaran.id_obat = obat.id_obat
          LEFT JOIN perawatan ON pembayaran.id_perawatan = perawatan.id_perawatan
          ORDER BY pembayaran.id_pembayaran ASC";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pembayaran</title>
    <!-- Include Bootstrap and custom styles -->
    <link href="../../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../../assets/css/font-awesome.css" rel="stylesheet" />
    <link href="../../assets/css/custom-styles.css" rel="stylesheet" />
</head>
<body>
    <div id="wrapper">
        <div id="page-wrapper">
            <div id="page-inner">
            <h1 class="page-header text-center">Data Pembayaran</h1>
                <a href="edit_pembayaran.php" class="btn btn-primary mb-3">Tambah Pembayaran</a>
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary text-white text-center">
                        <tr>
                            <th class="text-center">No.</th>
                            <th class="text-center">Nama Pasien</th>
                            <th class="text-center">Nama Obat</th>
                            <th class="text-center">Jumlah Obat</th>
                            <th class="text-center">Nama Perawatan</th>
                            <th class="text-center">Jumlah Perawatan</th>
                            <th class="text-center">Metode Pembayaran</th>
                            <th class="text-center">Total Harga</th>
                            <th class="text-center">Bayar</th>
                            <th class="text-center">Kembali</th>
                            <th class="text-center">Tanggal Pembayaran</th>
                            <th class="text-center" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td class='text-center'>{$no}</td>";
                                echo "<td class='text-center'>" . htmlspecialchars($row['nama_pasien']) . "</td>";
                                echo "<td class='text-center'>" . ($row['nama_obat'] ?? '-') . "</td>";
                                echo "<td class='text-center'>" . ($row['jumlah_obat'] ?? '-') . "</td>";
                                echo "<td class='text-center'>" . ($row['nama_perawatan'] ?? '-') . "</td>";
                                echo "<td class='text-center'>" . ($row['jumlah_perawatan'] ?? '-') . "</td>";
                                echo "<td class='text-center'>" . htmlspecialchars($row['metode_pembayaran']) . "</td>";
                                echo "<td class='text-center'>Rp. " . number_format($row['total_harga'], 0, ',', '.') . "</td>";
                                echo "<td class='text-center'>Rp. " . number_format($row['bayar'], 0, ',', '.') . "</td>";
                                echo "<td class='text-center'>Rp. " . number_format($row['kembali'], 0, ',', '.') . "</td>";
                                echo "<td class='text-center'>" . htmlspecialchars($row['tanggal_pembayaran']) . "</td>";
                                echo "<td class='text-center'>
                                        <div class='d-flex justify-content-center gap-2'>
                                            <a href='edit_pembayaran.php?id_pembayaran=" . htmlspecialchars($row['id_pembayaran']) . "' class='btn btn-warning btn-sm'>Edit</a>
                                            <a href='struk_pembayaran.php?id_pembayaran=" . htmlspecialchars($row['id_pembayaran']) . "' class='btn btn-success btn-sm' target='_blank'>Cetak</a>
                                            <a href='pembayaran.php?action=delete&id_pembayaran=" . htmlspecialchars($row['id_pembayaran']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>
                                        </div>
                                    </td>";
                                echo "</tr>";
                                $no++;
                            }
                        } else {
                            echo "<tr><td colspan='12' class='text-center'>Data pembayaran belum tersedia.</td></tr>";
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