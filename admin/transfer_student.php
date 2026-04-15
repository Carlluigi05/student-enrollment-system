<?php
session_start();
include("../Login/connection.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']);
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Not logged in']);
    exit;
}

$from = (int)$_SESSION['admin_id'];
$to = isset($_POST['to_admin']) ? (int)$_POST['to_admin'] : 0;
$student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;

if ($to <= 0 || $student_id <= 0) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid parameters']);
    exit;
}

try {

    $stmt = $conn->prepare("SELECT created_by FROM basic_enrollment_students WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if ((int)$row['created_by'] !== $from) {
            http_response_code(403);
            echo json_encode(['success'=>false,'message'=>'You do not own this record']);
            exit;
        }
    } else {
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'Student not found']);
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("
        INSERT INTO student_transfers 
        (student_id, from_admin, to_admin, status, seen_by_sender, seen_by_receiver)
        VALUES (?, ?, ?, 'pending', 0, 0)
    ");
    $stmt->bind_param("iii", $student_id, $from, $to);

    $ok = $stmt->execute();
    $stmt->close();


    if ($ok) {
        echo json_encode(['success'=>true,'message'=>'Transfer request sent']);
    } else {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>'Failed to create transfer']);
    }
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success'=>false,
        'message'=>'Server error',
        'details'=>$e->getMessage()
    ]);
    exit;
}
