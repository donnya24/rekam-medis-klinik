<?php
session_start();
include "../../config/db.php";

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role']; // Ambil role dari session

// Ambil data user dari database
$query = "SELECT * FROM user WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Jika data user tidak ditemukan
if (!$user) {
    echo "Data user tidak ditemukan.";
    exit();
}

// Proses update profil
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        // Update profil
        $nama_lengkap = $_POST['nama_lengkap'];
        $no_telepon = $_POST['no_telepon'];
        $alamat = $_POST['alamat'];

        $update_query = "UPDATE user SET nama_lengkap = ?, no_telepon = ?, alamat = ? WHERE username = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssss", $nama_lengkap, $no_telepon, $alamat, $username);

        if ($stmt->execute()) {
            echo "<script>alert('Profil berhasil diperbarui!'); window.location.href='dashboard_apoteker.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui profil!');</script>";
        }
    } elseif (isset($_POST['update_password'])) {
        // Update password
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $konfirmasi_password = $_POST['konfirmasi_password'];

        // Validasi password lama
        if ($password_lama !== $user['password']) {
            echo "<script>alert('Password lama salah!');</script>";
        } elseif ($password_baru !== $konfirmasi_password) {
            echo "<script>alert('Password baru dan konfirmasi password tidak cocok!');</script>";
        } else {
            // Update password baru
            $update_password_query = "UPDATE user SET password = ? WHERE username = ?";
            $stmt = $conn->prepare($update_password_query);
            $stmt->bind_param("ss", $password_baru, $username);

            if ($stmt->execute()) {
                echo "<script>alert('Password berhasil diubah!'); window.location.href='dashboard_apoteker.php';</script>";
            } else {
                echo "<script>alert('Gagal mengubah password!');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - <?php echo ucfirst($role); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
    <script src="../../assets/js/bootstrap.min.js"></script>
    <script src="../../assets/js/custom-scripts.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fc;
            margin: 0;
            display: flex;
        }

        .main-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            min-height: 100vh;
        }

        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            max-width: 450px;
            width: 100%;
        }

        .profile-card h2 {
            text-align: center;
            color: #343a40;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        .form-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group input[readonly] {
            background-color: #f0f0f0;
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            padding: 10px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #007bff;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            margin-right: 10px;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .toggle-password {
            position: absolute;
            right: 10px; /* Jarak dari tepi kanan */
            top: 50%; /* Posisi vertikal di tengah */
            transform: translateY(30%); /* Menggeser ikon ke atas setengah dari tingginya */
            cursor: pointer;
            color: #555;
            font-size: 16px; /* Ukuran ikon */
            z-index: 0; /* Pastikan ikon berada di atas input */
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="profile-card">
            <h2>Edit Profil - <?php echo ucfirst($role); ?></h2>
            <form method="POST">
                <div class="form-group">
                    <label for="username"><i class="fa fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="role"><i class="fa fa-user-tag"></i> Role</label>
                    <input type="text" id="role" name="role" value="<?php echo htmlspecialchars(ucfirst($role)); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="nama_lengkap"><i class="fa fa-user"></i> Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="no_telepon"><i class="fa fa-phone"></i> No. Telepon</label>
                    <input type="text" id="no_telepon" name="no_telepon" value="<?php echo htmlspecialchars($user['no_telepon']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="alamat"><i class="fa fa-home"></i> Alamat</label>
                    <input type="text" id="alamat" name="alamat" value="<?php echo htmlspecialchars($user['alamat']); ?>" required>
                </div>
                <div class="btn-group">
                    <a href="dashboard_apoteker.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profil</button>
                </div>
            </form>

            <hr>

            <h3>Ubah Password</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="password_lama"><i class="fa fa-lock"></i> Password Lama</label>
                    <input type="password" id="password_lama" name="password_lama" required>
                    <i class="fa fa-eye toggle-password" onclick="togglePassword('password_lama')"></i>
                </div>
                <div class="form-group">
                    <label for="password_baru"><i class="fa fa-lock"></i> Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru" required>
                    <i class="fa fa-eye toggle-password" onclick="togglePassword('password_baru')"></i>
                </div>
                <div class="form-group">
                    <label for="konfirmasi_password"><i class="fa fa-lock"></i> Konfirmasi Password Baru</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" required>
                    <i class="fa fa-eye toggle-password" onclick="togglePassword('konfirmasi_password')"></i>
                </div>
                <div class="btn-group">
                    <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fungsi untuk menampilkan/menyembunyikan password
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.querySelector(`#${inputId} + .toggle-password`);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
</body>
</html>