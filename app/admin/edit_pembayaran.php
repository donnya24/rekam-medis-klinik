<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

// Cek apakah ada ID Pembayaran
if (isset($_GET['id_pembayaran'])) {
    $id_pembayaran = $_GET['id_pembayaran'];

    // Ambil data pembayaran berdasarkan ID
    $stmt = $conn->prepare("SELECT * FROM pembayaran WHERE id_pembayaran = ?");
    $stmt->bind_param("i", $id_pembayaran);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $pembayaran = $result->fetch_assoc();
    } else {
        echo "<script>alert('Data pembayaran tidak ditemukan.'); window.location='pembayaran.php';</script>";
        exit;
    }
} else {
    // Inisialisasi form kosong jika ID tidak ada (tambah pembayaran)
    $pembayaran = [
        'id_pasien' => '',
        'id_obat' => '',
        'id_perawatan' => '',
        'jumlah_obat' => '',
        'metode_pembayaran' => '', // Pastikan ini ada
        'jumlah_perawatan' => '',
        'jenis_pembayaran' => ''
    ];
}

// Query untuk mengambil pasien yang sudah melakukan reservasi
$pasien_query = "SELECT p.* FROM pasien p WHERE EXISTS (SELECT 1 FROM reservasi r WHERE r.id_pasien = p.id_pasien)";
$pasien_result = mysqli_query($conn, $pasien_query);

// Query Data Obat
$obat_query = "SELECT * FROM obat";
$obat_result = mysqli_query($conn, $obat_query);

// Query Data Perawatan
$perawatan_query = "SELECT * FROM perawatan";
$perawatan_result = mysqli_query($conn, $perawatan_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pasien = mysqli_real_escape_string($conn, $_POST['id_pasien']);
    $jenis_pembayaran = mysqli_real_escape_string($conn, $_POST['jenis_pembayaran']);
    $id_obat = isset($_POST['id_obat']) ? mysqli_real_escape_string($conn, $_POST['id_obat']) : null;
    $id_perawatan = isset($_POST['id_perawatan']) ? mysqli_real_escape_string($conn, $_POST['id_perawatan']) : null;
    $jumlah_obat = isset($_POST['jumlah_obat']) ? intval($_POST['jumlah_obat']) : 0;
    $jumlah_perawatan = isset($_POST['jumlah_perawatan']) ? intval($_POST['jumlah_perawatan']) : 0;
    $metode_pembayaran = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);
    $total_harga = 0;
    $bayar = isset($_POST['bayar']) ? floatval($_POST['bayar']) : 0;

    // Proses logika jenis pembayaran
    if (empty($id_pasien) || empty($jenis_pembayaran)) {
        echo "<script>alert('Field pasien dan jenis pembayaran harus diisi.');</script>";
        exit;
    } else {
        // Perhitungan harga obat
        if ($jenis_pembayaran === 'obat' || $jenis_pembayaran === 'perawatan_obat') {
            $query_obat = "SELECT harga, stok FROM obat WHERE id_obat = '$id_obat'";
            $result_obat = mysqli_query($conn, $query_obat);

            if ($result_obat && mysqli_num_rows($result_obat) > 0) {
                $data_obat = mysqli_fetch_assoc($result_obat);

                // Cek stok obat
                if ($data_obat['stok'] < $jumlah_obat) {
                    echo "<script>
                            alert('Stok obat tidak mencukupi.');
                            window.location.href = 'edit_pembayaran.php?id_pembayaran=$id_pembayaran';
                          </script>";
                    exit;
                }

                $harga_obat = $data_obat['harga'];
                $subtotal_obat = $harga_obat * $jumlah_obat;
                $total_harga += $subtotal_obat;
            } else {
                echo "<script>alert('Data obat tidak ditemukan.');</script>";
                exit;
            }
        }

        // Perhitungan harga perawatan
        if ($jenis_pembayaran === 'perawatan' || $jenis_pembayaran === 'perawatan_obat') {
            $query_perawatan = "SELECT harga_perawatan FROM perawatan WHERE id_perawatan = '$id_perawatan'";
            $result_perawatan = mysqli_query($conn, $query_perawatan);

            if ($result_perawatan && mysqli_num_rows($result_perawatan) > 0) {
                $data_perawatan = mysqli_fetch_assoc($result_perawatan);

                $harga_perawatan = $data_perawatan['harga_perawatan'];
                $subtotal_perawatan = $harga_perawatan * $jumlah_perawatan;
                $total_harga += $subtotal_perawatan;
            } else {
                echo "<script>alert('Data perawatan tidak ditemukan.');</script>";
                exit;
            }
        }

        $kembali = $bayar - $total_harga;
        if ($kembali < 0) {
            echo "<script>alert('Jumlah bayar tidak mencukupi untuk total harga.');</script>";
            exit;
        }

        if (isset($id_pembayaran)) {
            $update_query = "UPDATE pembayaran SET 
                             id_pasien = '$id_pasien', 
                             id_obat = '$id_obat', 
                             id_perawatan = '$id_perawatan',
                             jumlah_obat = '$jumlah_obat', 
                             metode_pembayaran = '$metode_pembayaran', 
                             jumlah_perawatan = '$jumlah_perawatan',
                             total_harga = '$total_harga',
                             bayar = '$bayar',
                             kembali = '$kembali'
                             WHERE id_pembayaran = '$id_pembayaran'";
        } else {
            $insert_query = "INSERT INTO pembayaran (id_pasien, id_obat, id_perawatan, jumlah_obat, metode_pembayaran, jumlah_perawatan, total_harga, bayar, kembali, tanggal_pembayaran) 
                             VALUES ('$id_pasien', '$id_obat', '$id_perawatan', '$jumlah_obat', '$metode_pembayaran', '$jumlah_perawatan', '$total_harga', '$bayar', '$kembali', NOW())";
        }

        if (mysqli_query($conn, isset($update_query) ? $update_query : $insert_query)) {
            // Ambil ID pembayaran terakhir jika transaksi baru
            if (!isset($id_pembayaran)) {
                $id_pembayaran = mysqli_insert_id($conn);
            }

            // Jika jenis pembayaran mencakup perawatan, insert ke transaksi_perawatan
            if ($jenis_pembayaran === 'perawatan' || $jenis_pembayaran === 'perawatan_obat') {
                // Check if the transaction already exists before inserting
                $check_query = "SELECT COUNT(*) FROM transaksi_perawatan WHERE id_pasien = '$id_pasien' AND id_perawatan = '$id_perawatan'";
                $check_result = mysqli_query($conn, $check_query);
                $check_row = mysqli_fetch_row($check_result);

                if ($check_row[0] == 0) {
                    // If the record does not exist, insert it
                    $insert_transaksi_query = "INSERT INTO transaksi_perawatan (id_pasien, id_perawatan, jumlah, tanggal) 
                                               VALUES ('$id_pasien', '$id_perawatan', '$jumlah_perawatan', NOW())";
                    if (!mysqli_query($conn, $insert_transaksi_query)) {
                        echo "<script>alert('Gagal menyimpan data ke transaksi_perawatan.');</script>";
                        exit;
                    }
                } else {
                    echo "<script>alert('Data perawatan untuk pasien ini sudah ada.');</script>";
                }
            }

            // Jika jenis pembayaran mencakup obat, kurangi stok obat
            if ($jenis_pembayaran === 'obat' || $jenis_pembayaran === 'perawatan_obat') {
                $update_stok_query = "UPDATE obat SET stok = stok - $jumlah_obat WHERE id_obat = '$id_obat'";
                mysqli_query($conn, $update_stok_query);
            }

            echo "<script>alert('Pembayaran berhasil disimpan!'); window.location='pembayaran.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan saat menyimpan pembayaran!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($id_pembayaran) ? 'Edit Pembayaran' : 'Tambah Pembayaran'; ?></title>
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

        .form-group {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-group label {
            width: 30%;
            font-weight: bold;
            margin-right: 10px;
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 70%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            resize: none;
        }

        .form-container button {
            width: 100%;
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
                <h1><?php echo isset($id_pembayaran) ? 'Edit Pembayaran' : 'Tambah Pembayaran'; ?></h1>
                <form action="" method="POST" id="payment_form">
                    <div class="form-group">
                        <label for="id_pasien">Pilih Pasien:</label>
                        <select name="id_pasien" id="id_pasien" required>
                            <?php
                            mysqli_data_seek($pasien_result, 0); // Reset pointer hasil query
                            while ($row = mysqli_fetch_assoc($pasien_result)) {
                                $selected = $pembayaran['id_pasien'] == $row['id_pasien'] ? 'selected' : '';
                                echo "<option value='{$row['id_pasien']}' $selected>{$row['nama_pasien']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jenis_pembayaran">Jenis Pembayaran:</label>
                        <select name="jenis_pembayaran" id="jenis_pembayaran" required onchange="toggleItemOptions()">
                            <option value="obat" <?php if (!isset($pembayaran['jenis_pembayaran']) || $pembayaran['jenis_pembayaran'] == 'obat') echo 'selected'; ?>>Obat</option>
                            <option value="perawatan" <?php if (isset($pembayaran['jenis_pembayaran']) && $pembayaran['jenis_pembayaran'] == 'perawatan') echo 'selected'; ?>>Perawatan</option>
                            <option value="perawatan_obat" <?php if (isset($pembayaran['jenis_pembayaran']) && $pembayaran['jenis_pembayaran'] == 'perawatan_obat') echo 'selected'; ?>>Perawatan dan Obat</option>
                        </select>
                    </div>

                    <div class="form-group" id="obat_options" style="display: none;">
                        <label for="id_obat">Pilih Obat:</label>
                        <select name="id_obat" id="id_obat">
                            <?php
                            mysqli_data_seek($obat_result, 0); // Reset pointer hasil query
                            while ($row = mysqli_fetch_assoc($obat_result)) {
                                echo "<option value='{$row['id_obat']}' data-price='{$row['harga']}'>{$row['nama_obat']} - Rp " . number_format($row['harga'], 0, ',', '.') . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group" id="perawatan_options" style="display: none;">
                        <label for="id_perawatan">Pilih Perawatan:</label>
                        <select name="id_perawatan" id="id_perawatan" onchange="hitungTotal()">
                            <?php
                            mysqli_data_seek($perawatan_result, 0); // Reset pointer hasil query
                            while ($row = mysqli_fetch_assoc($perawatan_result)) {
                                echo "<option value='{$row['id_perawatan']}' data-price='{$row['harga_perawatan']}'>{$row['nama_perawatan']} - Rp " . number_format($row['harga_perawatan'], 0, ',', '.') . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group" id="jumlah_obat_options" style="display: none;">
                        <label for="jumlah_obat">Jumlah Obat:</label>
                        <input type="number" name="jumlah_obat" id="jumlah_obat" value="1" oninput="hitungTotal()">
                    </div>

                    <div class="form-group" id="jumlah_perawatan_options" style="display: none;">
                        <label for="jumlah_perawatan">Jumlah Perawatan:</label>
                        <input type="number" name="jumlah_perawatan" id="jumlah_perawatan" value="1" oninput="hitungTotal()">
                    </div>

                    <div class="form-group">
                        <label for="total_harga">Total Harga:</label>
                        <input type="text" name="total_harga" id="total_harga" value="0" readonly>
                    </div>

                    <div class="form-group">
                        <label for="bayar">Bayar:</label>
                        <input type="number" name="bayar" id="bayar" oninput="hitungKembalian()">
                    </div>

                    <div class="form-group">
                        <label for="kembali">Kembali:</label>
                        <input type="text" name="kembali" id="kembali" value="0" readonly>
                    </div>

                    <div class="form-group">
                    <label for="metode_pembayaran">Metode Pembayaran:</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" required>
                        <option value="Tunai" <?php echo (isset($pembayaran['metode_pembayaran']) && $pembayaran['metode_pembayaran'] == 'Tunai') ? 'selected' : ''; ?>>Tunai</option>
                        <option value="Transfer" <?php echo (isset($pembayaran['metode_pembayaran']) && $pembayaran['metode_pembayaran'] == 'Transfer') ? 'selected' : ''; ?>>Transfer</option>
                        <option value="Kartu Kredit" <?php echo (isset($pembayaran['metode_pembayaran']) && $pembayaran['metode_pembayaran'] == 'Kartu Kredit') ? 'selected' : ''; ?>>Kartu Kredit</option>
                    </select>
                </div>

                    <button type="submit" name="submit">Simpan Pembayaran</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleItemOptions() {
    const jenis = document.getElementById('jenis_pembayaran').value;

    // Obat dan jumlah obat
    document.getElementById('obat_options').style.display = jenis === 'obat' || jenis === 'perawatan_obat' ? 'block' : 'none';
    document.getElementById('jumlah_obat_options').style.display = jenis === 'obat' || jenis === 'perawatan_obat' ? 'block' : 'none';

    // Perawatan dan jumlah perawatan
    document.getElementById('perawatan_options').style.display = jenis === 'perawatan' || jenis === 'perawatan_obat' ? 'block' : 'none';
    document.getElementById('jumlah_perawatan_options').style.display = jenis === 'perawatan' || jenis === 'perawatan_obat' ? 'block' : 'none';

    // Set jumlah obat menjadi kosong jika memilih perawatan
    if (jenis === 'perawatan' || jenis === 'perawatan_obat') {
        document.getElementById('jumlah_obat').value = 0; // Set jumlah obat menjadi 0
    }

    // Set jumlah perawatan menjadi kosong jika memilih obat
    if (jenis === 'obat') {
        document.getElementById('jumlah_perawatan').value = 0; // Set jumlah perawatan menjadi 0
    }
}

function hitungTotal() {
    const jumlah_obat = parseInt(document.getElementById('jumlah_obat').value || 0);
    const jumlah_perawatan = parseInt(document.getElementById('jumlah_perawatan').value || 1);
    const jenis_pembayaran = document.getElementById('jenis_pembayaran').value;
    let total = 0;

    if (jenis_pembayaran === 'obat' || jenis_pembayaran === 'perawatan_obat') {
        const harga_obat = parseFloat(document.querySelector("#id_obat option:checked").textContent.split(' - Rp ')[1].replace(/\./g, '').replace('Rp ', ''));
        total += harga_obat * jumlah_obat;
    }

    if (jenis_pembayaran === 'perawatan' || jenis_pembayaran === 'perawatan_obat') {
        const harga_perawatan = parseFloat(document.querySelector("#id_perawatan option:checked").textContent.split(' - Rp ')[1].replace(/\./g, '').replace('Rp ', ''));
        total += harga_perawatan * jumlah_perawatan;
    }

    document.getElementById('total_harga').value = total.toLocaleString('id-ID');
    hitungKembalian();
}

function hitungKembalian() {
    const total_harga = parseFloat(document.getElementById('total_harga').value.replace(/\./g, '') || 0);
    const bayar = parseFloat(document.getElementById('bayar').value || 0);

    const kembali = bayar - total_harga;
    
    if (kembali < 0) {
        document.getElementById('kembali').value = "Jumlah bayar kurang";
    } else {
        document.getElementById('kembali').value = kembali.toLocaleString('id-ID');
    }
}
</script>

</body>
</html>

<?php $conn->close(); ?>