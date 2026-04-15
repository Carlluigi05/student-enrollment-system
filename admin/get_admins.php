<?php
session_start();
include("../Login/connection.php");

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['admins' => [], 'error' => 'Not logged in']);
        exit;
    }

    $me = (int)$_SESSION['admin_id'];

    $stmt = $conn->prepare("SELECT id, username, email FROM admin_login WHERE id != ? ORDER BY username ASC");
    if (!$stmt) throw new Exception("Prepare failed: ".$conn->error);

    $stmt->bind_param("i", $me);
    $stmt->execute();
    $res = $stmt->get_result();

    $admins = [];
    while ($row = $res->fetch_assoc()) {
        $admins[] = [
            'id' => (int)$row['id'],
            'username' => $row['username'],
            'email' => $row['email']
        ];
    }

    $stmt->close();

    echo json_encode(['admins' => $admins]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['admins'=>[], 'error'=>'Database error','details'=>$e->getMessage()]);
    exit;
}
