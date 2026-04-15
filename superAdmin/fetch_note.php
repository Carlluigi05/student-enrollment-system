<?php
session_start();
include("../Login/connection.php");

header('Content-Type: application/json');

// Ensure student_id is numeric
$student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;

$response = ['note' => ''];

if ($student_id > 0) {
    // Use prepared statement for safety
    $stmt = $conn->prepare("SELECT note FROM notes WHERE student_id = ? LIMIT 1");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['note'] = $row['note'];
    }

    $stmt->close();
}

// Return JSON response
echo json_encode($response);
?>
