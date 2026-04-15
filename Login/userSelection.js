document.getElementById('userTypeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const type = document.getElementById('userType').value;
    
    if (type === "superAdmin") {
        window.location.href = "superAdminLogin.php"; 
    } 
    else if (type === "admin") {
        window.location.href = "adminLogin.php";
    }
    else {
        alert("Please select a valid role");
    }
});


