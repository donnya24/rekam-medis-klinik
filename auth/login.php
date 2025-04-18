<?php
session_start();
include('../config/db.php');

// Cek koneksi database
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Gunakan Prepared Statements untuk keamanan
        $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap, role FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            // **Tanpa hashing: langsung bandingkan password**
            if ($password === $row['password']) {
                // Simpan data ke session
                $_SESSION['id_user'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
                $_SESSION['role'] = $row['role']; 

                // Jika role adalah dokter, ambil id_dokter dari tabel dokter
                if ($row['role'] === 'dokter') {
                    $stmt_dokter = $conn->prepare("SELECT id_dokter FROM dokter WHERE akun_dokter = ?");
                    $stmt_dokter->bind_param("s", $username);
                    $stmt_dokter->execute();
                    $result_dokter = $stmt_dokter->get_result();
                    if ($result_dokter->num_rows == 1) {
                        $row_dokter = $result_dokter->fetch_assoc();
                        $_SESSION['id_dokter'] = $row_dokter['id_dokter']; // Simpan id_dokter ke session
                    }
                    $stmt_dokter->close();
                }

                // Jika role adalah perawat, ambil id_perawat dari tabel perawat
                if ($row['role'] === 'perawat') {
                    $stmt_perawat = $conn->prepare("SELECT id_perawat FROM perawat WHERE akun_perawat = ?");
                    $stmt_perawat->bind_param("s", $username);
                    $stmt_perawat->execute();
                    $result_perawat = $stmt_perawat->get_result();
                    if ($result_perawat->num_rows == 1) {
                        $row_perawat = $result_perawat->fetch_assoc();
                        $_SESSION['id_perawat'] = $row_perawat['id_perawat']; // Simpan id_perawat ke session
                    }
                    $stmt_perawat->close();
                }

                // Arahkan berdasarkan peran (role)
                if ($row['role'] === 'admin') {
                    header("Location: ../app/admin/dashboard.php");
                } elseif ($row['role'] === 'administrasi') {
                    header("Location: ../app/administrasi/dashboard_administrasi.php");
                } elseif ($row['role'] === 'dokter') {
                    header("Location: ../app/dokter/dashboard_dokter.php");
                } elseif ($row['role'] === 'perawat') {
                    header("Location: ../app/perawat/dashboard_perawat.php");
                } elseif ($row['role'] === 'apoteker') {
                    header("Location: ../app/apoteker/dashboard_apoteker.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $error = "Username atau Password salah!";
            }
        } else {
            $error = "Username atau Password salah!";
        }
        $stmt->close();
    } else {
        $error = "Harap isi semua kolom!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body {
            display: flex; justify-content: center; align-items: center;
            height: 100vh; color: white; padding: 20px;
            background: url('../assets/img/farmasi.jpg') no-repeat center center/cover;
            position: relative;
        }
        
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            z-index: 1;
        }
        
        .container {
            position: relative;
            background: rgba(0, 0, 0, 0.7);
            padding: 40px; border-radius: 16px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center; width: 100%; max-width: 400px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 2;
        }
        
        .container h2 { font-size: 26px; font-weight: 700; margin-bottom: 10px; }
        .container p { font-size: 14px; color: rgba(255, 255, 255, 0.8); margin-bottom: 20px; }
        .form-group { position: relative; margin-bottom: 20px; }
        
        .form-group input {
            width: 100%; padding: 12px 45px 12px 40px;
            border: none; border-radius: 8px; font-size: 16px;
            background: rgba(255, 255, 255, 0.2); color: white;
            outline: none; transition: 0.3s ease-in-out;
        }
        
        .form-group input::placeholder { color: rgba(255, 255, 255, 0.6); }
        .form-group input:hover, .form-group input:focus {
            background: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
        }
        
        .form-group i {
            position: absolute; top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
        }
        
        .form-group .fa-user, 
        .form-group .fa-lock {
            left: 15px;
            color: rgba(0, 174, 255, 0.98);
        }

        .form-group .fa-eye, 
        .form-group .fa-eye-slash { 
            right: 15px; cursor: pointer; color: rgba(255, 255, 255, 0.6);
        }
        
        .btn-primary {
            width: 100%; padding: 12px; border: none;
            border-radius: 8px; font-size: 16px; font-weight: bold;
            cursor: pointer; background: linear-gradient(135deg, #007bff, #0056b3);
            color: white; transition: 0.3s;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3, #004494);
            transform: scale(1.05);
        }
        
        .alert {
            margin-bottom: 20px;
            font-size: 14px;
            background: rgba(255, 0, 0, 0.3);
            color: white;
            border-radius: 8px;
            padding: 10px;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Login Sebagai :</h2>
        <p>Admin / Administrasi / Dokter / Perawat / Apoteker</p>
        <?php if (isset($error)) { echo "<div class='alert'>$error</div>"; } ?>
        <form method="POST" action="">
            <div class="form-group">
                <i class="fa fa-user"></i>
                <input type="text" name="username" required placeholder="Username">
            </div>
            
            <div class="form-group">
                <i class="fa fa-lock"></i>
                <input type="password" id="password" name="password" required placeholder="Kata Sandi">
                <i class="fa fa-eye" id="togglePassword"></i>
            </div>
            
            <button type="submit" class="btn-primary">Masuk</button>
        </form>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
