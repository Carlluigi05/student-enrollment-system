<?php 
session_start();
include("../Login/connection.php");

if (!isset($_SESSION['superAdmin_id'])) {
    header("Location: ../Login/superAdminLogin.php");
    exit;
}

$showSuccessModal = $_SESSION['showSuccessModal'] ?? false;
$showAccounts = $_SESSION['show_accounts'] ?? false;
unset($_SESSION['showSuccessModal']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['email'], $_POST['password'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); 

    $stmt = $conn->prepare("INSERT INTO admin_login (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();
    $stmt->close();

    $_SESSION['showSuccessModal'] = true;
    $_SESSION['show_accounts'] = true; 
    header("Location: superAdmin.php");
    exit();
}

$adminsResult = $conn->query("SELECT * FROM admin_login ORDER BY id ASC");
$admins = [];
if ($adminsResult) {
    while ($row = $adminsResult->fetch_assoc()) {
        $admins[] = $row;
    }
}

// Super Admin info
$superAdminId = $_SESSION['superAdmin_id'];
$superAdmin = $conn->query("SELECT * FROM superadmin_login WHERE id='$superAdminId'")->fetch_assoc();

// Update online status
$conn->query("UPDATE admin_login SET is_online = 0 WHERE last_activity < NOW() - INTERVAL 1 MINUTE");

// Dashboard stats
$totalStudents = $conn->query("SELECT COUNT(*) AS total FROM basic_enrollment_students")->fetch_assoc()['total'];
$totalAdmins = $conn->query("SELECT COUNT(*) AS total FROM admin_login")->fetch_assoc()['total'];
$activeAdminsNow = $conn->query("SELECT COUNT(*) AS total FROM admin_login WHERE is_online = 1")->fetch_assoc()['total'];
$studentsToday = $conn->query("SELECT COUNT(*) AS total FROM basic_enrollment_students WHERE DATE(date_submitted) = CURDATE()")->fetch_assoc()['total'];


// Enrollment Summary (Grade Level)
$summaryQuery = $conn->query("
    SELECT grade_level,
           COUNT(*) AS total,
           SUM(sex='Male') AS male,
           SUM(sex='Female') AS female
    FROM basic_enrollment_students
    GROUP BY grade_level
    ORDER BY grade_level ASC
");

// Summary Per Admin (simplified)
$adminSummaryQuery = $conn->query("
    SELECT 
        a.id AS admin_id,
        a.username,
        COUNT(b.student_id) AS total_students,
        SUM(b.sex='Male') AS male,
        SUM(b.sex='Female') AS female
    FROM admin_login a
    LEFT JOIN basic_enrollment_students b
        ON a.id = b.created_by
    GROUP BY a.id
    ORDER BY total_students DESC
");

// Last 30 days submission chart
$dateLabels = [];
$dateCounts = [];
$result = $conn->query("
    SELECT DATE(date_submitted) AS submission_date, COUNT(*) AS total
    FROM basic_enrollment_students
    WHERE date_submitted >= DATE_SUB(CURDATE(), INTERVAL 80 DAY)
    GROUP BY DATE(date_submitted)
    ORDER BY submission_date ASC
");
while ($row = $result->fetch_assoc()) {
    $dateLabels[] = $row['submission_date'];
    $dateCounts[] = (int)$row['total'];
}

// Gender distribution
$maleCount = (int)$conn->query("SELECT COUNT(*) AS total FROM basic_enrollment_students WHERE sex='Male'")->fetch_assoc()['total'];
$femaleCount = (int)$conn->query("SELECT COUNT(*) AS total FROM basic_enrollment_students WHERE sex='Female'")->fetch_assoc()['total'];

// Fetch all students submitted today
$todayStudentsList = $conn->query("
    SELECT * FROM basic_enrollment_students 
    WHERE DATE(date_submitted) = CURDATE()
    ORDER BY date_submitted DESC
");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="superAdmin.css">
    <link rel="icon" href="../Pics/logoOrg.png" type="image/png">
    <title>Super Admin Panel</title>
</head>
<style>
.stat-card {
    border-radius: 1rem;
    padding: 0.75rem 0;
    transition: all 0.3s ease;
    animation: fadeUp 0.8s forwards;
    opacity: 0;
    transform: translateY(20px);
  }
  .stat-card:nth-child(1) { animation-delay: 0.1s; }
  .stat-card:nth-child(2) { animation-delay: 0.3s; }
  .stat-card:nth-child(3) { animation-delay: 0.5s; }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
  }

  .card-body i { opacity: 0.9; }
  .card-body h3 { font-size: 1.6rem; }

  .chart-card {
    border-radius: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .chart-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.1);
  }
  .chart-container {
    position: relative;
  }
  canvas {
    animation: popIn 1s ease forwards;
    opacity: 0;
    transform: scale(0.95);
  }

  @keyframes popIn {
    0% { opacity: 0; transform: scale(0.95); }
    100% { opacity: 1; transform: scale(1); }
  }
  .dashboard-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer; 
}

  .dashboard-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 20px rgba(0, 0, 0, 0.25);
}
  
</style>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#"><img src="../Pics/logoOrg.png" alt="Logo" width="50" height="50" class="d-inline-block align-text-bot me-1"></i><span class="h2 align-middle ">MANGALDAN CENTRAL SCHOOL</span></a>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            
            <nav class="col-md-3 col-lg-2 d-md-block  p-3" id="sidebar">  
                <h6 class="text-light mt-2">
                <button class="btn btn-success me-2" id="toggleBtn">☰</button><span class="text-warning" id="spanMenu">MASTER CONSOLE</span>
                </h6>

                <ul class="nav flex-column">    
                    <li class="nav-item">
                        <a class="nav-link text-light" href="#" id="mainDashboard" data-bs-toggle="tooltip" data-bs-placement="right" title="DASHBOARD">
                            <i class="bi bi-box-seam-fill me-2"><span></i><span>DASHBOARD</span>
                         </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link text-light" href="#" id="studentsDashboard" data-bs-toggle="tooltip" data-bs-placement="right" title="STUDENTS">
                            <i class="bi bi-people-fill me-2"></i><span>STUDENTS</span>
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link  text-light" href="#" id="documentsDashboard" data-bs-toggle="tooltip" data-bs-placement="right" title="ACCOUNTS">
                            <i class="bi bi bi-person-fill-gear me-2"></i><span>ACCOUNTS</span>
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link  text-light" href="#" id="reportDashboard" data-bs-toggle="tooltip" data-bs-placement="right" title="SETTINGS">
                            <i class="bi bi-file-earmark-text-fill me-2"></i><span>REPORT</span>
                        </a>
                    </li>
                    
                    <button class="btn btn-success text-center" id="btnLogout" data-bs-toggle="tooltip" data-bs-placement="top" title="LOGOUT" onclick="showLogoutModal()">
                        <i class="bi bi-box-arrow-left me-2"></i><span>LOGOUT</span>
                    </button>

                </ul>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" id="mainContent">
                    <section id="dashboardSection" class="content-section mt-4 d-none">
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h3 class="mb-0 mt-0 fw-bold text-success">DASHBOARD</h3>
                            <?php include 'superProfileHeader.php'; ?>
                        </div>
                        <div class="mt-4 border-bottom border-3 border-success"></div>

                        <div class="row m-3 ">
                             <div class="col-md-3">
                                <div class="card shadow-sm border-0 text-white bg-primary rounded-4 dashboard-card" style="cursor:pointer"onclick="showStudentsModal()">
                                    <div class="card-body text-center">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="bi bi-person-lines-fill"></i> Total Students</h5>
                                            <h2><?= $totalStudents ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Admins -->
                            <div class="col-md-3">
                                <div class="card shadow-sm border-0 text-white bg-success rounded-4 dashboard-card" style="cursor:pointer"onclick="showAdminListModal()">
                                    <div class="card-body text-center">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="bi bi-people-fill"></i> Total Admins</h5>
                                            <h2><?= $totalAdmins ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Active Admins -->
                            <div class="col-md-3">
                                <div class="card shadow-sm border-0 text-white bg-warning rounded-4 dashboard-card" style="cursor:pointer"onclick="showActiveAdminsModal()">
                                    <div class="card-body text-center">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="bi bi-person-check-fill"></i> Active Admins</h5>
                                            <h2 id="activeAdminsCount"><?= $activeAdminsNow ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card shadow-sm border-0 text-white bg-info rounded-4 dashboard-card" style="cursor:pointer" onclick="showTodayStudentsModal()">
                                    <div class="card-body text-center">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="bi bi-calendar-check-fill"></i> New Students (Today)</h5>
                                            <h2><?= $studentsToday ?></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="container mt-4">
                                <div class="row g-4">
                                    <div class="col-lg-8 col-md-7">
                                        <div class="card chart-card shadow-sm p-4 h-100">
                                            <h5 class="text-success fw-bold mb-3">
                                                <i class="bi bi-graph-up-arrow me-2"></i> Students Enrolled by Date
                                            </h5>
                                            <div class="chart-container" style="height: 280px; width: 100%;">
                                                <canvas id="gradeChart"></canvas>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-5">
                                        <div class="card chart-card shadow-sm p-4 h-100">
                                            <h5 class="text-success fw-bold mb-3">
                                                <i class="bi bi-pie-chart-fill me-2"></i> Gender Distribution
                                            </h5>
                                            <div class="chart-container d-flex justify-content-center align-items-center" style="height: 280px; width: 100%;">
                                                <canvas id="genderChart" style="max-width: 200px; max-height: 280px;"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="studentsSection" class="content-section mt-4 d-none">
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h3 class="mb-0 mt-0 fw-bold text-success">STUDENTS</h3>
                            <?php include 'superProfileHeader.php'; ?>
                        </div>
                        <div class="mt-4 border-bottom border-3 border-success"></div>

                        <div class="d-flex student-header align-items-center gap-2 flex-wrap mt-4">
                            <!-- Search Form -->
                            <form action="" method="GET" class="search-form d-flex me-2">
                                <input type="text" id="studentSearch" name="search" placeholder="Search student..." class="search-input">
                            </form>

                            <!-- Admin Filter Dropdown -->
                            <div class="filter-form">
                                <select id="adminFilter" class="filter-select w-auto">
                                    <option value="">All Admins</option>
                                    <?php foreach ($admins as $adminOption): ?>
                                        <option value="<?= $adminOption['id'] ?>"><?= htmlspecialchars($adminOption['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-form bg-warning">
                                <select id="genderFilter" name="gender" class="filter-select w-auto">
                                    <option value="">Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        
                        <div class="container-fluid mt-4 p-4 shadow-lg rounded-4 overflow-auto" style="background: rgba(255, 255, 255, 0.25);backdrop-filter: blur(10px);-webkit-backdrop-filter: blur(10px);border: 1px solid rgba(255, 255, 255, 0.3);">
                            <h4 class="fw-bold text-success mb-3"><i class="bi bi-person-plus-fill me-2"></i> All New Students</h4>

                            <div class="card shadow-sm mb-4">
                                <div class="card-body p-3">
                                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto; overflow-x: auto;">
                                    <table class="table table-bordered table-striped table-hover align-middle table-sm">
                                        <thead class="table-success text-center">
                                            <tr>
                                                <th style="min-width: 120px; max-width: 100px;"></th>
                                                <th style="min-width: 60px; max-width: 100px;">ID</th>
                                                <th style="min-width: 150px; max-width: 200px;">School Year</th>
                                                <th style="min-width: 150px; max-width: 200px;">LRN</th>
                                                <th style="min-width: 150px; max-width: 200px;">Grade Level</th>
                                                <th style="min-width: 150px; max-width: 200px;">PSA No.</th>
                                                <th style="min-width: 150px; max-width: 200px;">Last Name</th>
                                                <th style="min-width: 150px; max-width: 200px;">First Name</th>
                                                <th style="min-width: 130px; max-width: 200px;">Middle Name</th>
                                                <th style="min-width: 150px; max-width: 200px;">Birthdate</th>
                                                <th style="min-width: 70px; max-width: 100px;">Age</th>
                                                <th style="min-width: 80px; max-width: 100px;">Sex</th>
                                                <th style="min-width: 180px; max-width: 200px;">Birthplace</th>
                                                <th style="min-width: 150px; max-width: 200px;">Religion</th>
                                                <th style="min-width: 220px; max-width: 250px;">Mother Tongue</th>
                                                <th style="min-width: 480px; max-width: 500px;">Current Address</th>
                                                <th style="min-width: 480px; max-width: 500px;">Permanent Address</th>
                                                <th style="min-width: 200px; max-width: 250px;">Father</th>
                                                <th style="min-width: 200px; max-width: 250px;">Mother’s Maiden</th>
                                                <th style="min-width: 200px; max-width: 250px;">Guardian</th>
                                                <th style="min-width: 150px; max-width: 200px;">Last Grade</th>
                                                <th style="min-width: 170px; max-width: 200px;">Last School Attended</th>
                                                <th style="min-width: 150px; max-width: 200px;">School ID</th>
                                                <th style="min-width: 150px; max-width: 200px;">Blended</th>
                                                <th style="min-width: 150px; max-width: 200px;">Homeschool</th>
                                                <th style="min-width: 150px; max-width: 200px;">Modular Print</th>
                                                <th style="min-width: 155px; max-width: 200px;">Modular Digital</th>
                                                <th style="min-width: 130px; max-width: 180px;">Online</th>
                                                <th style="min-width: 220px; max-width: 250px;">Date Submitted</th>
                                            </tr>
                                        </thead>

                                        <tbody id="newStudentsBody">
                                            <?php
                                            include("../Login/connection.php");
                                            $query = "SELECT * FROM basic_enrollment_students ORDER BY date_submitted DESC";
                                            $result = $conn->query($query);

                                            if ($result && $result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    $student_id = $row['student_id'];

                                                    $noteCheck = $conn->query("SELECT id FROM notes WHERE student_id='$student_id' LIMIT 1");
                                                    $hasNote = $noteCheck->num_rows > 0;
                                                    $btnClass = $hasNote ? "btn-success" : "btn-info"; 

                                                    echo "<tr>
                                                        <td class='text-center'>
                                                        <!-- Edit button -->
                                                        <a href='../admin/editStudent.php?type=new&id=" . urlencode($student_id) . "&from=superAdminPanel'
                                                            class='btn btn-warning btn-sm me-0' title='Edit Student'>
                                                            <i class='bi bi-pencil-square'></i>
                                                        </a>

                                                        <!-- View Notes button -->
                                                        <button class='btn $btnClass btn-sm text-white viewNotesBtn' data-id='$student_id' title='View Note'>
                                                            <i class='bi bi-journal-text'></i>
                                                        </button>

                                                        <!-- Delete button -->
                                                        <a href='#' class='btn btn-danger btn-sm ms-1 deleteBtn' data-id='" . htmlspecialchars($student_id) . "' title='Delete Student'>
                                                            <i class='bi bi-trash-fill'></i>
                                                        </a>
                                                    </td>
                                                        <td>" . htmlspecialchars($student_id) . "</td>
                                                        <td>" . htmlspecialchars($row['school_year']) . "</td>
                                                        <td>" . htmlspecialchars($row['lrn']) . "</td>
                                                        <td>" . htmlspecialchars($row['grade_level']) . "</td>
                                                        <td>" . htmlspecialchars($row['psa_birth_cert_no']) . "</td>
                                                        <td>" . htmlspecialchars($row['last_name']) . "</td>
                                                        <td>" . htmlspecialchars($row['first_name']) . "</td>
                                                        <td>" . htmlspecialchars($row['middle_name']) . "</td>
                                                        <td>" . htmlspecialchars($row['birthdate']) . "</td>
                                                        <td>" . htmlspecialchars($row['age']) . "</td>
                                                        <td>" . htmlspecialchars($row['sex']) . "</td>
                                                        <td>" . htmlspecialchars($row['place_of_birth']) . "</td>
                                                        <td>" . htmlspecialchars($row['religion']) . "</td>
                                                        <td>" . htmlspecialchars($row['mother_tongue']) . "</td>
                                                        <td>" . htmlspecialchars($row['current_address']) . "</td>
                                                        <td>" . htmlspecialchars($row['permanent_address']) . "</td>
                                                        <td>" . htmlspecialchars($row['father_name']) . "</td>
                                                        <td>" . htmlspecialchars($row['mother_maiden_name']) . "</td>
                                                        <td>" . htmlspecialchars($row['guardian_name']) . "</td>
                                                        <td>" . htmlspecialchars($row['last_grade_completed']) . "</td>
                                                        <td>" . htmlspecialchars($row['last_school_attended']) . "</td>
                                                        <td>" . htmlspecialchars($row['last_school_id']) . "</td>
                                                        <td>" . ($row['blended'] ? '✔️' : '') . "</td>
                                                        <td>" . ($row['homeschooling'] ? '✔️' : '') . "</td>
                                                        <td>" . ($row['modular_print'] ? '✔️' : '') . "</td>
                                                        <td>" . ($row['modular_digital'] ? '✔️' : '') . "</td>
                                                        <td>" . ($row['online'] ? '✔️' : '') . "</td>
                                                        <td>" . htmlspecialchars($row['date_submitted']) . "</td>
                                                    </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='28' class='text-center'>No Students Found</td></tr>";
                                            }
                                            ?>
                                            </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="accountsSection" class="content-section mt-4">
                        <div class="section-scroll ">
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <h3 class="mb-0 mt-0 fw-bold text-success">ACCOUNTS</h3>
                                <?php include 'superProfileHeader.php'; ?>
                            </div>
                            <div class="mt-4 border-bottom border-3 border-success"></div>

                            <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                                    <?php echo $_SESSION['message']; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php unset($_SESSION['message']); ?>
                            <?php endif; ?>

                            <div class="card shadow p-4 mt-4">
                                <h3 class="mb-3">Admin Account List</h3>
                                <div class="table-responsive" style="max-height: 250px; overflow-y: auto; overflow-x: auto;">
                                    <table class="table table-striped table-bordered table-hover table-sm text-center align-middle">
                                        <thead class="table-success text-center"
                                            <tr>
                                                <th>ID</th>
                                                <th style="min-width: 150px;">Username</th>
                                                <th style="min-width: 150px;">Email</th>
                                                <th style="min-width: 150px;">Password</th>
                                                <th style="min-width: 100px;">Photo</th>
                                                <th style="min-width: 150px;">Date Created</th>
                                                <th style="min-width: 150px;">Last Login</th>
                                                <th style="min-width: 120px;">Status</th>
                                                <th style="min-width: 150px;">Last Activity</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach ($admins as $row): ?>
                                            <tr>
                                            <td><?= htmlspecialchars($row['id']) ?></td>
                                            <td><?= htmlspecialchars($row['username']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <input type="password" class="form-control form-control-md text-center me-1" 
                                                        value="<?= htmlspecialchars($row['password']) ?>" 
                                                        id="password-<?= $row['id'] ?>" readonly 
                                                        style="max-width: 120px;">
                                                    <i class="bi bi-eye" 
                                                    id="toggle-icon-<?= $row['id'] ?>" 
                                                    style="cursor: pointer;" 
                                                    onclick="togglePassword(<?= $row['id'] ?>)"></i>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['photo'])): ?>
                                                <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" width="40" class="rounded-circle">
                                                <?php else: ?>
                                                <span>No Photo</span>
                                                <?php endif ?>
                                            </td>
                                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                                            <td><?= htmlspecialchars($row['last_login']) ?></td>
                                            <td>
                                                <?php if ($row['is_online'] == 1): ?>
                                                <span class="badge bg-success">Online</span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary">Offline</span>
                                                <?php endif ?>
                                            </td>
                                            <td><?= htmlspecialchars($row['last_activity']) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        
                                    </table>
                                </div>
                            </div>

                            <script>
                                function togglePassword(id) {
                                const passwordField = document.getElementById(`password-${id}`);
                                const icon = document.getElementById(`toggle-icon-${id}`);

                                if (passwordField.type === "password") {
                                    passwordField.type = "text";
                                    icon.classList.remove("bi-eye");
                                    icon.classList.add("bi-eye-slash");
                                } else {
                                    passwordField.type = "password";
                                    icon.classList.remove("bi-eye-slash");
                                    icon.classList.add("bi-eye");
                                }
                            }
                                function toggleCreatePassword() {
                                    const input = document.getElementById("password");
                                    const icon = document.getElementById("createPassIcon");

                                    if (input.type === "password") {
                                        input.type = "text";
                                        icon.classList.remove("bi-eye");
                                        icon.classList.add("bi-eye-slash");
                                    } else {
                                        input.type = "password";
                                        icon.classList.remove("bi-eye-slash");
                                        icon.classList.add("bi-eye");
                                    }
                            }
                            </script>

                            <div class="card shadow p-4 mt-4">
                                <h3 class="mb-3">Create Account</h3>
                                <form action="superAdmin.php" method="POST">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" required>

                                            <span class="input-group-text" style="cursor:pointer;" onclick="toggleCreatePassword()">
                                                <i class="bi bi-eye" id="createPassIcon"></i>
                                            </span>
                                        </div>
                                    </div>


                                    <button type="submit" class="btn btn-success w-100">Create Admin</button>
                                </form>
                            </div>
                        </div>
              
                </section>
                    <section id="reportSection" class="content-section mt-4 d-none">
                        <div class="section-scroll">
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <h3 class="mb-0 fw-bold text-success">REPORTS</h3>
                                <?php include 'superProfileHeader.php'; ?>
                            </div>

                            <div class="mt-4 border-bottom border-3 border-success"></div>

                    
                            <div class="card mt-4 shadow-sm p-3">
                                <h5 class="fw-bold text-success"><i class="bi bi-table me-2"></i>Enrollment Summary (All Students)</h5>
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto; overflow-x: auto;">
                                    <table class="table table-bordered table-sm table-striped text-center align-middle mb-0">
                                        <thead class="table-success">
                                            <tr>
                                                <th>Grade</th>
                                                <th>Total Students</th>
                                                <th>Male</th>
                                                <th>Female</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $summaryQuery->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['grade_level']) ?></td>
                                                    <td><?= $row['total'] ?></td>
                                                    <td><?= $row['male'] ?></td>
                                                    <td><?= $row['female'] ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Summary Per Admin -->
                            <div class="card mt-4 shadow-sm p-3">
                                <h5 class="fw-bold text-success"><i class="bi bi-people-fill me-2"></i>Summary Report Per Admin</h5>
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto; overflow-x: auto;">
                                    <table class="table table-bordered table-sm table-striped text-center align-middle mb-0">
                                        <thead class="table-success">
                                            <tr>
                                                <th>Admin</th>
                                                <th>Total Students</th>
                                                <th>Male</th>
                                                <th>Female</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $adminSummaryQuery = $conn->query("
                                                SELECT 
                                                    a.id AS admin_id,
                                                    a.username,
                                                    COUNT(b.student_id) AS total_students,
                                                    SUM(b.sex='Male') AS male,
                                                    SUM(b.sex='Female') AS female
                                                FROM admin_login a
                                                LEFT JOIN basic_enrollment_students b
                                                    ON a.id = b.created_by
                                                GROUP BY a.id
                                                ORDER BY total_students DESC
                                            ");
                                            while ($row = $adminSummaryQuery->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                                    <td><?= $row['total_students'] ?></td>
                                                    <td><?= $row['male'] ?></td>
                                                    <td><?= $row['female'] ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-4 text-end">

                                <div class="btn-group">
                                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-file-earmark-excel-fill me-2"></i>Export Excel
                                    </button>
                                    <ul class="dropdown-menu" style="border: 2px solid #198754;">
                                        <li><a class="dropdown-item" href="export_excel.php?report=enrollment" target="_blank">Enrollment Summary</a></li>
                                        <li><a class="dropdown-item" href="export_excel.php?report=admin" target="_blank">Summary Report Per Admin</a></li>
                                    </ul>
                                </div>


                                <div class="btn-group ms-2">
                                    <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-file-earmark-pdf-fill me-2"></i>Export PDF
                                    </button>
                                    <ul class="dropdown-menu" style="border: 2px solid #dc3545;">
                                        <li><a class="dropdown-item" href="export_pdf.php?report=enrollment" target="_blank">Enrollment Summary</a></li>
                                        <li><a class="dropdown-item" href="export_pdf.php?report=admin" target="_blank">Summary Report Per Admin</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                </section>
            </main>
        </div>
    </div>

    
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-success text-white rounded-top-4">
        <h5 class="modal-title">Confirm Logout</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-4">
        <p class="fs-5">Are you sure you want to logout?</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">No</button>
        <button type="button" class="btn btn-success px-4" onclick="logout()">Yes</button>
      </div>
    </div>
  </div>
</div>

<!-- View Note Modal -->
<div class="modal fade" id="viewNotesModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div id="viewNotesHeader" class="modal-header bg-primary text-white">
        <h5 class="modal-title">Student Note</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div id="view_note_text" class="border rounded p-2" style="min-height:120px;"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const studentSearch = document.getElementById("studentSearch");
    const adminFilter = document.getElementById("adminFilter");
    const genderFilter = document.getElementById("genderFilter");
    const newBody = document.getElementById("newStudentsBody");

    const viewModalEl = document.getElementById("viewNotesModal");
    const viewModal = new bootstrap.Modal(viewModalEl);
    const noteTextarea = document.getElementById("view_note_text");

    // Fetch students with filters
    function fetchStudents() {
        const search = studentSearch.value.trim();
        const adminId = adminFilter.value;
        const gender = genderFilter.value;

        const params = new URLSearchParams();
        if (search !== '') params.append('q', search);
        if (adminId !== '') params.append('admin_id', adminId);
        if (gender !== '') params.append('gender', gender);

        fetch("superAdminSearch.php?" + params.toString())
            .then(res => res.json())
            .then(data => {
                newBody.innerHTML = data.new || "<tr><td colspan='28' class='text-center'>No Students Found</td></tr>";
            })
            .catch(err => console.error('Fetch error:', err));
    }

    // Event delegation for notes modal buttons
    newBody.addEventListener("click", (e) => {
    const btn = e.target.closest(".viewNotesBtn");
    if (!btn) return;

    const studentId = btn.dataset.id;
    const header = document.getElementById("viewNotesHeader");
    const noteDiv = document.getElementById("view_note_text");

    fetch("../SuperAdmin/fetch_note.php", {
        method: "POST",
        body: new URLSearchParams({ student_id: studentId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.note && data.note.trim() !== "") {
            // Note exists → keep primary color
            header.classList.remove("bg-danger");
            header.classList.add("bg-primary");

            noteDiv.innerHTML = data.note;
        } else {
            // No note → turn header red
            header.classList.remove("bg-primary");
            header.classList.add("bg-danger");

            noteDiv.innerHTML = "<span class='text-danger fw-bold'>No notes available for this student.</span>";
        }

        viewModal.show();
    })
    .catch(err => {
        console.error(err);

        header.classList.remove("bg-primary");
        header.classList.add("bg-danger");

        noteDiv.innerHTML = "<span class='text-danger fw-bold'>Failed to fetch note.</span>";

        viewModal.show();
    });
});


    // Trigger fetch on input or filter change
    studentSearch.addEventListener("input", fetchStudents);
    adminFilter.addEventListener("change", fetchStudents);
    genderFilter.addEventListener("change", fetchStudents);

    // Initial load
    fetchStudents();
});
</script>

<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-primary shadow-lg rounded-4" id="statusModalContent">
      <div class="modal-header" id="statusModalHeader">
        <h5 class="modal-title fw-bold" id="statusModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-4" id="statusModalBody"></div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn" id="statusModalBtn" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="studentsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title"><i class="bi bi-people-fill me-2"></i> All Students</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php include 'studentsTable.php'; ?>
      </div>
    </div>
  </div>
</div>

<!-- Admin List Modal -->
<div class="modal fade" id="adminListModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-success text-white rounded-top-4">
        <h5 class="modal-title"><i class="bi bi-people-fill me-2"></i> Admin Accounts</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <table class="table table-striped table-bordered table-hover table-sm text-center align-middle">
          <thead class="table-success">
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Password</th>
              <th>Photo</th>
              <th>Date Created</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($admins as $row): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= $row['username'] ?></td>
              <td><?= $row['email'] ?></td>
              <td>•••••••••</td>
              <td>
                <?php if ($row['photo']): ?>
                  <img src="../uploads/<?= $row['photo'] ?>" width="40" class="rounded-circle">
                <?php else: ?>
                  <span>No Photo</span>
                <?php endif ?>
              </td>
              <td><?= $row['created_at'] ?></td>
              <td>
                <?php if ($row['is_online']): ?>
                  <span class="badge bg-success">Online</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Offline</span>
                <?php endif ?>
              </td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>


<!-- Active Admins Modal -->
<div class="modal fade" id="activeAdminsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-warning text-dark rounded-top-4">
        <h5 class="modal-title"><i class="bi bi-person-check-fill me-2"></i> Active Admins</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <table class="table table-striped table-bordered table-hover table-sm text-center align-middle">
          <thead class="table-warning">
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Last Activity</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach ($admins as $admin):
                if ($admin['is_online'] == 1):
            ?>
            <tr>
              <td><?= $admin['id'] ?></td>
              <td><?= $admin['username'] ?></td>
              <td><?= $admin['email'] ?></td>
              <td><?= $admin['last_activity'] ?></td>
            </tr>
            <?php endif; endforeach; ?>
          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>

<!-- Modal: Students Enrolled Today -->
<div class="modal fade" id="todayStudentsModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Students Enrolled Today</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm">
                        <thead class="table-success">
                            <tr>
                                <th style="min-width: 60px;">ID</th>
                                <th style="min-width: 150px;">School Year</th>
                                <th style="min-width: 150px;">LRN</th>
                                <th style="min-width: 150px;">Grade Level</th>
                                <th style="min-width: 150px;">PSA No.</th>
                                <th style="min-width: 150px;">Last Name</th>
                                <th style="min-width: 150px;">First Name</th>
                                <th style="min-width: 130px;">Middle Name</th>
                                <th style="min-width: 150px;">Birthdate</th>
                                <th style="min-width: 70px;">Age</th>
                                <th style="min-width: 80px;">Sex</th>
                                <th style="min-width: 180px;">Birthplace</th>
                                <th style="min-width: 150px;">Religion</th>
                                <th style="min-width: 220px;">Mother Tongue</th>
                                <th style="min-width: 450px;">Current Address</th>
                                <th style="min-width: 450px;">Permanent Address</th>
                                <th style="min-width: 200px;">Father</th>
                                <th style="min-width: 200px;">Mother’s Maiden</th>
                                <th style="min-width: 200px;">Guardian</th>
                                <th style="min-width: 150px;">Last Grade</th>
                                <th style="min-width: 170px;">Last School Attended</th>
                                <th style="min-width: 150px;">School ID</th>
                                <th style="min-width: 150px;">Blended</th>
                                <th style="min-width: 150px;">Homeschool</th>
                                <th style="min-width: 150px;">Modular Print</th>
                                <th style="min-width: 155px;">Modular Digital</th>
                                <th style="min-width: 130px;">Online</th>
                                <th style="min-width: 220px;">Date Submitted</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = $todayStudentsList->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['student_id'] ?></td>
                                    <td><?= $row['school_year'] ?></td>
                                    <td><?= $row['lrn'] ?></td>
                                    <td><?= $row['grade_level'] ?></td>
                                    <td><?= $row['psa_birth_cert_no'] ?></td>
                                    <td><?= $row['last_name'] ?></td>
                                    <td><?= $row['first_name'] ?></td>
                                    <td><?= $row['middle_name'] ?></td>
                                    <td><?= $row['birthdate'] ?></td>
                                    <td><?= $row['age'] ?></td>
                                    <td><?= $row['sex'] ?></td>
                                    <td><?= $row['place_of_birth'] ?></td>
                                    <td><?= $row['religion'] ?></td>
                                    <td><?= $row['mother_tongue'] ?></td>
                                    <td><?= $row['current_address'] ?></td>
                                    <td><?= $row['permanent_address'] ?></td>
                                    <td><?= $row['father_name'] ?></td>
                                    <td><?= $row['mother_maiden_name'] ?></td>
                                    <td><?= $row['guardian_name'] ?></td>
                                    <td><?= $row['last_grade_completed'] ?></td>
                                    <td><?= $row['last_school_attended'] ?></td>
                                    <td><?= $row['last_school_id'] ?></td>
                                    <td class="text-center"><?= $row['blended'] ? '✔️' : '' ?></td>
                                    <td class="text-center"><?= $row['homeschooling'] ? '✔️' : '' ?></td>
                                    <td class="text-center"><?= $row['modular_print'] ? '✔️' : '' ?></td>
                                    <td class="text-center"><?= $row['modular_digital'] ? '✔️' : '' ?></td>
                                    <td class="text-center"><?= $row['online'] ? '✔️' : '' ?></td>
                                    <td><?= $row['date_submitted'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body fs-5">
        Are you sure you want to delete this record?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
      </div>
    </div>
  </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function () {
    /* -------------------------
       SIDEBAR TOGGLE + TOOLTIP
    ------------------------- */
    const toggleBtn = document.getElementById('toggleBtn');
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('mainContent');
    let tooltipInstances = [];

    function enableTooltips() {
        disableTooltips();
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            tooltipInstances.push(new bootstrap.Tooltip(el));
        });
    }

    function disableTooltips() {
        tooltipInstances.forEach(instance => instance.dispose());
        tooltipInstances = [];
    }

    if (toggleBtn) {
        toggleBtn.addEventListener("click", () => {
            const collapsed = sidebar.classList.toggle("collapsed");
            main.classList.toggle("expanded", collapsed);
            if (collapsed) enableTooltips();
            else disableTooltips();
            localStorage.setItem("sidebarCollapsed", collapsed ? "true" : "false");
        });
    }

    if (localStorage.getItem("sidebarCollapsed") === "true") {
        sidebar.classList.add("collapsed");
        main.classList.add("expanded");
        enableTooltips();
    }

    /* -------------------------
       SECTION SWITCHING
    ------------------------- */
    const sections = {
        main: document.getElementById("dashboardSection"),
        students: document.getElementById("studentsSection"),
        accounts: document.getElementById("accountsSection"),
        settings: document.getElementById("reportSection")
    };

    const buttons = {
        main: document.getElementById("mainDashboard"),
        students: document.getElementById("studentsDashboard"),
        accounts: document.getElementById("documentsDashboard"),
        settings: document.getElementById("reportDashboard")
    };

    window.showSection = function(key) {
        Object.values(sections).forEach(sec => sec?.classList.add("d-none"));
        if (sections[key]) sections[key].classList.remove("d-none");
        localStorage.setItem("lastSection", key);
    };

    Object.keys(buttons).forEach(key => {
        if (buttons[key]) {
            buttons[key].addEventListener("click", e => {
                e.preventDefault();
                showSection(key);
            });
        }
    });

    /* -------------------------
       INITIAL SECTION LOAD
    ------------------------- */
    const showAccounts = <?= $showAccounts ? 'true' : 'false' ?>;
    if (showAccounts) showSection("accounts");
    else showSection("main"); // default dashboard

    // Clear stored section so reload always goes to dashboard
    localStorage.removeItem("lastSection");

    /* -------------------------
       SUCCESS MODAL
    ------------------------- */
    const showSuccess = <?= $showSuccessModal ? 'true' : 'false' ?>;
    if (showSuccess) {
        const modalEl = document.getElementById("statusModal");
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            document.getElementById("statusModalHeader").className = "modal-header bg-success text-white rounded-top-4";
            document.getElementById("statusModalLabel").innerHTML = "<i class='bi bi-person-plus-fill me-2'></i> Admin Added";
            document.getElementById("statusModalBody").innerHTML = "🎉 The new admin account has been successfully created!";
            document.getElementById("statusModalBtn").className = "btn btn-success px-4";
            modal.show();
        }
    }
});
</script>


<script>
let studentToDelete = null;

document.addEventListener('click', function(e) {
    if (e.target.closest('.deleteBtn')) {
        const btn = e.target.closest('.deleteBtn');
        studentToDelete = btn.getAttribute('data-id');

        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!studentToDelete) return;

    fetch('deleteStudent.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(studentToDelete) + '&type=new'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide modal
            const deleteModalEl = document.getElementById('deleteModal');
            const modalInstance = bootstrap.Modal.getInstance(deleteModalEl);
            modalInstance.hide();

            // Remove the row
            const row = document.querySelector(`.deleteBtn[data-id='${studentToDelete}']`).closest('tr');
            if (row) row.remove();

            // Optional: show status modal instead of alert
            const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
            document.getElementById('statusModalHeader').className = "modal-header bg-success text-white rounded-top-4";
            document.getElementById('statusModalLabel').innerHTML = "Record Deleted";
            document.getElementById('statusModalBody').innerHTML = "🎉 Student record has been deleted successfully!";
            document.getElementById('statusModalBtn').className = "btn btn-success px-4";
            statusModal.show();
        } else {
            alert('Failed to delete the record: ' + data.message);
        }
        studentToDelete = null;
    })
    .catch(err => {
        alert('Error occurred: ' + err);
        studentToDelete = null;
    });
});


</script>

<script>
function showStudentsModal() {
    const modal = new bootstrap.Modal(document.getElementById('studentsModal'));
    modal.show();
}

function showAdminListModal() {
    const modal = new bootstrap.Modal(document.getElementById('adminListModal'));
    modal.show();
}
function showActiveAdminsModal() {
    const modal = new bootstrap.Modal(document.getElementById('activeAdminsModal'));
    modal.show();
}
function showTodayStudentsModal() {
    var modal = new bootstrap.Modal(document.getElementById('todayStudentsModal'));
    modal.show();
}


document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('studentSearch');
    const newBody = document.querySelector("#newStudentsBody");
    const oldBody = document.querySelector("#oldStudentsBody");

    searchInput.addEventListener("input", function() {
        let query = searchInput.value.trim();

        // If search empty → show full list
        if (query === "") {
            loadFullList();
            return;
        }

        search(query);
    });

    // --- FUNCTIONS --- //

    function search(query) {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "superAdminSearch.php?q=" + encodeURIComponent(query), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                newBody.innerHTML = data.new;
                oldBody.innerHTML = data.old;
            }
        };
        xhr.send();
    }

    function loadFullList() {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "superAdminSearch.php", true); // no q parameter
        xhr.onload = function() {
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                newBody.innerHTML = data.new;
                oldBody.innerHTML = data.old;
            }
        };
        xhr.send();
    }
});
</script>

<!-- Student Update Success Modal -->
<div class="modal fade" id="studentUpdateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Success</h5>
      </div>
      <div class="modal-body">
        🎉 Student record has been successfully updated!
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('updated') === '1') {
        // Switch to students section first
        window.showSection("students");

        // Wait for DOM to update
        requestAnimationFrame(() => {
            const studentModal = new bootstrap.Modal(document.getElementById('studentUpdateModal'));
            studentModal.show();
        });

        // Remove URL param
        urlParams.delete('updated');
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.history.replaceState({}, document.title, newUrl);
    }
});


</script>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const dateLabels = <?= json_encode($dateLabels) ?>;
  const dateCounts = <?= json_encode($dateCounts) ?>;
  const maleCount = <?= json_encode($maleCount) ?>;
  const femaleCount = <?= json_encode($femaleCount) ?>;

  const ctxBar = document.getElementById("gradeChart").getContext("2d");

  new Chart(ctxBar, {
      type: "line",
      data: {
          labels: dateLabels,
          datasets: [
              {
                  label: "Students Enrolled",
                  data: dateCounts,
                  borderWidth: 3,
                  tension: 0.4,
                  backgroundColor: "rgba(236, 236, 236, 0.98)",
                  borderColor: "rgba(25, 135, 84, 1)",
                  fill: true,
              }
          ]
      },
      options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
              legend: { display: true },
              title: {
                  display: true,
                  text: "Students Enrolled by Date",
                  font: { size: 16, weight: "bold" },
                  color: "rgba(25, 135, 84, 1)",
              },
          },
          scales: {
              y: {
                  beginAtZero: true,
                  title: { display: true, text: "Number of Students" },
                  grid: { color: "rgba(0,0,0,0.05)" },
                  ticks: { stepSize: 1 }
              },
              x: {
                  title: { display: true, text: "Date" },
                  grid: { display: false },
                  ticks: { color: "#333", font: { size: 13 } }
              }
          }
      }
  });

  const ctxGender = document.getElementById("genderChart").getContext("2d");
  const genderChart = new Chart(ctxGender, {
    type: "doughnut",
    data: {
      labels: ["Male", "Female"],
      datasets: [
        {
          data: [maleCount, femaleCount],
          backgroundColor: [
            "rgba(25, 135, 84, 1)",
            "rgba(220, 53, 69, 0.85)", 
          ],
          borderColor: "#fff",
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: "70%",
      rotation: 0,
      animation: {
        animateRotate: true,
        duration: 2000,
        easing: "easeInOutCubic",
        onProgress: function (animation) {
          genderChart.options.rotation = (animation.currentStep / animation.numSteps) * 2 * Math.PI;
        },
      },
      plugins: {
        legend: {
          position: "bottom",
          labels: { font: { size: 14 }, boxWidth: 18 },
        },
        title: {
          display: false,
          text: "Gender Distribution",
          font: { size: 18, weight: "bold" },
          color: "rgba(25, 135, 84, 1)",
        },
      },
    },
  });
});
</script>

<script>
    var showAccounts = <?= $showAccounts ? 'true' : 'false'; ?>;
</script>

<script>
function showLogoutModal() {
  const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
  modal.show();
}

function logout() {
  window.location.href = "../Login/superAdminLogout.php";
}

function updateActiveAdmins() {
  fetch('get_active_admins.php')
    .then(response => response.json())
    .then(data => {
      const elem = document.getElementById("activeAdminsCount");
      if (elem) elem.textContent = data.active_admins;
    })
    .catch(err => console.error("Error fetching active admins:", err));
}

setInterval(updateActiveAdmins, 5000);
updateActiveAdmins();

</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const url = new URL(window.location.href);
    const show = url.searchParams.get("show");

    if (show === "students") {
        // Hide all sections
        document.querySelectorAll(".content-section").forEach(sec => {
            sec.classList.add("d-none");
        });

        // Show students section
        const studentsSection = document.getElementById("studentsSection");
        if (studentsSection) {
            studentsSection.classList.remove("d-none");
        }

        // Optional header update
        const header = document.getElementById("sectionTitle");
        if (header) {
            header.textContent = "STUDENTS";
        }

        // 🔑 REMOVE ?show=students AFTER it is used
        url.searchParams.delete("show");
        window.history.replaceState({}, document.title, url.pathname);
    }
});
</script>

<?php unset($_SESSION['show_accounts']); ?>
</html>