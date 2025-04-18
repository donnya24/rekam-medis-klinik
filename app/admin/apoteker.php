<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

// Fetch apoteker data with user account
$sql = "SELECT a.*, u.username 
        FROM apoteker a 
        LEFT JOIN user u ON a.akun_apoteker = u.username";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Proses penghapusan data apoteker yang dipilih
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'deleteSingle' && isset($_POST['id'])) {
        $id = $_POST['id']; // Ambil ID apoteker yang akan dihapus

        // Mulai transaksi
        $conn->begin_transaction();
        try {
            // Query DELETE untuk menghapus satu data apoteker
            $sql = "DELETE FROM apoteker WHERE id_apoteker = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id); // 'i' berarti tipe data integer
            $stmt->execute();

            // Commit transaksi
            $conn->commit();

            // Respons JSON untuk sukses
            header('Content-Type: application/json'); // Set header JSON
            echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
            exit();
        } catch (Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            $conn->rollback();

            // Respons JSON untuk kesalahan
            header('Content-Type: application/json'); // Set header JSON
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Apoteker</title>
    <link href="../../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../../assets/css/font-awesome.css" rel="stylesheet" />
    <link href="../../assets/css/custom-styles.css" rel="stylesheet" />
    <style>
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .btn-warning {
            background-color: #d4a017;
            color: white;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .right-buttons {
            display: flex;
            gap: 10px;
        }
        #deleteSelected {
            display: none;
        }
        /* Tambahkan style untuk kolom Aksi */
        .table th.aksi-header, .table td.aksi-cell {
            width: 150px; /* Sesuaikan lebar sesuai kebutuhan */
        }
    </style>
</head>
<body>
<div id="wrapper">
    <div id="page-wrapper">
        <div id="page-inner">
            <h1 class="page-header text-center">Data Apoteker</h1>
            <div class="button-container">
                <a href="edit_apoteker.php?action=tambah" class="btn btn-primary mb-3">Tambah Apoteker</a>
                <div class="right-buttons">
                    <button id="toggleCheckboxes" class="btn btn-warning mb-3">Pilih Semua</button>
                    <button id="deleteSelected" class="btn btn-danger mb-3" disabled>Hapus Semua yang Dipilih</button>
                </div>
            </div>

            <table class="table table-bordered table-striped">
                <thead class="bg-primary text-white">
                    <tr>
                        <th id="selectAllHeader" style="display: none;"><input type="checkbox" id="selectAll"></th>
                        <th>No.</th>
                        <th>Nama Apoteker</th>
                        <th>Akun Apoteker</th>
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>Tanggal Bergabung</th>
                        <th>No. Telepon</th>
                        <th>Email</th>
                        <th class="aksi-header">Aksi</th> <!-- Tambahkan class aksi-header -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='checkboxColumn' style='display: none;'><input type='checkbox' class='selectItem' value='" . htmlspecialchars($row['id_apoteker']) . "'></td>";
                            echo "<td>" . $no . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_apoteker']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['akun_apoteker']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['jenis_kelamin']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['tanggal_lahir']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['tanggal_bergabung']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['no_telepon']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td class='aksi-cell'> <!-- Tambahkan class aksi-cell -->
                                    <a href='edit_apoteker.php?action=edit&id=" . htmlspecialchars($row['id_apoteker']) . "' class='btn btn-warning btn-sm'>Edit</a>
                                    <a href='apoteker.php' class='btn btn-danger btn-sm' onclick='deleteSingle(" . htmlspecialchars($row['id_apoteker']) . ")'>Hapus</a>
                                  </td>";
                            echo "</tr>";
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>Tidak ada data apoteker.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../../assets/js/jquery-1.10.2.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script>
    document.getElementById('toggleCheckboxes').addEventListener('click', function() {
        let checkboxes = document.querySelectorAll('.checkboxColumn');
        let selectAllHeader = document.getElementById('selectAllHeader');
        let deleteButton = document.getElementById('deleteSelected');
        if (checkboxes[0].style.display === 'none') {
            checkboxes.forEach(cb => cb.style.display = 'table-cell');
            selectAllHeader.style.display = 'table-cell';
            deleteButton.style.display = 'inline-block';
            this.textContent = 'Sembunyikan Checkbox';
        } else {
            checkboxes.forEach(cb => cb.style.display = 'none');
            selectAllHeader.style.display = 'none';
            deleteButton.style.display = 'none';
            this.textContent = 'Pilih Semua';
        }
    });

    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.selectItem').forEach(cb => cb.checked = this.checked);
        toggleDeleteButton();
    });

    document.querySelectorAll('.selectItem').forEach(cb => {
        cb.addEventListener('change', toggleDeleteButton);
    });

    function toggleDeleteButton() {
        document.getElementById('deleteSelected').disabled = document.querySelectorAll('.selectItem:checked').length === 0;
    }

    document.getElementById('deleteSelected').addEventListener('click', function() {
        let selectedIds = Array.from(document.querySelectorAll('.selectItem:checked')).map(cb => cb.value);
        if (selectedIds.length > 0 && confirm("Yakin ingin menghapus data yang dipilih?")) {
            fetch('hapus_semua_apoteker.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'deleteSelected', ids: JSON.stringify(selectedIds) })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Data berhasil dihapus');
                    location.reload();
                } else {
                    alert('Terjadi kesalahan saat menghapus data');
                }
            })
            .catch(() => alert('Terjadi kesalahan saat menghapus data'));
        }
    });

    function deleteSingle(id) {
        if (confirm("Yakin ingin menghapus data ini?")) {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ action: 'deleteSingle', id: id })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message); // Tampilkan pesan sukses
                    location.reload(); // Muat ulang halaman setelah penghapusan
                } else {
                    alert(data.message); // Tampilkan pesan kesalahan
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Data Berhasil di Hapus');
            });
        }
    }
</script>

</body>
</html>

<?php $conn->close(); ?>