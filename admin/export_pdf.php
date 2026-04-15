<?php
require('../fpdf/fpdf.php');
include("../Login/connection.php");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont("Arial", "B", 16);
$pdf->Cell(0, 10, "Student Enrollment Report", 0, 1, "C");
$pdf->Ln(5);

$pdf->SetFont("Arial", "B", 12);
$pdf->Cell(50, 10, "Grade Level", 1);
$pdf->Cell(40, 10, "Total", 1);
$pdf->Cell(40, 10, "Male", 1);
$pdf->Cell(40, 10, "Female", 1);
$pdf->Ln();


$pdf->SetFont("Arial", "", 12);
$result = $conn->query("
  SELECT grade_level,
         COUNT(*) AS total,
         SUM(sex='Male') AS male,
         SUM(sex='Female') AS female
  FROM basic_enrollment_students
  GROUP BY grade_level
");

while ($row = $result->fetch_assoc()) {
  $pdf->Cell(50, 10, $row['grade_level'], 1);
  $pdf->Cell(40, 10, $row['total'], 1);
  $pdf->Cell(40, 10, $row['male'], 1);
  $pdf->Cell(40, 10, $row['female'], 1);
  $pdf->Ln();
}

$pdf->Output("D", "student_report.pdf");
?>
