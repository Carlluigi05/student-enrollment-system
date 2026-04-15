<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="login.css">
  <link rel="icon" href="../Pics/logoOrg.png" type="image/png">
  <title>Admin Login</title>
</head>
<body class="d-flex justify-content-center align-items-center" id="background">

  <div class="card shadow p-4 rounded-3" style="min-width: 350px;" id="glass">
    <h3 class="text-center mb-3 fw-bold">Admin Login</h3>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger text-center">
        <?php 
          echo $_SESSION['error']; 
          unset($_SESSION['error']); 
        ?>
      </div>
    <?php endif; ?>

    <form action="ValidateLogin.php" method="POST">
      <input type="hidden" name="role" value="admin">

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" name="email" required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required>
      </div>

      <button type="submit" class="btn btn-primary w-100" id="btn">Login</button>
      <a href="userSelection.html" class="btn btn-secondary w-100" id="backbtn" style="margin-top:15px; color:white;">BACK</button></a>
      
    </form>
  </div>

</body>
</html>
