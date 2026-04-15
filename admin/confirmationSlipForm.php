<?php
session_start();
include("../Login/connection.php");

$isEdit = isset($_GET['edit']) && $_GET['edit'] == 1;
$studentData = [];

if ($isEdit && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM confirmation_slip_students WHERE slip_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $studentData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$showSuccessModal = isset($_GET['success']);
$showUpdatedModal = isset($_GET['updated']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" href="../Pics/logoOrg.png" type="image/png">
  <title><?= $isEdit ? 'Edit Confirmation Record' : 'Confirmation Slip' ?></title>
  <style>
    body { background-color: #cfe7ff; }
    .form-section { border: 1px solid #dee2e6; border-radius: .5rem; padding: 1.5rem; margin-bottom: 1.5rem; background-color: #fff; }
    .section-title { font-weight: 600; font-size: 1.1rem; margin-bottom: 1rem; color: #0d6efd; text-transform: uppercase; }
    .form-label { font-weight: 500; }
  </style>
</head>
<body>
<div class="container my-4">
    <div class="text-center mb-4">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Seal_of_the_Department_of_Education_of_the_Philippines.png/1024px-Seal_of_the_Department_of_Education_of_the_Philippines.png" alt="DepEd Logo" width="80">
        <h4 class="mt-2 fw-bold text-uppercase">Department of Education</h4>
        <p class="text-muted mb-1">Region: I | Division: Pangasinan II</p>
        <p class="text-muted mb-1">School ID: 101815 | School Name: Mangaldan Central School</p>
        <h5 class="mt-3 fw-bold text-primary">CONFIRMATION SLIP</h5>
    </div>

    <?php if ($isEdit): ?>
        <div class="alert alert-warning text-center fw-bold">
            <i class="bi bi-pencil-square"></i> Editing Existing Student Record
        </div>
    <?php endif; ?>

    <form id="confirmationForm" method="POST" action="submitConfirmation.php">
        <input type="hidden" name="edit" value="<?= $isEdit ? '1' : '0' ?>">
        <?php if ($isEdit && isset($studentData['slip_id'])): ?>
            <input type="hidden" name="slip_id" value="<?= htmlspecialchars($studentData['slip_id']) ?>">
        <?php endif; ?>

        <div class="form-section">
            <div class="section-title">Learner Information</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="learner_name" class="form-control" placeholder="Enter learner’s full name"
                        value="<?= $isEdit ? htmlspecialchars($studentData['learner_name']) : '' ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">LRN</label>
                    <input type="text" name="lrn" class="form-control" placeholder="Learner Reference No."
                        value="<?= $isEdit ? htmlspecialchars($studentData['lrn']) : '' ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Grade Level</label>
                    <select name="grade_level" class="form-select" required>
                        <option disabled <?= !$isEdit ? 'selected' : '' ?>>Choose...</option>
                        <?php
                        $grades = ["Grade 2","Grade 3","Grade 4","Grade 5","Grade 6"];
                        foreach ($grades as $grade) {
                            $selected = ($isEdit && $studentData['grade_level'] == $grade) ? 'selected' : '';
                            echo "<option value='$grade' $selected>$grade</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">Confirmation of Enrollment</div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="confirmation_status" id="yes" value="YES"
                    <?= ($isEdit && $studentData['confirmation_status'] == 'YES') ? 'checked' : '' ?> required>
                <label class="form-check-label" for="yes">YES</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="confirmation_status" id="no" value="NO"
                    <?= ($isEdit && $studentData['confirmation_status'] == 'NO') ? 'checked' : '' ?>>
                <label class="form-check-label" for="no">NO</label>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? 'Update Record' : 'Submit Confirmation' ?>
            </button>
        </div>
    </form>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" id="statusModalContent">
            <div class="modal-header" id="statusModalHeader">
                <h5 class="modal-title" id="statusModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4" id="statusModalBody"></div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn" id="statusModalBtn" data-bs-dismiss="modal" onclick="redirectToDashboard()">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const showSuccess = <?= $showSuccessModal ? 'true' : 'false' ?>;
    const showUpdated = <?= $showUpdatedModal ? 'true' : 'false' ?>;

    if (showSuccess || showUpdated) {
        const modal = new bootstrap.Modal(document.getElementById('statusModal'));
        const modalLabel = document.getElementById('statusModalLabel');
        const modalBody = document.getElementById('statusModalBody');
        const modalHeader = document.getElementById('statusModalHeader');
        const modalBtn = document.getElementById('statusModalBtn');

        if (showSuccess) {
            modalHeader.className = "modal-header bg-success text-white rounded-top-4";
            modalLabel.innerHTML = "✅ Student Added";
            modalBody.innerHTML = "The student has been successfully added!";
            modalBtn.className = "btn btn-success px-4";
        } else {
            modalHeader.className = "modal-header bg-primary text-white rounded-top-4";
            modalLabel.innerHTML = "✏️ Record Updated";
            modalBody.innerHTML = "The student record has been successfully updated!";
            modalBtn.className = "btn btn-primary px-4";
        }

        modal.show();
    }
});

function redirectToDashboard() {
    window.location.href = "adminDash.php?show=students";
}
</script>
</body>
</html>
