document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggleBtn');
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('mainContent');
    const navLinks = document.querySelectorAll('#sidebar .nav-link');
    const sections = document.querySelectorAll('.content-section');
    let tooltipInstances = [];

    function enableTooltips() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(el => {
            const instance = new bootstrap.Tooltip(el);
            tooltipInstances.push(instance);
        });
    }

    function disableTooltips() {
        tooltipInstances.forEach(instance => instance.dispose());
        tooltipInstances = [];
    }

    toggleBtn.addEventListener('click', function () {
        const isCollapsed = sidebar.classList.toggle('collapsed');
        main.classList.toggle('expanded', isCollapsed);

        if (isCollapsed) {
            enableTooltips();
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            disableTooltips();
            localStorage.setItem('sidebarCollapsed', 'false');
        }
    });

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            sections.forEach(section => section.classList.add('d-none'));

            let targetId = '';
            if (this.id === 'mainDashboard') targetId = 'dashboardSection';
            else if (this.id === 'studentsDashboard') targetId = 'studentsSection';
            else if (this.id === 'reportDashboard') targetId = 'reportSection';
            else if (this.id === 'settingsDashboard') targetId = 'settingsSection';

            if (targetId) {
                document.getElementById(targetId).classList.remove('d-none');
            }
        });
    });

    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true') {
        sidebar.classList.add('collapsed');
        main.classList.add('expanded');
        enableTooltips();
    } else {
        disableTooltips();
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const params = new URLSearchParams(window.location.search);
    if (params.get("show") === "students") {

        const dashboard = document.getElementById("dashboardSection");
        const students = document.getElementById("studentsSection");

        if (dashboard && students) {
            dashboard.classList.add("d-none");
            students.classList.remove("d-none");
        }

   
        const studentLink = document.getElementById("studentsDashboard");
        if (studentLink) studentLink.classList.add("active");
    }
});
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('success') === '1') {
      const firstRow = document.querySelector('#studentsSection table tbody tr');
      if (firstRow) {
        firstRow.classList.add('table-warning');
        setTimeout(() => firstRow.classList.remove('table-warning'), 2500);
      }
    }
});