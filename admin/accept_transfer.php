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

$me = (int)$_SESSION['admin_id'];
$transfer_id = isset($_POST['transfer_id']) ? (int)$_POST['transfer_id'] : 0;

if ($transfer_id <= 0) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid transfer ID']);
    exit;
}

try {
    // Check transfer exists and belongs to receiver
    $stmt = $conn->prepare("SELECT student_id, to_admin, from_admin, status FROM student_transfers WHERE id = ?");
    $stmt->bind_param("i", $transfer_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $tr = $res->fetch_assoc();
    $stmt->close();

    if (!$tr || (int)$tr['to_admin'] !== $me || $tr['status'] !== 'pending') {
        http_response_code(403);
        echo json_encode(['success'=>false,'message'=>'Invalid transfer']);
        exit;
    }

    // 1) Update student ownership
    $stmt = $conn->prepare("UPDATE basic_enrollment_students SET created_by = ? WHERE student_id = ?");
    $stmt->bind_param("ii", $me, $tr['student_id']);
    $stmt->execute();
    $stmt->close();

    // 2) Update transfer record to accepted, reset seen_by_sender
    $stmt = $conn->prepare("UPDATE student_transfers SET status='accepted', updated_at=NOW(), seen_by_sender=0 WHERE id = ?");
    $stmt->bind_param("i", $transfer_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success'=>true,'message'=>'Transfer accepted']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Server error','details'=>$e->getMessage()]);
}
