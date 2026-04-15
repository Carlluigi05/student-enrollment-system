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
    $stmt = $conn->prepare("UPDATE student_transfers SET seen_by_receiver = 1 WHERE id = ? AND to_admin = ?");
    $stmt->bind_param("ii", $transfer_id, $me);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        echo json_encode(['success'=>true,'message'=>'Marked as seen']);
    } else {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>'Failed to mark as seen']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Server error','details'=>$e->getMessage()]);
}
