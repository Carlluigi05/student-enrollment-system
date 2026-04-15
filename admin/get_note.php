<?php
session_start();
include("../Login/connection.php");

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (empty($_GET['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing student ID']);
    exit;
}

$student_id = (int)$_GET['student_id'];

// Get the latest note for this student
$query = "SELECT note FROM notes WHERE student_id = '$student_id' ORDER BY date_created DESC LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['success' => true, 'note' => $row['note']]);
} else {
    echo json_encode(['success' => true, 'note' => '']); // no note yet
}
?>
