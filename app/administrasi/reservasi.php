<?php
include "../../includes/administrasi/sidebar.php";
include "../../config/db.php";

// Delete reservasi jika ada aksi delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_reservasi = $_GET['id'];

    // Check if there are any related payment records
    $check_payment_sql = "SELECT * FROM pembayaran WHERE id_reservasi = '$id_reservasi'";
    $payment_result = $conn->query($check_payment_sql);
    
    if ($payment_result->num_rows > 0) {
        // If there are related payment records, prevent deletion
        echo "<script>alert('Reservasi ini tidak bisa dihapus karena ada data pembayaran yang terkait!'); window.location='reservasi.php';</script>";
    } else {
        // If no payment records, proceed with deleting related data
        // Start a transaction to delete all related records
        $conn->begin_transaction();
        
        try {
            // Delete related pembayaran data
            $delete_payment_sql = "DELETE FROM pembayaran WHERE id_reservasi = '$id_reservasi'";
            $conn->query($delete_payment_sql);
            
            // Delete related catatan medis data
            $delete_medical_record_sql = "DELETE FROM catatan_medis WHERE id_reservasi = '$id_reservasi'";
            $conn->query($delete_medical_record_sql);
            
            // Delete related hasil pemeriksaan data
            $delete_exam_result_sql = "DELETE FROM hasil_pemeriksaan WHERE id_reservasi = '$id_reservasi'";
            $conn->query($delete_exam_result_sql);
            
            // Finally, delete the reservasi
            $delete_reservation_sql = "DELETE FROM reservasi WHERE id_reservasi = '$id_reservasi'";
            $conn->query($delete_reservation_sql);
            
            // Commit the transaction
            $conn->commit();
            
            echo "<script>alert('Reservasi beserta data terkait berhasil dihapus!'); window.location='reservasi.php';</script>";
        } catch (Exception $e) {
            // If an error occurs, roll back the transaction
            $conn->rollback();
            echo "<script>alert('Terjadi kesalahan saat menghapus data: " . $e->getMessage() . "'); window.location='reservasi.php';</script>";
        }
    }
}

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
            <a href="edit_reservasi.php" class="btn btn-primary" style="margin-bottom: 20px;">Tambah Reservasi</a>
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
                        <th>Aksi</th>
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
                            echo "<td>
                                <a href='edit_reservasi.php?action=edit&id=" . htmlspecialchars($row['id_reservasi']) . "' class='btn btn-warning btn-sm'>Edit</a>
                                <a href='reservasi.php?action=delete&id=" . htmlspecialchars($row['id_reservasi']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>
                                <a href='struk_reservasi.php?id_reservasi=" . htmlspecialchars($row['id_reservasi']) . "' class='btn btn-info btn-sm'>Tampilkan Struk</a>
                            </td>";
                            echo "</tr>";
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align: center;'>Tidak ada data reservasi</td></tr>";
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
