<?php
include("../Login/connection.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];


    $stmt = $conn->prepare("UPDATE admin_login SET is_online = 1, last_activity = NOW() WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->close();
}
?>
