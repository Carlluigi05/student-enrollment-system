<?php
session_start();
include("../Login/connection.php");

$isEdit = isset($_GET['edit']) && $_GET['edit'] == 1;
$studentData = [];
$redirectSource = $_GET['from'] ?? $_SESSION['edit_redirect'] ?? '';

if ($isEdit && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM basic_enrollment_students WHERE student_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $studentData = $result->fetch_assoc();
    } else {
        $isEdit = false;
        echo "<div class='alert alert-danger text-center fw-bold mt-3'>
                Invalid edit request. Record not found.
              </div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="../Pics/logoOrg.png" type="image/png">
  <title><?= $isEdit ? 'Edit Student Record' : 'Enrollment Form' ?></title>
  <style>
    body { background-color: #cfe7ff; }
    .form-section { border: 1px solid #dee2e6; border-radius: .5rem; padding: 1.5rem; margin-bottom: 1.5rem; background-color: #fff; }
    .section-title { font-weight: 600; font-size: 1.1rem; margin-bottom: 1rem; color: #0d6efd; }
    .form-label { font-weight: 500; }
  </style>
</head>
<body>
  <div class="container my-4">
    <div class="text-center mb-4">
      <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fa/Seal_of_the_Department_of_Education_of_the_Philippines.png/1024px-Seal_of_the_Department_of_Education_of_the_Philippines.png" alt="DepEd Logo" width="80">
      <h4 class="mt-2 fw-bold">BASIC EDUCATION ENROLLMENT FORM</h4>
      <p class="text-muted">This form is not for sale. Please fill out all fields legibly and accurately.</p>
    </div>

    <?php if ($isEdit): ?>
      <div class="alert alert-warning text-center fw-bold">
        <i class="bi bi-pencil-square"></i> Editing Existing Student Record
      </div>
    <?php endif; ?>

    <form action="submit_beef.php" method="POST" id="enrollForm">
      <?php if ($isEdit && isset($studentData['student_id'])): ?>
        <input type="hidden" name="edit" value="1">
        <input type="hidden" name="student_id" value="<?= htmlspecialchars($studentData['student_id']) ?>">
      <?php else: ?>
        <input type="hidden" name="edit" value="0">
      <?php endif; ?>

      <!-- Redirect tracking -->
      <input type="hidden" name="from" value="<?= htmlspecialchars($_SESSION['edit_redirect'] ?? '') ?>">

      <!-- SCHOOL INFO -->
      <div class="form-section">
        <div class="section-title">1. School Information</div>
        <div class="row g-3">

          <!-- AUTO SCHOOL YEAR DROPDOWN -->
          <div class="col-md-6">
            <label for="school_year" class="form-label">School Year</label>
            <select name="school_year" id="school_year" class="form-select" required>
              <option value="">Select School Year</option>
              <?php
                $currentYear = date("Y");
                $selectedSY = $studentData['school_year'] ?? '';

                for ($i = $currentYear - 2; $i <= $currentYear + 5; $i++) {
                    $nextYear = $i + 1;
                    $sy = $i . "-" . $nextYear;
                    $selected = ($selectedSY === $sy) ? 'selected' : '';
                    echo "<option value='$sy' $selected>$sy</option>";
                }
              ?>
            </select>
          </div>

          <!-- LRN NOT REQUIRED -->
          <div class="col-md-6">
            <label class="form-label">Learner Reference No. (LRN)</label>
            <input type="text" class="form-control" name="lrn"
              value="<?= htmlspecialchars($studentData['lrn'] ?? '') ?>"
              placeholder="LRN (if applicable)">
          </div>

          <div class="col-md-6">
            <label class="form-label">Grade Level to Enroll</label>
            <select class="form-select" name="grade_level" required>
              <option disabled <?= empty($studentData['grade_level']) ? 'selected' : '' ?>>Choose grade level</option>
              <?php
                $grades = ['Kindergarten','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6'];
                foreach ($grades as $grade) {
                    $selected = ($studentData['grade_level'] ?? '') === $grade ? 'selected' : '';
                    echo "<option value='$grade' $selected>$grade</option>";
                }
              ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">PSA Birth Certificate No.</label>
            <input type="text" class="form-control" name="psa_birth_cert_no"
              value="<?= htmlspecialchars($studentData['psa_birth_cert_no'] ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- PERSONAL INFO -->
      <div class="form-section">
        <div class="section-title">2. Learner’s Personal Information</div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Last Name</label>
            <input type="text" class="form-control" name="last_name"
              value="<?= htmlspecialchars($studentData['last_name'] ?? '') ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="first_name"
              value="<?= htmlspecialchars($studentData['first_name'] ?? '') ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-control" name="middle_name"
              value="<?= htmlspecialchars($studentData['middle_name'] ?? '') ?>">
          </div>

          <div class="col-md-3">
            <label class="form-label">Birthdate</label>
            <input type="date" class="form-control" id="birthdate" name="birthdate"
              value="<?= htmlspecialchars($studentData['birthdate'] ?? '') ?>" required>
          </div>

          <div class="col-md-2">
            <label class="form-label">Age</label>
            <input type="number" class="form-control" id="age" name="age"
              value="<?= htmlspecialchars($studentData['age'] ?? '') ?>" readonly required>
          </div>

          <div class="col-md-3">
            <label class="form-label">Sex</label>
            <select class="form-select" name="sex" required>
              <option value="">Select</option>
              <option value="Male" <?= ($studentData['sex'] ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= ($studentData['sex'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Place of Birth</label>
            <input type="text" class="form-control" name="place_of_birth"
              value="<?= htmlspecialchars($studentData['place_of_birth'] ?? '') ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label">Religion</label>
            <input type="text" class="form-control" name="religion"
              value="<?= htmlspecialchars($studentData['religion'] ?? '') ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label">Mother Tongue</label>
            <input type="text" class="form-control" name="mother_tongue"
              value="<?= htmlspecialchars($studentData['mother_tongue'] ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- ADDRESS -->
      <div class="form-section">
        <div class="section-title">3. Address Information</div>

        <div class="mb-2 form-check">
          <input type="checkbox" class="form-check-input" id="sameAddress">
          <label for="sameAddress" class="form-check-label">Same as current address</label>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Current Address</label>
            <input type="text" class="form-control" name="current_address"
              value="<?= htmlspecialchars($studentData['current_address'] ?? '') ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Permanent Address</label>
            <input type="text" class="form-control" name="permanent_address"
              value="<?= htmlspecialchars($studentData['permanent_address'] ?? '') ?>" required>
          </div>
        </div>
      </div>

      <!-- PARENTS -->
      <div class="form-section">
        <div class="section-title">4. Parent’s / Guardian’s Information</div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Father's Name</label>
            <input type="text" class="form-control" name="father_name"
              value="<?= htmlspecialchars($studentData['father_name'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Mother’s Maiden Name</label>
            <input type="text" class="form-control" name="mother_maiden_name"
              value="<?= htmlspecialchars($studentData['mother_maiden_name'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Legal Guardian’s Name</label>
            <input type="text" class="form-control" name="guardian_name"
              value="<?= htmlspecialchars($studentData['guardian_name'] ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- TRANSFER -->
      <div class="form-section">
        <div class="section-title">5. For Returning / Transfer Learners</div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Last Grade Level Completed</label>
            <input type="text" class="form-control" name="last_grade_completed"
              value="<?= htmlspecialchars($studentData['last_grade_completed'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Last School Attended</label>
            <input type="text" class="form-control" name="last_school_attended"
              value="<?= htmlspecialchars($studentData['last_school_attended'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">School ID</label>
            <input type="text" class="form-control" name="last_school_id"
              value="<?= htmlspecialchars($studentData['last_school_id'] ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- LEARNING MODALITIES -->
      <div class="form-section">
        <div class="section-title">6. Preferred Learning Modalities</div>
        <div class="row">

          <?php
            $modalities = ['blended','homeschooling','modular_print','modular_digital','online'];
            foreach ($modalities as $modality):
          ?>
          <div class="col-md-6">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="<?= $modality ?>" name="<?= $modality ?>"
                <?= !empty($studentData[$modality]) && $studentData[$modality] == 1 ? 'checked' : '' ?>>
              <label class="form-check-label" for="<?= $modality ?>">
                <?= ucwords(str_replace('_',' ', $modality)) ?>
              </label>
            </div>
          </div>
          <?php endforeach; ?>

        </div>
      </div>

      <!-- SUBMIT -->
      <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary">
          <?= $isEdit ? 'Update Record' : 'Submit Enrollment' ?>
        </button>
      </div>
    </form>
  </div>


<!-- JS Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // AUTO COPY ADDRESS
  document.getElementById('sameAddress').addEventListener('change', function() {
    const current = document.querySelector('input[name="current_address"]');
    const permanent = document.querySelector('input[name="permanent_address"]');

    if (this.checked) permanent.value = current.value;
    else permanent.value = '';
  });

  // AUTO CALCULATE AGE
  document.getElementById('birthdate').addEventListener('change', function() {
    const birthdate = new Date(this.value);
    const today = new Date();

    let age = today.getFullYear() - birthdate.getFullYear();
    const monthDiff = today.getMonth() - birthdate.getMonth();
    const dayDiff = today.getDate() - birthdate.getDate();

    if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) age--;

    document.getElementById('age').value = age;
  });

  document.addEventListener("DOMContentLoaded", function() {
    let duplicated = localStorage.getItem("duplicateFields");
    if (!duplicated) return;

    let fields = duplicated.split(",");
    fields.forEach(field => {
        let input = document.querySelector(`[name="${field}"]`);
        if (input) {
            input.style.border = "2px solid red";
            input.style.background = "#ffe6e6";
        }
    });

    localStorage.removeItem("duplicateFields");
});
</script>

</body>
</html>
