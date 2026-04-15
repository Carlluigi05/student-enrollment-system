<?php
session_start();
include("../Login/connection.php");

// Logout ONLY admin, NOT superAdmin
if (!empty($_SESSION['admin_id'])) {
    $adminId = $_SESSION['admin_id'];

    // Set admin offline
    $stmt = $conn->prepare("UPDATE admin_login SET is_online = 0 WHERE id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $stmt->close();

    // Remove ONLY admin session variables
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']); // if you have this
}

// DO NOT destroy session — superAdmin may still be logged in

header("Location: ../Login/adminLogin.php");
exit;
?>
