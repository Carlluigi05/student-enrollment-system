<?php
session_start();
include("../Login/connection.php");

/*
|-----------------------------------------------------------------------
| ACCESS CONTROL
|-----------------------------------------------------------------------
*/
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['superAdmin_id'])) {
    echo "<script>alert('Access denied.'); window.location='../Login/adminLogin.php';</script>";
    exit;
}

/*
|-----------------------------------------------------------------------
| IDENTIFY USER
|-----------------------------------------------------------------------
*/
$admin_id = $_SESSION['admin_id'] ?? $_SESSION['superAdmin_id'];

/*
|-----------------------------------------------------------------------
| GET REDIRECT SOURCE
|-----------------------------------------------------------------------
*/
$from = $_POST['from'] ?? $_SESSION['edit_redirect'] ?? 'adminPanel';

/* 
|-----------------------------------------------------------------------
| DETERMINE RETURN URL
|-----------------------------------------------------------------------
*/
switch ($from) {
    case 'superAdminPanel':
        $returnUrl = "../SuperAdmin/superAdmin.php?show=students";
        break;

    case 'adminPanel':
    default:
        $returnUrl = "../Admin/adminDash.php?show=students";
        break;
}

// CLEAR SESSION TO PREVENT LEAK
unset($_SESSION['edit_redirect']);

/*
|-----------------------------------------------------------------------
| PROCESS FORM
|-----------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // -----------------------------
    // COLLECT INPUTS
    // -----------------------------
    $school_year = trim($_POST['school_year'] ?? '');
    $lrn = !empty($_POST['lrn']) ? trim($_POST['lrn']) : NULL;
    $grade_level = trim($_POST['grade_level'] ?? '');
    $psa_birth_cert_no = trim($_POST['psa_birth_cert_no'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $sex = trim($_POST['sex'] ?? '');
    $place_of_birth = trim($_POST['place_of_birth'] ?? '');
    $religion = trim($_POST['religion'] ?? '');
    $mother_tongue = trim($_POST['mother_tongue'] ?? '');
    $current_address = trim($_POST['current_address'] ?? '');
    $permanent_address = trim($_POST['permanent_address'] ?? '');
    $father_name = trim($_POST['father_name'] ?? '');
    $mother_maiden_name = trim($_POST['mother_maiden_name'] ?? '');
    $guardian_name = trim($_POST['guardian_name'] ?? '');
    $last_grade_completed = trim($_POST['last_grade_completed'] ?? '');
    $last_school_attended = trim($_POST['last_school_attended'] ?? '');
    $last_school_id = trim($_POST['last_school_id'] ?? '');

    $blended = isset($_POST['blended']) ? 1 : 0;
    $homeschooling = isset($_POST['homeschooling']) ? 1 : 0;
    $modular_print = isset($_POST['modular_print']) ? 1 : 0;
    $modular_digital = isset($_POST['modular_digital']) ? 1 : 0;
    $online = isset($_POST['online']) ? 1 : 0;

    $isEdit = isset($_POST['edit']) && $_POST['edit'] == 1;
    $student_id = intval($_POST['student_id'] ?? 0);

    // -----------------------------
    // REQUIRED FIELDS
    // -----------------------------
    if (empty($first_name) || empty($last_name) || empty($grade_level)) {
        echo "<script>alert('Please fill in all required fields.'); window.location='$returnUrl';</script>";
        exit;
    }

    // -----------------------------
    // DUPLICATE CHECKS
    // -----------------------------
    $errors = [];

    // LRN
    if (!empty($lrn)) {
        $stmt = $conn->prepare("SELECT student_id FROM basic_enrollment_students WHERE lrn = ? AND student_id != ?");
        $stmt->bind_param("si", $lrn, $student_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = "LRN already exists.";
        $stmt->close();
    }

    // PSA
    if (!empty($psa_birth_cert_no)) {
        $stmt = $conn->prepare("SELECT student_id FROM basic_enrollment_students WHERE psa_birth_cert_no = ? AND student_id != ?");
        $stmt->bind_param("si", $psa_birth_cert_no, $student_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = "PSA Birth Certificate Number already exists.";
        $stmt->close();
    }

    // FULL NAME
    $stmt = $conn->prepare("
        SELECT student_id FROM basic_enrollment_students
        WHERE last_name = ? AND first_name = ? AND middle_name = ? AND student_id != ?
    ");
    $stmt->bind_param("sssi", $last_name, $first_name, $middle_name, $student_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "A student with the SAME complete name already exists.";
    }
    $stmt->close();

    // -----------------------------
    // DUPLICATE FOUND → SHOW MODAL
    // -----------------------------
    if (!empty($errors)) {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Duplicate Record</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="icon" href="../Pics/logoOrg.png" type="image/png">
        </head>
        <body>
        <div class="modal fade show" style="display:block;" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Duplicate Record Found</h5>
                    </div>
                    <div class="modal-body">
                        <ul>';
        foreach ($errors as $err) {
            echo "<li>" . htmlspecialchars($err) . "</li>";
        }
        echo '</ul>
                    </div>
                    <div class="modal-footer">
                        <a href="' . $returnUrl . '" class="btn btn-primary">OK</a>
                    </div>
                </div>
            </div>
        </div>
        </body>
        </html>';
        exit;
    }

    // -----------------------------
    // UPDATE
    // -----------------------------
    if ($isEdit && $student_id > 0) {
        $stmt = $conn->prepare("
            UPDATE basic_enrollment_students SET
                school_year=?, lrn=?, grade_level=?, psa_birth_cert_no=?,
                last_name=?, first_name=?, middle_name=?, birthdate=?, age=?, sex=?,
                place_of_birth=?, religion=?, mother_tongue=?,
                current_address=?, permanent_address=?,
                father_name=?, mother_maiden_name=?, guardian_name=?,
                last_grade_completed=?, last_school_attended=?, last_school_id=?,
                blended=?, homeschooling=?, modular_print=?, modular_digital=?, online=?
            WHERE student_id=?
        ");

        $stmt->bind_param(
            "ssssssssssssssssssssssiiiii",
            $school_year, $lrn, $grade_level, $psa_birth_cert_no,
            $last_name, $first_name, $middle_name, $birthdate, $age, $sex,
            $place_of_birth, $religion, $mother_tongue,
            $current_address, $permanent_address,
            $father_name, $mother_maiden_name, $guardian_name,
            $last_grade_completed, $last_school_attended, $last_school_id,
            $blended, $homeschooling, $modular_print, $modular_digital, $online,
            $student_id
        );

        if ($stmt->execute()) {
            header("Location: $returnUrl&updated=1");
            exit;
        }
        $stmt->close();
    }

    // -----------------------------
    // INSERT NEW STUDENT
    // -----------------------------
    $stmt = $conn->prepare("
        INSERT INTO basic_enrollment_students (
            school_year, lrn, grade_level, psa_birth_cert_no,
            last_name, first_name, middle_name, birthdate, age, sex,
            place_of_birth, religion, mother_tongue,
            current_address, permanent_address,
            father_name, mother_maiden_name, guardian_name,
            last_grade_completed, last_school_attended, last_school_id,
            blended, homeschooling, modular_print, modular_digital, online,
            created_by
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "ssssssssssssssssssssssiiiii",
        $school_year, $lrn, $grade_level, $psa_birth_cert_no,
        $last_name, $first_name, $middle_name, $birthdate, $age, $sex,
        $place_of_birth, $religion, $mother_tongue,
        $current_address, $permanent_address,
        $father_name, $mother_maiden_name, $guardian_name,
        $last_grade_completed, $last_school_attended, $last_school_id,
        $blended, $homeschooling, $modular_print, $modular_digital, $online,
        $admin_id
    );

    if ($stmt->execute()) {
        header("Location: $returnUrl&success=1");
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>
