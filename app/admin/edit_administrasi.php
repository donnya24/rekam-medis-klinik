<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

// Fetch user data with role 'administrasi'
$users = [];
$user_query = "SELECT id, username FROM user WHERE role = 'administrasi'";
$user_result = mysqli_query($conn, $user_query);
if (!$user_result) {
    die("Error fetching user data: " . mysqli_error($conn));
}
while ($user_row = mysqli_fetch_assoc($user_result)) {
    $users[] = $user_row;
}

if (isset($_POST['submit'])) {
    $nama_administrasi = mysqli_real_escape_string($conn, $_POST['nama_administrasi']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $akun_administrasi = mysqli_real_escape_string($conn, $_POST['akun_administrasi']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $tanggal_bergabung = mysqli_real_escape_string($conn, $_POST['tanggal_bergabung']);
    $id_administrasi = isset($_POST['id_administrasi']) ? intval($_POST['id_administrasi']) : null;

    // Validasi: Cek apakah akun administrasi sudah digunakan
    $check_query = "SELECT id_administrasi FROM administrasi WHERE akun_administrasi = '$akun_administrasi'";
    if ($id_administrasi) {
        $check_query .= " AND id_administrasi != $id_administrasi"; // Exclude current administrasi when editing
    }
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Akun sudah digunakan
        echo "<script>alert('Akun administrasi sudah digunakan! Silakan pilih akun lain.');</script>";
    } else {
        // Jika tidak ada id_administrasi yang dikirim, lakukan operasi INSERT (Tambah Administrasi)
        if (empty($id_administrasi)) {
            $query = "INSERT INTO administrasi (nama_administrasi, akun_administrasi, no_telepon, email, jenis_kelamin, tanggal_lahir, alamat, tanggal_bergabung) 
                      VALUES ('$nama_administrasi', '$akun_administrasi', '$no_telepon', '$email', '$jenis_kelamin', '$tanggal_lahir', '$alamat', '$tanggal_bergabung')";
            $message = "Petugas administrasi berhasil ditambahkan!";
        } else {
            // Jika id_administrasi ada, lakukan operasi UPDATE (Edit Administrasi)
            $query = "UPDATE administrasi SET 
                      nama_administrasi='$nama_administrasi', 
                      akun_administrasi='$akun_administrasi', 
                      no_telepon='$no_telepon', 
                      email='$email', 
                      jenis_kelamin='$jenis_kelamin', 
                      tanggal_lahir='$tanggal_lahir', 
                      alamat='$alamat', 
                      tanggal_bergabung='$tanggal_bergabung' 
                      WHERE id_administrasi=$id_administrasi";
            $message = "Petugas administrasi berhasil diupdate!";
        }

        // Eksekusi query
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('$message'); window.location.href = 'petugas_administrasi.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// Fetch administrasi data if editing
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_administrasi = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM administrasi WHERE id_administrasi = $id_administrasi");
    if (!$result) {
        die("Error fetching administrasi data: " . mysqli_error($conn));
    }
    $administrasi = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($administrasi) ? 'Edit Petugas Administrasi' : 'Tambah Petugas Administrasi'; ?></title>
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
                <h1><?php echo isset($administrasi) ? 'Edit Petugas Administrasi' : 'Tambah Petugas Administrasi'; ?></h1>
                <form action="edit_administrasi.php" method="POST">
                    <label for="nama_administrasi">Nama Petugas Administrasi:</label>
                    <input type="text" id="nama_administrasi" name="nama_administrasi" value="<?php echo isset($administrasi) ? $administrasi['nama_administrasi'] : ''; ?>" required>

                    <label for="no_telepon">No. Telepon:</label>
                    <input type="text" id="no_telepon" name="no_telepon" value="<?php echo isset($administrasi) ? $administrasi['no_telepon'] : ''; ?>" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($administrasi) ? $administrasi['email'] : ''; ?>" required>

                    <label for="akun_administrasi">Akun Petugas Administrasi:</label>
                    <select id="akun_administrasi" name="akun_administrasi" required>
                        <option value="">Pilih Akun Petugas Administrasi</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['username']); ?>" <?php echo (isset($administrasi) && $administrasi['akun_administrasi'] == $user['username']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="jenis_kelamin">Jenis Kelamin:</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="L" <?php echo (isset($administrasi) && $administrasi['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="P" <?php echo (isset($administrasi) && $administrasi['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                    </select>

                    <label for="tanggal_lahir">Tanggal Lahir:</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo isset($administrasi) ? $administrasi['tanggal_lahir'] : ''; ?>" required>

                    <label for="alamat">Alamat:</label>
                    <textarea id="alamat" name="alamat" required><?php echo isset($administrasi) ? $administrasi['alamat'] : ''; ?></textarea>

                    <label for="tanggal_bergabung">Tanggal Bergabung:</label>
                    <input type="date" id="tanggal_bergabung" name="tanggal_bergabung" value="<?php echo isset($administrasi) ? $administrasi['tanggal_bergabung'] : ''; ?>" required>

                    <?php if (isset($administrasi)) { ?>
                        <input type="hidden" name="id_administrasi" value="<?php echo $administrasi['id_administrasi']; ?>">
                    <?php } ?>

                    <button type="submit" name="submit"><?php echo isset($administrasi) ? 'Update Petugas Administrasi' : 'Tambah Petugas Administrasi'; ?></button>
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