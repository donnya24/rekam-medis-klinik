<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

if (isset($_POST['submit'])) {
    $nama_perawatan = mysqli_real_escape_string($conn, $_POST['nama_perawatan']);
    $harga_perawatan = mysqli_real_escape_string($conn, $_POST['harga_perawatan']);
    $id_dokter = mysqli_real_escape_string($conn, $_POST['id_dokter']);
    $id_ruangan = mysqli_real_escape_string($conn, $_POST['id_ruangan']);

    // Cek status ruangan
    $ruangan_query = "SELECT status FROM ruangan WHERE id_ruangan = '$id_ruangan'";
    $ruangan_result = mysqli_query($conn, $ruangan_query);
    $ruangan_data = mysqli_fetch_assoc($ruangan_result);

    if ($ruangan_data['status'] == 'Tidak Tersedia') {
        echo "<script>alert('Ruangan yang dipilih tidak tersedia. Silakan pilih ruangan lain.'); window.location='edit_perawatan.php';</script>";
        exit;
    }

    if (isset($_POST['id_perawatan']) && $_POST['id_perawatan'] != '') {
        // Update Perawatan
        $id_perawatan = mysqli_real_escape_string($conn, $_POST['id_perawatan']);
        $query = "UPDATE perawatan 
                  SET nama_perawatan='$nama_perawatan', harga_perawatan='$harga_perawatan', 
                      id_dokter='$id_dokter', id_ruangan='$id_ruangan' 
                  WHERE id_perawatan=$id_perawatan";
        $message = "Perawatan berhasil diperbarui!";
    } else {
        // Cek apakah dokter sudah memiliki perawatan
        $checkQuery = "SELECT * FROM perawatan WHERE id_dokter = '$id_dokter'";
        $checkResult = mysqli_query($conn, $checkQuery);
        
        if (mysqli_num_rows($checkResult) > 0) {
            echo "<script>alert('Dokter ini sudah memiliki perawatan!'); window.location='perawatan.php';</script>";
            exit();
        }
        
        // Tambah Perawatan Baru
        $query = "INSERT INTO perawatan (nama_perawatan, harga_perawatan, id_dokter, id_ruangan) 
                  VALUES ('$nama_perawatan', '$harga_perawatan', '$id_dokter', '$id_ruangan')";
        $message = "Perawatan berhasil ditambahkan!";
    }

    // Pastikan query tidak kosong sebelum dieksekusi
    if (isset($query) && !empty($query)) {
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('$message'); window.location='perawatan.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('Query tidak valid.');</script>";
    }
}

// Proses Edit Perawatan
$perawatan = null;
if (isset($_GET['id'])) {
    $id_perawatan = mysqli_real_escape_string($conn, $_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM perawatan WHERE id_perawatan = $id_perawatan");
    $perawatan = mysqli_fetch_assoc($result);
}

// Mengambil data dokter dan ruangan untuk dropdown
$dokter_result = mysqli_query($conn, "SELECT * FROM dokter");
$ruangan_result = mysqli_query($conn, "SELECT * FROM ruangan");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Perawatan</title>
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

        .form-container form {
            display: flex;
            flex-direction: column;
        }

        .form-container label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-container input, .form-container select {
            margin-bottom: 15px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-container button {
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
                <h1><?php echo isset($perawatan) ? 'Edit Perawatan' : 'Tambah Perawatan'; ?></h1>
                <form action="edit_perawatan.php" method="POST">
                    <label for="nama_perawatan">Nama Perawatan:</label>
                    <input type="text" id="nama_perawatan" name="nama_perawatan" placeholder="Masukkan nama perawatan" 
                           value="<?php echo isset($perawatan) ? $perawatan['nama_perawatan'] : ''; ?>" required>

                    <label for="harga_perawatan">Harga Perawatan (Rp):</label>
                    <input type="number" id="harga_perawatan" name="harga_perawatan" placeholder="Masukkan harga perawatan" 
                           value="<?php echo isset($perawatan) ? $perawatan['harga_perawatan'] : ''; ?>" required>

                    <label for="id_dokter">Pilih Dokter:</label>
                    <select id="id_dokter" name="id_dokter" required>
                        <option value="">Pilih Dokter</option>
                        <?php while ($dokter = mysqli_fetch_assoc($dokter_result)) { ?>
                            <option value="<?php echo $dokter['id_dokter']; ?>" 
                                    <?php echo isset($perawatan) && $perawatan['id_dokter'] == $dokter['id_dokter'] ? 'selected' : ''; ?>>
                                <?php echo $dokter['nama_dokter']; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label for="id_ruangan">Pilih Ruangan:</label>
                    <select id="id_ruangan" name="id_ruangan" required>
                        <option value="">Pilih Ruangan</option>
                        <?php while ($ruangan = mysqli_fetch_assoc($ruangan_result)) { ?>
                            <option value="<?php echo $ruangan['id_ruangan']; ?>" 
                                    <?php echo isset($perawatan) && $perawatan['id_ruangan'] == $ruangan['id_ruangan'] ? 'selected' : ''; ?>>
                                <?php echo $ruangan['nama_ruangan'] . " - " . $ruangan['status']; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <?php if (isset($perawatan)) { ?>
                        <input type="hidden" name="id_perawatan" value="<?php echo $perawatan['id_perawatan']; ?>">
                    <?php } ?>

                    <button type="submit" name="submit"><?php echo isset($perawatan) ? 'Update Perawatan' : 'Tambah Perawatan'; ?></button>
                </form>
            </div>
        </div>    
    </div>
</div>

<!-- Include Scripts -->
<script src="../../assets/js/jquery-1.10.2.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script src="../../assets/js/custom-scripts.js"></script>
</body>
</html>

<?php $conn->close(); ?>