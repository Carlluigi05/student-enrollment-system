<?php
session_start();
include("connection.php"); 

$role = $_POST['role'] ?? '';
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($role) || empty($email) || empty($password)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: {$role}Login.php");
    exit;
}

switch ($role) {
    case "superAdmin":
        $table = "superadmin_login";
        $redirect = "superAdminLogin.php";
        $dashboard = "../SuperAdmin/superAdmin.php";
        break;

    case "admin":
        $table = "admin_login";
        $redirect = "adminLogin.php";
        $dashboard = "../admin/adminDash.php";
        break;

    default:
        $_SESSION['error'] = "Invalid role.";
        header("Location: userSelection.html");
        exit;
}

$sql = "SELECT id, username, email, password FROM $table WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($password === $row['password'] || password_verify($password, $row['password'])) {


        if ($role === "superAdmin") {
            $_SESSION['superAdmin_id'] = $row['id'];
            $_SESSION['superAdmin_username'] = $row['username'];
            $_SESSION['superAdmin_email'] = $row['email'];

            header("Location: $dashboard");
            exit;
        }

        elseif ($role === "admin") {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_email'] = $row['email'];

            $update = $conn->prepare("UPDATE admin_login SET is_online = 1, last_login = NOW() WHERE id = ?");
            $update->bind_param("i", $row['id']);
            $update->execute();

            header("Location: $dashboard");
            exit;
        }


    } else {
        $_SESSION['error'] = "Invalid Password!";
        header("Location: $redirect");
        exit;
    }
} else {
    $_SESSION['error'] = "Invalid Email!";
    header("Location: $redirect");
    exit;
}
?>
