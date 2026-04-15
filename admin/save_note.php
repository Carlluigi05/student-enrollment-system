<?php
session_start();
include("../Login/connection.php");

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Validate POST
if (empty($_POST['student_id']) || empty($_POST['note_text'])) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$student_id = (int)$_POST['student_id'];
$note_text = mysqli_real_escape_string($conn, $_POST['note_text']);
$admin_id = (int)$_SESSION['admin_id'];

// Insert note
$query = "INSERT INTO notes (student_id, note, created_by) VALUES ('$student_id', '$note_text', '$admin_id')";

if ($conn->query($query)) {
    echo json_encode(['success' => true, 'student_id' => $student_id]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>
