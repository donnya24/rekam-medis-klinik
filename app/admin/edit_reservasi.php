<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$action = isset($_GET['action']) ? $_GET['action'] : 'add';
$id_reservasi = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
$reservasi_data = [];
$title = $action === 'edit' ? "Edit Reservasi" : "Tambah Reservasi Baru";

if ($action === 'edit' && $id_reservasi) {
    $query = "
        SELECT reservasi.*, pasien.nama_pasien, 
               perawatan.nama_perawatan, 
               dokter.nama_dokter, 
               ruangan.nama_ruangan
        FROM reservasi
        LEFT JOIN pasien ON reservasi.id_pasien = pasien.id_pasien
        LEFT JOIN perawatan ON reservasi.id_perawatan = perawatan.id_perawatan
        LEFT JOIN dokter ON reservasi.id_dokter = dokter.id_dokter
        LEFT JOIN ruangan ON reservasi.id_ruangan = ruangan.id_ruangan
        WHERE reservasi.id_reservasi = '$id_reservasi'
    ";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $reservasi_data = mysqli_fetch_assoc($result);
    } else {
        echo "<script>alert('Data reservasi tidak ditemukan.'); window.location='reservasi.php';</script>";
        exit;
    }
}

// Mengambil data pasien yang belum memiliki reservasi
$pasien_result = mysqli_query($conn, "
    SELECT pasien.id_pasien, pasien.nama_pasien 
    FROM pasien
    LEFT JOIN reservasi ON pasien.id_pasien = reservasi.id_pasien
    WHERE reservasi.id_pasien IS NULL
");

// Mengambil data perawatan untuk dropdown
$perawatan_result = mysqli_query($conn, "
    SELECT perawatan.id_perawatan, perawatan.nama_perawatan, 
           dokter.nama_dokter, ruangan.nama_ruangan
    FROM perawatan
    LEFT JOIN dokter ON perawatan.id_dokter = dokter.id_dokter
    LEFT JOIN ruangan ON perawatan.id_ruangan = ruangan.id_ruangan
");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $id_pasien = mysqli_real_escape_string($conn, $_POST['id_pasien']);
    $id_perawatan = mysqli_real_escape_string($conn, $_POST['id_perawatan']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $waktu = mysqli_real_escape_string($conn, $_POST['waktu']);

    // Ambil ID dokter dan ID ruangan berdasarkan ID perawatan
    $perawatan_detail = mysqli_query($conn, "
        SELECT dokter.id_dokter, ruangan.id_ruangan
        FROM perawatan
        LEFT JOIN dokter ON perawatan.id_dokter = dokter.id_dokter
        LEFT JOIN ruangan ON perawatan.id_ruangan = ruangan.id_ruangan
        WHERE perawatan.id_perawatan = '$id_perawatan'
    ");
    $detail = mysqli_fetch_assoc($perawatan_detail);

    $id_dokter = $detail['id_dokter'];
    $id_ruangan = isset($detail['id_ruangan']) ? intval($detail['id_ruangan']) : 0;

    // Jika ruangan tidak ditemukan
    if ($id_ruangan == 0) {
        echo "<script>alert('Ruangan tidak tersedia untuk perawatan ini.'); window.location='edit_reservasi.php?action=$action&id=$id_reservasi';</script>";
        exit;
    }

    // Cek apakah perawatan dan waktu sudah terdaftar untuk pasien lain
    $query_check_reservasi = "
        SELECT * FROM reservasi 
        WHERE id_perawatan = '$id_perawatan' AND tanggal = '$tanggal' AND waktu = '$waktu'
    ";
    $result_check_reservasi = mysqli_query($conn, $query_check_reservasi);
    if (mysqli_num_rows($result_check_reservasi) > 0) {
        echo "<script>alert('Perawatan sudah terjadwal pada waktu yang sama. Silakan pilih waktu yang berbeda.');</script>";
        exit;
    }

    // Tambah data reservasi (untuk action 'add')
    if ($action === 'add') {
        $query = "
            INSERT INTO reservasi (id_pasien, id_perawatan, id_dokter, id_ruangan, tanggal, waktu)
            VALUES ('$id_pasien', '$id_perawatan', '$id_dokter', '$id_ruangan', '$tanggal', '$waktu')
        ";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Reservasi berhasil ditambahkan!'); window.location='reservasi.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
        }
    } 

    // Update data reservasi (untuk action 'edit')
    if ($action === 'edit' && $id_reservasi) {
        $query = "
            UPDATE reservasi 
            SET id_pasien = '$id_pasien', 
                id_perawatan = '$id_perawatan', 
                id_dokter = '$id_dokter', 
                id_ruangan = '$id_ruangan', 
                tanggal = '$tanggal', 
                waktu = '$waktu'
            WHERE id_reservasi = '$id_reservasi'
        ";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Reservasi berhasil diperbarui!'); window.location='reservasi.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
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

        .form-container input, .form-container select {
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
                <h1><?php echo $title; ?></h1>
                <form action="edit_reservasi.php?action=<?php echo $action; ?><?php echo $action === 'edit' ? '&id=' . htmlspecialchars($id_reservasi) : ''; ?>" method="POST">
                    <?php if ($action === 'edit') { ?>
                        <input type="hidden" name="id_reservasi" value="<?php echo htmlspecialchars($id_reservasi); ?>">
                    <?php } ?>

                    <label for="id_pasien">Pilih Pasien:</label>
                    <select id="id_pasien" name="id_pasien" required>
                        <option value="">-- Pilih Pasien --</option>
                        <?php while ($pasien = mysqli_fetch_assoc($pasien_result)) { ?>
                            <option value="<?php echo $pasien['id_pasien']; ?>"
                                    <?php echo isset($reservasi_data['id_pasien']) && $reservasi_data['id_pasien'] == $pasien['id_pasien'] ? 'selected' : ''; ?>>
                                <?php echo $pasien['nama_pasien']; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label for="id_perawatan">Pilih Perawatan:</label>
                    <select id="id_perawatan" name="id_perawatan" required>
                        <option value="">-- Pilih Perawatan --</option>
                        <?php while ($perawatan = mysqli_fetch_assoc($perawatan_result)) { ?>
                            <option value="<?php echo $perawatan['id_perawatan']; ?>"
                                    <?php echo isset($reservasi_data['id_perawatan']) && $reservasi_data['id_perawatan'] == $perawatan['id_perawatan'] ? 'selected' : ''; ?>>
                                <?php echo $perawatan['nama_perawatan'] . " (Dokter: " . $perawatan['nama_dokter'] . ", Ruangan: " . $perawatan['nama_ruangan'] . ")"; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label for="tanggal">Tanggal Reservasi:</label>
                    <input type="date" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($reservasi_data['tanggal'] ?? ''); ?>" required>

                    <label for="waktu">Waktu Reservasi:</label>
                    <input type="text" id="waktu" name="waktu" value="<?php echo htmlspecialchars($reservasi_data['waktu'] ?? ''); ?>" required>

                    <button type="submit" name="submit"><?php echo $action === 'edit' ? 'Perbarui Reservasi' : 'Tambah Reservasi'; ?></button>
                </form>
            </div>
        </div>    
    </div>
</div>
<script src="../../assets/js/jquery-1.10.2.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script src="../../assets/js/custom-scripts.js"></script>

    <script>
        // Function to update the time every second
        function updateTime() {
            var currentDate = new Date();
            var hours = String(currentDate.getHours()).padStart(2, '0');
            var minutes = String(currentDate.getMinutes()).padStart(2, '0');
            var seconds = String(currentDate.getSeconds()).padStart(2, '0');
            document.getElementById('waktu').value = hours + ":" + minutes + ":" + seconds;
        }

        // Update time every second
        setInterval(updateTime, 1000);

        // Set initial time
        updateTime();
    </script>
</body>
</html>

<?php $conn->close(); ?>