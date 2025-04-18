<?php
include "../../config/db.php";

// Ambil ID Pembayaran dari URL
$id_pembayaran = $_GET['id_pembayaran'];

// Ambil data pembayaran berdasarkan ID
$query = mysqli_query($conn, "
    SELECT pembayaran.id_pembayaran, pasien.nama_pasien, pembayaran.metode_pembayaran, 
           pembayaran.total_harga, pembayaran.bayar, pembayaran.kembali, 
           pembayaran.tanggal_pembayaran, obat.nama_obat, pembayaran.jumlah_obat, 
           perawatan.nama_perawatan, pembayaran.jumlah_perawatan
    FROM pembayaran
    JOIN pasien ON pembayaran.id_pasien = pasien.id_pasien
    LEFT JOIN obat ON pembayaran.id_obat = obat.id_obat
    LEFT JOIN perawatan ON pembayaran.id_perawatan = perawatan.id_perawatan
    WHERE pembayaran.id_pembayaran = '$id_pembayaran'
");

$pembayaran = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <style>
        .content {
            max-width: 600px;
            margin: auto;
            font-family: Arial, sans-serif;
        }
        .struk-container {
            background: #f7f7f7;
            border: 2px solid #333;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: rgb(38, 106, 63);
        }
        .id-pembayaran {
            text-align: center;
            margin-bottom: 20px;
        }
        .id-pembayaran p {
            margin: 0;
            font-size: 14px;
            color: #7f8c8d;
        }
        .id-pembayaran h2 {
            margin: 0;
            color: hsl(142, 41.2%, 30%);
        }
        table {
            width: 100%;
            font-size: 16px;
            color: #2c3e50;
        }
        td {
            padding: 5px 0;
        }
        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }
        .note {
            text-align: center;
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        .print-button {
            display: block;
            text-align: center;
            margin-top: 10px;
        }
        .print-button button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
        }
        @media print {
            @page {
                margin: 0;
                size: auto;
            }
            body * {
                visibility: hidden;
            }
            .struk-container, .struk-container * {
                visibility: visible;
            }
            .struk-container {
                position: absolute;
                left: 50%;
                top: 40%;
                transform: translate(-50%, -50%);
                width: 100%;
                max-width: 600px;
                border: 2px solid black;
                padding: 20px;
                background: white;
            }
            .print-button, .back-link {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="content">
    <div class="struk-container">
        <h1>Struk Pembayaran</h1>
        <div class="id-pembayaran">
            <p>ID Pembayaran</p>
            <h2><?php echo $pembayaran['id_pembayaran']; ?></h2>
        </div>
        <hr>

        <table>
            <tr><td><strong>Nama Pasien:</strong></td><td style="text-align: right;"> <?php echo $pembayaran['nama_pasien']; ?></td></tr>
            <tr><td><strong>Nama Obat:</strong></td><td style="text-align: right;"> <?php echo $pembayaran['nama_obat'] ?? '-'; ?></td></tr>
            <tr><td><strong>Jumlah Obat:</strong></td><td style="text-align: right;"> <?php echo $pembayaran['jumlah_obat'] ?? '-'; ?></td></tr>
            <tr><td><strong>Nama Perawatan:</strong></td><td style="text-align: right;"> <?php echo $pembayaran['nama_perawatan'] ?? '-'; ?></td></tr>
            <tr><td><strong>Jumlah Perawatan:</strong></td><td style="text-align: right;"> <?php echo $pembayaran['jumlah_perawatan'] ?? '-'; ?></td></tr>
            <tr><td><strong>Metode Pembayaran:</strong></td><td style="text-align: right;"> <?php echo $pembayaran['metode_pembayaran']; ?></td></tr>
            <tr><td><strong>Total Harga:</strong></td><td style="text-align: right;"> Rp. <?php echo number_format($pembayaran['total_harga'], 0, ',', '.'); ?></td></tr>
            <tr><td><strong>Bayar:</strong></td><td style="text-align: right;"> Rp. <?php echo number_format($pembayaran['bayar'], 0, ',', '.'); ?></td></tr>
            <tr><td><strong>Kembali:</strong></td><td style="text-align: right;"> Rp. <?php echo number_format($pembayaran['kembali'], 0, ',', '.'); ?></td></tr>
            <tr><td><strong>Tanggal Pembayaran:</strong></td><td style="text-align: right;"> <?php echo $pembayaran['tanggal_pembayaran']; ?></td></tr>
        </table>
        <hr>

        <div class="note">Terima kasih telah melakukan pembayaran. Simpan struk ini sebagai bukti pembayaran.</div>
        <div class="print-button">
            <button onclick="window.print()">Cetak Struk</button>
        </div>
        <a href="pembayaran.php" class="back-link">Kembali ke Daftar Pembayaran</a>
    </div>
</div>

</body>
</html>