<?php
session_start();
include("../Login/connection.php");

// Logout ONLY super admin
if (!empty($_SESSION['superadmin_id'])) {
    $superAdminId = $_SESSION['superadmin_id'];

    // Remove ONLY super admin session variables
    unset($_SESSION['superadmin_id']);
    unset($_SESSION['superadmin_username']); // if exists
    unset($_SESSION['role']);
}

header("Location: superAdminLogin.php");
exit;
?>
