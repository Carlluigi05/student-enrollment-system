<?php
ob_start();
session_start();
include("../Login/connection.php");

$report = $_GET['report'] ?? 'admin';

$filename = "";
$headers = [];
$rows = [];

function escapeExcel($value) {
    $value = str_replace(["\t", "\n", "\r"], [' ', ' ', ' '], $value);
    return htmlspecialchars($value);
}

if ($report === 'enrollment') {
    $filename = "enrollment_summary.xls";
    $headers = ["Grade", "Total Students", "Male", "Female"];

    $result = $conn->query("
        SELECT 
            grade_level,
            COUNT(*) AS total_students,
            SUM(sex='Male') AS male,
            SUM(sex='Female') AS female
        FROM basic_enrollment_students
        GROUP BY grade_level
        ORDER BY grade_level ASC
    ");

    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            escapeExcel($row['grade_level']),
            $row['total_students'],
            $row['male'],
            $row['female']
        ];
    }

} else {
    $filename = "admin_summary.xls";
    $headers = ["Admin", "Total Students", "Male", "Female"];

    $result = $conn->query("
        SELECT 
            a.username,
            COUNT(b.student_id) AS total_students,
            SUM(b.sex='Male') AS male,
            SUM(b.sex='Female') AS female
        FROM admin_login a
        LEFT JOIN basic_enrollment_students b
            ON a.id = b.created_by
        GROUP BY a.id
        ORDER BY total_students DESC
    ");

    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            escapeExcel($row['username']),
            $row['total_students'],
            $row['male'],
            $row['female']
        ];
    }
}

if (ob_get_length()) ob_end_clean();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo implode("\t", $headers) . "\n";

foreach ($rows as $row) {
    echo implode("\t", $row) . "\n";
}

exit;
