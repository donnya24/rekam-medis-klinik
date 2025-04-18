<?php
include "../../includes/admin/sidebar.php";
include "../../config/db.php";

// Mengambil data ruangan
$sql = "SELECT * FROM ruangan";
if (isset($_GET['delete'])) {
    $id_ruangan = $_GET['delete'];
    $delete_sql = "DELETE FROM ruangan WHERE id_ruangan = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id_ruangan);

    if ($stmt->execute()) {
        echo "<script>alert('Ruangan berhasil dihapus!'); window.location='ruangan.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat menghapus ruangan: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Ruangan</title>
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
    </style>
</head>
<body>
<div id="wrapper">
    <div id="page-wrapper">
        <div id="page-inner">
            <h1 class="page-header text-center">Data Ruangan</h1>
            <div class="button-container">
                <a href="edit_ruangan.php" class="btn btn-primary mb-3">Tambah Ruangan</a>
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
                        <th>Nama Ruangan</th>
                        <th>Kapasitas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='checkboxColumn' style='display: none;'><input type='checkbox' class='selectItem' value='" . htmlspecialchars($row['id_ruangan']) . "'></td>";
                            echo "<td>" . $no . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama_ruangan']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['kapasitas']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>
                                    <a href='edit_ruangan.php?id=" . htmlspecialchars($row['id_ruangan']) . "' class='btn btn-warning btn-sm'>Edit</a>
                                    <a href='ruangan.php?delete=" . htmlspecialchars($row['id_ruangan']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>
                                  </td>";
                            echo "</tr>";
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>Tidak ada data ruangan</td></tr>";
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
            fetch('hapus_semua_ruangan.php', {
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
</script>

</body>
</html>

<?php $conn->close(); ?>
