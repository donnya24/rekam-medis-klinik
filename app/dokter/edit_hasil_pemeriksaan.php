<?php
session_start(); // Mulai session
include "../../includes/dokter/sidebar.php";
include "../../config/db.php";

// Cek apakah user sudah login dan memiliki role dokter
if (!isset($_SESSION['id_dokter']) || $_SESSION['role'] !== 'dokter') {
    header("Location: ../../login.php"); // Redirect ke halaman login jika tidak login sebagai dokter
    exit();
}

$id_dokter = $_SESSION['id_dokter']; // Ambil id_dokter dari session

// Check if form is submitted
if (isset($_POST['submit'])) {
    $id_hasil = isset($_POST['id_hasil']) ? $_POST['id_hasil'] : null;
    $id_reservasi = mysqli_real_escape_string($conn, $_POST['id_reservasi']);
    $jenis_pemeriksaan = mysqli_real_escape_string($conn, $_POST['jenis_pemeriksaan']);
    $hasil_pemeriksaan = mysqli_real_escape_string($conn, $_POST['hasil_pemeriksaan']);
    $tanggal_pemeriksaan = mysqli_real_escape_string($conn, $_POST['tanggal_pemeriksaan']);
    $id_obat = mysqli_real_escape_string($conn, $_POST['id_obat']);

    if ($id_hasil) {
        // Update Hasil Pemeriksaan
        $query = "UPDATE hasil_pemeriksaan SET 
                  id_reservasi='$id_reservasi', jenis_pemeriksaan='$jenis_pemeriksaan', 
                  hasil_pemeriksaan='$hasil_pemeriksaan', tanggal_pemeriksaan='$tanggal_pemeriksaan', id_obat='$id_obat' 
                  WHERE id_hasil='$id_hasil'";
        $message = "Hasil pemeriksaan berhasil diperbarui!";
    } else {
        // Tambah Hasil Pemeriksaan Baru
        $query = "INSERT INTO hasil_pemeriksaan (id_reservasi, jenis_pemeriksaan, hasil_pemeriksaan, tanggal_pemeriksaan, id_obat) 
                  VALUES ('$id_reservasi', '$jenis_pemeriksaan', '$hasil_pemeriksaan', '$tanggal_pemeriksaan', '$id_obat')";
        $message = "Hasil pemeriksaan berhasil ditambahkan!";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('$message'); window.location='hasil_pemeriksaan.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
    }
}

// Proses Edit Hasil Pemeriksaan
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_hasil = $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM hasil_pemeriksaan WHERE id_hasil = '$id_hasil'");
    $edit_data = mysqli_fetch_assoc($result);
}

// Get the list of reservasi for the logged-in dokter
$reservasi_result = mysqli_query($conn, "
    SELECT reservasi.id_reservasi, reservasi.tanggal, 
           pasien.nama_pasien, dokter.nama_dokter, perawatan.nama_perawatan 
    FROM reservasi
    JOIN pasien ON reservasi.id_pasien = pasien.id_pasien
    JOIN dokter ON reservasi.id_dokter = dokter.id_dokter
    JOIN perawatan ON reservasi.id_perawatan = perawatan.id_perawatan
    WHERE reservasi.id_dokter = '$id_dokter' -- Filter berdasarkan id_dokter yang login
    ORDER BY reservasi.tanggal DESC
");

$obat_result = mysqli_query($conn, "SELECT id_obat, nama_obat FROM obat ORDER BY nama_obat ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($edit_data) ? 'Edit Hasil Pemeriksaan' : 'Tambah Hasil Pemeriksaan'; ?></title>
    <link href="../../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../../assets/css/font-awesome.css" rel="stylesheet" />
    <link href="../../assets/css/custom-styles.css" rel="stylesheet" />
    <style>
        .form-container {
            width: 60%;
            margin: 30px auto;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-container h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-group label {
            width: 30%;
            font-weight: bold;
            margin-right: 10px;
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 70%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            resize: none;
        }

        .form-container button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div id="wrapper">
    <div id="page-wrapper">
        <div id="page-inner">
            <div class="form-container">
                <h1><?php echo isset($edit_data) ? 'Edit Hasil Pemeriksaan' : 'Tambah Hasil Pemeriksaan'; ?></h1>
                <form action="edit_hasil_pemeriksaan.php" method="POST">
                    <?php if (isset($edit_data)) { ?>
                        <input type="hidden" name="id_hasil" value="<?php echo $edit_data['id_hasil']; ?>">
                    <?php } ?>

                    <div class="form-group">
                        <label for="id_reservasi">Pilih Reservasi:</label>
                        <select id="id_reservasi" name="id_reservasi" required>
                            <option value="">Pilih Reservasi</option>
                            <?php while ($reservasi = mysqli_fetch_assoc($reservasi_result)) { ?>
                                <option value="<?php echo $reservasi['id_reservasi']; ?>" 
                                    <?php echo ($edit_data && $edit_data['id_reservasi'] == $reservasi['id_reservasi']) ? 'selected' : ''; ?>>
                                    <?php echo "{$reservasi['id_reservasi']} - {$reservasi['nama_pasien']} (Dokter: {$reservasi['nama_dokter']}, Perawatan: {$reservasi['nama_perawatan']})"; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jenis_pemeriksaan">Jenis Pemeriksaan:</label>
                        <input type="text" id="jenis_pemeriksaan" name="jenis_pemeriksaan" 
                               value="<?php echo $edit_data ? $edit_data['jenis_pemeriksaan'] : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="hasil_pemeriksaan">Hasil Pemeriksaan:</label>
                        <textarea id="hasil_pemeriksaan" name="hasil_pemeriksaan" rows="5" required><?php echo $edit_data ? $edit_data['hasil_pemeriksaan'] : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_pemeriksaan">Tanggal Pemeriksaan:</label>
                        <input type="date" id="tanggal_pemeriksaan" name="tanggal_pemeriksaan" 
                               value="<?php echo $edit_data ? $edit_data['tanggal_pemeriksaan'] : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="id_obat">Pilih Obat:</label>
                        <select id="id_obat" name="id_obat" required>
                            <option value="">Pilih Obat</option>
                            <?php while ($obat = mysqli_fetch_assoc($obat_result)) { ?>
                                <option value="<?php echo $obat['id_obat']; ?>" 
                                    <?php echo ($edit_data && $edit_data['id_obat'] == $obat['id_obat']) ? 'selected' : ''; ?>>
                                    <?php echo $obat['nama_obat']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <button type="submit" name="submit"><?php echo isset($edit_data) ? 'Update Hasil' : 'Tambah Hasil'; ?></button>
                </form>
            </div>
        </div>    
    </div>
</div>

<script src="../../assets/js/jquery-1.10.2.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script src="../../assets/js/custom-scripts.js"></script>
</body>
</html>

<?php $conn->close(); ?>