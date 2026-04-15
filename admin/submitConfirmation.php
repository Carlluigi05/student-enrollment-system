<?php
session_start();
$admin_id = $_SESSION['admin_id'];
include("../Login/connection.php");

// Determine where the request came from (default to admin)
$from = $_GET['from'] ?? $_SESSION['edit_redirect'] ?? 'adminPanel';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $learner_name = trim($_POST['learner_name'] ?? '');
    $lrn = trim($_POST['lrn'] ?? '');
    $grade_level = trim($_POST['grade_level'] ?? '');
    $confirmation_status = trim($_POST['confirmation_status'] ?? '');
    $isEdit = isset($_POST['edit']) && $_POST['edit'] == 1;
    $id = $_POST['slip_id'] ?? null;

    if (empty($learner_name) || empty($lrn) || empty($grade_level) || empty($confirmation_status)) {
        echo "<script>
            alert('Please fill in all required fields.');
            window.history.back();
        </script>";
        exit();
    }

    if ($isEdit && !empty($id)) {
        $stmt = $conn->prepare("
            UPDATE confirmation_slip_students 
            SET learner_name=?, grade_level=?, lrn=?, confirmation_status=? 
            WHERE slip_id=?
        ");

        if ($stmt) {
            $stmt->bind_param("ssssi", $learner_name, $grade_level, $lrn, $confirmation_status, $id);
            if ($stmt->execute()) {
                // Redirect based on source
                if ($from === 'superAdminPanel') {
                    header("Location: ../SuperAdmin/superAdmin.php?show=students&updated=1");
                } else {
                    header("Location: ../Admin/adminDash.php?show=students&updated=1");
                }
                exit();
            } else {
                echo "<script>
                    alert('Error updating record: " . htmlspecialchars($stmt->error, ENT_QUOTES) . "');
                    window.history.back();
                </script>";
            }
            $stmt->close();
        } else {
            echo "<script>
                alert('Database prepare failed: " . htmlspecialchars($conn->error, ENT_QUOTES) . "');
                window.history.back();
            </script>";
        }

    } else {
        $stmt = $conn->prepare("
            INSERT INTO confirmation_slip_students (learner_name, lrn, grade_level, confirmation_status, created_by)
            VALUES (?, ?, ?, ?, ?)
        ");

        if ($stmt) {
            $stmt->bind_param("ssssi", $learner_name, $lrn, $grade_level, $confirmation_status, $admin_id);
            if ($stmt->execute()) {
                // Redirect based on source
                if ($from === 'superAdminPanel') {
                    header("Location: ../SuperAdmin/superAdmin.php?show=students&success=1");
                } else {
                    header("Location: ../Admin/adminDash.php?show=students&success=1");
                }
                exit();
            } else {
                echo "<script>
                    alert('Error saving confirmation: " . htmlspecialchars($stmt->error, ENT_QUOTES) . "');
                    window.history.back();
                </script>";
            }
            $stmt->close();
        } else {
            echo "<script>
                alert('Database prepare failed: " . htmlspecialchars($conn->error, ENT_QUOTES) . "');
                window.history.back();
            </script>";
        }
    }

    $conn->close();
}
?>
