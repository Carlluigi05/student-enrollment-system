<?php
session_start();
include("../Login/connection.php");

header('Content-Type: application/json; charset=utf-8');

function json_error($code = 500, $payload = []) {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    json_error(401, [
        'incoming' => [],
        'updates'  => [],
        'error'    => 'not_logged'
    ]);
}

$me = (int) $_SESSION['admin_id'];

try {
    $incoming = [];
    $updates  = [];

    $sqlIncoming = "
        SELECT
            t.id AS transfer_id,
            t.student_id,
            t.from_admin,
            COALESCE(a.username, '') AS from_username,
            COALESCE(s.last_name, '') AS last_name,
            COALESCE(s.first_name, '') AS first_name,
            COALESCE(s.middle_name, '') AS middle_name,
            t.created_at AS date_submitted
        FROM student_transfers t
        LEFT JOIN admin_login a ON a.id = t.from_admin
        LEFT JOIN basic_enrollment_students s ON s.student_id = t.student_id
        WHERE t.to_admin = ?
          AND t.status = 'pending'
          AND (t.seen_by_receiver = 0 OR t.seen_by_receiver IS NULL)
        ORDER BY t.created_at ASC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sqlIncoming);
    if (!$stmt) {
        json_error(500, ['error' => 'db_prepare_failed', 'details' => $conn->error]);
    }
    $stmt->bind_param('i', $me);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $incoming[] = [
            'id'            => (int)$row['transfer_id'],
            'student_id'    => (int)$row['student_id'],
            'from_admin'    => (int)$row['from_admin'],
            'from_username' => $row['from_username'],
            'last_name'     => $row['last_name'],
            'first_name'    => $row['first_name'],
            'middle_name'   => $row['middle_name'],
            'date_submitted'=> $row['date_submitted']
        ];
    }
    $stmt->close();

    $sqlUpdates = "
        SELECT
            t.id AS transfer_id,
            t.student_id,
            COALESCE(a.username, '') AS to_username,
            t.status,
            COALESCE(t.updated_at, t.created_at) AS date_updated
        FROM student_transfers t
        LEFT JOIN admin_login a ON a.id = t.to_admin
        WHERE t.from_admin = ?
          AND t.status IN ('accepted','declined')
          AND (t.seen_by_sender = 0 OR t.seen_by_sender IS NULL)
        ORDER BY COALESCE(t.updated_at, t.created_at) DESC
        LIMIT 20
    ";

    $stmt = $conn->prepare($sqlUpdates);
    if (!$stmt) {
        json_error(500, ['error' => 'db_prepare_failed', 'details' => $conn->error]);
    }
    $stmt->bind_param('i', $me);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $updates[] = [
            'id'          => (int)$row['transfer_id'],
            'student_id'  => (int)$row['student_id'],
            'to_username' => $row['to_username'],
            'status'      => $row['status'],
            'date_updated'=> $row['date_updated']
        ];
    }
    $stmt->close();

    echo json_encode([
        'incoming' => $incoming,
        'updates'  => $updates
    ], JSON_UNESCAPED_UNICODE);
    exit;

} 
catch (Throwable $e) {
    json_error(500, [
        'error' => 'exception',
        'details' => $e->getMessage()
    ]);
}
