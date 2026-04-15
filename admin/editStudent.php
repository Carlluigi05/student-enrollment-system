<?php
session_start();
include("../Login/connection.php");

// Get parameters from URL
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';
$from = $_GET['from'] ?? ''; // 'superAdminPanel' or empty for admin

// Validate required parameters
if (empty($type) || empty($id)) {
    die("Invalid edit request.");
}

// Save the redirect source in session
$redirectSource = $_GET['from'] ?? $_SESSION['edit_redirect'] ?? '';
$_SESSION['edit_redirect'] = $redirectSource;


// Determine the correct form based on student type and source
switch ($type) {
    case 'new':
        if ($from === 'superAdminPanel') {
            $redirectUrl = "../admin/beefForm.php?edit=1&id=" . urlencode($id) . "&from=superAdminPanel";
        } else {
            $redirectUrl = "../admin/beefForm.php?edit=1&id=" . urlencode($id);
        }
        break;

    case 'old':
        if ($from === 'superAdminPanel') {
            $redirectUrl = "../admin/confirmationSlipForm.php?edit=1&id=" . urlencode($id) . "&from=superAdminPanel";
        } else {
            $redirectUrl = "../admin/confirmationSlipForm.php?edit=1&id=" . urlencode($id);
        }
        break;

    default:
        die("Unknown student type.");
}

// Redirect to the appropriate form
header("Location: $redirectUrl");
exit;
?>
