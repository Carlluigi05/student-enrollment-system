<?php
session_start();
include("../Login/connection.php");

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['type'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $type = $_POST['type'];

    if ($type === 'new') {
        $stmt = $conn->prepare("DELETE FROM basic_enrollment_students WHERE student_id=?");
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['message'] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = "Invalid type";
    }
} else {
    $response['message'] = "Invalid request";
}

echo json_encode($response);
