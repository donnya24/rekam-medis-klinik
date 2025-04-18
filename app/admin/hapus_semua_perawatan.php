<?php
include "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'deleteSelected') {
    $ids = json_decode($_POST['ids'], true);

    if (is_array($ids) && count($ids) > 0) {
        // Prepare a SQL statement to delete selected perawatan
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM perawatan WHERE id_perawatan IN ($placeholders)";

        // Prepare the statement
        $stmt = $conn->prepare($sql);

        // Bind the parameters dynamically
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);

        // Execute the query
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
