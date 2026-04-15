<?php
session_start();
include("connection.php");

if (isset($_SESSION['admin_id'])) {
    $adminId = $_SESSION['admin_id'];
    $stmt = $conn->prepare("UPDATE admin_login SET is_online = 0 WHERE id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
}
?>
