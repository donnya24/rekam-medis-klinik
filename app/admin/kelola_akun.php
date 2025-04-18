<?php
ob_start(); // Start output buffering
session_start();
include "../../config/db.php";

// Fungsi untuk validasi input
function sanitize_input($data) {
    return $data ? htmlspecialchars(stripslashes(trim($data))) : ''; // Tambahkan pemeriksaan null
}

// Role yang diperbolehkan
$allowed_roles = ['admin', 'administrasi', 'dokter', 'perawat', 'apoteker'];

// Handle tambah akun
if (isset($_POST['tambah'])) {
    $username = sanitize_input($_POST['username'] ?? '');
    $nama_lengkap = sanitize_input($_POST['nama_lengkap'] ?? '');
    $no_telepon = sanitize_input($_POST['no_telepon'] ?? '');
    $alamat = sanitize_input($_POST['alamat'] ?? '');
    $password = sanitize_input($_POST['password'] ?? ''); // Ambil password tanpa hashing
    $role = sanitize_input($_POST['role'] ?? '');

    if ($username && $nama_lengkap && $no_telepon && $alamat && $password && in_array($role, $allowed_roles)) {
        // Cek apakah username sudah ada
        $stmt = $conn->prepare("SELECT id FROM user WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['error'] = 'Username sudah digunakan!';
        } else {
            // Simpan password dalam bentuk plaintext
            $stmt = $conn->prepare("INSERT INTO user (username, nama_lengkap, no_telepon, alamat, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $nama_lengkap, $no_telepon, $alamat, $password, $role);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Akun berhasil ditambahkan!';
            } else {
                $_SESSION['error'] = 'Gagal menambah akun!';
            }
        }
    } else {
        $_SESSION['error'] = 'Data tidak valid!';
    }
    header("Location: kelola_akun.php");
    exit();
}

// Handle update akun
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $username = sanitize_input($_POST['username'] ?? '');
    $nama_lengkap = sanitize_input($_POST['nama_lengkap'] ?? '');
    $no_telepon = sanitize_input($_POST['no_telepon'] ?? '');
    $alamat = sanitize_input($_POST['alamat'] ?? '');
    $role = sanitize_input($_POST['role'] ?? '');

    if ($username && $nama_lengkap && $no_telepon && $alamat && in_array($role, $allowed_roles)) {
        if (!empty($_POST['password'])) {
            // Simpan password dalam bentuk plaintext
            $password = sanitize_input($_POST['password']); // Ambil password tanpa hashing
            $stmt = $conn->prepare("UPDATE user SET username = ?, nama_lengkap = ?, no_telepon = ?, alamat = ?, password = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $username, $nama_lengkap, $no_telepon, $alamat, $password, $role, $id);
        } else {
            $stmt = $conn->prepare("UPDATE user SET username = ?, nama_lengkap = ?, no_telepon = ?, alamat = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $username, $nama_lengkap, $no_telepon, $alamat, $role, $id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Akun berhasil diperbarui!';
        } else {
            $_SESSION['error'] = 'Gagal memperbarui akun!';
        }
    } else {
        $_SESSION['error'] = 'Data tidak valid!';
    }
    header("Location: kelola_akun.php");
    exit();
}

// Handle hapus akun
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Akun berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus akun!';
    }
    header("Location: kelola_akun.php");
    exit();
}

// Ambil data akun (kecuali admin)
$result = $conn->query("SELECT * FROM user WHERE role != 'admin'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Akun</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color : #f8f9fa;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .modal-content {
            border-radius: 10px;
        }
        .btn-primary, .btn-success, .btn-warning, .btn-danger {
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h2 class="text-center mb-3">Kelola Akun Petugas</h2>

    <!-- Notifikasi -->
    <?php if (isset($_SESSION['success'])) { ?>
        <div class="alert alert-success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php } ?>
    <?php if (isset($_SESSION['error'])) { ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php } ?>

    <div class="d-flex justify-content-start gap-2 mb-3">
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
            <i class="bi bi-plus-lg"></i> Tambah Akun
        </button>
    </div>

    <table class="table table-striped table-hover shadow-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>No. Telepon</th>
                <th>Alamat</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= $row['username']; ?></td>
                    <td><?= $row['nama_lengkap']; ?></td>
                    <td><?= $row['no_telepon']; ?></td>
                    <td><?= $row['alamat']; ?></td>
                    <td>
                        <span class="badge bg-<?= $row['role'] == 'administrasi' ? 'primary' : ($row['role'] == 'dokter' ? 'danger' : ($row['role'] == 'perawat' ? 'success' : 'warning')) ?>">
                            <?= ucfirst($row['role']); ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id']; ?>">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                        <a href="kelola_akun.php?hapus=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?');">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    </td>
                </tr>

                <!-- Modal Edit -->
                <div class="modal fade" id="editModal<?= $row['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-white">
                                <h5 class="modal-title">Edit Akun</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                    <label>Username:</label>
                                    <input type="text" name="username" class="form-control" value="<?= $row['username']; ?>" required>
                                    <label>Nama Lengkap:</label>
                                    <input type="text" name="nama_lengkap" class="form-control" value="<?= $row['nama_lengkap']; ?>" required>
                                    <label>No. Telepon:</label>
                                    <input type="text" name="no_telepon" class="form-control" value="<?= $row['no_telepon']; ?>" required>
                                    <label>Alamat:</label>
                                    <textarea name="alamat" class="form-control" required><?= $row['alamat']; ?></textarea>
                                    <label>Password (kosongkan jika tidak ingin mengubah):</label>
                                    <input type="password" name="password" class="form-control">
                                    <label>Role:</label>
                                    <select name="role" class="form-control"> <!-- Hapus spasi di nama input -->
                                        <option value="administrasi" <?= $row['role'] == 'administrasi' ? 'selected' : ''; ?>>Administrasi</option>
                                        <option value="dokter" <?= $row['role'] == 'dokter' ? 'selected' : ''; ?>>Dokter</option>
                                        <option value="perawat" <?= $row['role'] == 'perawat' ? 'selected' : ''; ?>>Perawat</option>
                                        <option value="apoteker" <?= $row['role'] == 'apoteker' ? 'selected' : ''; ?>>Apoteker</option>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="update" class="btn btn-success">Update</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <label>Username:</label>
                    <input type="text" name="username" class="form-control" required>
                    <label>Nama Lengkap:</label>
                    <input type="text" name="nama_lengkap" class="form-control" required>
                    <label>No. Telepon:</label>
                    <input type="text" name="no_telepon" class="form-control" required>
                    <label>Alamat:</label>
                    <textarea name="alamat" class="form-control" required></textarea>
                    <label>Password:</label>
                    <input type="password" name="password" class="form-control" required>
                    <label>Role:</label>
                    <select name="role" class="form-control">
                        <option value="administrasi">Administrasi</option>
                        <option value="dokter">Dokter</option>
                        <option value="perawat">Perawat</option>
                        <option value="apoteker">Apoteker</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>