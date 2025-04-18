<?php
include "../../config/db.php";

if (isset($_POST['action']) && $_POST['action'] === 'deleteSelected') {
    $ids = json_decode($_POST['ids']);
    $ids = implode(",", array_map('intval', $ids));  // Ensure all IDs are integers

    // Delete query for multiple doctors
    $delete_sql = "DELETE FROM dokter WHERE id_dokter IN ($ids)";
    if ($conn->query($delete_sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
