<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

// Fetch user data with role 'dokter'
$users = [];
$user_query = "SELECT id, username FROM user WHERE role = 'dokter'";
$user_result = mysqli_query($conn, $user_query);
if (!$user_result) {
    die("Error fetching user data: " . mysqli_error($conn));
}
while ($user_row = mysqli_fetch_assoc($user_result)) {
    $users[] = $user_row;
}

if (isset($_POST['submit'])) {
    $nama_dokter = mysqli_real_escape_string($conn, $_POST['nama_dokter']);
    $spesialis = mysqli_real_escape_string($conn, $_POST['spesialis']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $akun_dokter = mysqli_real_escape_string($conn, $_POST['akun_dokter']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $tanggal_bergabung = mysqli_real_escape_string($conn, $_POST['tanggal_bergabung']);
    $id_dokter = isset($_POST['id_dokter']) ? intval($_POST['id_dokter']) : null;

    // Validasi: Cek apakah akun dokter sudah digunakan
    $check_query = "SELECT id_dokter FROM dokter WHERE akun_dokter = '$akun_dokter'";
    if ($id_dokter) {
        $check_query .= " AND id_dokter != $id_dokter"; // Exclude current dokter when editing
    }
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Akun sudah digunakan
        echo "<script>alert('Akun dokter sudah digunakan! Silakan pilih akun lain.');</script>";
    } else {
        // Jika tidak ada id_dokter yang dikirim, lakukan operasi INSERT (Tambah Dokter)
        if (empty($id_dokter)) {
            $query = "INSERT INTO dokter (nama_dokter, akun_dokter, spesialis, no_telepon, email, jenis_kelamin, tanggal_lahir, alamat, tanggal_bergabung) 
                      VALUES ('$nama_dokter', '$akun_dokter', '$spesialis', '$no_telepon', '$email', '$jenis_kelamin', '$tanggal_lahir', '$alamat', '$tanggal_bergabung')";
            $message = "Dokter berhasil ditambahkan!";
        } else {
            // Jika id_dokter ada, lakukan operasi UPDATE (Edit Dokter)
            $query = "UPDATE dokter SET 
                      nama_dokter='$nama_dokter', 
                      akun_dokter='$akun_dokter', 
                      spesialis='$spesialis', 
                      no_telepon='$no_telepon', 
                      email='$email', 
                      jenis_kelamin='$jenis_kelamin', 
                      tanggal_lahir='$tanggal_lahir', 
                      alamat='$alamat', 
                      tanggal_bergabung='$tanggal_bergabung' 
                      WHERE id_dokter=$id_dokter";
            $message = "Dokter berhasil diupdate!";
        }

        // Eksekusi query
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('$message'); window.location.href = 'dokter.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// Fetch doctor data if editing
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_dokter = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM dokter WHERE id_dokter = $id_dokter");
    if (!$result) {
        die("Error fetching doctor data: " . mysqli_error($conn));
    }
    $dokter = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($dokter) ? 'Edit Dokter' : 'Tambah Dokter'; ?></title>
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
                <h1><?php echo isset($dokter) ? 'Edit Dokter' : 'Tambah Dokter'; ?></h1>
                <form action="edit_dokter.php" method="POST">
                    <label for="nama_dokter">Nama Dokter:</label>
                    <input type="text" id="nama_dokter" name="nama_dokter" value="<?php echo isset($dokter) ? $dokter['nama_dokter'] : ''; ?>" required>

                    <label for="spesialis">Spesialisasi:</label>
                    <input type="text" id="spesialis" name="spesialis" value="<?php echo isset($dokter) ? $dokter['spesialis'] : ''; ?>" required>

                    <label for="no_telepon">No. Telepon:</label>
                    <input type="text" id="no_telepon" name="no_telepon" value="<?php echo isset($dokter) ? $dokter['no_telepon'] : ''; ?>" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($dokter) ? $dokter['email'] : ''; ?>" required>

                    <label for="akun_dokter">Akun Dokter:</label>
                    <select id="akun_dokter" name="akun_dokter" required>
                        <option value="">Pilih Akun Dokter</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['username']); ?>" <?php echo (isset($dokter) && $dokter['akun_dokter'] == $user['username']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="jenis_kelamin">Jenis Kelamin:</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="L" <?php echo (isset($dokter) && $dokter['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="P" <?php echo (isset($dokter) && $dokter['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                    </select>

                    <label for="tanggal_lahir">Tanggal Lahir:</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo isset($dokter) ? $dokter['tanggal_lahir'] : ''; ?>" required>

                    <label for="alamat">Alamat:</label>
                    <textarea id="alamat" name="alamat" required><?php echo isset($dokter) ? $dokter['alamat'] : ''; ?></textarea>

                    <label for="tanggal_bergabung">Tanggal Bergabung:</label>
                    <input type="date" id="tanggal_bergabung" name="tanggal_bergabung" value="<?php echo isset($dokter) ? $dokter['tanggal_bergabung'] : ''; ?>" required>

                    <?php if (isset($dokter)) { ?>
                        <input type="hidden" name="id_dokter" value="<?php echo $dokter['id_dokter']; ?>">
                    <?php } ?>

                    <button type="submit" name="submit"><?php echo isset($dokter) ? 'Update Dokter' : 'Tambah Dokter'; ?></button>
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