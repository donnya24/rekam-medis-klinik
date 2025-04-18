<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

// Fetch user data with role 'perawat'
$users = [];
$user_query = "SELECT id, username FROM user WHERE role = 'perawat'";
$user_result = mysqli_query($conn, $user_query);
if (!$user_result) {
    die("Error fetching user data: " . mysqli_error($conn));
}
while ($user_row = mysqli_fetch_assoc($user_result)) {
    $users[] = $user_row;
}

if (isset($_POST['submit'])) {
    $nama_perawat = mysqli_real_escape_string($conn, $_POST['nama_perawat']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $akun_perawat = mysqli_real_escape_string($conn, $_POST['akun_perawat']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $tanggal_bergabung = mysqli_real_escape_string($conn, $_POST['tanggal_bergabung']);
    $id_perawat = isset($_POST['id_perawat']) ? intval($_POST['id_perawat']) : null;

    // Validasi: Cek apakah akun perawat sudah digunakan
    $check_query = "SELECT id_perawat FROM perawat WHERE akun_perawat = '$akun_perawat'";
    if ($id_perawat) {
        $check_query .= " AND id_perawat != $id_perawat"; // Exclude current perawat when editing
    }
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Akun sudah digunakan
        echo "<script>alert('Akun perawat sudah digunakan! Silakan pilih akun lain.');</script>";
    } else {
        // Jika tidak ada id_perawat yang dikirim, lakukan operasi INSERT (Tambah Perawat)
        if (empty($id_perawat)) {
            $query = "INSERT INTO perawat (nama_perawat, akun_perawat, no_telepon, email, jenis_kelamin, tanggal_lahir, alamat, tanggal_bergabung) 
                      VALUES ('$nama_perawat', '$akun_perawat', '$no_telepon', '$email', '$jenis_kelamin', '$tanggal_lahir', '$alamat', '$tanggal_bergabung')";
            $message = "Perawat berhasil ditambahkan!";
        } else {
            // Jika id_perawat ada, lakukan operasi UPDATE (Edit Perawat)
            $query = "UPDATE perawat SET 
                      nama_perawat='$nama_perawat', 
                      akun_perawat='$akun_perawat', 
                      no_telepon='$no_telepon', 
                      email='$email', 
                      jenis_kelamin='$jenis_kelamin', 
                      tanggal_lahir='$tanggal_lahir', 
                      alamat='$alamat', 
                      tanggal_bergabung='$tanggal_bergabung' 
                      WHERE id_perawat=$id_perawat";
            $message = "Perawat berhasil diupdate!";
        }

        // Eksekusi query
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('$message'); window.location.href = 'perawat.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
        }
    }
}

// Fetch perawat data if editing
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_perawat = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM perawat WHERE id_perawat = $id_perawat");
    if (!$result) {
        die("Error fetching perawat data: " . mysqli_error($conn));
    }
    $perawat = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($perawat) ? 'Edit Perawat' : 'Tambah Perawat'; ?></title>
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
                <h1><?php echo isset($perawat) ? 'Edit Perawat' : 'Tambah Perawat'; ?></h1>
                <form action="edit_perawat.php" method="POST">
                    <label for="nama_perawat">Nama Perawat:</label>
                    <input type="text" id="nama_perawat" name="nama_perawat" value="<?php echo isset($perawat) ? $perawat['nama_perawat'] : ''; ?>" required>

                    <label for="no_telepon">No. Telepon:</label>
                    <input type="text" id="no_telepon" name="no_telepon" value="<?php echo isset($perawat) ? $perawat['no_telepon'] : ''; ?>" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($perawat) ? $perawat['email'] : ''; ?>" required>

                    <label for="akun_perawat">Akun Perawat:</label>
                    <select id="akun_perawat" name="akun_perawat" required>
                        <option value="">Pilih Akun Perawat</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['username']); ?>" <?php echo (isset($perawat) && $perawat['akun_perawat'] == $user['username']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="jenis_kelamin">Jenis Kelamin:</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="L" <?php echo (isset($perawat) && $perawat['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="P" <?php echo (isset($perawat) && $perawat['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                    </select>

                    <label for="tanggal_lahir">Tanggal Lahir:</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo isset($perawat) ? $perawat['tanggal_lahir'] : ''; ?>" required>

                    <label for="alamat">Alamat:</label>
                    <textarea id="alamat" name="alamat" required><?php echo isset($perawat) ? $perawat['alamat'] : ''; ?></textarea>

                    <label for="tanggal_bergabung">Tanggal Bergabung:</label>
                    <input type="date" id="tanggal_bergabung" name="tanggal_bergabung" value="<?php echo isset($perawat) ? $perawat['tanggal_bergabung'] : ''; ?>" required>

                    <?php if (isset($perawat)) { ?>
                        <input type="hidden" name="id_perawat" value="<?php echo $perawat['id_perawat']; ?>">
                    <?php } ?>

                    <button type="submit" name="submit"><?php echo isset($perawat) ? 'Update Perawat' : 'Tambah Perawat'; ?></button>
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