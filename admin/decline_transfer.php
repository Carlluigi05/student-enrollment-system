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
    $stmt = $conn->prepare("SELECT to_admin, status FROM student_transfers WHERE id = ?");
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

    // Update transfer status to declined, reset seen_by_sender
    $stmt = $conn->prepare("UPDATE student_transfers SET status='declined', updated_at=NOW(), seen_by_sender=0 WHERE id = ?");
    $stmt->bind_param("i", $transfer_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success'=>true,'message'=>'Transfer declined']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Server error','details'=>$e->getMessage()]);
}
