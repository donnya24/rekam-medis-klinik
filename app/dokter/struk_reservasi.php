<?php
include "../../config/db.php";

// Ambil ID Reservasi dari URL
$id_reservasi = $_GET['id_reservasi'];

// Ambil data reservasi berdasarkan ID
$query = mysqli_query($conn, "
    SELECT reservasi.id_reservasi, pasien.nama_pasien, dokter.nama_dokter, ruangan.nama_ruangan,
    reservasi.tanggal, reservasi.waktu
    FROM reservasi
    JOIN pasien ON reservasi.id_pasien = pasien.id_pasien
    JOIN dokter ON reservasi.id_dokter = dokter.id_dokter
    JOIN ruangan ON reservasi.id_ruangan = ruangan.id_ruangan
    WHERE reservasi.id_reservasi = '$id_reservasi'
");

$reservasi = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Reservasi</title>
    <style>
        /* Styling untuk tampilan utama */
        .content {
            max-width: 600px;
            margin: auto;
            font-family: Arial, sans-serif;
        }
        .struk-container {
            background: #f7f7f7;
            border: 2px solid #333; /* Frame yang lebih jelas */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: rgb(38, 106, 63);
        }
        .no-antrian {
            text-align: center;
            margin-bottom: 20px;
        }
        .no-antrian p {
            margin: 0;
            font-size: 14px;
            color: #7f8c8d;
        }
        .no-antrian h2 {
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

        /* CSS untuk tampilan cetak */
        @media print {
            @page {
            margin: 0;
            size: auto; /* Menghilangkan header dan footer browser */
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
                border: 2px solid black; /* Frame lebih jelas saat dicetak */
                padding: 20px;
                background: white;
            }
            .print-button, .back-link {
                display: none; /* Sembunyikan tombol cetak dan kembali */
            }
        }
    </style>
</head>
<body>

<div class="content">
    <div class="struk-container">
        <h1>Struk Reservasi</h1>
        <div class="no-antrian">
            <p>No. Antrian</p>
            <h2><?php echo $reservasi['id_reservasi']; ?></h2>
        </div>
        <hr>

        <table>
            <tr>
                <td><strong>Nama Pasien:</strong></td>
                <td style="text-align: right;"><?php echo $reservasi['nama_pasien']; ?></td>
            </tr>
            <tr>
                <td><strong>Dokter:</strong></td>
                <td style="text-align: right;"><?php echo $reservasi['nama_dokter']; ?></td>
            </tr>
            <tr>
                <td><strong>Ruangan:</strong></td>
                <td style="text-align: right;"><?php echo $reservasi['nama_ruangan']; ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal:</strong></td>
                <td style="text-align: right;"><?php echo $reservasi['tanggal']; ?></td>
            </tr>
            <tr>
                <td><strong>Waktu:</strong></td>
                <td style="text-align: right;"><?php echo $reservasi['waktu']; ?></td>
            </tr>
        </table>

        <hr>

        <div class="note">
            <p>Terima kasih telah melakukan reservasi. Silakan tunjukkan struk ini pada saat datang.</p>
        </div>

        <div class="print-button">
            <button onclick="window.print()">Cetak Struk</button>
        </div>
        <a href="reservasi.php" class="back-link">Kembali ke Daftar Reservasi</a>
    </div>
</div>

<script>
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
