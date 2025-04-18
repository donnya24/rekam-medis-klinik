<?php
// Sertakan file header, sidebar, dan koneksi database
include "../../includes/apoteker/sidebar.php";
include "../../config/db.php";

// Atur rentang tanggal default
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$until_date = isset($_GET['until_date']) ? $_GET['until_date'] : date('Y-m-t');

// Atur sorting default
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'tanggal_pembayaran';

// Validasi kolom yang dapat digunakan untuk sorting
$valid_sort_columns = ['nama_obat', 'jenis_obat', 'tanggal_pembayaran', 'jumlah_obat', 'total_harga'];
if (!in_array($sort_by, $valid_sort_columns)) {
    $sort_by = 'tanggal_pembayaran';
}

// Query untuk mengambil data transaksi obat
$sql = "SELECT p.id_pembayaran, o.nama_obat, o.jenis_obat, p.jumlah_obat, o.harga, 
        (p.jumlah_obat * o.harga) AS total_harga, p.tanggal_pembayaran 
        FROM pembayaran p
        JOIN obat o ON p.id_obat = o.id_obat
        WHERE p.tanggal_pembayaran BETWEEN '$from_date' AND '$until_date'
        ORDER BY $sort_by ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Obat</title>
    <link href="../../assets/css/bootstrap.css" rel="stylesheet" />
    <style>
    @media print {
        @page {
            margin: 0;
            size: auto;
        }

        body * {
            visibility: hidden;
        }

        .print-area, .print-area * {
            visibility: visible;
        }

        .table-responsive, .table-responsive * {
            visibility: visible;
        }

        form, button, select, .btn, .form-control:not([type="date"]) {
            display: none !important;
        }

        .container {
            display: block !important;
            position: relative;
        }

        h1 {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .table-responsive {
            margin-top: 20px;
        }
    }

        #page-wrapper {
            margin-left: 250px; /* Sesuaikan dengan lebar sidebar */
            padding: 20px;
        }

        @media (max-width: 768px) {
            #page-wrapper {
                margin-left: 0;
            }
        }

    </style>
</head>
<body>
<div id="wrapper">
    <div id="page-wrapper">
        <div class="container-fluid mt-4">
            <h1 class="mb-4 text-center print-area">Laporan Transaksi Obat</h1>

            <!-- Form Filter -->
            <form method="GET" action="">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="from_date">Dari Tanggal:</label>
                        <input type="date" class="form-control" name="from_date" value="<?= htmlspecialchars($from_date) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="until_date">Sampai Tanggal:</label>
                        <input type="date" class="form-control" name="until_date" value="<?= htmlspecialchars($until_date) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="sort_by">Urutkan Berdasarkan:</label>
                        <select class="form-control" name="sort_by">
                            <option value="nama_obat" <?= $sort_by == 'nama_obat' ? 'selected' : '' ?>>Nama Obat</option>
                            <option value="jenis_obat" <?= $sort_by == 'jenis_obat' ? 'selected' : '' ?>>Jenis Obat</option>
                            <option value="tanggal_pembayaran" <?= $sort_by == 'tanggal_pembayaran' ? 'selected' : '' ?>>Tanggal</option>
                            <option value="jumlah_obat" <?= $sort_by == 'jumlah_obat' ? 'selected' : '' ?>>Jumlah</option>
                            <option value="total_harga" <?= $sort_by == 'total_harga' ? 'selected' : '' ?>>Total Harga</option>
                        </select>
                    </div>
                    <div class="col-md-3 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                    </div>
                </div>
            </form>

            <!-- Tombol Cetak -->
            <div class="text-end mb-3">
                <button class="btn btn-success" onclick="window.print()">Cetak Laporan</button>
            </div>

            <!-- Tabel laporan transaksi -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary text-white text-center">
                        <tr>
                            <th>No.</th>
                            <th>Nama Obat</th>
                            <th>Jenis Obat</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0) { 
                            $no = 1;
                            $grand_total = 0;
                            while ($row = $result->fetch_assoc()) {
                                $total = $row['jumlah_obat'] * $row['harga'];
                                $grand_total += $total;
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama_obat']); ?></td>
                            <td><?= htmlspecialchars($row['jenis_obat']); ?></td>
                            <td class="text-center"><?= date('d F Y', strtotime($row['tanggal_pembayaran'])); ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['jumlah_obat']); ?></td>
                            <td class="text-end">Rp. <?= number_format($total, 0, ',', '.'); ?></td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Total Keseluruhan</td>
                            <td class="fw-bold text-end">Rp. <?= number_format($grand_total, 0, ',', '.'); ?></td>
                        </tr>
                        <?php } else { ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data transaksi.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Skrip JS -->
<script src="js/jquery-1.10.2.js">
        document.addEventListener("DOMContentLoaded", function() {
        document.querySelector(".btn-success").addEventListener("click", function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
        window.addEventListener("afterprint", function() {
            window.location.href = "laporan_perawatan.php";
        });
    });
</script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script src="../../assets/js/custom-scripts.js"></script>
</body>
</html>
