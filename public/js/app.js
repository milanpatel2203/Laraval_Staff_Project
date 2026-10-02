// UEST HRMS - Minimal JS

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');

    // Sidebar toggle
    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            if (window.innerWidth <= 1024) {
                sidebar.classList.toggle('mobile-open');
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });
    }

    // Close mobile sidebar on outside click
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 1024 && sidebar && sidebar.classList.contains('mobile-open')) {
            if (!sidebar.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
            }
        }
    });
});
