<?php
ob_start();
session_start();
require('../fpdf/fpdf.php');
include("../Login/connection.php");

$report = $_GET['report'] ?? 'admin';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);
$pdf->SetAutoPageBreak(true, 10);

function escapeCell($value) {
    $value = str_replace(["\t","\n","\r"], [' ',' ',' '], $value);
    return htmlspecialchars($value);
}

if ($report === 'enrollment') {
    $pdf->Cell(50,10,'Grade',1,0,'C');
    $pdf->Cell(40,10,'Total Students',1,0,'C');
    $pdf->Cell(30,10,'Male',1,0,'C');
    $pdf->Cell(30,10,'Female',1,0,'C');
    $pdf->Ln();

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
        $pdf->Cell(50,10,escapeCell($row['grade_level']),1,0,'C');
        $pdf->Cell(40,10,$row['total_students'],1,0,'C');
        $pdf->Cell(30,10,$row['male'],1,0,'C');
        $pdf->Cell(30,10,$row['female'],1,0,'C');
        $pdf->Ln();
    }

    if(ob_get_length()) ob_end_clean();
    $pdf->Output('D','enrollment_summary.pdf');

} else {
    $pdf->Cell(60,10,'Admin',1,0,'C');
    $pdf->Cell(40,10,'Total Students',1,0,'C');
    $pdf->Cell(30,10,'Male',1,0,'C');
    $pdf->Cell(30,10,'Female',1,0,'C');
    $pdf->Ln();

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

    while($row = $result->fetch_assoc()){
        $pdf->Cell(60,10,escapeCell($row['username']),1,0,'C');
        $pdf->Cell(40,10,$row['total_students'],1,0,'C');
        $pdf->Cell(30,10,$row['male'],1,0,'C');
        $pdf->Cell(30,10,$row['female'],1,0,'C');
        $pdf->Ln();
    }

    if(ob_get_length()) ob_end_clean();
    $pdf->Output('D','admin_summary.pdf');
}

exit;
