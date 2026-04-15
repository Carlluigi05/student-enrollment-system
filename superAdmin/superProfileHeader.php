<div class="d-flex align-items-center">
    <div class="profile-pic-container me-3">
        <img src="<?php echo !empty($superAdmin['photo']) ? '../uploads/'.$superAdmin['photo'] : '../images/default.png'; ?>" title="Photo" id="profilePicPreview">

        <form action="upload_superadmin_photo.php" method="POST" enctype="multipart/form-data" id="photoUploadForm">
            <input type="file" name="superadmin_photo" id="uploadPhotoInput" accept="image/*" onchange="document.getElementById('photoUploadForm').submit();">
            <label for="uploadPhotoInput" title="Upload new photo"></label>
        </form>
    </div>
                                
    <span class="fw-normal text-dark ms-0" id="adminEmail" style="font-size: 25px;">
        <?php echo htmlspecialchars($_SESSION['superAdmin_username']); ?>
    </span>
</div>          