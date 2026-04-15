<?php
include("../Login/connection.php");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=student_report.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr>
        <th>Grade Level</th>
        <th>Total Students</th>
        <th>Male</th>
        <th>Female</th>
      </tr>";

$query = $conn->query("
  SELECT grade_level,
         COUNT(*) AS total,
         SUM(sex='Male') AS male,
         SUM(sex='Female') AS female
  FROM basic_enrollment_students
  GROUP BY grade_level
");

while ($row = $query->fetch_assoc()) {
  echo "<tr>
          <td>{$row['grade_level']}</td>
          <td>{$row['total']}</td>
          <td>{$row['male']}</td>
          <td>{$row['female']}</td>
        </tr>";
}
echo "</table>";
?>
