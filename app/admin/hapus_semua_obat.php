<?php
include "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'deleteSelected') {
    $ids = json_decode($_POST['ids'], true);

    if (is_array($ids) && count($ids) > 0) {
        // Buat placeholder untuk query
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM obat WHERE id_obat IN ($placeholders)";

        // Persiapkan statement
        $stmt = $conn->prepare($sql);

        // Bind parameter secara dinamis
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);

        // Eksekusi query
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false]);
    }
}

$conn->close();
?>
