<?php
session_start();
require_once "../Login/connection.php";

if (isset($_FILES['admin_photo']) && isset($_SESSION['admin_id'])) {

    $adminId = $_SESSION['admin_id'];
    $image = $_FILES['admin_photo'];

    $imageName = time() . "_" . basename($image['name']);
    $targetDir = "../uploads/";
    $targetFile = $targetDir . $imageName;

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (move_uploaded_file($image["tmp_name"], $targetFile)) {

        $stmt = $conn->prepare("UPDATE admin_login SET photo = ? WHERE id = ?");
        $stmt->bind_param("si", $imageName, $adminId);
        $stmt->execute();
        $stmt->close();

        $_SESSION['admin_photo'] = $imageName;

        header("Location: adminDash.php?upload=success");
        exit();

    } else {
        echo "Error uploading photo.";
    }
} else {
    echo "No image selected or session missing.";
}
?>
