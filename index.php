<?php
session_start();

// Jika pengguna sudah login
if (isset($_SESSION['id_user'])) {
    // Periksa role pengguna
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: app/admin/dashboard.php");
            break;
        case 'administrasi':
            header("Location: app/administrasi/dashboard_administrasi.php");
            break;
        case 'dokter':
            header("Location: app/dokter/dashboard_dokter.php");
            break;
        case 'perawat':
            header("Location: app/perawat/dashboard_perawat.php");
            break;
        case 'apoteker':
            header("Location: app/apoteker/dashboard_apoteker.php");
            break;
        default:
            header("Location: dashboard.php"); // Default jika role tidak dikenali
            break;
    }
    exit();
}

// Jika belum login, arahkan ke halaman login
header("Location: auth/login.php");
exit();
?>
