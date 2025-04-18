<?php
include "../../includes/perawat/sidebar.php";
include "../../config/db.php";

// Fetch data reservasi
$sql = "
    SELECT reservasi.id_reservasi, reservasi.tanggal, reservasi.waktu, 
           pasien.nama_pasien, 
           perawatan.nama_perawatan, 
           dokter.nama_dokter, 
           ruangan.nama_ruangan
    FROM reservasi
    LEFT JOIN pasien ON reservasi.id_pasien = pasien.id_pasien
    LEFT JOIN perawatan ON reservasi.id_perawatan = perawatan.id_perawatan
    LEFT JOIN dokter ON reservasi.id_dokter = dokter.id_dokter
    LEFT JOIN ruangan ON reservasi.id_ruangan = ruangan.id_ruangan
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Reservasi</title>
    <link href="../../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../../assets/css/font-awesome.css" rel="stylesheet" />
    <link href="../../assets/css/custom-styles.css" rel="stylesheet" />
    <style>
        .table th, .table td {
            text-align: center;
        }
        .table td:first-child {
            text-align: center;
        }
    </style>
</head>
<body>
<div id="wrapper">
    <div id="page-wrapper">
        <div id="page-inner">
            <h1 class="page-header" style="text-align: center;">Data Reservasi</h1>
            <table class="table table-bordered table-striped">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>No.</th>
                        <th>Nama Pasien</th>
                        <th>Perawatan</th>
                        <th>Dokter</th>
                        <th>Ruangan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $no . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_pasien']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_perawatan']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_dokter']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_ruangan']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['tanggal']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['waktu']) . "</td>";
                            echo "</tr>";
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center;'>Tidak ada data reservasi</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../../assets/js/jquery-1.10.2.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script src="../../assets/js/custom-scripts.js"></script>
</body>
</html>

<?php $conn->close(); ?>