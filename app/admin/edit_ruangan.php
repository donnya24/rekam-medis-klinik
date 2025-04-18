<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

// Proses Tambah atau Edit Ruangan
if (isset($_GET['id'])) {
    $id_ruangan = intval($_GET['id']);
    // Mengambil data ruangan jika ada ID ruangan (untuk proses Edit)
    $result = mysqli_query($conn, "SELECT * FROM ruangan WHERE id_ruangan = $id_ruangan");
    if ($result && mysqli_num_rows($result) > 0) {
        $ruangan = mysqli_fetch_assoc($result);
    } else {
        echo "<script>alert('Ruangan tidak ditemukan!'); window.location='ruangan.php';</script>";
        exit;
    }
}

// Proses Simpan (Tambah atau Update Ruangan)
if (isset($_POST['submit'])) {
    $id_ruangan = isset($_POST['id_ruangan']) ? intval($_POST['id_ruangan']) : null;
    $nama_ruangan = mysqli_real_escape_string($conn, $_POST['nama_ruangan']);
    $kapasitas = intval($_POST['kapasitas']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Validasi Data
    if (empty($nama_ruangan) || empty($kapasitas) || empty($status)) {
        echo "<script>alert('Semua field harus diisi!');</script>";
        exit;
    }

    // Proses Tambah Ruangan
    if ($id_ruangan === null) {
        // Query untuk menambah ruangan baru
        $query = "INSERT INTO ruangan (nama_ruangan, kapasitas, status) VALUES ('$nama_ruangan', '$kapasitas', '$status')";
        $message = "Ruangan berhasil ditambahkan!";
    } else {
        // Query untuk update ruangan yang sudah ada
        $query = "UPDATE ruangan SET nama_ruangan='$nama_ruangan', kapasitas='$kapasitas', status='$status' WHERE id_ruangan=$id_ruangan";
        $message = "Ruangan berhasil diupdate!";
    }

    // Eksekusi Query
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('$message'); window.location='ruangan.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($ruangan) ? 'Edit Ruangan' : 'Tambah Ruangan'; ?></title>
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

        .form-container input,
        .form-container select {
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
                <h1><?php echo isset($ruangan) ? 'Edit Ruangan' : 'Tambah Ruangan'; ?></h1>
                <form action="edit_ruangan.php" method="POST">
                    <label for="nama_ruangan">Nama Ruangan:</label>
                    <input type="text" id="nama_ruangan" name="nama_ruangan" value="<?php echo isset($ruangan) ? $ruangan['nama_ruangan'] : ''; ?>" required>

                    <label for="kapasitas">Kapasitas:</label>
                    <input type="number" id="kapasitas" name="kapasitas" value="<?php echo isset($ruangan) ? $ruangan['kapasitas'] : ''; ?>" required>

                    <label for="status">Status:</label>
                    <select name="status" id="status" required>
                        <option value="Tersedia" <?php echo isset($ruangan) && $ruangan['status'] == 'Tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="Tidak Tersedia" <?php echo isset($ruangan) && $ruangan['status'] == 'Tidak Tersedia' ? 'selected' : ''; ?>>Tidak Tersedia</option>
                    </select>

                    <?php if (isset($ruangan)) { ?>
                        <input type="hidden" name="id_ruangan" value="<?php echo $ruangan['id_ruangan']; ?>">
                    <?php } ?>

                    <button type="submit" name="submit"><?php echo isset($ruangan) ? 'Update Ruangan' : 'Tambah Ruangan'; ?></button>
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
