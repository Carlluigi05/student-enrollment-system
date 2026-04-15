<?php
include("../Login/connection.php");

$result = $conn->query("SELECT COUNT(*) AS active_admins FROM admin_login WHERE is_online = 1");
$row = $result ? $result->fetch_assoc() : ['active_admins' => 0];


$activeAdmins = isset($row['active_admins']) && $row['active_admins'] > 0 ? $row['active_admins'] : 0;


echo json_encode(['active_admins' => $activeAdmins]);
?>
