<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

// Fetch user data with role 'apoteker'
$users = [];
$user_query = "SELECT id, username FROM user WHERE role = 'apoteker'";
$user_result = mysqli_query($conn, $user_query);
if (!$user_result) {
    die("Error fetching user data: " . mysqli_error($conn));
}
while ($user_row = mysqli_fetch_assoc($user_result)) {
    $users[] = $user_row;
}

if (isset($_POST['submit'])) {
    $nama_apoteker = mysqli_real_escape_string($conn, $_POST['nama_apoteker']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $akun_apoteker = mysqli_real_escape_string($conn, $_POST['akun_apoteker']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $tanggal_bergabung = mysqli_real_escape_string($conn, $_POST['tanggal_bergabung']);
    $id_apoteker = isset($_POST['id_apoteker']) ? intval($_POST['id_apoteker']) : null;

    // Validasi: Cek apakah akun apoteker sudah digunakan
    $check_query = "SELECT id_apoteker FROM apoteker WHERE akun_apoteker = '$akun_apoteker'";
    if ($id_apoteker) {
        $check_query .= " AND id_apoteker != $id_apoteker"; // Exclude current apoteker when editing
    }
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Akun sudah digunakan
        echo "<script>alert('Akun apoteker sudah digunakan! Silakan pilih akun lain.');</script>";
    } else {
        // Jika tidak ada id_apoteker yang dikirim, lakukan operasi INSERT (Tambah Apoteker)
        if (empty($id_apoteker)) {
            $query = "INSERT INTO apoteker (nama_apoteker, akun_apoteker, no_telepon, email, jenis_kelamin, tanggal_lahir, alamat, tanggal_bergabung) 
                      VALUES ('$nama_apoteker', '$akun_apoteker', '$no_telepon', '$email', '$jenis_kelamin', '$tanggal_lahir', '$alamat', '$tanggal_bergabung')";
            $message = "Petugas apoteker berhasil ditambahkan!";
        } else {
            // Jika id_apoteker ada, lakukan operasi UPDATE (Edit Apoteker)
            $query = "UPDATE apoteker SET 
                      nama_apoteker='$nama_apoteker', 
                      akun_apoteker='$akun_apoteker', 
                      no_telepon='$no_telepon', 
                      email='$email', 
                      jenis_kelamin='$jenis_kelamin', 
                      tanggal_lahir='$tanggal_lahir', 
                      alamat='$alamat', 
                      tanggal_bergabung='$tanggal_bergabung' 
                      WHERE id_apoteker=$id_apoteker";
            $message = "Petugas apoteker berhasil diupdate!";
        }

        // Eksekusi query
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('$message'); window.location.href = 'apoteker.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// Fetch apoteker data if editing
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_apoteker = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM apoteker WHERE id_apoteker = $id_apoteker");
    if (!$result) {
        die("Error fetching apoteker data: " . mysqli_error($conn));
    }
    $apoteker = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($apoteker) ? 'Edit Petugas Apoteker' : 'Tambah Petugas Apoteker'; ?></title>
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
                <h1><?php echo isset($apoteker) ? 'Edit Petugas Apoteker' : 'Tambah Petugas Apoteker'; ?></h1>
                <form action="edit_apoteker.php" method="POST">
                    <label for="nama_apoteker">Nama Petugas Apoteker:</label>
                    <input type="text" id="nama_apoteker" name="nama_apoteker" value="<?php echo isset($apoteker) ? $apoteker['nama_apoteker'] : ''; ?>" required>

                    <label for="no_telepon">No. Telepon:</label>
                    <input type="text" id="no_telepon" name="no_telepon" value="<?php echo isset($apoteker) ? $apoteker['no_telepon'] : ''; ?>" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($apoteker) ? $apoteker['email'] : ''; ?>" required>

                    <label for="akun_apoteker">Akun Apoteker:</label>
                    <select id="akun_apoteker" name="akun_apoteker" required>
                        <option value="">Pilih Akun Apoteker</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['username']); ?>" <?php echo (isset($apoteker) && $apoteker['akun_apoteker'] == $user['username']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="jenis_kelamin">Jenis Kelamin:</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="L" <?php echo (isset($apoteker) && $apoteker['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="P" <?php echo (isset($apoteker) && $apoteker['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                    </select>

                    <label for="tanggal_lahir">Tanggal Lahir:</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo isset($apoteker) ? $apoteker['tanggal_lahir'] : ''; ?>" required>

                    <label for="alamat">Alamat:</label>
                    <textarea id="alamat" name="alamat" required><?php echo isset($apoteker) ? $apoteker['alamat'] : ''; ?></textarea>

                    <label for="tanggal_bergabung">Tanggal Bergabung:</label>
                    <input type="date" id="tanggal_bergabung" name="tanggal_bergabung" value="<?php echo isset($apoteker) ? $apoteker['tanggal_bergabung'] : ''; ?>" required>

                    <?php if (isset($apoteker)) { ?>
                        <input type="hidden" name="id_apoteker" value="<?php echo $apoteker['id_apoteker']; ?>">
                    <?php } ?>

                    <button type="submit" name="submit"><?php echo isset($apoteker) ? 'Update Petugas Apoteker' : 'Tambah Petugas Apoteker'; ?></button>
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