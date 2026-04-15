<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../Login/connection.php";

if (!isset($_SESSION['admin_email'])) {
    header("Location: ../Login/adminLogin.php");
    exit();
}

$adminEmail = $_SESSION['admin_email'];

$stmt = $conn->prepare("SELECT id, username, photo FROM admin_login WHERE email = ?");
$stmt->bind_param("s", $adminEmail);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    $admin = [
        'username' => 'Unknown User',
        'photo' => null
    ];
}
?>

<div class="profile-header d-flex align-items-center">
    <div class="profile-pic-container me-3">
        <img src="<?php echo !empty($admin['photo'])
            ? '../uploads/' . htmlspecialchars($admin['photo'])
            : '../images/default.png'; ?>" 
            id="profilePicPreview" class="profile-img">

        <form action="admin.php" method="POST" enctype="multipart/form-data" id="photoUploadForm">
            <input type="file" name="admin_photo" id="uploadPhotoInput" accept="image/*"
                   onchange="document.getElementById('photoUploadForm').submit();">

            <label for="uploadPhotoInput" title="Upload new photo">
            </label>
        </form>
    </div>

    <span class="fw-normal text-dark username-display">
        <?php echo strtoupper(htmlspecialchars($admin['username'])); ?>
    </span>
</div>
