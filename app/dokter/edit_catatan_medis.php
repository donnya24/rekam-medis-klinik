<?php
session_start(); // Mulai session
include "../../includes/dokter/sidebar.php";
include "../../config/db.php";

// Cek apakah user sudah login dan role-nya adalah dokter
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'dokter') {
    echo "<script>alert('Anda harus login sebagai dokter!'); window.location='../../login.php';</script>";
    exit();
}

// Ambil id_dokter dari session
$id_dokter = $_SESSION['id_dokter'];

// Menangani proses tambah/edit catatan medis
if (isset($_POST['submit'])) {
    $id_reservasi = mysqli_real_escape_string($conn, $_POST['id_reservasi']);
    $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis']);
    $rencana_perawatan = mysqli_real_escape_string($conn, $_POST['rencana_perawatan']);
    $catatan_pasca_pemeriksaan = mysqli_real_escape_string($conn, $_POST['catatan_pasca_pemeriksaan']);
    $tanggal_periksa = mysqli_real_escape_string($conn, $_POST['tanggal_periksa']);
    $id_perawat = isset($_POST['id_perawat']) ? mysqli_real_escape_string($conn, $_POST['id_perawat']) : null;

    if (isset($_POST['id_catatan']) && $_POST['id_catatan'] != '') {
        $id_catatan = mysqli_real_escape_string($conn, $_POST['id_catatan']);
        $query = "UPDATE catatan_medis SET 
                    id_reservasi='$id_reservasi', 
                    diagnosis='$diagnosis', 
                    rencana_perawatan='$rencana_perawatan', 
                    catatan_pasca_pemeriksaan='$catatan_pasca_pemeriksaan', 
                    tanggal_periksa='$tanggal_periksa', 
                    id_perawat=" . ($id_perawat ? "'$id_perawat'" : "NULL") . "
                  WHERE id_catatan='$id_catatan'";
        $message = "Catatan medis berhasil diupdate!";
    } else {
        $query = "INSERT INTO catatan_medis (id_reservasi, diagnosis, rencana_perawatan, catatan_pasca_pemeriksaan, tanggal_periksa, id_perawat) 
                  VALUES ('$id_reservasi', '$diagnosis', '$rencana_perawatan', '$catatan_pasca_pemeriksaan', '$tanggal_periksa', " . ($id_perawat ? "'$id_perawat'" : "NULL") . ")";
        $message = "Catatan medis berhasil ditambahkan!";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('$message'); window.location='catatan_medis.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
    }
}

// Mengambil data reservasi sesuai dengan dokter yang login
$query_reservasi = "SELECT r.id_reservasi, p.nama_pasien, d.nama_dokter, r.tanggal 
                    FROM reservasi r
                    JOIN pasien p ON r.id_pasien = p.id_pasien
                    JOIN dokter d ON r.id_dokter = d.id_dokter
                    WHERE r.id_dokter = '$id_dokter'"; // Filter berdasarkan id_dokter yang login
$result_reservasi = mysqli_query($conn, $query_reservasi);

// Mengambil data perawat untuk dropdown
$query_perawat = "SELECT id_perawat, akun_perawat FROM perawat";
$result_perawat = mysqli_query($conn, $query_perawat);
$perawat_options = [];
while ($row = $result_perawat->fetch_assoc()) {
    $perawat_options[] = $row;
}

// Mengambil data untuk edit (jika ada)
$catatan = [];
if (isset($_GET['edit'])) {
    $id_catatan = $_GET['edit'];
    $result_edit = mysqli_query($conn, "SELECT * FROM catatan_medis WHERE id_catatan = $id_catatan");
    $catatan = mysqli_fetch_assoc($result_edit);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($catatan['id_catatan']) ? 'Edit Catatan Medis' : 'Tambah Catatan Medis'; ?></title>
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

        .form-container input, .form-container textarea, .form-container select {
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
                <h1><?php echo isset($catatan['id_catatan']) ? 'Edit Catatan Medis' : 'Tambah Catatan Medis'; ?></h1>
                <form action="edit_catatan_medis.php" method="POST">
                    <?php if (isset($catatan['id_catatan'])) { ?>
                        <input type="hidden" name="id_catatan" value="<?php echo htmlspecialchars($catatan['id_catatan']); ?>">
                    <?php } ?>

                    <label for="id_reservasi">Pilih Reservasi:</label>
                    <select id="id_reservasi" name="id_reservasi" required>
                        <option value="">-- Pilih Reservasi --</option>
                        <?php while ($row_reservasi = mysqli_fetch_assoc($result_reservasi)) { ?>
                            <option value="<?php echo htmlspecialchars($row_reservasi['id_reservasi']); ?>" 
                                <?php echo (isset($catatan['id_reservasi']) && $catatan['id_reservasi'] == $row_reservasi['id_reservasi']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row_reservasi['id_reservasi'] . ' - ' . $row_reservasi['nama_pasien'] . ' (Dokter: ' . $row_reservasi['nama_dokter'] . ', Tanggal: ' . $row_reservasi['tanggal'] . ')'); ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label for="tanggal_periksa">Tanggal Pemeriksaan:</label>
                    <input type="date" id="tanggal_periksa" name="tanggal_periksa" value="<?php echo isset($catatan['tanggal_periksa']) ? htmlspecialchars($catatan['tanggal_periksa']) : ''; ?>" required>

                    <label for="diagnosis">Diagnosis:</label>
                    <textarea id="diagnosis" name="diagnosis" required><?php echo isset($catatan['diagnosis']) ? htmlspecialchars($catatan['diagnosis']) : ''; ?></textarea>

                    <label for="rencana_perawatan">Rencana Perawatan:</label>
                    <textarea id="rencana_perawatan" name="rencana_perawatan" required><?php echo isset($catatan['rencana_perawatan']) ? htmlspecialchars($catatan['rencana_perawatan']) : ''; ?></textarea>

                    <label for="catatan_pasca_pemeriksaan">Catatan Pasca Pemeriksaan:</label>
                    <textarea id="catatan_pasca_pemeriksaan" name="catatan_pasca_pemeriksaan" required><?php echo isset($catatan['catatan_pasca_pemeriksaan']) ? htmlspecialchars($catatan['catatan_pasca_pemeriksaan']) : ''; ?></textarea>

                    <label for="id_perawat">Pilih Perawat (Opsional):</label>
                    <select id="id_perawat" name="id_perawat">
                        <option value="">-- Pilih Perawat (Opsional) --</option>
                        <?php foreach ($perawat_options as $perawat): ?>
                            <option value="<?php echo htmlspecialchars($perawat['id_perawat']); ?>" 
                                <?php echo (isset($catatan['id_perawat']) && $catatan['id_perawat'] == $perawat['id_perawat']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($perawat['akun_perawat']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" name="submit"><?php echo isset($catatan['id_catatan']) ? 'Update Catatan Medis' : 'Tambah Catatan Medis'; ?></button>
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