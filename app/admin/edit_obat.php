<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

// Check if form is submitted
if (isset($_POST['submit'])) {
    $nama_obat = mysqli_real_escape_string($conn, $_POST['nama_obat']);
    $jenis_obat = mysqli_real_escape_string($conn, $_POST['jenis_obat']);
    $stok = mysqli_real_escape_string($conn, $_POST['stok']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);

    if (isset($_POST['id_obat']) && $_POST['id_obat'] != '') {
        // Update Obat
        $id_obat = mysqli_real_escape_string($conn, $_POST['id_obat']);
        $query = "UPDATE obat 
                  SET nama_obat='$nama_obat', jenis_obat='$jenis_obat', stok='$stok', harga='$harga' 
                  WHERE id_obat='$id_obat'";
        $message = "Obat berhasil diperbarui!";
    } else {
        // Tambah Obat Baru
        $query = "INSERT INTO obat (nama_obat, jenis_obat, stok, harga) 
                  VALUES ('$nama_obat', '$jenis_obat', '$stok', '$harga')";
        $message = "Obat berhasil ditambahkan!";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('$message'); window.location='obat.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
    }
}

// Proses Edit Obat
if (isset($_GET['id'])) {
    $id_obat = $_GET['id'];
    $result = mysqli_query($conn, "SELECT * FROM obat WHERE id_obat = $id_obat");
    $obat = mysqli_fetch_assoc($result);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Obat</title>
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

        .form-container input {
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
                <h1><?php echo isset($obat) ? 'Edit Obat' : 'Tambah Obat'; ?></h1>
                <form action="edit_obat.php" method="POST">
                    <label for="nama_obat">Nama Obat:</label>
                    <input type="text" id="nama_obat" name="nama_obat" value="<?php echo isset($obat) ? $obat['nama_obat'] : ''; ?>" required>

                    <label for="jenis_obat">Jenis Obat:</label>
                    <input type="text" id="jenis_obat" name="jenis_obat" value="<?php echo isset($obat) ? $obat['jenis_obat'] : ''; ?>" required>

                    <label for="stok">Stok:</label>
                    <input type="number" id="stok" name="stok" value="<?php echo isset($obat) ? $obat['stok'] : ''; ?>" required>

                    <label for="harga">Harga:</label>
                    <input type="number" step="0.01" id="harga" name="harga" value="<?php echo isset($obat) ? $obat['harga'] : ''; ?>" required>

                    <?php if (isset($obat)) { ?>
                        <input type="hidden" name="id_obat" value="<?php echo $obat['id_obat']; ?>">
                    <?php } ?>

                    <button type="submit" name="submit"><?php echo isset($obat) ? 'Update Obat' : 'Tambah Obat'; ?></button>
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
