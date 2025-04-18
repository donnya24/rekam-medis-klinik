<?php 
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

// Handle Edit and Delete actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'edit') {
        $query = "SELECT * FROM pasien WHERE id_pasien = $id";
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            $pasien = $result->fetch_assoc();
        } else {
            echo "<script>alert('Pasien tidak ditemukan!'); window.location = 'pasien.php';</script>";
        }
    } elseif ($action === 'delete') {
        $query = "DELETE FROM pasien WHERE id_pasien = $id";
        if ($conn->query($query) === TRUE) {
            echo "<script>alert('Data pasien berhasil dihapus!'); window.location = 'pasien.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pasien = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $tanggal_lahir = $_POST['tgl_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $no_telepon = $_POST['no_telepon'];

    if (isset($_POST['id_pasien'])) {
        // Update existing patient
        $id_pasien = intval($_POST['id_pasien']);
        $query = "UPDATE pasien 
                  SET nama_pasien = '$nama_pasien', alamat = '$alamat', tanggal_lahir = '$tanggal_lahir', 
                      jenis_kelamin = '$jenis_kelamin', no_telepon = '$no_telepon' 
                  WHERE id_pasien = $id_pasien";

        if ($conn->query($query) === TRUE) {
            echo "<script>alert('Data pasien berhasil diperbarui!'); window.location = 'pasien.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        // Insert new patient
        $query = "INSERT INTO pasien (nama_pasien, alamat, tanggal_lahir, jenis_kelamin, no_telepon) 
                  VALUES ('$nama_pasien', '$alamat', '$tanggal_lahir', '$jenis_kelamin', '$no_telepon')";

        if ($conn->query($query) === TRUE) {
            echo "<script>alert('Pasien berhasil ditambahkan!'); window.location = 'pasien.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pasien</title>
    <link href="../../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../../assets/css/font-awesome.css" rel="stylesheet" />
    <link href="../../assets/css/custom-styles.css" rel="stylesheet" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color:rgb(255, 255, 255);
            margin: 0;
            padding: 0;
        }

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

        .form-container input, .form-container select, .form-container textarea {
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
                <h1><?php echo isset($pasien) ? 'Edit Data Pasien' : 'Pendaftaran Pasien Baru'; ?></h1>
                <form action="pendaftaran.php" method="POST" class="form-horizontal">
                    <?php if (isset($pasien)) { ?>
                        <input type="hidden" name="id_pasien" value="<?php echo $pasien['id_pasien']; ?>">
                    <?php } ?>
                    
                    <label for="nama">Nama Lengkap:</label>
                    <input type="text" id="nama" name="nama" value="<?php echo isset($pasien) ? $pasien['nama_pasien'] : ''; ?>" required>

                    <label for="alamat">Alamat:</label>
                    <textarea id="alamat" name="alamat"><?php echo isset($pasien) ? $pasien['alamat'] : ''; ?></textarea>

                    <label for="tgl_lahir">Tanggal Lahir:</label>
                    <input type="date" id="tgl_lahir" name="tgl_lahir" value="<?php echo isset($pasien) ? $pasien['tanggal_lahir'] : ''; ?>" required>

                    <label for="jenis_kelamin">Jenis Kelamin:</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="L" <?php echo isset($pasien) && $pasien['jenis_kelamin'] === 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="P" <?php echo isset($pasien) && $pasien['jenis_kelamin'] === 'P' ? 'selected' : ''; ?>>Perempuan</option>
                    </select>

                    <label for="no_telepon">No. Telepon:</label>
                    <input type="text" id="no_telepon" name="no_telepon" value="<?php echo isset($pasien) ? $pasien['no_telepon'] : ''; ?>">

                    <button type="submit">Simpan</button>
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
