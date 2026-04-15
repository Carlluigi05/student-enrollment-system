<?php
include("../Login/connection.php");

// Get filter parameters
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$adminId = isset($_GET['admin_id']) ? trim($_GET['admin_id']) : '';
$gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';

// Base query
$query = "SELECT * FROM basic_enrollment_students WHERE 1=1";

// Search filter
if ($search !== '') {
    $searchEscaped = $conn->real_escape_string($search);
    $query .= " AND (
        student_id LIKE '%$searchEscaped%' OR
        lrn LIKE '%$searchEscaped%' OR
        psa_birth_cert_no LIKE '%$searchEscaped%' OR
        first_name LIKE '%$searchEscaped%' OR
        last_name LIKE '%$searchEscaped%' OR
        middle_name LIKE '%$searchEscaped%' OR
        school_year LIKE '%$searchEscaped%'
    )";
}

// Admin filter
if ($adminId !== '') {
    $adminId = (int)$adminId; // ensure integer
    $query .= " AND created_by = $adminId";
}

// Gender filter
if ($gender !== '') {
    $genderEscaped = $conn->real_escape_string($gender);
    $query .= " AND sex = '$genderEscaped'";
}

// Order by latest submission
$query .= " ORDER BY date_submitted DESC";

$result = $conn->query($query);

$rowsHtml = '';
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $student_id = htmlspecialchars($row['student_id'], ENT_QUOTES);

        // Check if a note exists
        $noteCheck = $conn->query("SELECT id FROM notes WHERE student_id='$student_id' LIMIT 1");
        $hasNote = $noteCheck && $noteCheck->num_rows > 0;
        $btnClass = $hasNote ? "btn-success" : "btn-info";

        // Use correct column: created_by
        $adminData = isset($row['created_by']) ? htmlspecialchars($row['created_by'], ENT_QUOTES) : '';

        $rowsHtml .= "<tr data-admin='{$adminData}'>
            <td class='text-center'>
                <a href='../admin/editStudent.php?type=new&id={$student_id}&from=superAdminPanel' class='btn btn-warning btn-sm me-0' title='Edit Student'>
                   <i class='bi bi-pencil-square'></i>
                </a>
                <button class='btn {$btnClass} btn-sm text-white viewNotesBtn' data-id='{$student_id}' title='View Note'>
                    <i class='bi bi-journal-text'></i>
                </button>
                <button class='btn btn-danger btn-sm ms-1 deleteBtn' data-id='{$student_id}' title='Delete Student'>
                    <i class='bi bi-trash-fill'></i>
                </button>
            </td>
            <td>{$student_id}</td>
            <td>" . htmlspecialchars($row['school_year'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['lrn'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['grade_level'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['psa_birth_cert_no'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['last_name'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['first_name'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['middle_name'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['birthdate'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['age'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['sex'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['place_of_birth'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['religion'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['mother_tongue'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['current_address'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['permanent_address'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['father_name'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['mother_maiden_name'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['guardian_name'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['last_grade_completed'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['last_school_attended'], ENT_QUOTES) . "</td>
            <td>" . htmlspecialchars($row['last_school_id'], ENT_QUOTES) . "</td>
            <td class='text-center'>" . ($row['blended'] ? '✔️' : '') . "</td>
            <td class='text-center'>" . ($row['homeschooling'] ? '✔️' : '') . "</td>
            <td class='text-center'>" . ($row['modular_print'] ? '✔️' : '') . "</td>
            <td class='text-center'>" . ($row['modular_digital'] ? '✔️' : '') . "</td>
            <td class='text-center'>" . ($row['online'] ? '✔️' : '') . "</td>
            <td>" . htmlspecialchars($row['date_submitted'], ENT_QUOTES) . "</td>
        </tr>";
    }
} else {
    $rowsHtml = "<tr><td colspan='28' class='text-center'>No Students Found</td></tr>";
}

echo json_encode(['new' => $rowsHtml]);
?>
