<?php
session_start();
include("../Login/connection.php");

$adminId = $_SESSION['admin_id'] ?? 0;
$isSuperAdmin = ($adminId == 1);

$search = $_GET['q'] ?? '';
$search = $conn->real_escape_string($search);

$gender = $_GET['gender'] ?? '';
$gender = $conn->real_escape_string($gender);

// --- Helper: Render new student row ---
function renderNewStudentRow($row) {
    $student_id = $row['student_id'];
    $last = htmlspecialchars($row['last_name'], ENT_QUOTES);
    $first = htmlspecialchars($row['first_name'], ENT_QUOTES);
    $middle = htmlspecialchars($row['middle_name'], ENT_QUOTES);

    global $conn;
    $noteCheck = $conn->query("SELECT id FROM notes WHERE student_id = '$student_id' LIMIT 1");
    $hasNote = $noteCheck && $noteCheck->num_rows > 0;
    $btnClass = $hasNote ? "btn-success" : "btn-info";

    return "<tr>
        <td class='text-center'>
            <a href='editStudent.php?type=new&id={$student_id}' class='btn btn-sm btn-warning me-1 editBtn' data-id='{$student_id}'>
                <i class='bi bi-pencil-square'></i>
            </a>
            <button class='btn btn-sm {$btnClass} text-white me-1 addNotesBtn' data-id='{$student_id}' title='Add Notes'>
                <i class='bi bi-journal-text'></i>
            </button>
            <button class='btn btn-sm btn-secondary transferBtn' data-id='{$student_id}' data-last='{$last}' data-first='{$first}' data-middle='{$middle}' title='Transfer'>
                <i class='bi bi-arrow-left-right'></i>
            </button>
        </td>
        <td>{$row['student_id']}</td>
        <td>{$row['school_year']}</td>
        <td>{$row['lrn']}</td>
        <td>{$row['grade_level']}</td>
        <td>{$row['psa_birth_cert_no']}</td>
        <td>{$row['last_name']}</td>
        <td>{$row['first_name']}</td>
        <td>{$row['middle_name']}</td>
        <td>{$row['birthdate']}</td>
        <td>{$row['age']}</td>
        <td>{$row['sex']}</td>
        <td>{$row['place_of_birth']}</td>
        <td>{$row['religion']}</td>
        <td>{$row['mother_tongue']}</td>
        <td>{$row['current_address']}</td>
        <td>{$row['permanent_address']}</td>
        <td>{$row['father_name']}</td>
        <td>{$row['mother_maiden_name']}</td>
        <td>{$row['guardian_name']}</td>
        <td>{$row['last_grade_completed']}</td>
        <td>{$row['last_school_attended']}</td>
        <td>{$row['last_school_id']}</td>
        <td class='text-center'>" . ($row['blended'] ? '✔️' : '') . "</td>
        <td class='text-center'>" . ($row['homeschooling'] ? '✔️' : '') . "</td>
        <td class='text-center'>" . ($row['modular_print'] ? '✔️' : '') . "</td>
        <td class='text-center'>" . ($row['modular_digital'] ? '✔️' : '') . "</td>
        <td class='text-center'>" . ($row['online'] ? '✔️' : '') . "</td>
        <td>{$row['date_submitted']}</td>
    </tr>";
}

// --- Helper: Render old student row ---
function renderOldStudentRow($row) {
    $slip_id = $row['slip_id'];
    return "<tr>
        <td class='text-center'>
            <a href='editStudent.php?type=old&id={$slip_id}' class='btn btn-warning btn-sm me-1 editBtn' data-id='{$slip_id}'>
                <i class='bi bi-pencil-square'></i>
            </a>
            <button class='btn btn-info btn-sm addNotesBtn' data-id='{$slip_id}' title='Add Notes'>
                <i class='bi bi-journal-text text-white'></i>
            </button>
            <button class='btn btn-secondary btn-sm transferBtn' data-id='{$slip_id}' title='Transfer'>
                <i class='bi bi-arrow-left-right'></i>
            </button>
        </td>
        <td>{$row['slip_id']}</td>
        <td>{$row['learner_name']}</td>
        <td>{$row['grade_level']}</td>
        <td>{$row['lrn']}</td>
        <td>{$row['confirmation_status']}</td>
        <td>{$row['date_submitted']}</td>
    </tr>";
}

// --- NEW STUDENTS QUERY ---
$searchFieldsNew = "student_id LIKE '%$search%' 
                 OR last_name LIKE '%$search%' 
                 OR first_name LIKE '%$search%' 
                 OR middle_name LIKE '%$search%' 
                 OR school_year LIKE '%$search%' 
                 OR lrn LIKE '%$search%' 
                 OR psa_birth_cert_no LIKE '%$search%'";

// Gender filter
$genderFilter = $gender ? " AND sex = '$gender'" : "";

if ($isSuperAdmin) {
    $queryNew = "SELECT * FROM basic_enrollment_students WHERE ($searchFieldsNew) $genderFilter ORDER BY date_submitted DESC";
} else {
    $queryNew = "SELECT * FROM basic_enrollment_students WHERE created_by = $adminId AND ($searchFieldsNew) $genderFilter ORDER BY date_submitted DESC";
}

$resultNew = $conn->query($queryNew);
$newHtml = "";
if ($resultNew && $resultNew->num_rows > 0) {
    while ($row = $resultNew->fetch_assoc()) {
        $newHtml .= renderNewStudentRow($row);
    }
} else {
    $newHtml = "<tr><td colspan='27' class='text-center'>No New Students Found</td></tr>";
}

// --- OLD STUDENTS QUERY ---
$searchFieldsOld = "slip_id LIKE '%$search%' OR learner_name LIKE '%$search%' OR lrn LIKE '%$search%'";

if ($isSuperAdmin) {
    $queryOld = "SELECT slip_id, learner_name, grade_level, lrn, confirmation_status, date_submitted FROM confirmation_slip_students WHERE $searchFieldsOld ORDER BY date_submitted DESC";
} else {
    $queryOld = "SELECT slip_id, learner_name, grade_level, lrn, confirmation_status, date_submitted FROM confirmation_slip_students WHERE created_by = $adminId AND ($searchFieldsOld) ORDER BY date_submitted DESC";
}

$resultOld = $conn->query($queryOld);
$oldHtml = "";
if ($resultOld && $resultOld->num_rows > 0) {
    while ($row = $resultOld->fetch_assoc()) {
        $oldHtml .= renderOldStudentRow($row);
    }
} else {
    $oldHtml = "<tr><td colspan='7' class='text-center'>No Old Students Found</td></tr>";
}

echo json_encode(['new' => $newHtml, 'old' => $oldHtml]);
?>
