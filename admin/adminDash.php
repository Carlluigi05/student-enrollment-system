<?php
session_start();
include("../Login/connection.php");
include("../superAdmin/update_admin_status.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../Login/adminLogin.php");
    exit;
}

$adminId = $_SESSION['admin_id'];
$isSuperAdmin = ($adminId == 1);


// Determine modal display flags
$showSuccessModal = $_SESSION['showSuccessModal'] ?? false;
$showUpdatedModal = $_SESSION['showUpdatedModal'] ?? false;

// Clear the session flags immediately so refresh won't re-trigger the modal
unset($_SESSION['showSuccessModal'], $_SESSION['showUpdatedModal']);


if ($isSuperAdmin) {

    $newStudentsQuery = $conn->query("SELECT COUNT(*) AS total FROM basic_enrollment_students");
    $newCount = $newStudentsQuery->fetch_assoc()['total'] ?? 0;

    $maleQuery = $conn->query("SELECT COUNT(*) AS total FROM basic_enrollment_students WHERE sex='Male'");
    $maleCount = $maleQuery->fetch_assoc()['total'] ?? 0;

    $femaleQuery = $conn->query("SELECT COUNT(*) AS total FROM basic_enrollment_students WHERE sex='Female'");
    $femaleCount = $femaleQuery->fetch_assoc()['total'] ?? 0;

    $gradeQuery = $conn->query("SELECT grade_level, COUNT(*) AS total FROM basic_enrollment_students GROUP BY grade_level ORDER BY grade_level ASC");

    $gradeLevels = [];
    $gradeCounts = [];
    while ($row = $gradeQuery->fetch_assoc()) {
        $gradeLevels[] = $row['grade_level'];
        $gradeCounts[] = (int)$row['total'];
    }

} else {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM basic_enrollment_students WHERE created_by = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $newStudentsQuery = $stmt->get_result();
    $newCount = $newStudentsQuery->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM basic_enrollment_students WHERE sex='Male' AND created_by = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $maleCount = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM basic_enrollment_students WHERE sex='Female' AND created_by = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $femaleCount = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    $stmt = $conn->prepare("SELECT grade_level, COUNT(*) AS total FROM basic_enrollment_students WHERE created_by = ? GROUP BY grade_level ORDER BY grade_level ASC");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $gradeLevels = [];
    $gradeCounts = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $gradeLevels[] = $row['grade_level'];
        $gradeCounts[] = (int)$row['total'];
    }
    $stmt->close();
}

if ($isSuperAdmin) {
    $dateQuery = $conn->query("
        SELECT DATE(date_submitted) AS submit_date, COUNT(*) AS total
        FROM basic_enrollment_students
        GROUP BY DATE(date_submitted)
        ORDER BY submit_date ASC
    ");
} else {
    $stmt = $conn->prepare("
        SELECT DATE(date_submitted) AS submit_date, COUNT(*) AS total
        FROM basic_enrollment_students
        WHERE created_by = ?
        GROUP BY DATE(date_submitted)
        ORDER BY submit_date ASC
    ");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $dateQuery = $stmt->get_result();
}
$dateLabels = [];
$dateCounts = [];

while ($row = $dateQuery->fetch_assoc()) {
    $dateLabels[] = $row['submit_date'];
    $dateCounts[] = (int)$row['total'];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" href="../Pics/logoOrg.png" type="image/png">
    <title>Admin Panel</title>
</head>

<style>
  body {
    background-color: #f9fafc;
  }

  .bg-gradient-primary { background: linear-gradient(135deg, #007bff, #5a9fff); }
  .bg-gradient-success { background: linear-gradient(135deg, #28a745, #60d394); }
  .bg-gradient-warning { background: linear-gradient(135deg, #ffc107, #ffda6a); }
  .bg-gradient-info { background: linear-gradient(135deg, #17a2b8, #5bc0de); }
  .bg-gradient-pink { background: linear-gradient(135deg, #d63384, #f78fb3); }

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
</style>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#"><img src="../Pics/logoOrg.png" alt="Logo" width="50" height="50" class="d-inline-block align-text-bot me-1"></i><span class="h2 align-middle ">MANGALDAN CENTRAL SCHOOL</span></a>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            
            <nav class="col-md-3 col-lg-2 d-md-block  p-3" id="sidebar">  
                <h6 class="text-light mt-2">
                <button class="btn btn-primary me-2" id="toggleBtn">☰</button><span class="text-warning" id="spanMenu">ADMIN CONSOLE</span>
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
                        <a class="nav-link  text-light" href="#" id="reportDashboard" data-bs-toggle="tooltip" data-bs-placement="right" title="SETTINGS">
                            <i class="bi bi-file-earmark-text-fill me-2"></i><span>REPORT</span>
                        </a>
                    </li>
                    
                    <button class="btn btn-primary text-center" id="btnLogout" data-bs-toggle="tooltip" data-bs-placement="top" title="LOGOUT" onclick="showLogoutModal()">
                        <i class="bi bi-box-arrow-left me-2"></i><span>LOGOUT</span>
                    </button>
                </ul>
            </nav>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" id="mainContent">
                <section id="dashboardSection" class="content-section mt-4">
                  <div class="d-flex justify-content-between align-items-center mt-4">
                    <h3 class="mb-0 fw-bold text-primary">DASHBOARD</h3>
                    <?php include 'profileHeader.php'; ?>
                  </div>

                  <div class="mt-3 border-bottom border-3 border-primary"></div>

                  <div class="container mt-4">
                    <div class="row justify-content-center text-center g-4">
                      <div class="col-md-4 col-sm-6">
                        <div class="card stat-card bg-gradient-primary text-white shadow-sm h-100">
                          <div class="card-body py-3">
                            <i class="bi bi-people-fill fs-3 mb-2"></i>
                            <h6 class="fw-semibold mb-1">Total New Students</h6>
                            <h3 class="fw-bold mb-0"><?= $newCount ?></h3>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-4 col-sm-6">
                        <div class="card stat-card bg-gradient-info text-white shadow-sm h-100">
                          <div class="card-body py-3">
                            <i class="bi bi-gender-male fs-3 mb-2"></i>
                            <h6 class="fw-semibold mb-1">Male Students</h6>
                            <h3 class="fw-bold mb-0"><?= $maleCount ?></h3>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-4 col-sm-6">
                        <div class="card stat-card bg-gradient-pink text-white shadow-sm h-100">
                          <div class="card-body py-3">
                            <i class="bi bi-gender-female fs-3 mb-2"></i>
                            <h6 class="fw-semibold mb-1">Female Students</h6>
                            <h3 class="fw-bold mb-0"><?= $femaleCount ?></h3>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="container mt-4">
                    <div class="row g-4">
                      <div class="col-lg-8 col-md-7">
                        <div class="card chart-card shadow-sm p-4 h-100">
                          <h5 class="text-primary fw-bold mb-3">
                            <i class="bi bi-graph-up-arrow me-2"></i> Students Enrolled by Date
                          </h5>
                        <div class="chart-container" style="height: 280px; width: 100%;">
                          <canvas id="gradeChart"></canvas>
                        </div>
                        </div>
                      </div>

                      <div class="col-lg-4 col-md-5">
                        <div class="card chart-card shadow-sm p-4 h-100">
                          <h5 class="text-primary fw-bold mb-3">
                            <i class="bi bi-pie-chart-fill me-2"></i> Gender Distribution
                          </h5>
                          <div class="chart-container d-flex justify-content-center align-items-center" style="height: 280px; width: 100%;">
                          <canvas id="genderChart" style="max-width: 200px; max-height: 280px;"></canvas>
                        </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </section>
                <!-- START: Students Section (full updated) -->
                <section id="studentsSection" class="content-section mt-4 d-none">
                    <div class="d-flex justify-content-between align-items-center mt-4">
                      <h3 class="mt-0 mb-0 fw-bold text-primary">STUDENTS</h3>
                      <?php include 'profileHeader.php'; ?>
                    </div>

                    <div class="mt-3 border-bottom border-3 border-primary"></div>

                    <?php
                      $showSuccessModal = isset($_GET['success']) && $_GET['success'] == 1;
                      $showUpdatedModal = isset($_GET['updated']) && $_GET['updated'] == 1;
                    ?>

                    <!-- Status modal for success/updated -->
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

                    <script>
                      document.addEventListener("DOMContentLoaded", function () {
                          document.querySelectorAll('.content-section').forEach(s => s.classList.add('d-none'));
                          const dashboard = document.getElementById('dashboardSection');
                          if (dashboard) dashboard.classList.remove('d-none');

                          try {
                              const url = new URL(window.location.href);
                              url.searchParams.delete('section');  
                              url.searchParams.delete('success');
                              url.searchParams.delete('updated');
                              history.replaceState(null, "", url.pathname + url.search + url.hash);
                          } catch (e) {
                              console.warn("URL cleanup failed", e);
                          }
                      });
                      </script>

                      <script>
                      document.addEventListener("DOMContentLoaded", function () {
                        const showSuccess = <?= $showSuccessModal ? 'true' : 'false' ?>;
                        const showUpdated = <?= $showUpdatedModal ? 'true' : 'false' ?>;
                        const statusModalEl = document.getElementById('statusModal');

                        if (statusModalEl && (showSuccess || showUpdated)) {
                          const modal = new bootstrap.Modal(statusModalEl);
                          const modalLabel = document.getElementById('statusModalLabel');
                          const modalBody = document.getElementById('statusModalBody');
                          const modalHeader = document.getElementById('statusModalHeader');
                          const modalBtn = document.getElementById('statusModalBtn');

                          if (showSuccess) {
                            modalHeader.className = "modal-header bg-primary text-white rounded-top-4";
                            modalLabel.innerHTML = "<i class='bi bi-person-plus-fill me-2'></i> Student Added";
                            modalBody.innerHTML = "🎉 The new student has been successfully added!";
                            modalBtn.className = "btn btn-primary px-4";
                          } else if (showUpdated) {
                            modalHeader.className = "modal-header bg-primary text-white rounded-top-4";
                            modalLabel.innerHTML = "<i class='bi bi-check-circle me-2'></i> Record Updated";
                            modalBody.innerHTML = "✅ The record has been successfully updated!";
                            modalBtn.className = "btn btn-primary px-4";
                          }

                          modal.show();
                        }
                      });
                    </script>

                   <div class="d-flex justify-content-between align-items-center mt-4 student-header">
                    <div class="d-flex align-items-center gap-2">
                        <form action="" method="GET" class="search-form">
                            <input type="text" id="studentSearch" name="search" placeholder="Search student..." class="search-input form-control form-control-sm">
                        </form>

                        <div class="dropdown">
                            <button class="btn btn-sm gender-filter dropdown-toggle" type="button" id="genderFilterBtn " data-bs-toggle="dropdown" aria-expanded="false">
                                Gender
                            </button>
                            <ul class="dropdown-menu gender-dropdown shadow-sm" aria-labelledby="genderFilterBtn">
                                <li><a class="dropdown-item gender-filter-item" href="#" data-gender="">All</a></li>
                                <li><a class="dropdown-item gender-filter-item" href="#" data-gender="Male">Male</a></li>
                                <li><a class="dropdown-item gender-filter-item" href="#" data-gender="Female">Female</a></li>
                            </ul>
                        </div>
                    </div>

                    <a href="formTypeSelection.html" class="add-btn btn btn-primary">Add Student</a>
                  </div>



                    <div class="container-fluid mt-4 p-4 shadow-lg rounded-4 overflow-auto" style="background: rgba(255,255,255,0.25);backdrop-filter: blur(10px);-webkit-backdrop-filter: blur(10px);border: 1px solid rgba(255,255,255,0.3);">
                        <h4 class="fw-bold text-primary mb-3"><i class="bi bi-person-plus-fill me-2"></i> New Students</h4>

                        <div class="card shadow-sm mb-4">
                            <div class="card-body p-3">
                                <div class="table-responsive" style="max-height: 550px; overflow-y: auto; overflow-x: auto;">
                                <table class="table table-bordered table-striped table-hover align-middle table-sm">
                                    <thead class="table-primary text-center">
                                        <tr>
                                            <th style="min-width: 140px;"></th>
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
                                            <th style="min-width: 250px;">Mother Tongue</th>
                                            <th style="min-width: 450px;">Current Address</th>
                                            <th style="min-width: 450px;">Permanent Address</th>
                                            <th style="min-width: 180px;">Father</th>
                                            <th style="min-width: 180px;">Mother’s Maiden</th>
                                            <th style="min-width: 180px;">Guardian</th>
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

                                    <tbody id="newStudentsBody">
                                        <?php
                                            // use existing $isSuperAdmin and $adminId variables from the top of the page
                                            include("../Login/connection.php");

                                            if ($isSuperAdmin) {
                                              $query = "SELECT * FROM basic_enrollment_students ORDER BY date_submitted DESC";
                                            } else {
                                              // ensure $adminId is integer
                                              $adminId = (int)$adminId;
                                              $query = "SELECT * FROM basic_enrollment_students WHERE created_by = $adminId ORDER BY date_submitted DESC";
                                            }
                                            $result = $conn->query($query);

                                            if ($result && $result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    // break out of PHP to avoid quote issues
                                                    ?>
                                                    <tr>
                                                    <td class="text-center">
                                                        <a href="editStudent.php?type=new&id=<?php echo $row['student_id']; ?>&from=adminPanel" class="btn btn-warning btn-sm me-1" title="Edit Student">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>

                                                       <!-- Notes button -->
                                                        <?php
                                                        $student_id = $row['student_id'];
                                                        $noteCheck = $conn->query("SELECT id FROM notes WHERE student_id = '$student_id' LIMIT 1");
                                                        $hasNote = $noteCheck->num_rows > 0;
                                                        $btnClass = $hasNote ? "btn-success" : "btn-info";
                                                        ?>
                                                        <button class="btn btn-sm <?php echo $btnClass; ?> text-white me-1 addNotesBtn"
                                                                data-id="<?php echo $student_id; ?>"
                                                                title="Add Notes">
                                                            <i class="bi bi-journal-text"></i>
                                                        </button>

                                                        <!-- Transfer button -->
                                                        <button type="button"
                                                            class="btn btn-secondary btn-sm transferBtn"
                                                            data-id="<?php echo $row['student_id']; ?>"
                                                            data-last="<?php echo htmlspecialchars($row['last_name'], ENT_QUOTES); ?>"
                                                            data-first="<?php echo htmlspecialchars($row['first_name'], ENT_QUOTES); ?>"
                                                            data-middle="<?php echo htmlspecialchars($row['middle_name'], ENT_QUOTES); ?>"
                                                            title="Transfer Record">
                                                            <i class="bi bi-arrow-left-right"></i>
                                                        </button>
                                                    </td>

                                                    <td><?php echo $row['student_id']; ?></td>
                                                    <td><?php echo htmlspecialchars($row['school_year']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['lrn']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['grade_level']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['psa_birth_cert_no']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['middle_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['birthdate']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['age']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['sex']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['place_of_birth']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['religion']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['mother_tongue']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['current_address']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['permanent_address']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['father_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['mother_maiden_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['guardian_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['last_grade_completed']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['last_school_attended']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['last_school_id']); ?></td>
                                                    <td><?php echo $row['blended'] ? '✔️' : ''; ?></td>
                                                    <td><?php echo $row['homeschooling'] ? '✔️' : ''; ?></td>
                                                    <td><?php echo $row['modular_print'] ? '✔️' : ''; ?></td>
                                                    <td><?php echo $row['modular_digital'] ? '✔️' : ''; ?></td>
                                                    <td><?php echo $row['online'] ? '✔️' : ''; ?></td>
                                                    <td><?php echo htmlspecialchars($row['date_submitted']); ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                echo "<tr><td colspan='30' class='text-center'>No New Students Found</td></tr>";
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Old Students list (kept intact) -->
                    <h4 class="fw-bold text-success mt-5 mb-3">
                    <i class="bi bi-person-check-fill me-2"></i> Old Students</h4>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body p-3">
                            <div class="table-responsive" style="max-height: 550px; overflow-y: auto; overflow-x: auto;">
                                <table class="table table-bordered table-striped table-hover align-middle table-sm">
                                    <thead class="table-success text-center">
                                        <tr>
                                            <th style="width: 90px;"></th>
                                            <th>ID</th>
                                            <th>Learner Name</th>
                                            <th>Grade Level</th>
                                            <th>LRN</th>
                                            <th>Confirmation</th>
                                            <th>Date Submitted</th>
                                        </tr>
                                    </thead>

                                    <tbody id="oldStudentsBody">
                                        <?php
                                          if ($isSuperAdmin) {
                                            $query2 = "SELECT * FROM confirmation_slip_students ORDER BY date_submitted DESC";
                                          } else {
                                            $adminId = (int)$adminId;
                                            $query2 = "SELECT * FROM confirmation_slip_students WHERE created_by = $adminId ORDER BY date_submitted DESC";
                                          }
                                          $result2 = $conn->query($query2);

                                            if ($result2 && $result2->num_rows > 0) {
                                                while ($row = $result2->fetch_assoc()) {
                                                    echo "<tr>
                                                    <td class='text-center'>
                                                        <a href='editStudent.php?type=old&id={$row['slip_id']}&from=adminPanel' class='btn btn-warning btn-sm me-0' title='Edit Student'>
                                                        <i class='bi bi-pencil-square'></i>
                                                        </a>
                                                        <a href='addNotes.php?id={$row['slip_id']}' class='btn btn-info btn-sm'>
                                                            <i class='bi bi-journal-text text-white'></i>
                                                        </a>
                                                    </td>
                                                    <td>{$row['slip_id']}</td>
                                                    <td>{$row['learner_name']}</td>
                                                    <td>{$row['grade_level']}</td>
                                                    <td>{$row['lrn']}</td>
                                                    <td>{$row['confirmation_status']}</td>
                                                    <td>{$row['date_submitted']}</td>
                                                    </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center'><div class='alert alert-info mb-0'><i class='bi bi-info-circle me-2'></i>No Old Students Found</div></td></tr>";
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                  

                    <!-- -------------
                        Transfer JS
                        ------------- -->
                    <script>
document.addEventListener('DOMContentLoaded', () => {
  const FETCH_TIMEOUT = 8000;
  const POLL_INTERVAL = 5000;

  // safe modal getter using getOrCreateInstance (prevents duplicate modal instances)
  function getModalInstance(id) {
    const el = document.getElementById(id);
    if (!el) return null;
    if (typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
    try {
      // use getOrCreateInstance if available (Bootstrap 5+)
      if (bootstrap.Modal.getOrCreateInstance) return bootstrap.Modal.getOrCreateInstance(el);
      return new bootstrap.Modal(el);
    } catch (err) {
      console.error('Modal init failed for #' + id, err);
      return null;
    }
  }

  // ensure modals are single instances
  const selectReceiverModal = getModalInstance('selectReceiverModal');
  const confirmTransferModal = getModalInstance('confirmTransferModal');
  const incomingModal = getModalInstance('incomingTransferModal');
  const senderResultModal = getModalInstance('senderResultModal');

  const adminsListEl = document.getElementById('adminsList');
  const confirmBtn = document.getElementById('confirmTransferBtn');
  const incomingBody = document.getElementById('incomingTransferBody');
  const senderTitle = document.getElementById('senderResultTitle');
  const senderBody = document.getElementById('senderResultBody');

  let selectedStudentId = null;
  let selectedReceiver = null;
  let isLoadingAdmins = false;
  let pendingTransfer = false;

  async function fetchJson(url, options = {}, timeout = FETCH_TIMEOUT) {
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), timeout);
    options.signal = controller.signal;
    try {
      const res = await fetch(url, options);
      clearTimeout(id);
      const text = await res.text();
      try { return { ok: res.ok, status: res.status, json: JSON.parse(text), raw: text }; }
      catch (parseErr) { return { ok: res.ok, status: res.status, json: null, raw: text }; }
    } catch (err) {
      clearTimeout(id);
      return { ok: false, status: 0, json: null, raw: null, error: err };
    }
  }

  function setTransferButtonsDisabled(state) {
    document.querySelectorAll('.transferBtn').forEach(b => { b.disabled = state; });
  }

  function cleanupStuckModal() {
    // hide all programmatic modals and remove backdrops if any stuck
    try {
      [selectReceiverModal, confirmTransferModal, incomingModal, senderResultModal].forEach(m => {
        try { m?.hide(); } catch(e) {}
      });
    } catch(e){}
    document.querySelectorAll('.modal-backdrop').forEach(n => n.remove());
    document.body.classList.remove('modal-open');
  }

  async function loadAdminsForTransfer() {
    if (isLoadingAdmins) return;
    isLoadingAdmins = true;
    if (adminsListEl) adminsListEl.innerHTML = '<div class="text-muted">Loading admins…</div>';

    let res;
    try {
      res = await fetchJson('get_admins.php', { method: 'GET' });
    } catch (err) {
      console.error('loadAdminsForTransfer fetch failed', err);
      res = { ok: false, json: null, raw: null, error: err };
    } finally {
      isLoadingAdmins = false;
    }

    if (!res.ok || !res.json || !Array.isArray(res.json.admins)) {
      if (adminsListEl) adminsListEl.innerHTML = '<div class="text-danger">Failed to load admins. Check console/Network.</div>';
      try { if (selectReceiverModal) selectReceiverModal.show(); } catch(e){ console.error(e); cleanupStuckModal(); }
      return;
    }

    const admins = res.json.admins;
    if (!admins.length) {
      if (adminsListEl) adminsListEl.innerHTML = '<div class="text-warning">No admins available</div>';
      try { if (selectReceiverModal) selectReceiverModal.show(); } catch(e){ console.error(e); cleanupStuckModal(); }
      return;
    }

    // populate list
    if (adminsListEl) adminsListEl.innerHTML = '';
    admins.forEach(a => {
      const el = document.createElement('button');
      el.type = 'button';
      el.className = 'list-group-item list-group-item-action';
      el.textContent = a.username + (a.email ? ' — ' + a.email : '');
      el.dataset.id = a.id;
      el.dataset.username = a.username;
      el.onclick = () => {
        selectedReceiver = el.dataset.id;
        const ct = document.getElementById('confirmTransferText');
        if (ct) ct.innerHTML = `Are you sure you want to transfer student <strong>#${selectedStudentId}</strong> to <strong>${el.dataset.username}</strong>?`;
        try {
          if (selectReceiverModal) selectReceiverModal.hide();
          // small delay to avoid bootstrap collision
          setTimeout(() => { try { if (confirmTransferModal) confirmTransferModal.show(); } catch(e){ console.error(e); cleanupStuckModal(); } }, 200);
        } catch (err) {
          console.error('Error showing confirm modal', err);
          cleanupStuckModal();
        }
      };
      adminsListEl.appendChild(el);
    });

    try { if (selectReceiverModal) selectReceiverModal.show(); } catch(e){ console.error(e); cleanupStuckModal(); }
  }

  if (confirmBtn) {
    confirmBtn.addEventListener('click', async () => {
      if (pendingTransfer) return;
      if (!selectedStudentId || !selectedReceiver) return;
      pendingTransfer = true;
      confirmBtn.disabled = true;
      setTransferButtonsDisabled(true);

      try {
        const body = `student_id=${encodeURIComponent(selectedStudentId)}&to_admin=${encodeURIComponent(selectedReceiver)}`;
        const res = await fetchJson('transfer_student.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body
        });

        if (res.ok && res.json && res.json.success) {
          try { if (confirmTransferModal) confirmTransferModal.hide(); } catch(e){ console.warn(e); cleanupStuckModal(); }
          if (senderTitle) senderTitle.textContent = 'Transfer Sent';
          if (senderBody) senderBody.innerHTML = res.json.message || 'Transfer request has been sent.';
          try { if (senderResultModal) senderResultModal.show(); } catch(e){ console.error(e); cleanupStuckModal(); }
          const row = document.querySelector(`.transferBtn[data-id='${selectedStudentId}']`)?.closest('tr');
          if (row) row.classList.add('table-warning');
        } else {
          const serverMsg = res.json?.message || res.json?.error || res.raw || 'Unknown error';
          alert('Transfer failed: ' + serverMsg + '\n(Check console Network/Response for details)');
          console.error('transfer_student.php returned:', res);
        }
      } catch (err) {
        console.error('confirm transfer error', err);
        cleanupStuckModal();
      } finally {
        pendingTransfer = false;
        confirmBtn.disabled = false;
        setTransferButtonsDisabled(false);
      }
    });
  }

  // delegate transfer buttons
  document.body.addEventListener('click', (e) => {
    const el = (e.target && e.target.closest) ? e.target.closest('.transferBtn') : null;
    if (!el) return;
    const id = el.dataset.id;
    if (!id) { console.warn('transferBtn without data-id'); return; }
    selectedStudentId = id;
    selectedReceiver = null;
    setTransferButtonsDisabled(true);
    // ensure loadAdminsForTransfer errors won't leave buttons disabled
    loadAdminsForTransfer().finally(() => { if (!pendingTransfer) setTransferButtonsDisabled(false); });
  });

  // poll function left unchanged but wrapped to be defensive
  async function poll() {
    try {
      const res = await fetchJson('poll_transfers.php');
      if (!res.ok || !res.json) { return; }
      const data = res.json;
      if (Array.isArray(data.incoming) && data.incoming.length > 0) {
        const item = data.incoming[0];
        if (incomingBody) {
          incomingBody.innerHTML = `
            <p><strong>${escapeHtml(item.from_username || 'Unknown')}</strong> wants to transfer:</p>
            <ul>
              <li><strong>Student ID:</strong> ${escapeHtml(item.student_id || '')}</li>
              <li><strong>Name:</strong> ${escapeHtml(item.last_name || '')}, ${escapeHtml(item.first_name || '')} ${escapeHtml(item.middle_name || '')}</li>
              <li><strong>Date Submitted:</strong> ${escapeHtml(item.date_submitted || '')}</li>
            </ul>
            <div class="d-flex justify-content-end gap-2">
              <button class="btn btn-secondary" id="declineBtn">Decline</button>
              <button class="btn btn-primary" id="acceptBtn">Accept</button>
            </div>`;
        }
        try { if (incomingModal) incomingModal.show(); } catch(e){ console.error(e); cleanupStuckModal(); }
        setTimeout(() => {
          const acc = document.getElementById('acceptBtn');
          const dec = document.getElementById('declineBtn');
          if (acc) acc.onclick = async () => {
            const r = await fetchJson('accept_transfer.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body:`transfer_id=${encodeURIComponent(item.id)}` });
            if (r.ok && r.json && r.json.success) {
              try { incomingModal?.hide(); } catch(e){ console.warn(e); cleanupStuckModal(); }
              senderTitle && (senderTitle.textContent = 'Transfer Accepted');
              senderBody && (senderBody.innerHTML = `You accepted student #${escapeHtml(item.student_id)}`);
              try { senderResultModal?.show(); } catch(e){ cleanupStuckModal(); }
            } else { alert('Accept failed. See console/network for details.'); console.error('accept_transfer response', r); }
          };
          if (dec) dec.onclick = async () => {
            const r = await fetchJson('decline_transfer.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body:`transfer_id=${encodeURIComponent(item.id)}` });
            if (r.ok && r.json && r.json.success) {
              try { incomingModal?.hide(); } catch(e){ console.warn(e); cleanupStuckModal(); }
              senderTitle && (senderTitle.textContent = 'Transfer Declined');
              senderBody && (senderBody.innerHTML = `You declined student #${escapeHtml(item.student_id)}`);
              try { senderResultModal?.show(); } catch(e){ cleanupStuckModal(); }
            } else { alert('Decline failed. See console/network for details.'); console.error('decline_transfer response', r); }
          };
        }, 100);
      }

      if (Array.isArray(data.updates) && data.updates.length > 0) {
        data.updates.forEach(u => {
          const title = (u.status === 'accepted') ? 'Transfer Accepted' : 'Transfer Declined';
          const msg = (u.status === 'accepted')
            ? `Your transfer request (student #${u.student_id}) has been accepted by ${u.to_username}.`
            : `Your transfer request (student #${u.student_id}) has been declined by ${u.to_username}.`;
          senderTitle && (senderTitle.textContent = title);
          senderBody && (senderBody.innerHTML = msg);
          try { senderResultModal?.show(); } catch(e){ cleanupStuckModal(); }
          fetch('mark_seen_sender.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`transfer_id=${encodeURIComponent(u.id)}` }).catch(()=>{});
        });
      }
    } catch (err) {
      console.error('poll failed', err);
    }
  }

  function escapeHtml(str) {
    if (!str && str !== 0) return '';
    return String(str).replace(/[&<>"'`=\/]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'})[s]);
  }

  // start polling
  poll();
  setInterval(poll, POLL_INTERVAL);
});
</script>




                </section>
                <!-- END: Students Section -->


                <section id="reportSection" class="content-section mt-4 d-none">
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <h3 class="mt-0 mb-0 fw-bold text-primary">REPORT</h3>
                        <?php include 'profileHeader.php'; ?>
                    </div>
                    <div class="mt-4 border-bottom border-3 border-primary"></div>

                    <div class="row text-center g-3 mt-4">
                  
                      <div class="card mt-4 shadow-sm p-3">
                        <h5 class="fw-bold text-primary"><i class="bi bi-table me-2"></i>Enrollment Summary</h5>
                        <div class="table-responsive">
                          <table class="table table-bordered table-sm table-striped">
                            <thead class="table-primary text-center">
                              <tr>
                                <th>Grade</th>
                                <th>Total Students</th>
                                <th>Male</th>
                                <th>Female</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php
                                if ($isSuperAdmin) {
                                  $reportQuery = "SELECT grade_level, COUNT(*) AS total, SUM(sex='Male') AS male, SUM(sex='Female') AS female FROM basic_enrollment_students GROUP BY grade_level";
                                } 
                                else {
                                  $reportQuery = "SELECT grade_level, COUNT(*) AS total, SUM(sex='Male') AS male, SUM(sex='Female') AS female FROM basic_enrollment_students WHERE created_by = $adminId GROUP BY grade_level";
                                }
                                $report = $conn->query($reportQuery);

                                while ($r = $report->fetch_assoc()) {
                                  echo "<tr>
                                          <td>{$r['grade_level']}</td>
                                          <td>{$r['total']}</td>
                                          <td>{$r['male']}</td>
                                          <td>{$r['female']}</td>
                                        </tr>";
                                }
                              ?>
                            </tbody>
                          </table>
                        </div>
                      </div>

                      <div class="mt-4 text-end">
                        <a href="export_excel.php" class="btn btn-success"><i class="bi bi-file-earmark-excel-fill me-2"></i>Export Excel</a>
                        <a href="export_pdf.php" class="btn btn-danger"><i class="bi bi-file-earmark-pdf-fill me-2"></i>Export PDF</a>
                      </div>
                    </div>
                    
                </section>
            </main>
        </div>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header bg-primary text-white rounded-top-4">
              <h5 class="modal-title">Confirm Logout</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center p-4">
              <p class="fs-5">Are you sure you want to logout?</p>
          </div>
          <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">No</button>
              <button type="button" class="btn btn-primary px-4" onclick="logout()">Yes</button>
          </div>
          </div>
      </div>
    </div>
    
<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="notesForm">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Add Note</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <textarea class="form-control" id="note_text" name="note_text" rows="4"
                    placeholder="Write note here..." required></textarea>
          <input type="hidden" name="student_id" id="student_id">
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" id="saveNoteBtn">
            <i class="bi bi-save me-1"></i> Save Note
          </button>
          <button type="button" class="btn btn-danger" id="removeNoteBtn">
            <i class="bi bi-trash me-1"></i> Remove Note
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Message Modal (For confirmations & success messages) -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" id="messageModalHeader">
        <h5 class="modal-title" id="messageModalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="messageModalBody" style="font-size: 1.1rem;"></div>
      <div class="modal-footer" id="messageModalFooter"></div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    let currentStudentId = null;

    const notesModalEl = document.getElementById("notesModal");
    const messageModalEl = document.getElementById("messageModal");
    const notesModal = new bootstrap.Modal(notesModalEl);
    const messageModal = new bootstrap.Modal(messageModalEl);

    const noteText = document.getElementById("note_text");
    const studentIdInput = document.getElementById("student_id");
    const messageModalHeader = document.getElementById("messageModalHeader");

    /* ===============================
       OPEN NOTES MODAL (DELEGATION)
    =============================== */
    document.getElementById("newStudentsBody").addEventListener("click", function (e) {
        const btn = e.target.closest(".addNotesBtn");
        if (!btn) return;

        currentStudentId = btn.dataset.id;
        studentIdInput.value = currentStudentId;

        fetch("fetch_note.php", {
            method: "POST",
            body: new URLSearchParams({ student_id: currentStudentId })
        })
        .then(res => res.json())
        .then(data => {
            noteText.value = data.note || "";
            notesModal.show();
        });
    });

    /* ===============================
       SAVE NOTE
    =============================== */
    document.getElementById("notesForm").addEventListener("submit", function(e) {
        e.preventDefault();

        fetch("save_note.php", {
            method: "POST",
            body: new FormData(this)
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message);
                return;
            }

            const btn = document.querySelector(`.addNotesBtn[data-id='${currentStudentId}']`);
            if (btn) {
                btn.classList.remove("btn-info");
                btn.classList.add("btn-success");
            }

            messageModalHeader.className = "modal-header bg-primary text-white";
            document.getElementById("messageModalTitle").innerText = "Success";
            document.getElementById("messageModalBody").innerText = "Note saved successfully!";
            document.getElementById("messageModalFooter").innerHTML =
                `<button class="btn btn-light" data-bs-dismiss="modal">OK</button>`;

            notesModal.hide();
            messageModal.show();
        });
    });

    /* ===============================
       REMOVE NOTE
    =============================== */
    document.getElementById("removeNoteBtn").addEventListener("click", () => {
        if (!currentStudentId) return;

        messageModalHeader.className = "modal-header bg-warning text-dark";
        document.getElementById("messageModalTitle").innerText = "Confirm Remove";
        document.getElementById("messageModalBody").innerText =
            "Are you sure you want to remove this note?";
        document.getElementById("messageModalFooter").innerHTML = `
            <button class="btn btn-secondary" data-bs-dismiss="modal">No</button>
            <button class="btn btn-danger" id="confirmRemoveBtn">Yes</button>
        `;

        messageModal.show();

        document.getElementById("confirmRemoveBtn").onclick = () => {
            fetch("remove_note.php", {
                method: "POST",
                body: new URLSearchParams({ student_id: currentStudentId })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message);
                    return;
                }

                const btn = document.querySelector(`.addNotesBtn[data-id='${currentStudentId}']`);
                if (btn) {
                    btn.classList.remove("btn-success", "btn-danger");
                    btn.classList.add("btn-info");
                }

                noteText.value = "";
                notesModal.hide();

                messageModalHeader.className = "modal-header bg-primary text-white";
                document.getElementById("messageModalTitle").innerText = "Deleted";
                document.getElementById("messageModalBody").innerText =
                    "Note successfully deleted!";
                document.getElementById("messageModalFooter").innerHTML =
                    `<button class="btn btn-light" data-bs-dismiss="modal">OK</button>`;

                messageModal.show();
            });
        };
    });

});
</script>




                            
    <div class="modal fade" id="selectReceiverModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Select Admin</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div id="adminsList" class="list-group"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="confirmTransferModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-warning">
            <h5 class="modal-title">Confirm Transfer</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p id="confirmTransferText">Processing...</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button id="confirmTransferBtn" class="btn btn-primary">Confirm</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="incomingTransferModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">Incoming Transfer</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="incomingTransferBody">
            Loading...
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="senderResultModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 id="senderResultTitle" class="modal-title">Result</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="senderResultBody">Done</div>
        </div>
      </div>
    </div>

</body>
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                  borderColor: "rgba(13, 110, 253, 1)",
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
                  color: "#0d6efd",
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
            "rgba(13, 110, 253, 0.85)",
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
          color: "#0d6efd",
        },
      },
    },
  });
});
</script>



<script src="admin.js"></script>

<script>
function showLogoutModal() {
  const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
  modal.show();
}

function logout() {
  window.location.href = "../Login/adminLogout.php";
}
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('studentSearch');
    const genderDropdownItems = document.querySelectorAll('.gender-filter-item');
    const newBody = document.querySelector("#newStudentsBody");
    const oldBody = document.querySelector("#oldStudentsBody");

    let selectedGender = '';

    function fetchStudents() {
        const query = searchInput.value.trim();
        const params = new URLSearchParams({ q: query, gender: selectedGender });

        fetch("searchStudents.php?" + params.toString())
            .then(res => res.json())
            .then(data => {
                newBody.innerHTML = data.new;
                oldBody.innerHTML = data.old;
            });
    }

    searchInput.addEventListener("input", fetchStudents);

    genderDropdownItems.forEach(item => {
        item.addEventListener("click", function(e) {
            e.preventDefault();
            selectedGender = this.dataset.gender;
            fetchStudents();
        });
    });
});

</script>

<script>
  window.addEventListener("beforeunload", function (e) {
    navigator.sendBeacon("../Login/setOffline.php");
});

setInterval(() => {
  fetch('../superAdmin/update_admin_status.php');
}, 5000);
</script>
<script>
window.addEventListener("beforeunload", function () {
  navigator.sendBeacon("../superAdmin/setOffline.php");
});
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
</html>