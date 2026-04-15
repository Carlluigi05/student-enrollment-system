<?php
session_start();
include("../Login/connection.php");
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$student_id = $_POST['student_id'];

// Delete the note
$query = "DELETE FROM notes WHERE student_id = '$student_id'";

if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>
