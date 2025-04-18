<?php
include "../../includes/administrasi/sidebar.php";
include "../../config/db.php";

$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$until_date = isset($_GET['until_date']) ? $_GET['until_date'] : date('Y-m-t');

$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'tanggal';

$valid_sort_columns = ['nama_pasien', 'nama_perawatan', 'tanggal', 'total'];
if (!in_array($sort_by, $valid_sort_columns)) {
    $sort_by = 'tanggal';
}

$sql = "SELECT tp.id_transaksi, p.nama_pasien, pr.nama_perawatan, tp.tanggal, 
               tp.jumlah, pr.harga_perawatan, 
               (tp.jumlah * pr.harga_perawatan) AS total
        FROM transaksi_perawatan tp
        JOIN perawatan pr ON tp.id_perawatan = pr.id_perawatan
        JOIN pasien p ON tp.id_pasien = p.id_pasien
        WHERE tp.tanggal BETWEEN '$from_date' AND '$until_date'
        ORDER BY $sort_by";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Perawatan</title>
    <link href="../../assets/css/bootstrap.css" rel="stylesheet" />
    <style>
            @media print {
        @page {
            margin: 0;
            size: auto; /* Menghilangkan header dan footer browser */
        }
        body {
            visibility: hidden;
        }
        .print-area {
            visibility: visible;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }
    }

        #page-wrapper {
            margin-left: 250px;
            padding: 20px;
        }

        @media (max-width: 768px) {
            #page-wrapper {
                margin-left: 0;
            }
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .print-area, .print-area * {
                visibility: visible;
            }
            .print-area {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }
            form, button, select, .btn, .form-control {
                display: none !important;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            table, th, td {
                border: 1px solid black;
            }
            th, td {
                padding: 8px;
                text-align: left;
            }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <div id="page-wrapper">
        <div class="container-fluid mt-4 print-area">
            <h1 class="mb-4 text-center">Laporan Transaksi Perawatan</h1>

            <!-- Form Filter -->
            <form method="GET" action="">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="from_date">From Date:</label>
                        <input type="date" class="form-control" name="from_date" value="<?= htmlspecialchars($from_date) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="until_date">Until Date:</label>
                        <input type="date" class="form-control" name="until_date" value="<?= htmlspecialchars($until_date) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="sort_by">Sort By:</label>
                        <select class="form-control" name="sort_by">
                            <option value="nama_pasien" <?= $sort_by == 'nama_pasien' ? 'selected' : '' ?>>Nama Pasien</option>
                            <option value="nama_perawatan" <?= $sort_by == 'nama_perawatan' ? 'selected' : '' ?>>Nama Perawatan</option>
                            <option value="tanggal" <?= $sort_by == 'tanggal' ? 'selected' : '' ?>>Tanggal</option>
                            <option value="total" <?= $sort_by == 'total' ? 'selected' : '' ?>>Total</option>
                        </select>
                    </div>
                    <div class="col-md-3 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">Process</button>
                    </div>
                </div>
            </form>

            <!-- Tombol Cetak -->
            <div class="text-end mb-3">
                <button onclick="window.print()" class="btn btn-success">Cetak Laporan</button>
            </div>

            <!-- Tabel Laporan -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>No.</th>
                            <th>Nama Pasien</th>
                            <th>Nama Perawatan</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        $no = 1;
                        $grand_total = 0;
                        while ($row = $result->fetch_assoc()) {
                            $grand_total += $row['total'];
                            echo "<tr>";
                            echo "<td>" . $no . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_pasien']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_perawatan']) . "</td>";
                            echo "<td>" . date('d F Y', strtotime($row['tanggal'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['jumlah']) . "</td>";
                            echo "<td>Rp. " . number_format($row['total'], 0, ',', '.') . "</td>";
                            echo "</tr>";
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>Tidak ada data transaksi perawatan.</td></tr>";
                    }
                    ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Total Keseluruhan</td>
                            <td class="fw-bold">Rp. <?= number_format($grand_total ?? 0, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/js/bootstrap.min.js">
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
</body>
</html>

<?php $conn->close(); ?>
