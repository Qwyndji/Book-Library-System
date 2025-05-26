<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "Invalid rent ID.";
    exit;
}

$sql = "DELETE FROM rents WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../rent.php");
    exit;
} else {
    echo "Failed to delete rent: " . $stmt->error;
}
?>