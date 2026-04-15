<?php
include ("../Login/connection.php");
session_start();

if (isset($_FILES['superadmin_photo']) && isset($_SESSION['superAdmin_id'])) {
    $superAdminId = $_SESSION['superAdmin_id'];
    $image = $_FILES['superadmin_photo'];
    $imageName = time() . "_" . basename($image['name']);
    $targetDir = "../uploads/";
    $targetFile = $targetDir . $imageName;

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (move_uploaded_file($image["tmp_name"], $targetFile)) {
        $sql = "UPDATE superadmin_login SET photo='$imageName' WHERE id='$superAdminId'";
        mysqli_query($conn, $sql);
        header("Location: superAdmin.php?upload=success");
        exit();
    } else {
        echo "Error uploading photo.";
    }
} else {
    echo "No image selected.";
}
?>
