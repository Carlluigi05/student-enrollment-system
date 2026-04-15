<?php
session_start();
include("../Login/connection.php");
header('Content-Type: application/json');

$student_id = $_POST['student_id'];
$result = $conn->query("SELECT note FROM notes WHERE student_id='$student_id' LIMIT 1");

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['note' => $row['note']]);
} else {
    echo json_encode(['note' => '']);
}
?>
